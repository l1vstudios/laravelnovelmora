<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mst_ads')) {
            Schema::create('mst_ads', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('media_type', 20);
                $table->string('media_url', 2048);
                $table->string('target_url', 2048)->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_ads');
    }
};
