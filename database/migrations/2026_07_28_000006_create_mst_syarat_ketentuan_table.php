<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mst_syarat_ketentuan')) {
            return;
        }

        Schema::create('mst_syarat_ketentuan', function (Blueprint $table) {
            $table->increments('id');
            $table->text('konten');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_syarat_ketentuan');
    }
};
