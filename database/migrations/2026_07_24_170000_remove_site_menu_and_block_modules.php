<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_pages')) {
            DB::table('site_pages')
                ->select(['id', 'meta'])
                ->orderBy('id')
                ->eachById(function ($page) {
                    $meta = json_decode($page->meta ?: '{}', true) ?: [];

                    unset(
                        $meta['has_block'],
                        $meta['blocks'],
                        $meta['has_slider'],
                        $meta['slider'],
                    );

                    DB::table('site_pages')
                        ->where('id', $page->id)
                        ->update(['meta' => json_encode($meta, JSON_UNESCAPED_UNICODE)]);
                });
        }

        $modelTypes = [
            'App\\Models\\Site\\Menu',
            'App\\Models\\Site\\Block',
        ];

        if (Schema::hasTable('medias')) {
            DB::table('medias')->whereIn('model_type', $modelTypes)->delete();
        }

        if (Schema::hasTable('activity_log')) {
            DB::table('activity_log')->whereIn('subject_type', $modelTypes)->delete();
        }

        Schema::dropIfExists('site_blocks');
        Schema::dropIfExists('site_menus');
    }

    public function down(): void
    {
        // The removed modules and their data are intentionally not restored.
    }
};
