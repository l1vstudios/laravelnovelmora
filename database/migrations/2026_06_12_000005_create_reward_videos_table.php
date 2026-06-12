<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mst_reward_videos')) {
            Schema::create('mst_reward_videos', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('video_url', 2048);
                $table->string('video_path')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_reward_videos');
    }
};
