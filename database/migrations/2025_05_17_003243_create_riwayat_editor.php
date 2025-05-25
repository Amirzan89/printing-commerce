<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_editor', function (Blueprint $table) {
            $table->id('id_riwayat_editor');
            $table->string('nama_editor', 50);
            $table->string('deskripsi_pengerjaan', 500);
            $table->unsignedTinyInteger('revisi');
            $table->timestamps();
            $table->unsignedBigInteger('id_editor');
            $table->foreign('id_editor')->references('id_editor')->on('editor')->onDelete('cascade');
            $table->unsignedBigInteger('id_pesanan');
            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_editor');
    }
};