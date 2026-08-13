<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePSisterGanjilTlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('p_sister_ganjil')) {
        Schema::create('p_sister_ganjil', function (Blueprint $table) {
            $table->string('nidn', 50);
            $table->string('nuptk', 50);
            $table->string('no_sertifikat', 250)->nullable();
            $table->string('nama_dosen', 250)->nullable();
            $table->string('kode_pt', 10)->nullable();
            $table->string('pt', 250)->nullable();
            $table->string('prodi', 250)->nullable();
            $table->string('kesimpulan_bkd', 250)->nullable();
            $table->string('kewajiban_khusus', 250)->nullable();
            $table->string('kesimpulan', 250)->nullable();
            $table->decimal('kd', 10, 2)->nullable();
            $table->decimal('kp', 10, 2)->nullable();
            $table->decimal('potongan_periodik', 10, 2)->nullable();
            $table->timestamps();

            $table->primary('nidn');

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
        Schema::dropIfExists('p_sister_ganjil');
    }
}
