<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVPejabatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('v_pejabat')) {
        Schema::create('v_pejabat', function (Blueprint $table) {
            $table->integer('id');
            $table->string('pejabat1', 100)->nullable();
            $table->string('nip_pejabat1', 100)->nullable();
            $table->string('pejabat2', 100)->nullable();
            $table->string('nip_pejabat2', 100)->nullable();
            $table->string('pejabat3', 100)->nullable();
            $table->string('nip_pejabat3', 100)->nullable();

            $table->primary('id');

            // Table options
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';
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
        Schema::dropIfExists('v_pejabat');
    }
}
