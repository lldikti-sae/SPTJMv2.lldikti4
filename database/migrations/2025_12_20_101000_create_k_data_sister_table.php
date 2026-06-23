<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateKDataSisterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('k_data_sister')) {
        Schema::create('k_data_sister', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tahun', 255);
            $table->string('periode', 255)->nullable();
            $table->string('bulan', 50)->nullable();
            $table->string('dokumen', 255)->nullable();
            $table->date('tanggal');
            $table->timestamps();

            // Table options
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });

        // Optional: set AUTO_INCREMENT starting value to 43 to match original SQL
        DB::statement("ALTER TABLE `k_data_sister` AUTO_INCREMENT = 43;");
        }

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('k_data_sister');
    }
}
