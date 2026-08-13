<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename o_sister_genap_tl to sister_genap
        if (Schema::hasTable('o_sister_genap_tl') && !Schema::hasTable('sister_genap')) {
            Schema::rename('o_sister_genap_tl', 'sister_genap');
        }

        // 2. Drop n_sister_genap_bj
        Schema::dropIfExists('n_sister_genap_bj');

        // 3. Drop existing primary key on sister_genap and create a composite one
        if (Schema::hasTable('sister_genap')) {
            Schema::table('sister_genap', function (Blueprint $table) {
                // To drop a primary key safely in some MySQL versions we need to do it via raw DB statement
                // or just use dropPrimary().
                $table->dropPrimary();
            });
            
            Schema::table('sister_genap', function (Blueprint $table) {
                $table->primary(['nidn', 'tahun']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Rename sister_genap back to o_sister_genap_tl
        if (Schema::hasTable('sister_genap') && !Schema::hasTable('o_sister_genap_tl')) {
            Schema::table('sister_genap', function (Blueprint $table) {
                $table->dropPrimary();
            });
            
            Schema::table('sister_genap', function (Blueprint $table) {
                $table->primary('nidn');
            });
            
            Schema::rename('sister_genap', 'o_sister_genap_tl');
        }
    }
};
