<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mst_role_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('mst_roles')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('mst_menu')->cascadeOnDelete();
            $table->boolean('can_view')->default(false);
            $table->boolean('can_insert')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->unique(['role_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mst_role_menu');
    }
};
