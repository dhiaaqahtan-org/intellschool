<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: sync_cursors table
 *
 * Per flutter-implementation-plan.md §10.3:
 *   sync_cursor table: entity TEXT PK, cursor TEXT (ISO-8601 or server token)
 *
 * This table is used by the SyncController to track the last-pulled cursor
 * per entity per device/user. In the current implementation, cursors are
 * sent by the client in the pull request, so this table serves as a
 * server-side audit/log of sync activity.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_cursors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('entity');
            $table->text('cursor')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'entity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_cursors');
    }
};
