<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipeSptjmToRProsesCairTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('r_proses_cair', function (Blueprint $table) {
            $table->string('tipe_sptjm', 50)->nullable()->default('SPTJM')->after('eligible_span');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('r_proses_cair', function (Blueprint $table) {
            $table->dropColumn('tipe_sptjm');
        });
    }
}
