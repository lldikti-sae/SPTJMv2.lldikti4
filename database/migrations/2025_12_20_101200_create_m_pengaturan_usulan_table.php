<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateMPengaturanUsulanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('m_pengaturan_usulan')) {
        Schema::create('m_pengaturan_usulan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('jenis_usulan', 50)->nullable();
            $table->string('tahun', 50)->nullable();
            $table->string('bulan', 50)->nullable();
            $table->integer('pencairan_ke')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('status', 255)->nullable();
            $table->timestamps();

            // Table options
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });

        // Optional: set AUTO_INCREMENT to 35 to match provided SQL
        DB::statement("ALTER TABLE `m_pengaturan_usulan` AUTO_INCREMENT = 35;");
        }

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_pengaturan_usulan');
    }
}
