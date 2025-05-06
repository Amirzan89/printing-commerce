<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refresh_token_user', function (Blueprint $table) {
            $table->id('id_refresh_token_user');
            $table->string('email', 45);
            $table->longText('token');
            $table->enum('device',['website','mobile']);
            $table->unsignedSmallInteger('number');
            $table->timestamps();
            $table->unsignedBigInteger('id_auth');
            $table->foreign('id_auth')->references('id_auth')->on('auth')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refresh_token_user');
    }
};