<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mst_cerita') || Schema::hasColumn('mst_cerita', 'positions_index')) {
            return;
        }

        Schema::table('mst_cerita', function (Blueprint $table) {
            $table->integer('positions_index')->default(0)->after('id_kategori');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mst_cerita') || ! Schema::hasColumn('mst_cerita', 'positions_index')) {
            return;
        }

        Schema::table('mst_cerita', function (Blueprint $table) {
            $table->dropColumn('positions_index');
        });
    }
};
