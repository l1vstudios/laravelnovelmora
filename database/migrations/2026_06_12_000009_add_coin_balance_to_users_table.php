<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'coin_balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('coin_balance')->default(0)->after('role_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'coin_balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('coin_balance');
            });
        }
    }
};
