<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLCutoffSisterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('l_cutoff_sister')) {
        Schema::create('l_cutoff_sister', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('pelaporan', 255)->nullable();
            $table->string('pembayaran', 250)->nullable();
            $table->string('dokumen', 255)->nullable();
            $table->string('url', 100);
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
        Schema::dropIfExists('l_cutoff_sister');
    }
}
