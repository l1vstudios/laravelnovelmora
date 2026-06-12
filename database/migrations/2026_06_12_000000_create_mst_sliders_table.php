<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mst_sliders')) {
            Schema::create('mst_sliders', function (Blueprint $table) {
                $table->id();
                $table->string('image_url');
                $table->boolean('status')->default(true);
                $table->timestamps();
            });

            return;
        }

        Schema::table('mst_sliders', function (Blueprint $table) {
            if (!Schema::hasColumn('mst_sliders', 'image_url')) {
                $table->string('image_url')->after('id');
            }

            if (!Schema::hasColumn('mst_sliders', 'status')) {
                $table->boolean('status')->default(true)->after('image_url');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_sliders');
    }
};
