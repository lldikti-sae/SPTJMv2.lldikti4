<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (Schema::hasTable('o_sister_genap_tl') && !Schema::hasTable('sister_genap')) {
    Schema::rename('o_sister_genap_tl', 'sister_genap');
}

Schema::dropIfExists('n_sister_genap_bj');

if (Schema::hasTable('sister_genap')) {
    try {
        Schema::table('sister_genap', function (Blueprint $table) {
            $table->dropPrimary();
        });
    } catch (\Exception $e) {
        // ignore if no primary key exists
    }
    try {
        Schema::table('sister_genap', function (Blueprint $table) {
            $table->primary(['nidn', 'tahun']);
        });
    } catch (\Exception $e) {
        // ignore if already exists
    }
}
echo 'Migration completed.\n';
