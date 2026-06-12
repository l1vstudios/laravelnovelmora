<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mst_ads') && !Schema::hasColumn('mst_ads', 'media_path')) {
            Schema::table('mst_ads', function (Blueprint $table) {
                $table->string('media_path')->nullable()->after('media_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mst_ads') && Schema::hasColumn('mst_ads', 'media_path')) {
            Schema::table('mst_ads', function (Blueprint $table) {
                $table->dropColumn('media_path');
            });
        }
    }
};
