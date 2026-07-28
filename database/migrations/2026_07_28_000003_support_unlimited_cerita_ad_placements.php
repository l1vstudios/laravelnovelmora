<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mst_cerita_ads')) {
            return;
        }

        Schema::table('mst_cerita_ads', function (Blueprint $table) {
            if (! Schema::hasColumn('mst_cerita_ads', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('is_global');
            }
        });

        Schema::table('mst_cerita_ads', function (Blueprint $table) {
            try {
                $table->dropUnique('mst_cerita_ads_story_ad_position_chapter_unique');
            } catch (Throwable) {
                //
            }

            try {
                $table->dropUnique(['cerita_id', 'ad_id', 'after_chapter']);
            } catch (Throwable) {
                //
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mst_cerita_ads')) {
            return;
        }

        Schema::table('mst_cerita_ads', function (Blueprint $table) {
            if (Schema::hasColumn('mst_cerita_ads', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });

        Schema::table('mst_cerita_ads', function (Blueprint $table) {
            try {
                $table->unique(
                    ['cerita_id', 'ad_id', 'placement_position', 'after_chapter'],
                    'mst_cerita_ads_story_ad_position_chapter_unique'
                );
            } catch (Throwable) {
                //
            }
        });
    }
};
