<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mst_fitur_store')) {
            return;
        }

        Schema::create('mst_fitur_store', function (Blueprint $table) {
            $table->increments('id');
            $table->jsonb('konten');
            $table->timestamps();
            $table->boolean('status')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_fitur_store');
    }
};
