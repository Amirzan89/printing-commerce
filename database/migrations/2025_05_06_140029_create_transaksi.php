<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->string('order_id');
            $table->unsignedTinyInteger('jumlah');
            $table->enum('status', ['pending', 'sukses', 'gagal']);
            $table->string('bukti_pembayaran');
            $table->dateTime('waktu_pembayaran');
            $table->dateTime('expired_at');
            $table->timestamps();
            $table->unsignedBigInteger('id_metode_pembayaran');
            $table->foreign('id_metode_pembayaran')->references('id_metode_pembayaran')->on('metode_pembayaran')->onDelete('cascade');
            $table->unsignedBigInteger('id_pesanan');
            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};