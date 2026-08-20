<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom adjustment tracking ke s_tunjangan_kinerja.
     *
     * Kolom baru:
     * - Nilai_Lebih: Potongan dari lebih bayar sebelumnya
     * - Netto_Adjustment: Netto kurang-lebih bayar yang diterapkan
     * - Sisa_Carry: Sisa lebih bayar yang belum terpotong (carry ke bulan berikutnya)
     */
    public function up(): void
    {
        Schema::table('s_tunjangan_kinerja', function (Blueprint $table) {
            // Cek apakah kolom sudah ada (safe guard)
            if (!Schema::hasColumn('s_tunjangan_kinerja', 'Nilai_Lebih')) {
                $table->decimal('Nilai_Lebih', 15, 2)->nullable()->after('Nilai_Bersih')
                      ->comment('Potongan dari lebih bayar sebelumnya');
            }
            if (!Schema::hasColumn('s_tunjangan_kinerja', 'Netto_Adjustment')) {
                $table->decimal('Netto_Adjustment', 15, 2)->nullable()->after('Nilai_Lebih')
                      ->comment('Netto kurang-lebih bayar yang diterapkan');
            }
            if (!Schema::hasColumn('s_tunjangan_kinerja', 'Sisa_Carry')) {
                $table->decimal('Sisa_Carry', 15, 2)->nullable()->after('Netto_Adjustment')
                      ->comment('Sisa lebih bayar yang belum terpotong, carry ke bulan berikutnya');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('s_tunjangan_kinerja', function (Blueprint $table) {
            $table->dropColumn(['Nilai_Lebih', 'Netto_Adjustment', 'Sisa_Carry']);
        });
    }
};
