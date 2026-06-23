<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateRProsesCairTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('r_proses_cair')) {
        Schema::create('r_proses_cair', function (Blueprint $table) {
            $table->bigIncrements('no');
            $table->integer('tahun')->nullable();
            $table->string('pencairan_ke', 50)->nullable();
            $table->string('status_pegawai', 250)->nullable();
            $table->string('jenis', 50)->nullable();
            $table->string('bank', 250)->nullable();
            $table->string('eligible_span', 50)->nullable();
            $table->decimal('jumlah_kotor', 11, 0)->nullable();
            $table->decimal('jumlah_pajak', 11, 0)->nullable();
            $table->decimal('jumlah_bersih', 11, 0)->nullable();
            $table->string('no_sp2d', 100)->nullable();
            $table->longText('nidns')->nullable();
            $table->timestamps();

            // Table options
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });

        // Optional: set AUTO_INCREMENT starting value to 14 to match original SQL
        DB::statement("ALTER TABLE `r_proses_cair` AUTO_INCREMENT = 14;");
        }

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('r_proses_cair');
    }
}
