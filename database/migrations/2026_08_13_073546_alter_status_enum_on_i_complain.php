<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE i_complain MODIFY COLUMN status ENUM('open', 'setuju', 'tolak', 'menunggu_konfirmasi') DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE i_complain MODIFY COLUMN status ENUM('open', 'setuju', 'tolak') DEFAULT NULL");
    }
};
