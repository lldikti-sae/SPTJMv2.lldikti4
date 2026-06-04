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
        Schema::create('skpp', function (Blueprint $table) {
            $table->id();
            $table->string('nidn')->nullable();
            $table->string('nuptk')->nullable();
            $table->string('nama')->nullable();
            $table->string('jabatan_status')->nullable();
            $table->string('kode_pt')->nullable();
            $table->string('pts')->nullable();
            $table->string('tahun')->nullable();
            $table->text('bulan_belum_usulan')->nullable(); // JSON / comma-separated list
            $table->enum('status', ['Proses', 'Selesai', 'Ditolak'])->default('Proses');
            $table->string('jenis_surat')->nullable(); // 'Surat Keterangan' or 'Surat SKPP'
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skpp');
    }
};
