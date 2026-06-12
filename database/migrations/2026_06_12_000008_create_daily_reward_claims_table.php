<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mst_daily_reward_claims')) {
            Schema::create('mst_daily_reward_claims', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('daily_reward_id')->constrained('mst_daily_rewards')->cascadeOnDelete();
                $table->foreignId('reward_video_id')->nullable()->constrained('mst_reward_videos')->nullOnDelete();
                $table->date('claim_date');
                $table->unsignedInteger('coin_reward')->default(0);
                $table->timestamps();

                $table->unique(['user_id', 'daily_reward_id', 'claim_date']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_daily_reward_claims');
    }
};
