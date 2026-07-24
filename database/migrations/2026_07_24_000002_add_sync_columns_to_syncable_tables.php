<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Migration: Add sync columns to tables used by offline sync.
 *
 * Per flutter-implementation-plan.md §10.1:
 *   "Add uuid/updated_at/deleted_at columns to synced tables that lack them
 *    (most already have uuid)."
 *
 * This migration is idempotent — it checks each column before adding.
 * Tables covered (from SyncController::SYNCABLE_ENTITIES):
 *   - batches, subjects, courses, divisions, timetables
 *   - students, employees, announcements, events, holidays
 *   - attendances, exam_marks, assignments, incidents
 */
return new class extends Migration
{
    /**
     * Tables that need uuid + updated_at + deleted_at for sync.
     */
    protected array $syncTables = [
        // Reference class
        'batches',
        'subjects',
        'courses',
        'divisions',
        'timetables',
        'students',
        'employees',
        'announcements',
        'events',
        'holidays',
        // Field class
        'attendances',
        'exam_marks',
        'assignments',
        'incidents',
    ];

    public function up(): void
    {
        foreach ($this->syncTables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            // Add uuid column if missing
            if (!Schema::hasColumn($table, 'uuid')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->uuid('uuid')->nullable()->after('id');
                    // Add index for sync queries
                    $t->index('uuid');
                });

                // Backfill uuid for existing rows that have a uuid-compatible
                // primary key. We use Str::uuid for each row.
                \DB::table($table)
                    ->whereNull('uuid')
                    ->orderBy('id')
                    ->chunkById(500, function ($rows) use ($table) {
                        foreach ($rows as $row) {
                            \DB::table($table)
                                ->where('id', $row->id)
                                ->update(['uuid' => (string) Str::uuid()]);
                        }
                    });
            }

            // Add updated_at if missing
            if (!Schema::hasColumn($table, 'updated_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->timestamp('updated_at')->nullable();
                });
            }

            // Add deleted_at if missing (soft-delete tombstone)
            if (!Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->timestamp('deleted_at')->nullable();
                    $t->index('deleted_at');
                });
            }
        }
    }

    public function down(): void
    {
        // We do NOT drop columns on rollback — too risky for production data.
        // The columns are nullable and harmless if unused.
    }
};
