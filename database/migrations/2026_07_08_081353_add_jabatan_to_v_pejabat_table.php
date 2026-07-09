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
        Schema::table('v_pejabat', function (Blueprint $table) {
            $table->string('jabatan1')->nullable();
            $table->string('jabatan2')->nullable();
            $table->string('jabatan3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('v_pejabat', function (Blueprint $table) {
            $table->dropColumn(['jabatan1', 'jabatan2', 'jabatan3']);
        });
    }
};
