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
        Schema::create('m_kop_surat', function (Blueprint $table) {
            $table->id();
            $table->string('logo_path')->nullable();
            $table->string('teks_1')->nullable();
            $table->string('teks_2')->nullable();
            $table->string('teks_3')->nullable();
            $table->string('teks_4')->nullable();
            $table->string('teks_5')->nullable();
            $table->string('nama_penandatangan')->nullable();
            $table->string('nip_penandatangan')->nullable();
            $table->string('jabatan_penandatangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_kop_surat');
    }
};
