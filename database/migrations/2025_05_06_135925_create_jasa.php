<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jasa', function (Blueprint $table) {
            $table->id('id_jasa');
            $table->uuid();
            $table->string('nama_jasa', 30);
            $table->string('thumbnail_jasa', 50);
            $table->enum('kategori', ['printing', 'desain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jasa');
    }
};