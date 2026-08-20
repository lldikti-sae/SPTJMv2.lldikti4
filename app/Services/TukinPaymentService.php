<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * TukinPaymentService
 *
 * Centralized service for TUKIN (Tunjangan Kinerja) payment logic.
 * Handles the integration with Kurang/Lebih Bayar adjustments.
 *
 * Business Rules:
 * - Semua dosen PNS mendapatkan TUKIN.
 * - Dosen yang memiliki Serdos: Serdos dibayar terlebih dahulu, baru TUKIN.
 * - Dosen tanpa Serdos: TUKIN langsung dibayarkan.
 * - Sebelum TUKIN dibayar, sistem mengecek riwayat Kurang/Lebih Bayar.
 * - Kurang Bayar → ditambahkan ke TUKIN.
 * - Lebih Bayar → dikurangkan dari TUKIN.
 * - Jika ada Kurang dan Lebih → dikompilasi/netting terlebih dahulu.
 * - TUKIN final minimal 0 (tidak boleh negatif).
 */
class TukinPaymentService
{
    /**
     * Cek apakah dosen memiliki Serdos (Sertifikat Dosen).
     *
     * @param string|null $sertifikatDosen Nilai kolom Sertifikat_Dosen dari s_transaksi_2
     * @return bool
     */
    public function hasSerdos(?string $sertifikatDosen): bool
    {
        if ($sertifikatDosen === null) {
            return false;
        }

        $val = trim($sertifikatDosen);

        if ($val === '' || $val === '-') {
            return false;
        }

        return true;
    }

    /**
     * Cek apakah Serdos (SPTJM) sudah diusulkan/dibayarkan untuk periode tertentu.
     *
     * Melihat apakah ada entri di q_sptjm untuk PTS + bulan + tahun tersebut.
     *
     * @param string $kodePts Kode PTS
     * @param string $bulanTeks Nama bulan dalam bahasa Indonesia (e.g., "Januari")
     * @param string $tahun Tahun (e.g., "2026")
     * @param string $prefixUsulan Prefix kode usulan ('B ' untuk Berjalan, 'S ' untuk Susulan)
     * @return bool
     */
    public function isSerdosPaid(string $kodePts, string $bulanTeks, string $tahun, string $prefixUsulan = 'B '): bool
    {
        return DB::table('q_sptjm')
            ->where('kode_pts', $kodePts)
            ->where('bulan', $bulanTeks)
            ->where('tahun', $tahun)
            ->where('id_usulan', 'like', $prefixUsulan . '%')
            ->exists();
    }

    /**
     * Hitung total Kurang/Lebih Bayar yang pending (belum diselesaikan).
     *
     * Sumber data: t_kekurangan (tabel yang sudah dinormalisasi).
     * Fallback ke t_uraian_pembayaran jika t_kekurangan tidak ada data.
     *
     * @param array $identifiers Array of NIDN/NUPTK identifiers untuk lookup
     * @param string $tahun Tahun
     * @return array<string, array{kurang: float, lebih: float, netto: float}> Keyed by identifier
     */
    public function getPendingAdjustments(array $identifiers, string $tahun): array
    {
        $result = [];
        foreach ($identifiers as $id) {
            $result[$id] = ['kurang' => 0.0, 'lebih' => 0.0, 'netto' => 0.0];
        }

        if (empty($identifiers)) {
            return $result;
        }

        // Sumber utama: t_kekurangan (normalized)
        $hasKekurangan = Schema::hasTable('t_kekurangan');
        if ($hasKekurangan) {
            try {
                $rows = DB::table('t_kekurangan')
                    ->where('tahun', $tahun)
                    ->where(function ($q) use ($identifiers) {
                        $q->whereIn('nidn', $identifiers)
                          ->orWhereIn('nuptk', $identifiers);
                    })
                    // Ambil selisih yang belum di-resolved (bukan PEMBAYARAN_)
                    ->where(function ($q) {
                        $q->where('jenis_pembayaran', 'like', 'K_%')
                          ->orWhere('jenis_pembayaran', 'like', 'L_%');
                    })
                    ->get();

                foreach ($rows as $r) {
                    $identifier = trim((string) ($r->nidn ?? ''));
                    if ($identifier === '' || $identifier === '-') {
                        $identifier = trim((string) ($r->nuptk ?? ''));
                    }
                    if (!isset($result[$identifier])) {
                        // Try matching via nuptk
                        $nuptk = trim((string) ($r->nuptk ?? ''));
                        if (isset($result[$nuptk])) {
                            $identifier = $nuptk;
                        } else {
                            continue;
                        }
                    }

                    $selisih = (float) $r->selisih;

                    // selisih < 0 = Kurang Bayar (dosen kurang terima, harus ditambahkan)
                    // selisih > 0 = Lebih Bayar (dosen kelebihan terima, harus dikurangkan)
                    if ($selisih < 0) {
                        $result[$identifier]['kurang'] += abs($selisih);
                    } elseif ($selisih > 0) {
                        $result[$identifier]['lebih'] += $selisih;
                    }
                }

                // Cek apakah sudah ada pembayaran (PEMBAYARAN_) yang meng-offset selisih
                $paidRows = DB::table('t_kekurangan')
                    ->where('tahun', $tahun)
                    ->where(function ($q) use ($identifiers) {
                        $q->whereIn('nidn', $identifiers)
                          ->orWhereIn('nuptk', $identifiers);
                    })
                    ->where('jenis_pembayaran', 'like', 'PEMBAYARAN_%')
                    ->get();

                foreach ($paidRows as $r) {
                    $identifier = trim((string) ($r->nidn ?? ''));
                    if ($identifier === '' || $identifier === '-') {
                        $identifier = trim((string) ($r->nuptk ?? ''));
                    }
                    if (!isset($result[$identifier])) {
                        $nuptk = trim((string) ($r->nuptk ?? ''));
                        if (isset($result[$nuptk])) {
                            $identifier = $nuptk;
                        } else {
                            continue;
                        }
                    }

                    $paidAmount = (float) $r->selisih;

                    // Pembayaran kurang bayar (positif) → mengurangi kurang bayar yang pending
                    if ($paidAmount > 0) {
                        $result[$identifier]['kurang'] = max(0, $result[$identifier]['kurang'] - $paidAmount);
                    }
                    // Pengembalian lebih bayar (negatif) → mengurangi lebih bayar yang pending
                    elseif ($paidAmount < 0) {
                        $result[$identifier]['lebih'] = max(0, $result[$identifier]['lebih'] - abs($paidAmount));
                    }
                }
            } catch (\Throwable $e) {
                // Jika tabel error, lanjutkan dengan fallback
            }
        }

        // Fallback: t_uraian_pembayaran (legacy)
        $hasUraian = Schema::hasTable('t_uraian_pembayaran');
        if ($hasUraian) {
            try {
                $uraianRows = DB::table('t_uraian_pembayaran')
                    ->whereIn('nidn', $identifiers)
                    ->where('status_cair', 0)
                    ->get();

                foreach ($uraianRows as $kb) {
                    $kbNidn = trim((string) $kb->nidn);
                    if (!isset($result[$kbNidn])) {
                        continue;
                    }

                    $bersih = (float) $kb->bersih;

                    // Nilai bersih negatif = kurang bayar (perlu ditambahkan ke TUKIN)
                    // Nilai bersih positif = lebih bayar (perlu dikurangkan dari TUKIN)
                    if ($bersih < 0) {
                        // Kurang bayar — hanya tambahkan jika belum ada dari t_kekurangan
                        if ($result[$kbNidn]['kurang'] == 0) {
                            $result[$kbNidn]['kurang'] += abs($bersih);
                        }
                    } elseif ($bersih > 0) {
                        // Lebih bayar — hanya tambahkan jika belum ada dari t_kekurangan
                        if ($result[$kbNidn]['lebih'] == 0) {
                            $result[$kbNidn]['lebih'] += $bersih;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Silently continue
            }
        }

        // Hitung netto untuk setiap identifier
        foreach ($result as $id => &$adj) {
            // Netto positif = kurang bayar (ditambahkan ke TUKIN)
            // Netto negatif = lebih bayar (dikurangkan dari TUKIN)
            $adj['netto'] = $adj['kurang'] - $adj['lebih'];
        }

        return $result;
    }

    /**
     * Hitung Selisih Serdos = Gaji Serdos (SP2D) - Pembayaran Serdos (TPD).
     *
     * @param object|null $transaksiRow Row dari s_transaksi_2
     * @param int $bulan Nomor bulan (1-12)
     * @return float Selisih: positif = kurang bayar serdos, negatif = lebih bayar serdos
     */
    public function getSelisihSerdos(?object $transaksiRow, int $bulan): float
    {
        if (!$transaksiRow || $bulan < 1 || $bulan > 12) {
            return 0.0;
        }

        $gajiSerdos = (float) ($transaksiRow->{'Gaji' . $bulan} ?? 0);
        $pembayaranSerdos = (float) ($transaksiRow->{'TPD' . $bulan} ?? 0);

        return $gajiSerdos - $pembayaranSerdos;
    }

    /**
     * Hitung nilai TUKIN final setelah penyesuaian Kurang/Lebih Bayar.
     *
     * Formula:
     * 1. TUKIN Murni = KD + KP - PP
     * 2. TUKIN Final = max(0, TUKIN Murni + nettoAdjustment)
     * 3. Sisa carry = jika TUKIN Final = 0, simpan kelebihan yang belum terpotong
     *
     * @param float $nilaiTukinMurni TUKIN murni (sebelum adjustment)
     * @param float $nettoAdjustment Netto adjustment (positif = kurang bayar, negatif = lebih bayar)
     * @return array{tukinFinal: float, adjustment: float, sisaCarry: float}
     */
    public function calculateAdjustedTukin(float $nilaiTukinMurni, float $nettoAdjustment): array
    {
        $tukinFinal = $nilaiTukinMurni + $nettoAdjustment;
        $sisaCarry = 0.0;

        if ($tukinFinal < 0) {
            // TUKIN tidak boleh negatif, sisa carry untuk bulan berikutnya
            $sisaCarry = abs($tukinFinal);
            $tukinFinal = 0.0;
        }

        return [
            'tukinFinal'  => $tukinFinal,
            'adjustment'  => $nettoAdjustment,
            'sisaCarry'   => $sisaCarry,
        ];
    }

    /**
     * Validasi apakah TUKIN boleh dibayarkan untuk seorang dosen.
     *
     * Rules:
     * - PNS + Serdos → Serdos harus sudah diusulkan
     * - PNS + tanpa Serdos → langsung boleh
     *
     * @param string $kodePts Kode PTS
     * @param string $bulanTeks Nama bulan
     * @param string $tahun Tahun
     * @param bool $hasSerdos Apakah dosen memiliki serdos
     * @param string $prefixUsulan Prefix kode usulan ('B ' atau 'S ')
     * @return array{allowed: bool, reason: string|null}
     */
    public function canPayTukin(string $kodePts, string $bulanTeks, string $tahun, bool $hasSerdos, string $prefixUsulan = 'B '): array
    {
        // Dosen tanpa Serdos → langsung boleh
        if (!$hasSerdos) {
            return ['allowed' => true, 'reason' => null];
        }

        // Dosen dengan Serdos → cek apakah Serdos sudah diusulkan/dibayar
        $serdosPaid = $this->isSerdosPaid($kodePts, $bulanTeks, $tahun, $prefixUsulan);

        if (!$serdosPaid) {
            return [
                'allowed' => false,
                'reason'  => 'Usulan TUKIN ditolak. Anda harus mengusulkan SPTJM (Serdos) terlebih dahulu untuk periode ini.',
            ];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Hitung penyesuaian lengkap untuk seorang dosen, termasuk Selisih Serdos + Kurang/Lebih Bayar.
     *
     * Ini adalah method "all-in-one" yang menggabungkan:
     * - getPendingAdjustments() → kurang/lebih bayar dari t_kekurangan
     * - getSelisihSerdos() → selisih dari pembayaran Serdos
     *
     * @param string $identifier NIDN/NUPTK
     * @param string $tahun Tahun
     * @param object|null $transaksiRow Row dari s_transaksi_2 (untuk selisih serdos)
     * @param int $bulan Nomor bulan
     * @param bool $hasSerdos Apakah dosen punya serdos
     * @return array{kurang: float, lebih: float, netto: float, selisih_serdos: float}
     */
    public function getFullAdjustment(string $identifier, string $tahun, ?object $transaksiRow, int $bulan, bool $hasSerdos): array
    {
        // 1. Ambil pending kurang/lebih bayar
        $adjustments = $this->getPendingAdjustments([$identifier], $tahun);
        $adj = $adjustments[$identifier] ?? ['kurang' => 0.0, 'lebih' => 0.0, 'netto' => 0.0];

        // 2. Hitung selisih Serdos jika dosen punya Serdos
        $selisihSerdos = 0.0;
        if ($hasSerdos && $transaksiRow) {
            $selisihSerdos = $this->getSelisihSerdos($transaksiRow, $bulan);

            // Selisih Serdos positif → kurang bayar serdos (tambahkan ke kurang)
            // Selisih Serdos negatif → lebih bayar serdos (tambahkan ke lebih)
            if ($selisihSerdos > 0) {
                $adj['kurang'] += $selisihSerdos;
            } elseif ($selisihSerdos < 0) {
                $adj['lebih'] += abs($selisihSerdos);
            }

            // Recalculate netto
            $adj['netto'] = $adj['kurang'] - $adj['lebih'];
        }

        $adj['selisih_serdos'] = $selisihSerdos;

        return $adj;
    }
}
