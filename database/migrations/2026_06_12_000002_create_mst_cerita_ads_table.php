<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mst_cerita_ads')) {
            Schema::create('mst_cerita_ads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cerita_id')->constrained('mst_cerita')->cascadeOnDelete();
                $table->foreignId('ad_id')->constrained('mst_ads')->cascadeOnDelete();
                $table->unsignedInteger('after_chapter');
                $table->timestamps();

                $table->unique(['cerita_id', 'ad_id', 'after_chapter']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_cerita_ads');
    }
};
