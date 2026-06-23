<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAPtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('a_pts')) {
            Schema::create('a_pts', function (Blueprint $table) {
                $table->id();
                $table->string('kode_pts', 100);
                $table->string('nama_pts', 250)->nullable();
                $table->string('nama_pimpinan', 100)->nullable();
                $table->string('jabatan_pimpinan', 100)->nullable();
                $table->string('alamat_pt', 250)->nullable();
                $table->string('password', 255)->nullable();
                $table->integer('aktif')->nullable();
                $table->string('wilayah', 50)->nullable();
                $table->string('dokumen', 255)->nullable();
                $table->timestamp('tanggal_update')->useCurrent();

                // Table options
                $table->engine = 'InnoDB';
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            });

            // If you want to set the AUTO_INCREMENT start value to 390 (as in the original SQL),
            // uncomment the following line. It's optional and may be skipped.
            DB::statement("ALTER TABLE `a_pts` AUTO_INCREMENT = 390;");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('a_pts');
    }
}
