<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mst_daily_rewards')) {
            Schema::create('mst_daily_rewards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reward_type_id')->constrained('mst_reward_types')->cascadeOnDelete();
                $table->string('title');
                $table->unsignedInteger('coin_reward')->default(0);
                $table->string('target_url', 2048)->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_daily_rewards');
    }
};
