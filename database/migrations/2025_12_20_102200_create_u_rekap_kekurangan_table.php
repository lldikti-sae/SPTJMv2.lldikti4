<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateURekapKekuranganTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('u_rekap_kekurangan')) {
        Schema::create('u_rekap_kekurangan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('periode', 50)->nullable();
            $table->string('pegawai', 50)->nullable();
            $table->string('tipe', 50)->nullable();
            $table->string('bank', 50)->nullable();
            $table->string('pdf', 500)->nullable();
            $table->string('excel', 500)->nullable();
            $table->string('sp2d', 50)->nullable();
            $table->date('tgl_sp2d')->nullable();
            $table->string('dokumen_sp2d', 500)->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('u_rekap_kekurangan');
    }
}
