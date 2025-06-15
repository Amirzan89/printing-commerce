<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('remember_token');
            $table->timestamp('fcm_token_updated_at')->nullable()->after('fcm_token');
            $table->string('device_id')->nullable()->after('fcm_token_updated_at');
            $table->string('device_type')->nullable()->after('device_id'); // android/ios
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['fcm_token', 'fcm_token_updated_at', 'device_id', 'device_type']);
        });
    }
};
