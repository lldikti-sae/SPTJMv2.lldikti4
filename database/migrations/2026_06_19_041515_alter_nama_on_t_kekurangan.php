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
        // Gunakan raw query untuk menghindari ketergantungan pada doctrine/dbal saat menggunakan ->change()
        // Ini memastikan field nama dapat menampung gelar yang sangat panjang.
        DB::statement('ALTER TABLE t_kekurangan MODIFY nama VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE t_kekurangan MODIFY nama VARCHAR(50) NULL');
    }
};
