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
            $table->string('nama');
            $table->text('isi_konten');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_syarat_ketentuan');
    }
};
