<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_jasa', function (Blueprint $table) {
            $table->id('id_paket_jasa');
            $table->string('nama_paket_jasa', 15);
            $table->string('deskripsi_paket_jasa', 15);
            $table->string('harga_paket_jasa', 8);
            $table->dateTime('waktu_pengerjaan');
            $table->tinyInteger('maksimal_revisi');
            $table->string('fitur', 300);
            $table->unsignedBigInteger('id_jasa');
            $table->foreign('id_jasa')->references('id_jasa')->on('jasa')->onDelete('cascade'); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_jasa');
    }
};