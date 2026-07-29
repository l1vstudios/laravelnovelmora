<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mst_daily_reward_claims')) {
            Schema::table('mst_daily_reward_claims', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'daily_reward_id', 'claim_date']);
                $table->string('claim_key', 100)->default('daily')->after('reward_video_id');
                $table->unique(['user_id', 'daily_reward_id', 'claim_date', 'claim_key'], 'daily_reward_claim_unique');
            });
        }

        if (Schema::hasTable('mst_daily_reward_video_schedules')) {
            Schema::table('mst_daily_reward_video_schedules', function (Blueprint $table) {
                $table->dropUnique(['daily_reward_id', 'day_of_week']);
                $table->unique(['daily_reward_id', 'day_of_week', 'reward_video_id'], 'daily_reward_video_day_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mst_daily_reward_video_schedules')) {
            Schema::table('mst_daily_reward_video_schedules', function (Blueprint $table) {
                $table->dropUnique('daily_reward_video_day_unique');
                $table->unique(['daily_reward_id', 'day_of_week']);
            });
        }

        if (Schema::hasTable('mst_daily_reward_claims')) {
            Schema::table('mst_daily_reward_claims', function (Blueprint $table) {
                $table->dropUnique('daily_reward_claim_unique');
                $table->dropColumn('claim_key');
                $table->unique(['user_id', 'daily_reward_id', 'claim_date']);
            });
        }
    }
};
