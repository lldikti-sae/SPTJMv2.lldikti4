<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTKekuranganTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('t_kekurangan')) {
        Schema::create('t_kekurangan', function (Blueprint $table) {
            $table->string('id', 50);
            $table->string('nidn', 50)->nullable();
            $table->string('nama', 225)->nullable();
            $table->string('total_gaji', 50)->nullable();
            $table->string('total_pembayaran', 50)->nullable();

            for ($i = 1; $i <= 12; $i++) {
                $table->string('k_tpd'.$i, 225)->nullable();
                $table->string('k_tkgb'.$i, 225)->nullable();
            }

            $table->string('jml_tpd', 225)->nullable();
            $table->string('jml_tkgb', 225)->nullable();
            $table->double('pajak', 8, 2)->nullable();
            $table->string('nilai_pjk_tpd', 225)->nullable();
            $table->string('nilai_pjk_tkgb', 225)->nullable();
            $table->string('bersih', 225)->nullable();
            $table->string('sp2d_tpd', 500)->nullable();
            $table->string('sp2d_tkgb', 500)->nullable();
            $table->date('tgl_tpd')->nullable();
            $table->date('tgl_tkgb')->nullable();
            $table->string('cek_validasi_tpd', 5)->nullable();
            $table->string('cek_validasi_tkgb', 5)->nullable();
            $table->string('tahun', 4)->nullable();

            $table->primary('id');

            // Table options
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });
        }

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('t_kekurangan');
    }
}
