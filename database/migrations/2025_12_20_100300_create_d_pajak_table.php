<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateDPajakTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('d_pajak')) {
        Schema::create('d_pajak', function (Blueprint $table) {
            $table->bigIncrements('no');
            $table->string('status', 255)->nullable();
            $table->string('akumulasi', 255)->nullable();
            $table->decimal('tarif_pajak', 10, 2)->nullable();
            $table->timestamps();

            // Table options
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
        });

        // Optional: set AUTO_INCREMENT starting value to 29 to match the original SQL
        DB::statement("ALTER TABLE `d_pajak` AUTO_INCREMENT = 29;");
        }

    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('d_pajak');
    }
}
