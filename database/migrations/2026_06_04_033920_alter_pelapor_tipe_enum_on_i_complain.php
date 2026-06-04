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
        // Ubah enum untuk menambahkan opsi 'admin' (dan membiarkan 'pts', 'dosen')
        DB::statement("ALTER TABLE i_complain MODIFY COLUMN pelapor_tipe ENUM('pts', 'dosen', 'admin')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum semula. Hati-hati jika sudah ada data 'admin', akan error jika tidak di-update/delete
        // Di sini kita update data 'admin' menjadi 'pts' sebagai default jika di-rollback
        DB::statement("UPDATE i_complain SET pelapor_tipe = 'pts' WHERE pelapor_tipe = 'admin'");
        DB::statement("ALTER TABLE i_complain MODIFY COLUMN pelapor_tipe ENUM('pts', 'dosen')");
    }
};
