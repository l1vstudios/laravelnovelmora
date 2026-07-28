<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mst_kategori') || Schema::hasColumn('mst_kategori', 'has_popup')) {
            return;
        }

        Schema::table('mst_kategori', function (Blueprint $table) {
            $table->boolean('has_popup')->default(false)->after('default_title');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('mst_kategori') || ! Schema::hasColumn('mst_kategori', 'has_popup')) {
            return;
        }

        Schema::table('mst_kategori', function (Blueprint $table) {
            $table->dropColumn('has_popup');
        });
    }
};
