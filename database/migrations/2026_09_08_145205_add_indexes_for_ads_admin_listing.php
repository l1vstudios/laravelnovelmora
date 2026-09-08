<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mst_ads')) {
            Schema::table('mst_ads', function (Blueprint $table) {
                $this->addIndexIfMissing($table, 'mst_ads', 'created_at', 'mst_ads_created_at_idx');
                $this->addIndexIfMissing($table, 'mst_ads', ['media_type', 'created_at'], 'mst_ads_media_type_created_at_idx');
                $this->addIndexIfMissing($table, 'mst_ads', ['status', 'created_at'], 'mst_ads_status_created_at_idx');
                $this->addIndexIfMissing($table, 'mst_ads', 'title', 'mst_ads_title_idx');
            });
        }

        if (Schema::hasTable('mst_cerita_ads')) {
            Schema::table('mst_cerita_ads', function (Blueprint $table) {
                $this->addIndexIfMissing($table, 'mst_cerita_ads', ['ad_id', 'cerita_id'], 'mst_cerita_ads_ad_story_idx');
                $this->addIndexIfMissing($table, 'mst_cerita_ads', ['cerita_id', 'ad_id', 'placement_position', 'after_chapter', 'is_global'], 'mst_cerita_ads_global_lookup_idx');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mst_ads')) {
            Schema::table('mst_ads', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'mst_ads', 'mst_ads_created_at_idx');
                $this->dropIndexIfExists($table, 'mst_ads', 'mst_ads_media_type_created_at_idx');
                $this->dropIndexIfExists($table, 'mst_ads', 'mst_ads_status_created_at_idx');
                $this->dropIndexIfExists($table, 'mst_ads', 'mst_ads_title_idx');
            });
        }

        if (Schema::hasTable('mst_cerita_ads')) {
            Schema::table('mst_cerita_ads', function (Blueprint $table) {
                $this->dropIndexIfExists($table, 'mst_cerita_ads', 'mst_cerita_ads_ad_story_idx');
                $this->dropIndexIfExists($table, 'mst_cerita_ads', 'mst_cerita_ads_global_lookup_idx');
            });
        }
    }

    private function addIndexIfMissing(Blueprint $table, string $tableName, string|array $columns, string $index): void
    {
        $columns = (array) $columns;

        foreach ($columns as $column) {
            if (! Schema::hasColumn($tableName, $column)) {
                return;
            }
        }

        if (! Schema::hasIndex($tableName, $index)) {
            $table->index($columns, $index);
        }
    }

    private function dropIndexIfExists(Blueprint $table, string $tableName, string $index): void
    {
        if (Schema::hasIndex($tableName, $index)) {
            $table->dropIndex($index);
        }
    }
};
