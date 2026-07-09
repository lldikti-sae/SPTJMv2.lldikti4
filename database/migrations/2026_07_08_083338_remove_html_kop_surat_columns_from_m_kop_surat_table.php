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
        Schema::table('m_kop_surat', function (Blueprint $table) {
            $table->dropColumn(['logo_path', 'teks_1', 'teks_2', 'teks_3', 'teks_4', 'teks_5']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_kop_surat', function (Blueprint $table) {
            $table->string('logo_path')->nullable();
            $table->string('teks_1')->nullable();
            $table->string('teks_2')->nullable();
            $table->string('teks_3')->nullable();
            $table->string('teks_4')->nullable();
            $table->string('teks_5')->nullable();
        });
    }
};
