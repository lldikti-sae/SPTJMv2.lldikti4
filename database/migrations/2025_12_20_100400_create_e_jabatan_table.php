<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEJabatanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('e_jabatan')) {
        Schema::create('e_jabatan', function (Blueprint $table) {
            $table->string('kode', 50);
            $table->string('jabatan', 50)->nullable();
            $table->decimal('nominal', 10, 2)->nullable();
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
        Schema::dropIfExists('e_jabatan');
    }
}
