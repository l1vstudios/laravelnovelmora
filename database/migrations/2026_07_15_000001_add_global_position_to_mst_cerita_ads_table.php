<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mst_cerita_ads')) {
            return;
        }

        Schema::table('mst_cerita_ads', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_cerita_ads', 'placement_position')) {
                $table->string('placement_position', 10)->default('after')->after('after_chapter');
            }

            if (!Schema::hasColumn('mst_cerita_ads', 'is_global')) {
                $table->boolean('is_global')->default(false)->after('placement_position');
            }
        });

        Schema::table('mst_cerita_ads', function (Blueprint $table) {
            try {
                $table->dropUnique(['cerita_id', 'ad_id', 'after_chapter']);
            } catch (Throwable) {
                //
            }

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

    public function down(): void
    {
        if (!Schema::hasTable('mst_cerita_ads')) {
            return;
        }

        Schema::table('mst_cerita_ads', function (Blueprint $table) {
            try {
                $table->dropUnique('mst_cerita_ads_story_ad_position_chapter_unique');
            } catch (Throwable) {
                //
            }

            try {
                $table->unique(['cerita_id', 'ad_id', 'after_chapter']);
            } catch (Throwable) {
                //
            }
        });

        Schema::table('mst_cerita_ads', function (Blueprint $table) {
            if (Schema::hasColumn('mst_cerita_ads', 'is_global')) {
                $table->dropColumn('is_global');
            }

            if (Schema::hasColumn('mst_cerita_ads', 'placement_position')) {
                $table->dropColumn('placement_position');
            }
        });
    }
};
