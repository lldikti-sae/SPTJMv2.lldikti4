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
        Schema::create('j_jadwal_pindah_pts', function (Blueprint $table) {
            $table->id();
            $table->string('nidn')->nullable();
            $table->string('nuptk')->nullable();
            $table->string('kode_pt_baru');
            $table->string('nama_pts_baru');
            $table->string('pemegang_wilayah_baru');
            $table->enum('status', ['pending', 'dieksekusi', 'dibatalkan'])->default('pending');
            $table->string('pengguna');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('j_jadwal_pindah_pts');
    }
};
