<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mst_cerita', function (Blueprint $table) {
            $table->string('cover')->nullable()->after('judul');
        });
    }

    public function down(): void
    {
        Schema::table('mst_cerita', function (Blueprint $table) {
            $table->dropColumn('cover');
        });
    }
};
