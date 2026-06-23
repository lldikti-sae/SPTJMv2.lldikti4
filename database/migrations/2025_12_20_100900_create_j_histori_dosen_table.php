<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateJHistoriDosenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('j_histori_dosen')) {
        Schema::create('j_histori_dosen', function (Blueprint $table) {
            $table->bigIncrements('no');
            $table->string('nidn', 50)->nullable();
            $table->string('nuptk', 50)->nullable();
            $table->string('nama', 255)->nullable();
            $table->string('pts', 255)->nullable();
            $table->string('kode_pt', 250)->nullable();
            $table->string('pemegang_wilayah', 50)->nullable();
            $table->char('aktif', 1)->nullable();
            $table->string('keterangan', 100)->nullable();
            $table->string('pengguna', 255)->nullable();
            $table->string('tanggal_update_terakhir', 100)->nullable();
            $table->string('no_dokumen_ubah', 50)->nullable();
            $table->date('tgl_dokumen_ubah')->nullable();
            $table->string('alasan_perubahan', 255)->nullable();
            $table->string('dokumen', 266)->nullable();
            $table->date('tanggal_update_terbaru')->nullable();
            $table->timestamps();

            // Table options
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });

        // Optional: set AUTO_INCREMENT starting value to 4423 to match original SQL
        DB::statement("ALTER TABLE `j_histori_dosen` AUTO_INCREMENT = 4423;");
        }

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('j_histori_dosen');
    }
}
