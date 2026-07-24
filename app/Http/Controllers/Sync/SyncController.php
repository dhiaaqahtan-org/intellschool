<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Sync Controller — bidirectional offline sync for the Flutter client.
 *
 * Pull: returns changes since a per-entity cursor, scoped by the user's
 *       spatie permissions + team.
 * Push: applies a batch of offline mutations, enforcing authorization
 *       per entity via spatie permissions.
 *
 * Data classes (per flutter-implementation-plan.md §10.2):
 * - Reference: config, academic, timetable, student/staff directory → pull-only
 * - Field: attendance, exam marks, homework, discipline → full offline CRUD
 * - Money/admin: finance, payroll, approvals, users/roles → ONLINE ONLY
 */
class SyncController extends Controller
{
    /**
     * Entity → table + required permission mapping.
     * Only Reference and Field class entities are listed here.
     * Money/admin entities are never synced.
     */
    protected const SYNCABLE_ENTITIES = [
        // Reference class (pull-only cache)
        'config' => ['table' => null, 'class' => 'reference', 'permission' => null],
        'academic_batches' => ['table' => 'batches', 'class' => 'reference', 'permission' => 'batch:read'],
        'academic_subjects' => ['table' => 'subjects', 'class' => 'reference', 'permission' => 'subject:read'],
        'academic_courses' => ['table' => 'courses', 'class' => 'reference', 'permission' => 'course:read'],
        'academic_divisions' => ['table' => 'divisions', 'class' => 'reference', 'permission' => 'division:read'],
        'timetable' => ['table' => 'timetables', 'class' => 'reference', 'permission' => 'timetable:read'],
        'student_directory' => ['table' => 'students', 'class' => 'reference', 'permission' => 'student:read'],
        'staff_directory' => ['table' => 'employees', 'class' => 'reference', 'permission' => 'employee:read'],
        'communication' => ['table' => 'announcements', 'class' => 'reference', 'permission' => 'announcement:read'],
        'calendar_events' => ['table' => 'events', 'class' => 'reference', 'permission' => 'event:read'],
        'calendar_holidays' => ['table' => 'holidays', 'class' => 'reference', 'permission' => 'holiday:read'],

        // Field class (full offline CRUD)
        'attendance' => ['table' => 'attendances', 'class' => 'field', 'permission' => 'student:mark-attendance'],
        'exam_marks' => ['table' => 'exam_marks', 'class' => 'field', 'permission' => 'exam:marks-record'],
        'homework' => ['table' => 'assignments', 'class' => 'field', 'permission' => 'assignment:create'],
        'discipline_notes' => ['table' => 'incidents', 'class' => 'field', 'permission' => 'incident:manage'],
    ];

    /**
     * POST /api/v1/app/sync/pull
     *
     * Request:  { "cursors": { "<entity>": "<iso8601|token>" }, "entities": ["attendance", ...] }
     * Response: { "changes": { "<entity>": [records] }, "deletions": { "<entity>": [uuids] }, "cursors": { "<entity>": "<new>" } }
     */
    public function pull(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cursors' => 'array',
            'cursors.*' => 'string',
            'entities' => 'array',
            'entities.*' => 'string',
        ]);

        $requestedEntities = $validated['entities'] ?? array_keys(self::SYNCABLE_ENTITIES);
        $cursors = $validated['cursors'] ?? [];

        $user = $request->user();
        $teamId = $user->current_team_id;

        $changes = [];
        $deletions = [];
        $newCursors = [];

        foreach ($requestedEntities as $entity) {
            $config = self::SYNCABLE_ENTITIES[$entity] ?? null;
            if (!$config) {
                continue; // Unknown entity — skip
            }

            // Money/admin entities are never synced
            if ($config['class'] === 'money_admin') {
                continue;
            }

            // Check permission — skip if the user lacks it
            $permission = $config['permission'];
            if ($permission && !$user->can($permission)) {
                continue;
            }

            $table = $config['table'];
            if (!$table || !Schema::hasTable($table)) {
                continue;
            }

            $cursor = $cursors[$entity] ?? null;

            // Build query — scope by team
            $query = DB::table($table);

            // Scope by team_id if the column exists
            if (Schema::hasColumn($table, 'team_id')) {
                $query->where('team_id', $teamId);
            }

            // Only include rows with uuid
            if (Schema::hasColumn($table, 'uuid')) {
                $query->whereNotNull('uuid');
            }

            // Filter by cursor (updated_at)
            if ($cursor && Schema::hasColumn($table, 'updated_at')) {
                $query->where('updated_at', '>', $cursor);
            }

            // Fetch changes (limit batch size)
            $records = $query->orderBy('updated_at', 'asc')->limit(500)->get();

            if ($records->isNotEmpty()) {
                $changes[$entity] = $records->toArray();
                $newCursors[$entity] = $records->last()->updated_at
                    ?? now()->toIso8601String();
            }

            // Fetch deletions (tombstones)
            if (Schema::hasColumn($table, 'deleted_at')) {
                $deletedQuery = DB::table($table)
                    ->whereNotNull('deleted_at');

                if (Schema::hasColumn($table, 'team_id')) {
                    $deletedQuery->where('team_id', $teamId);
                }

                if ($cursor) {
                    $deletedQuery->where('deleted_at', '>', $cursor);
                }

                $deletionRecords = $deletedQuery->limit(500)
                    ->pluck('uuid')
                    ->filter()
                    ->values()
                    ->toArray();

                if (!empty($deletionRecords)) {
                    $deletions[$entity] = $deletionRecords;
                }
            }
        }

        // Log sync activity to sync_cursors table
        $this->logSyncActivity($user->id, $requestedEntities, $newCursors);

        return response()->json([
            'changes' => $changes,
            'deletions' => $deletions,
            'cursors' => $newCursors,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * POST /api/v1/app/sync/push
     *
     * Request:  { "mutations": [ { "uuid", "entity", "op", "payload", "updatedAt" } ] }
     * Response: { "results": [ { "uuid", "status": "applied|conflict|rejected", "server": {record} } ] }
     */
    public function push(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mutations' => 'required|array',
            'mutations.*.uuid' => 'required|string',
            'mutations.*.entity' => 'required|string',
            'mutations.*.op' => 'required|in:create,update,delete',
            'mutations.*.payload' => 'required|array',
            'mutations.*.updatedAt' => 'required|string',
        ]);

        $user = $request->user();
        $results = [];

        foreach ($validated['mutations'] as $mutation) {
            $entity = $mutation['entity'];
            $config = self::SYNCABLE_ENTITIES[$entity] ?? null;

            // Unknown entity → reject
            if (!$config) {
                $results[] = [
                    'uuid' => $mutation['uuid'],
                    'status' => 'rejected',
                    'reason' => 'Unknown entity: ' . $entity,
                ];
                continue;
            }

            // Money/admin → reject (online-only)
            if ($config['class'] === 'money_admin') {
                $results[] = [
                    'uuid' => $mutation['uuid'],
                    'status' => 'rejected',
                    'reason' => 'This entity is online-only and cannot be modified offline',
                ];
                continue;
            }

            // Only Field class entities can be pushed
            if ($config['class'] !== 'field') {
                $results[] = [
                    'uuid' => $mutation['uuid'],
                    'status' => 'rejected',
                    'reason' => 'This entity is read-only (reference class)',
                ];
                continue;
            }

            // Check permission per mutation
            $permission = $config['permission'];
            if ($permission && !$user->can($permission)) {
                $results[] = [
                    'uuid' => $mutation['uuid'],
                    'status' => 'rejected',
                    'reason' => 'Insufficient permissions for entity: ' . $entity,
                ];
                continue;
            }

            $table = $config['table'];
            $uuid = $mutation['uuid'];
            $op = $mutation['op'];
            $payload = $mutation['payload'];
            $clientUpdatedAt = $mutation['updatedAt'];

            try {
                $serverRecord = $this->applyMutation(
                    $table, $uuid, $op, $payload, $clientUpdatedAt, $user
                );

                $results[] = [
                    'uuid' => $uuid,
                    'status' => 'applied',
                    'server' => $serverRecord,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'uuid' => $uuid,
                    'status' => 'rejected',
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'results' => $results,
            'server_time' => now()->toIso8601String(),
        ]);
    }

    /**
     * Apply a single mutation to the database.
     * Uses last-write-wins conflict resolution for Field class entities.
     */
    protected function applyMutation(
        string $table,
        string $uuid,
        string $op,
        array $payload,
        string $clientUpdatedAt,
        $user
    ): ?array {
        if (!Schema::hasTable($table)) {
            throw new \Exception("Table {$table} does not exist");
        }

        $existing = DB::table($table)->where('uuid', $uuid)->first();

        switch ($op) {
            case 'create':
                if ($existing) {
                    // Conflict: record already exists — last-write-wins
                    if (isset($existing->updated_at) &&
                        strtotime($existing->updated_at) > strtotime($clientUpdatedAt)) {
                        // Server is newer — return server version (conflict)
                        return (array) $existing;
                    }
                    // Client is newer — update
                    DB::table($table)->where('uuid', $uuid)->update(
                        array_merge($payload, ['updated_at' => now()])
                    );
                    return (array) DB::table($table)->where('uuid', $uuid)->first();
                }
                // Create new
                $payload['uuid'] = $uuid;
                if (Schema::hasColumn($table, 'team_id')) {
                    $payload['team_id'] = $user->current_team_id;
                }
                $payload['created_at'] = $payload['created_at'] ?? now();
                $payload['updated_at'] = now();
                DB::table($table)->insert($payload);
                return (array) DB::table($table)->where('uuid', $uuid)->first();

            case 'update':
                if (!$existing) {
                    throw new \Exception('Record not found for update');
                }
                // Last-write-wins
                if (isset($existing->updated_at) &&
                    strtotime($existing->updated_at) > strtotime($clientUpdatedAt)) {
                    return (array) $existing;
                }
                DB::table($table)->where('uuid', $uuid)->update(
                    array_merge($payload, ['updated_at' => now()])
                );
                return (array) DB::table($table)->where('uuid', $uuid)->first();

            case 'delete':
                if (!$existing) {
                    throw new \Exception('Record not found for deletion');
                }
                if (Schema::hasColumn($table, 'deleted_at')) {
                    DB::table($table)->where('uuid', $uuid)->update([
                        'deleted_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table($table)->where('uuid', $uuid)->delete();
                }
                return null;

            default:
                throw new \Exception("Unknown operation: {$op}");
        }
    }

    /**
     * Log sync activity to the sync_cursors table for audit/debugging.
     */
    protected function logSyncActivity(int $userId, array $entities, array $newCursors): void
    {
        if (!Schema::hasTable('sync_cursors')) {
            return;
        }

        foreach ($entities as $entity) {
            if (!isset($newCursors[$entity])) {
                continue;
            }

            DB::table('sync_cursors')->updateOrInsert(
                ['user_id' => $userId, 'entity' => $entity],
                [
                    'cursor' => $newCursors[$entity],
                    'last_synced_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
