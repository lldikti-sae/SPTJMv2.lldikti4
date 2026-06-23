<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class NormalizeTKekuranganTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('t_kekurangan')) {
        // Drop the old tables
        Schema::dropIfExists('t_uraian_pembayaran');
        Schema::dropIfExists('t_kekurangan');

        // Create the new vertical t_kekurangan table
        Schema::create('t_kekurangan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rekap_id')->nullable();
            $table->string('nidn', 50)->nullable();
            $table->string('nuptk', 50)->nullable();
            $table->string('nama', 225)->nullable();
            $table->string('tahun', 4)->nullable();
            $table->decimal('selisih', 15, 2)->default(0);
            $table->string('jenis_pembayaran', 50)->nullable();
            $table->string('kode_bayar_k', 100)->nullable()->comment('Nomor SP2D');
            $table->date('tgl_bayar_k')->nullable()->comment('Tanggal SP2D');
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();

            $table->index(['nidn', 'tahun']);
            $table->index('rekap_id');
        });
        }

    }


    public function down()
    {
        Schema::dropIfExists('t_kekurangan');
    }
}
