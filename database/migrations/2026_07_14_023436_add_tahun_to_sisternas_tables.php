<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTahunToSisternasTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = ['n_sister_genap_bj', 'o_sister_genap_tl', 'p_sister_ganjil_tl'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Drop primary key
                $table->dropPrimary();
                
                // Add tahun column
                $table->string('tahun', 4)->nullable();
            });

            // Set default year for existing records to 2026 or current year
            DB::table($tableName)->update(['tahun' => date('Y')]);

            Schema::table($tableName, function (Blueprint $table) {
                // Make tahun required
                $table->string('tahun', 4)->nullable(false)->change();
                
                // Add composite primary key
                $table->primary(['nidn', 'tahun']);
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
        $tables = ['n_sister_genap_bj', 'o_sister_genap_tl', 'p_sister_ganjil_tl'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropPrimary();
                $table->dropColumn('tahun');
                $table->primary('nidn');
            });
        }
    }
}
