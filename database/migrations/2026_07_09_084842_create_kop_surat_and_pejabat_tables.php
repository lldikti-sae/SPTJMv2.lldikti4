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
        // 1. Tabel m_kop_surat
        if (!Schema::hasTable('m_kop_surat')) {
            Schema::create('m_kop_surat', function (Blueprint $table) {
                $table->id();
                $table->string('file_pdf_url')->nullable();
                $table->timestamps();
            });
        }

        // 2. Tabel m_pejabat
        if (!Schema::hasTable('m_pejabat')) {
            Schema::create('m_pejabat', function (Blueprint $table) {
                $table->id();
                $table->integer('urutan')->default(1);
                $table->string('nama')->nullable();
                $table->string('nip')->nullable();
                $table->string('jabatan')->nullable();
                $table->timestamps();
            });

            // Seeding data pejabat secara aman
            try {
                // Cek apakah view v_pejabat ada dan bisa dibaca
                $oldData = DB::table('v_pejabat')->first();
                if ($oldData) {
                    if (!empty($oldData->pejabat1)) {
                        DB::table('m_pejabat')->insert([
                            'urutan' => 1,
                            'nama' => $oldData->pejabat1,
                            'nip' => $oldData->nip_pejabat1,
                            'jabatan' => $oldData->jabatan1 ?? 'Kuasa Pengguna Anggaran,',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                    if (!empty($oldData->pejabat2)) {
                        DB::table('m_pejabat')->insert([
                            'urutan' => 2,
                            'nama' => $oldData->pejabat2,
                            'nip' => $oldData->nip_pejabat2,
                            'jabatan' => $oldData->jabatan2 ?? 'Bendahara Pengeluaran,',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                    if (!empty($oldData->pejabat3)) {
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
            } catch (\Exception $e) {
                // Jika v_pejabat tidak ada di server tujuan, abaikan seeding otomatis.
                // Admin bisa mengisinya secara manual melalui UI.
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_kop_surat');
        Schema::dropIfExists('m_pejabat');
    }
};
