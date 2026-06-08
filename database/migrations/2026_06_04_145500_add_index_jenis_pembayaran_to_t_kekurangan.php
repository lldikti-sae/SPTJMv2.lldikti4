<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexJenisPembayaranToTKekurangan extends Migration
{
    public function up()
    {
        Schema::table('t_kekurangan', function (Blueprint $table) {
            // Composite index for the heavy pivot subquery that groups by nidn+tahun and filters by jenis_pembayaran
            if (!Schema::hasColumn('t_kekurangan', 'jenis_pembayaran')) return;
            $table->index(['tahun', 'nidn', 'jenis_pembayaran'], 'idx_tk_tahun_nidn_jenis');
        });
    }

    public function down()
    {
        Schema::table('t_kekurangan', function (Blueprint $table) {
            $table->dropIndex('idx_tk_tahun_nidn_jenis');
        });
    }
}
