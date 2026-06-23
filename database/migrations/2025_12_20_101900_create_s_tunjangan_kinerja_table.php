<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSTunjanganKinerjaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('s_tunjangan_kinerja')) {
        Schema::create('s_tunjangan_kinerja', function (Blueprint $table) {
            $table->bigIncrements('NO');
            $table->string('NUPTK', 50);
            $table->string('NIDN', 50)->nullable();
            $table->string('Nama', 255)->nullable();
            $table->string('Jenis', 255)->nullable();
            $table->string('Kode_PTS', 100)->nullable();
            $table->string('Nama_PTS', 255)->nullable();
            $table->string('Jabatan', 255)->nullable();
            $table->string('Kelas_Jabatan', 255)->nullable();
            $table->decimal('Nilai_tukin_Jabatan', 10, 2)->nullable();
            $table->string('Status', 250)->nullable();
            $table->string('Keterangan_Status', 250)->nullable();
            $table->string('Serdos', 100)->nullable();
            $table->string('Kode_Usulan', 50)->nullable();
            $table->string('Tanggal_Usulan', 100)->nullable();
            $table->string('Bulan', 25)->nullable();
            $table->string('Tahun', 5)->nullable();
            $table->string('Kode_Cair', 50)->nullable();
            $table->decimal('KD', 10, 2)->nullable();
            $table->decimal('KP', 10, 2)->nullable();
            $table->decimal('PP', 10, 2)->nullable();
            $table->decimal('Nilai_Bersih_Serdos', 10, 2)->nullable();
            $table->decimal('Nilai_Tukin', 10, 2)->nullable();
            $table->decimal('Pajak', 10, 2)->nullable();
            $table->decimal('Nilai_Pajak', 10, 2)->nullable();
            $table->decimal('Nilai_Bersih', 10, 2)->nullable();

            $table->timestamps();

            $table->index('NO');

            // Table options
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
        });

        // Optional: set AUTO_INCREMENT to match original SQL
        DB::statement("ALTER TABLE `s_tunjangan_kinerja` AUTO_INCREMENT = 2221;");
        }

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('s_tunjangan_kinerja');
    }
}
