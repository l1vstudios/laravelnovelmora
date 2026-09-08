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
            if (! Schema::hasIndex('mst_cerita_ads', 'mst_cerita_ads_detail_idx')) {
                $table->index(
                    ['ad_id', 'cerita_id', 'after_chapter', 'placement_position', 'sort_order'],
                    'mst_cerita_ads_detail_idx'
                );
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mst_cerita_ads')) {
            return;
        }

        Schema::table('mst_cerita_ads', function (Blueprint $table) {
            if (Schema::hasIndex('mst_cerita_ads', 'mst_cerita_ads_detail_idx')) {
                $table->dropIndex('mst_cerita_ads_detail_idx');
            }
        });
    }
};
