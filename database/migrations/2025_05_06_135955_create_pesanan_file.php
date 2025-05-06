<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_file', function (Blueprint $table) {
            $table->id('id_pesanan_file');
            $table->string('file_path');
            $table->enum('status', ['preview', 'final', 'revisi']);
            $table->dateTime('uploaded_at');
            $table->unsignedBigInteger('id_pesanan');
            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_file');
    }
};