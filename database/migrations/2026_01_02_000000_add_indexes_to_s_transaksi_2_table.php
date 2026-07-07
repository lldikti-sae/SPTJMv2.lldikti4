<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index untuk mempercepat query DataTables di halaman admin/data-dosen
        Schema::table('s_transaksi_2', function (Blueprint $table) {
            // Index gabungan berdasarkan Tahun_Versi, Aktif, dan Nama
            // Disesuaikan dengan where Tahun_Versi dan order by Aktif + Nama
            $table->index(['Tahun_Versi', 'Aktif', 'Nama'], 'idx_s_transaksi_2_tahun_aktif_nama');
        });
    }

    public function down(): void
    {
        Schema::table('s_transaksi_2', function (Blueprint $table) {
            $table->dropIndex('idx_s_transaksi_2_tahun_aktif_nama');
        });
    }
};
