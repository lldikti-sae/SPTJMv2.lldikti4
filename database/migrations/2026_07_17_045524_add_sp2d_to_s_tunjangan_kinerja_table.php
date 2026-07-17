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
        Schema::table('s_tunjangan_kinerja', function (Blueprint $table) {
            $table->string('No_SP2D', 150)->nullable()->after('Nilai_Bersih');
            $table->date('Tanggal_SP2D')->nullable()->after('No_SP2D');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('s_tunjangan_kinerja', function (Blueprint $table) {
            $table->dropColumn(['No_SP2D', 'Tanggal_SP2D']);
        });
    }
};
