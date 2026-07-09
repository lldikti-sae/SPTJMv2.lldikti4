<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapUsulanNonEligibleController extends Controller
{
  private function getNamaBulanForPencairan($pencairanKe, array $bulanMap): array
  {
    if ($pencairanKe === 'Semua' || $pencairanKe === null || $pencairanKe === '') {
      return array_values($bulanMap);
    }

    if (!is_numeric($pencairanKe)) {
      return [];
    }

    $pengaturan = DB::table('m_pengaturan_usulan')
      ->where('pencairan_ke', (int) $pencairanKe)
      ->orderBy('id', 'desc')
      ->first();

    if (!$pengaturan) {
      return [];
    }

    $bulanNama = ucfirst(strtolower($pengaturan->bulan));
    $bulanIndex = array_search($bulanNama, $bulanMap);

    if ($bulanIndex === false) {
      return [];
    }

    $namaBulan = [];
    for ($i = 1; $i <= $bulanIndex; $i++) {
      $namaBulan[] = $bulanMap[$i];
    }
    return $namaBulan;
  }

  public function index(Request $request)
  {
    $hasFilter = $request->hasAny(['pencairan_ke', 'Eligible_span', 'search']);
    $data = [];
    $namaBulan = [];

    // Map nomor ke nama bulan
    $bulanMap = [
      1 => 'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember',
    ];

    if ($hasFilter) {
      // determine month from session (fallback to 12) and alias selected month to jabatan12/gol12/tahun12
      $bulanSession = (int) session('bulan') ?: 12;
      if ($bulanSession < 1 || $bulanSession > 12) {
        $bulanSession = 12;
      }
      $monthSelect = "`Jabatan{$bulanSession}` AS jabatan12, `Gol{$bulanSession}` AS gol12, `Tahun{$bulanSession}` AS tahun12";

      // Query directly from s_transaksi_2 and alias columns to lowercase keys used in the view
      $query = DB::table('s_transaksi_2')
        ->selectRaw(
          "`NIDN` AS nidn, `NUPTK` AS nuptk, `Sertifikat_Dosen` AS sertifikat_dosen, `Nama` AS nama, {$monthSelect}, `Jenis` AS jenis, `Bank` AS bank, `Eligible_span` AS eligible_span, `Aktif` AS aktif, `No_Rek` AS no_rekening, `NPWP` AS npwp"
        )
        ->selectRaw(
          "`TPD1` AS tpd1, `TKGB1` AS tkgb1, `TPD2` AS tpd2, `TKGB2` AS tkgb2, `TPD3` AS tpd3, `TKGB3` AS tkgb3, `TPD4` AS tpd4, `TKGB4` AS tkgb4, `TPD5` AS tpd5, `TKGB5` AS tkgb5, `TPD6` AS tpd6, `TKGB6` AS tkgb6, `TPD7` AS tpd7, `TKGB7` AS tkgb7, `TPD8` AS tpd8, `TKGB8` AS tkgb8, `TPD9` AS tpd9, `TKGB9` AS tkgb9, `TPD10` AS tpd10, `TKGB10` AS tkgb10, `TPD11` AS tpd11, `TKGB11` AS tkgb11, `TPD12` AS tpd12, `TKGB12` AS tkgb12"
        )
        ->where('Aktif', 1)
        ->where('Tahun_Versi', session('tahun'));

      // Jika tipe TUKIN, hanya tampilkan dosen PNS
      $tipe_sptjm = $request->input('tipe_sptjm', 'SPTJM');
      if ($tipe_sptjm === 'TUKIN') {
        $query->where('Jenis', 'PNS');
      }

      if ($request->Eligible_span === 'TIDAK') {
        $query->where('Eligible_span', 'TIDAK');
      }

      $pencairanKe = $request->pencairan_ke;
      $search = $request->search;

      $namaBulan = $this->getNamaBulanForPencairan($pencairanKe, $bulanMap);

      if ($pencairanKe === 'Semua') {
        // Tambahkan filter: tampilkan baris yang memiliki minimal satu TPD{i} > 0 atau TKGB{i} > 0
        $query->where(function ($q) {
          for ($i = 1; $i <= 12; $i++) {
            if ($i === 1) {
              $q->where('TPD' . $i, '>', 0)
                ->orWhere('TKGB' . $i, '>', 0);
            } else {
              $q->orWhere('TPD' . $i, '>', 0)
                ->orWhere('TKGB' . $i, '>', 0);
            }
          }
        });
      } else {
        $bulanPendek = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        $query->where(function ($q) use ($pencairanKe, $bulanPendek) {
          for ($i = 1; $i <= 12; $i++) {
            $bln = $bulanPendek[$i - 1];
            $q->orWhere(function ($sub) use ($bln, $pencairanKe, $i) {
              $sub->where($bln, $pencairanKe)
                  ->where(function ($s2) use ($i) {
                    $s2->where('TPD' . $i, '>', 0)
                       ->orWhere('TKGB' . $i, '>', 0);
                  });
            });
          }
        });
      }

      // Apply search filter on nidn or nuptk if provided
      if (!empty($search)) {
        $query->where(function ($q) use ($search) {
          $q->where('NIDN', 'like', "%{$search}%")
            ->orWhere('NUPTK', 'like', "%{$search}%");
        });
      }

      $data = []; // Do not load all data into memory for the view
    }

    return view('admin.rekap-usulan-non-el', [
      'data' => $data,
      'hasFilter' => $hasFilter,
      'filter' => $request->all(),
      'namaBulan' => $namaBulan,
    ]);
  }

  // AJAX endpoint untuk DataTables (server-side)
  public function data(Request $request)
  {
    $pencairanKe = $request->input('pencairan_ke', 'Semua');
    $eligibleSpan = $request->input('Eligible_span', 'TIDAK');
    $tipe_sptjm = $request->input('tipe_sptjm', 'SPTJM');

    $bulanMap = [
      1 => 'Januari',
      2 => 'Februari',
      3 => 'Maret',
      4 => 'April',
      5 => 'Mei',
      6 => 'Juni',
      7 => 'Juli',
      8 => 'Agustus',
      9 => 'September',
      10 => 'Oktober',
      11 => 'November',
      12 => 'Desember',
    ];

    $namaBulan = $this->getNamaBulanForPencairan($pencairanKe, $bulanMap);

    // determine month from session (fallback to 12) and alias selected month to jabatan12/gol12/tahun12
    $bulanSession = (int) session('bulan') ?: 12;
    if ($bulanSession < 1 || $bulanSession > 12) {
      $bulanSession = 12;
    }
    $monthSelect = "`Jabatan{$bulanSession}` AS jabatan12, `Gol{$bulanSession}` AS gol12, `Tahun{$bulanSession}` AS tahun12";

    $query = DB::table('s_transaksi_2')
      ->selectRaw(
        "`NIDN` AS nidn, `NUPTK` AS nuptk, `Sertifikat_Dosen` AS sertifikat_dosen, `Nama` AS nama, {$monthSelect}, `Jenis` AS jenis, `Bank` AS bank, `Eligible_span` AS eligible_span, `Aktif` AS aktif, `No_Rek` AS no_rekening, `NPWP` AS npwp"
      )
      ->selectRaw(
        "`TPD1` AS tpd1, `TKGB1` AS tkgb1, `TPD2` AS tpd2, `TKGB2` AS tkgb2, `TPD3` AS tpd3, `TKGB3` AS tkgb3, `TPD4` AS tpd4, `TKGB4` AS tkgb4, `TPD5` AS tpd5, `TKGB5` AS tkgb5, `TPD6` AS tpd6, `TKGB6` AS tkgb6, `TPD7` AS tpd7, `TKGB7` AS tkgb7, `TPD8` AS tpd8, `TKGB8` AS tkgb8, `TPD9` AS tpd9, `TKGB9` AS tkgb9, `TPD10` AS tpd10, `TKGB10` AS tkgb10, `TPD11` AS tpd11, `TKGB11` AS tkgb11, `TPD12` AS tpd12, `TKGB12` AS tkgb12"
      )
      ->where('Aktif', 1)
      ->where('Tahun_Versi', session('tahun'));

    // Jika tipe TUKIN, hanya tampilkan dosen PNS
    if ($tipe_sptjm === 'TUKIN') {
      $query->where('Jenis', 'PNS');
    }

    if ($eligibleSpan === 'TIDAK') {
      $query->where('Eligible_span', 'TIDAK');
    }

    if ($pencairanKe === 'Semua') {
      $query->where(function ($q) {
        for ($i = 1; $i <= 12; $i++) {
          if ($i === 1) {
            $q->where('TPD' . $i, '>', 0)
              ->orWhere('TKGB' . $i, '>', 0);
          } else {
            $q->orWhere('TPD' . $i, '>', 0)
              ->orWhere('TKGB' . $i, '>', 0);
          }
        }
      });
    } else {
      $bulanPendek = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
      $query->where(function ($q) use ($pencairanKe, $bulanPendek) {
        for ($i = 1; $i <= 12; $i++) {
          $bln = $bulanPendek[$i - 1];
          $q->orWhere(function ($sub) use ($bln, $pencairanKe, $i) {
            $sub->where($bln, $pencairanKe)
                ->where(function ($s2) use ($i) {
                  $s2->where('TPD' . $i, '>', 0)
                     ->orWhere('TKGB' . $i, '>', 0);
                });
          });
        }
      });
    }

    // DataTables global search
    if ($request->filled('search') && isset($request->search['value']) && $request->search['value'] !== '') {
      $search = $request->search['value'];
      $query->where(function ($q) use ($search) {
        $q->where('NIDN', 'like', "%{$search}%")
          ->orWhere('NUPTK', 'like', "%{$search}%")
          ->orWhere('Nama', 'like', "%{$search}%")
          ->orWhere('Sertifikat_Dosen', 'like', "%{$search}%");
      });
    }

    // counts
    $recordsTotalQuery = DB::table('s_transaksi_2')
      ->where('Aktif', 1)
      ->where('Tahun_Versi', session('tahun'));
    if ($tipe_sptjm === 'TUKIN') {
      $recordsTotalQuery->where('Jenis', 'PNS');
    }
    if ($eligibleSpan === 'TIDAK') {
      $recordsTotalQuery->where('Eligible_span', 'TIDAK');
    }
    if ($pencairanKe === 'Semua') {
      $recordsTotalQuery->where(function ($q) {
        for ($i = 1; $i <= 12; $i++) {
          if ($i === 1) {
            $q->where('TPD' . $i, '>', 0)
              ->orWhere('TKGB' . $i, '>', 0);
          } else {
            $q->orWhere('TPD' . $i, '>', 0)
              ->orWhere('TKGB' . $i, '>', 0);
          }
        }
      });
    } else {
      $bulanPendek = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
      $recordsTotalQuery->where(function ($q) use ($pencairanKe, $bulanPendek) {
        for ($i = 1; $i <= 12; $i++) {
          $bln = $bulanPendek[$i - 1];
          $q->orWhere(function ($sub) use ($bln, $pencairanKe, $i) {
            $sub->where($bln, $pencairanKe)
                ->where(function ($s2) use ($i) {
                  $s2->where('TPD' . $i, '>', 0)
                     ->orWhere('TKGB' . $i, '>', 0);
                });
          });
        }
      });
    }
    $recordsTotal = $recordsTotalQuery->count();
    $recordsFiltered = $query->count();

    // ordering
    $orderCol = $request->input('order.0.column');
    $orderDir = $request->input('order.0.dir', 'asc');
    $columnMap = [
      0 => 'NIDN',
      1 => 'NUPTK',
      2 => 'Sertifikat_Dosen',
      3 => 'Nama',
      4 => 'Jabatan12',
      5 => 'Gol12',
      6 => 'Tahun12',
      7 => 'Jenis',
      8 => 'Bank',
      9 => 'Eligible_span',
    ];
    if (isset($columnMap[$orderCol])) {
      $query->orderBy($columnMap[$orderCol], $orderDir);
    }

    $start = intval($request->input('start', 0));
    $length = intval($request->input('length', 25));
    $rows = $query->offset($start)->limit($length)->get();

    $out = [];
    foreach ($rows as $item) {
      $row = [];
      $row[] = (string) ($item->nidn ?? '');
      $row[] = (string) (!empty($item->nuptk) ? $item->nuptk : '-');
      $row[] = (string) ($item->sertifikat_dosen ?? '');
      $row[] = (string) ($item->nama ?? '');
      $row[] = (string) ($item->jabatan12 ?? '');
      $row[] = (string) ($item->gol12 ?? '');
      $row[] = (string) ($item->tahun12 ?? '');
      $row[] = (string) ($item->jenis ?? '');
      $row[] = (string) ($item->bank ?? '');
      $row[] = (string) ($item->eligible_span ?? '');

      // monthly columns
      $monthCount = count($namaBulan);
      for ($i = 1; $i <= $monthCount; $i++) {
        $tpdField = 'tpd' . $i;
        $tkgbField = 'tkgb' . $i;
        $val = (float) ($item->$tpdField ?? 0) + (float) ($item->$tkgbField ?? 0);
        $row[] = (string) number_format($val, 0, ',', '.');
      }

      $row[] = (string) ($item->no_rekening ?? '');
      $row[] = (string) ($item->npwp ?? '');

      $identifier = trim((string) ($item->nidn ?? '')) ?: trim((string) ($item->nuptk ?? ''));
      $url = $identifier !== '' ? route('admin.lengkapi-dosen', ['nidn' => $identifier]) : '#';
      $row[] = '<a href="' . $url . '" class="sptjm-icon-btn sptjm-btn-edit" title="Edit Data"><i class="bx bx-edit"></i></a>';

      $out[] = $row;
    }

    return response()->json([
      'draw' => intval($request->input('draw')),
      'recordsTotal' => $recordsTotal,
      'recordsFiltered' => $recordsFiltered,
      'data' => $out,
    ]);
  }
}
