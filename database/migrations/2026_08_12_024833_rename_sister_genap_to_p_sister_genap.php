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
        if (Schema::hasTable('sister_genap') && !Schema::hasTable('p_sister_genap')) {
            Schema::rename('sister_genap', 'p_sister_genap');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('p_sister_genap') && !Schema::hasTable('sister_genap')) {
            Schema::rename('p_sister_genap', 'sister_genap');
        }
    }
};
