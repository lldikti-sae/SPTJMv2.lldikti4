<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('p_sister_ganjil_tl') && !Schema::hasTable('p_sister_ganjil')) {
            Schema::rename('p_sister_ganjil_tl', 'p_sister_ganjil');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('p_sister_ganjil') && !Schema::hasTable('p_sister_ganjil_tl')) {
            Schema::rename('p_sister_ganjil', 'p_sister_ganjil_tl');
        }
    }
};
