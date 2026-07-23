<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index untuk mempercepat query DataTables di halaman admin/data-dosen
        // Menggunakan raw SQL dengan prefix length pada kolom Nama
        // agar tidak melebihi batas max key length 1000 bytes (utf8mb4)
        \Illuminate\Support\Facades\DB::statement(
            'ALTER TABLE `s_transaksi_2` ADD INDEX `idx_s_transaksi_2_tahun_aktif_nama` (`Tahun_Versi`, `Aktif`, `Nama`(100))'
        );
    }

    public function down(): void
    {
        Schema::table('s_transaksi_2', function (Blueprint $table) {
            $table->dropIndex('idx_s_transaksi_2_tahun_aktif_nama');
        });
    }
};
