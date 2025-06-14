<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id('id_pesanan');
            $table->uuid();
            $table->string('deskripsi');
            $table->enum('status', ['pending', 'menunggu_konfirmasi', 'dikerjakan', 'revisi', 'selesai', 'dibatalkan']);
            $table->enum('status_pembayaran', ['belum_bayar', 'menunggu_konfirmasi', 'lunas']);
            $table->unsignedInteger('total_harga');
            $table->dateTime('estimasi_waktu');
            $table->unsignedTinyInteger('maksimal_revisi');
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('id_jasa');
            $table->foreign('id_jasa')->references('id_jasa')->on('jasa')->onDelete('cascade');
            $table->unsignedBigInteger('id_paket_jasa');
            $table->foreign('id_paket_jasa')->references('id_paket_jasa')->on('paket_jasa')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};