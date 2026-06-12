<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mst_daily_reward_video_schedules')) {
            Schema::create('mst_daily_reward_video_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('daily_reward_id')->constrained('mst_daily_rewards')->cascadeOnDelete();
                $table->foreignId('reward_video_id')->constrained('mst_reward_videos')->cascadeOnDelete();
                $table->unsignedTinyInteger('day_of_week');
                $table->timestamps();

                $table->unique(['daily_reward_id', 'day_of_week']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_daily_reward_video_schedules');
    }
};
