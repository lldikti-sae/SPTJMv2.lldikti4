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
        Schema::table('s_transaksi_2', function (Blueprint $table) {
            // 1. Rename JmlTPD_Selisih -> Kotor_Selisih
            if (Schema::hasColumn('s_transaksi_2', 'JmlTPD_Selisih')) {
                $table->renameColumn('JmlTPD_Selisih', 'Kotor_Selisih');
            } elseif (!Schema::hasColumn('s_transaksi_2', 'Kotor_Selisih')) {
                // Jika dijalankan di database fresh yang belum ada JmlTPD_Selisih, langsung buat kolom barunya
                $table->string('Kotor_Selisih', 50)->nullable();
            }

            // 2. Rename Pajak_TPD_Selisih -> Pajak_Selisih
            if (Schema::hasColumn('s_transaksi_2', 'Pajak_TPD_Selisih')) {
                $table->renameColumn('Pajak_TPD_Selisih', 'Pajak_Selisih');
            } elseif (!Schema::hasColumn('s_transaksi_2', 'Pajak_Selisih')) {
                $table->string('Pajak_Selisih', 20)->nullable();
            }

            // 3. Rename Bersih_TPD_Selisih -> Bersih_Selisih
            if (Schema::hasColumn('s_transaksi_2', 'Bersih_TPD_Selisih')) {
                $table->renameColumn('Bersih_TPD_Selisih', 'Bersih_Selisih');
            } elseif (!Schema::hasColumn('s_transaksi_2', 'Bersih_Selisih')) {
                $table->string('Bersih_Selisih', 20)->nullable();
            }

            // 4. Drop sisa kolom yang tidak terpakai (HANYA jika kolomnya ada)
            $columnsToDrop = [];
            $colsToCheck = [
                'JmlTKGB_Selisih', 
                'Pajak_TKGB_Selisih', 
                'Bersih_TKGB_Selisih', 
                'No_SPM_TPD', 
                'No_SPM_TKGB', 
                'TglTPD', 
                'TglTKGB'
            ];

            foreach ($colsToCheck as $col) {
                if (Schema::hasColumn('s_transaksi_2', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (count($columnsToDrop) > 0) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('s_transaksi_2', function (Blueprint $table) {
            // Rollback rename
            if (Schema::hasColumn('s_transaksi_2', 'Kotor_Selisih')) {
                $table->renameColumn('Kotor_Selisih', 'JmlTPD_Selisih');
            }
            if (Schema::hasColumn('s_transaksi_2', 'Pajak_Selisih')) {
                $table->renameColumn('Pajak_Selisih', 'Pajak_TPD_Selisih');
            }
            if (Schema::hasColumn('s_transaksi_2', 'Bersih_Selisih')) {
                $table->renameColumn('Bersih_Selisih', 'Bersih_TPD_Selisih');
            }

            // Kembalikan kolom yang di-drop
            if (!Schema::hasColumn('s_transaksi_2', 'JmlTKGB_Selisih')) $table->string('JmlTKGB_Selisih', 50)->nullable();
            if (!Schema::hasColumn('s_transaksi_2', 'Pajak_TKGB_Selisih')) $table->string('Pajak_TKGB_Selisih', 20)->nullable();
            if (!Schema::hasColumn('s_transaksi_2', 'Bersih_TKGB_Selisih')) $table->string('Bersih_TKGB_Selisih', 20)->nullable();
            if (!Schema::hasColumn('s_transaksi_2', 'No_SPM_TPD')) $table->string('No_SPM_TPD', 50)->nullable();
            if (!Schema::hasColumn('s_transaksi_2', 'No_SPM_TKGB')) $table->string('No_SPM_TKGB', 50)->nullable();
            if (!Schema::hasColumn('s_transaksi_2', 'TglTPD')) $table->string('TglTPD', 50)->nullable();
            if (!Schema::hasColumn('s_transaksi_2', 'TglTKGB')) $table->string('TglTKGB', 50)->nullable();
        });
    }
};
