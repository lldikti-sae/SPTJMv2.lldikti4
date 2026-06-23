<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCGradeSerdosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('c_grade_serdos')) {
        Schema::create('c_grade_serdos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->collation = 'armscii8_bin';

            $table->bigIncrements('id');
            $table->string('jabatan', 50)->nullable()->collation('armscii8_bin');
            $table->tinyInteger('masa_kerja_bawah')->unsigned();
            $table->tinyInteger('masa_kerja_atas')->unsigned();
            $table->string('golongan', 10)->nullable()->collation('armscii8_bin');
            $table->timestamps();
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
        Schema::dropIfExists('c_grade_serdos');
    }
}
