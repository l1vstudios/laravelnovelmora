<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mst_pusat_bantuan')) {
            return;
        }

        Schema::create('mst_pusat_bantuan', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama_layanan');
            $table->text('isi_layanan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_pusat_bantuan');
    }
};
