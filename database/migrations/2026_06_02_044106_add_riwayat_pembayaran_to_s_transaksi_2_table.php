<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('s_transaksi_2', function (Blueprint $table) {
            if (!Schema::hasColumn('s_transaksi_2', 'Riwayat_Pembayaran')) {
                $table->json('Riwayat_Pembayaran')->nullable()->comment('Menyimpan riwayat pembayaran/cicilan manual secara terpusat');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('s_transaksi_2', function (Blueprint $table) {
            if (Schema::hasColumn('s_transaksi_2', 'Riwayat_Pembayaran')) {
                $table->dropColumn('Riwayat_Pembayaran');
            }
        });
    }
};
