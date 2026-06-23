<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCGradeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('c_grade')) {
        Schema::create('c_grade', function (Blueprint $table) {
            $table->string('kode', 5);
            $table->string('gol', 5)->nullable();
            $table->integer('masa_kerja')->nullable();
            $table->integer('nominal')->nullable();
            $table->timestamps();

            $table->primary('kode');

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
        Schema::dropIfExists('c_grade');
    }
}
