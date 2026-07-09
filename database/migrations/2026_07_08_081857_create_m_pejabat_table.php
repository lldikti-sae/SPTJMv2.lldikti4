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
        Schema::create('m_pejabat', function (Blueprint $table) {
            $table->id();
            $table->integer('urutan')->default(1);
            $table->string('nama')->nullable();
            $table->string('nip')->nullable();
            $table->string('jabatan')->nullable();
            $table->timestamps();
        });

        // Migrate data from v_pejabat
        $oldData = DB::table('v_pejabat')->first();
        if ($oldData) {
            if ($oldData->pejabat1) {
                DB::table('m_pejabat')->insert([
                    'urutan' => 1,
                    'nama' => $oldData->pejabat1,
                    'nip' => $oldData->nip_pejabat1,
                    'jabatan' => $oldData->jabatan1 ?? 'Kuasa Pengguna Anggaran,',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            if ($oldData->pejabat2) {
                DB::table('m_pejabat')->insert([
                    'urutan' => 2,
                    'nama' => $oldData->pejabat2,
                    'nip' => $oldData->nip_pejabat2,
                    'jabatan' => $oldData->jabatan2 ?? 'Bendahara Pengeluaran,',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            if ($oldData->pejabat3) {
                DB::table('m_pejabat')->insert([
                    'urutan' => 3,
                    'nama' => $oldData->pejabat3,
                    'nip' => $oldData->nip_pejabat3,
                    'jabatan' => $oldData->jabatan3 ?? 'Pejabat Pembuat Komitmen,',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        
        // Optional: Drop the old table or leave it
        // Schema::dropIfExists('v_pejabat');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_pejabat');
    }
};
