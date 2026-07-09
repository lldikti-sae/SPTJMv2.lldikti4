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
            for ($i = 1; $i <= 12; $i++) {
                $table->string('Kode_PT_' . $i, 50)->nullable();
                $table->string('Nama_PT_' . $i, 255)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('s_transaksi_2', function (Blueprint $table) {
            for ($i = 1; $i <= 12; $i++) {
                $table->dropColumn('Kode_PT_' . $i);
                $table->dropColumn('Nama_PT_' . $i);
            }
        });
    }
};
