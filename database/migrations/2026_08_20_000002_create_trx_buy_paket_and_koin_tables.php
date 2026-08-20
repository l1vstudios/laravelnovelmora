<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trx_buy_paket', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('transaction_id')->nullable();
            $table->string('amount_price'); // stored as string, cast to integer when needed
            $table->string('nama_paket')->nullable();
            $table->string('status_payment')->default('pending'); // pending, verified
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->integer('count_daily')->default(0);
            $table->boolean('status')->default(false);
            $table->text('purchase_token')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('mst_users')->onDelete('cascade');
            $table->index('status_payment');
            $table->index('created_at');
        });

        Schema::create('trx_buy_koin', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->integer('amount_koin')->default(0);
            $table->integer('amount_price')->default(0);
            $table->string('status_payment')->default('pending'); // pending, verified
            $table->string('transaction_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('mst_users')->onDelete('cascade');
            $table->index('status_payment');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trx_buy_koin');
        Schema::dropIfExists('trx_buy_paket');
    }
};
