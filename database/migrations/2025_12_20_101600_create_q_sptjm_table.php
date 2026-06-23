<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateQSptjmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('q_sptjm')) {
        Schema::create('q_sptjm', function (Blueprint $table) {
            $table->bigIncrements('no');
            $table->string('id_usulan', 30)->nullable();
            $table->string('tanggal_usulan', 255)->nullable();
            $table->string('kode_pts', 100)->nullable();
            $table->string('nama_pts', 255)->nullable();
            $table->string('bulan', 100)->nullable();
            $table->string('tahun', 4)->nullable();
            $table->string('nidn', 250)->nullable();
            $table->string('nuptk', 250)->nullable();
            $table->string('nama', 250)->nullable();
            $table->string('jabatan', 250)->nullable();
            $table->string('kota', 250)->nullable();
            $table->string('nomor_surat', 250)->nullable();
            $table->string('alamat_pts', 250)->nullable();
            $table->string('wilayah', 50)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('aktif', 50)->nullable();
            $table->string('file', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->string('alasan_penolakan', 255)->nullable();
            $table->timestamps();

            // Table options
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });

        // Optional: set AUTO_INCREMENT starting value to 56117 to match original SQL
        DB::statement("ALTER TABLE `q_sptjm` AUTO_INCREMENT = 56117;");
        }

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('q_sptjm');
    }
}
