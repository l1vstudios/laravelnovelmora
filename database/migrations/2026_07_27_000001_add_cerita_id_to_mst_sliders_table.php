<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mst_sliders') || Schema::hasColumn('mst_sliders', 'cerita_id')) {
            return;
        }

        Schema::table('mst_sliders', function (Blueprint $table) {
            $table->foreignId('cerita_id')
                ->nullable()
                ->after('image_url')
                ->constrained('mst_cerita')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('mst_sliders') || !Schema::hasColumn('mst_sliders', 'cerita_id')) {
            return;
        }

        try {
            Schema::table('mst_sliders', function (Blueprint $table) {
                $table->dropForeign(['cerita_id']);
            });
        } catch (\Throwable) {
            //
        }

        Schema::table('mst_sliders', function (Blueprint $table) {
            $table->dropColumn('cerita_id');
        });
    }
};
