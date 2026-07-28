<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        $this->dropUniqueConstraint('mst_cerita_ads_story_ad_position_chapter_unique');
        $this->dropUniqueConstraint('mst_cerita_ads_cerita_id_ad_id_after_chapter_unique');
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

        try {
            Schema::table('mst_cerita_ads', function (Blueprint $table) {
                $table->unique(
                    ['cerita_id', 'ad_id', 'placement_position', 'after_chapter'],
                    'mst_cerita_ads_story_ad_position_chapter_unique'
                );
            });
        } catch (Throwable) {
            //
        }
    }

    private function dropUniqueConstraint(string $name): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(sprintf('alter table "mst_cerita_ads" drop constraint if exists "%s"', $name));

            return;
        }

        try {
            DB::statement(sprintf('alter table `mst_cerita_ads` drop index `%s`', $name));
        } catch (Throwable) {
            //
        }
    }
};
