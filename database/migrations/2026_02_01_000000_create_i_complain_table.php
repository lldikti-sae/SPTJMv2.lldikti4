<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTComplainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('i_complain')) {
        Schema::create('i_complain', function (Blueprint $table) {
            $table->id();

            // Pelapor
            $table->enum('pelapor_tipe', ['pts', 'dosen']);
            $table->unsignedBigInteger('pts_id')->nullable();
            $table->unsignedBigInteger('dosen_id')->nullable();

            // Snapshot identifiers (keep useful info even if source row changes)
            $table->string('kode_pts', 100)->nullable();
            $table->string('nidn', 100)->nullable();
            $table->string('nuptk', 100)->nullable();

            // Content
            $table->string('judul', 255)->nullable();
            $table->text('pesan')->nullable();
            $table->string('lampiran', 255)->nullable();

            // additional metadata
            $table->string('jenis_pengajuan', 50)->nullable()->default('');

            // Admin handling
            $table->enum('status', ['open', 'setuju', 'tolak'])->nullable();
            $table->text('admin_balasan')->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamp('handled_at')->nullable();

            $table->timestamps();

            $table->index(['pelapor_tipe', 'status']);
            $table->index('created_at');

            // Table options
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
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
        Schema::dropIfExists('i_complain');
    }
}
