<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Dummy wrapper to hold the trait just for copying logic
trait KekuranganBayarExportTrait {
  public function export(Request $request)
  {
    $versi = session('tahun');
    if (!$versi) {
      return redirect()->back()->with('error', 'Tahun versi belum dipilih pada sesi.');
    }

    $type = $request->input('type'); // kurang, lebih, selesai
    if (!in_array($type, ['kurang', 'lebih', 'selesai'])) {
      return redirect()->back()->with('error', 'Tipe export tidak valid.');
    }

    try {
      $tarifMap = $this->loadTarifPajakMap();
    } catch (\Throwable $e) {
      $tarifMap = [];
    }

    list($fullyPaidNidns, $paidKotorByNidnMonth) = $this->evaluateFullyPaidNidns($versi);
    
    $k2_sub_raw = $this->getPivotSubquery($versi);
    
    $buildBaseQuery = function ($k2_sub) use ($versi) {
        return DB::table('s_transaksi_2 as k')
          ->joinSub($k2_sub, 'k2', function ($join) {
            $join->on(DB::raw("COALESCE(NULLIF(k.NIDN, ''), k.NUPTK)"), '=', 'k2.nidn');
          })
          ->where('k.Tahun_Versi', $versi)
          ->select(
            'k.NIDN', 'k.NUPTK', 'k.Nama', 'k.Jenis', 'k.Jabatan12', 'k.Aktif', 'k.Bank',
            'k2.k_tpd1', 'k2.k_tkgb1', 'k2.k_tpd2', 'k2.k_tkgb2',
            'k2.k_tpd3', 'k2.k_tkgb3', 'k2.k_tpd4', 'k2.k_tkgb4',
            'k2.k_tpd5', 'k2.k_tkgb5', 'k2.k_tpd6', 'k2.k_tkgb6',
            'k2.k_tpd7', 'k2.k_tkgb7', 'k2.k_tpd8', 'k2.k_tkgb8',
            'k2.k_tpd9', 'k2.k_tkgb9', 'k2.k_tpd10', 'k2.k_tkgb10',
            'k2.k_tpd11', 'k2.k_tkgb11', 'k2.k_tpd12', 'k2.k_tkgb12',
            DB::raw('0 as jml_tpd'), DB::raw('0 as jml_tkgb'), DB::raw('0 as nilai_pjk_tpd'), DB::raw('0 as nilai_pjk_tkgb'), 'k2.bersih', 'k2.bersih as delta_bersih', 'k2.total_pembayaran',
            'k.Gol1', 'k.Gol2', 'k.Gol3', 'k.Gol4', 'k.Gol5', 'k.Gol6', 'k.Gol7', 'k.Gol8', 'k.Gol9', 'k.Gol10', 'k.Gol11', 'k.Gol12',
            'k.Jabatan1', 'k.Jabatan2', 'k.Jabatan3', 'k.Jabatan4', 'k.Jabatan5', 'k.Jabatan6', 'k.Jabatan7', 'k.Jabatan8', 'k.Jabatan9', 'k.Jabatan10', 'k.Jabatan11', 'k.Jabatan12 as Jabatan12Monthly',
            'k.TPD1', 'k.TPD2', 'k.TPD3', 'k.TPD4', 'k.TPD5', 'k.TPD6', 'k.TPD7', 'k.TPD8', 'k.TPD9', 'k.TPD10', 'k.TPD11', 'k.TPD12',
            'k.TKGB1', 'k.TKGB2', 'k.TKGB3', 'k.TKGB4', 'k.TKGB5', 'k.TKGB6', 'k.TKGB7', 'k.TKGB8', 'k.TKGB9', 'k.TKGB10', 'k.TKGB11', 'k.TKGB12',
            'k.Gaji1', 'k.Gaji2', 'k.Gaji3', 'k.Gaji4', 'k.Gaji5', 'k.Gaji6', 'k.Gaji7', 'k.Gaji8', 'k.Gaji9', 'k.Gaji10', 'k.Gaji11', 'k.Gaji12',
            'k.bersihTPD1', 'k.bersihTPD2', 'k.bersihTPD3', 'k.bersihTPD4', 'k.bersihTPD5', 'k.bersihTPD6', 'k.bersihTPD7', 'k.bersihTPD8', 'k.bersihTPD9', 'k.bersihTPD10', 'k.bersihTPD11', 'k.bersihTPD12',
            'k.bersihTKGB1', 'k.bersihTKGB2', 'k.bersihTKGB3', 'k.bersihTKGB4', 'k.bersihTKGB5', 'k.bersihTKGB6', 'k.bersihTKGB7', 'k.bersihTKGB8', 'k.bersihTKGB9', 'k.bersihTKGB10', 'k.bersihTKGB11', 'k.bersihTKGB12',
            'k.No_sp2d_1', 'k.No_sp2d_2', 'k.No_sp2d_3', 'k.No_sp2d_4', 'k.No_sp2d_5', 'k.No_sp2d_6', 'k.No_sp2d_7', 'k.No_sp2d_8', 'k.No_sp2d_9', 'k.No_sp2d_10', 'k.No_sp2d_11', 'k.No_sp2d_12',
            'k.Tgl_sp2d_1', 'k.Tgl_sp2d_2', 'k.Tgl_sp2d_3', 'k.Tgl_sp2d_4', 'k.Tgl_sp2d_5', 'k.Tgl_sp2d_6', 'k.Tgl_sp2d_7', 'k.Tgl_sp2d_8', 'k.Tgl_sp2d_9', 'k.Tgl_sp2d_10', 'k.Tgl_sp2d_11', 'k.Tgl_sp2d_12'
          );
    };

    $nidnsByKategori = DB::table('t_kekurangan')
        ->where('tahun', $versi)
        ->selectRaw("nidn, SUM(CASE WHEN jenis_pembayaran NOT LIKE 'PEMBAYARAN_%' THEN selisih ELSE 0 END) as bersih")
        ->groupBy('nidn')
        ->havingRaw('bersih <> 0')
        ->get();

    $nidnsKurang = [];
    $nidnsLebih = [];
    foreach ($nidnsByKategori as $row) {
        if ($row->bersih < 0) {
            $nidnsKurang[] = $row->nidn;
        } elseif ($row->bersih > 0) {
            $nidnsLebih[] = $row->nidn;
        }
    }

    $applySearchFilter = function ($queryBase, $searchTerm) {
        $term = trim((string)$searchTerm);
        if ($term === '') return;
        $lowerTerm = strtolower($term);
        $queryBase->where(function($q) use ($term, $lowerTerm) {
            $q->where('k.NIDN', 'like', '%' . $term . '%')
              ->orWhere('k.NUPTK', 'like', '%' . $term . '%')
              ->orWhere('k.Nama', 'like', '%' . $term . '%')
              ->orWhere('k.Jenis', 'like', '%' . $term . '%')
              ->orWhere('k.Jabatan12', 'like', '%' . $term . '%')
              ->orWhere('k.Bank', 'like', '%' . $term . '%');
            if ($lowerTerm === 'aktif') {
                $q->orWhere('k.Aktif', '=', '1');
            } elseif ($lowerTerm === 'tidak aktif' || $lowerTerm === 'tidak') {
                $q->orWhere(function($sub) {
                    $sub->where('k.Aktif', '!=', '1')->orWhereNull('k.Aktif');
                });
            } elseif (strpos('aktif', $lowerTerm) !== false) {
                $q->orWhere('k.Aktif', '=', '1');
            }
            for ($i = 1; $i <= 11; $i++) {
                $q->orWhere('k.Jabatan' . $i, 'like', '%' . $term . '%');
            }
        });
    };

    $processedRekapNidnsKurang = [];
    $processedRekapNidnsLebih = [];
    $allRekapsForSp2d = DB::table('u_rekap_kekurangan')
        ->whereRaw('RIGHT(periode, 4) = ?', [$versi])
        ->whereNotNull('sp2d')
        ->where('sp2d', '!=', '')
        ->get();
    foreach ($allRekapsForSp2d as $rek) {
        $periode = strtolower(trim($rek->periode ?? ''));
        $isKurangRek = (strpos($periode, 'kurang') !== false);
        $nids = $this->getNidnsInRekap($rek, $versi, $isKurangRek);
        if ($isKurangRek) {
            $processedRekapNidnsKurang = array_merge($processedRekapNidnsKurang, $nids);
        } else {
            $processedRekapNidnsLebih = array_merge($processedRekapNidnsLebih, $nids);
        }
    }
    $processedRekapNidnsKurang = array_unique($processedRekapNidnsKurang);
    $processedRekapNidnsLebih = array_unique($processedRekapNidnsLebih);

    if ($type === 'selesai') {
        $search = $request->input('search_selesai');
        $k2_sub_selesai = clone $k2_sub_raw;
        $selesaiNidns = array_unique(array_merge($fullyPaidNidns, $processedRekapNidnsKurang, $processedRekapNidnsLebih));
        if (!empty($selesaiNidns)) {
            $k2_sub_selesai->whereIn('nidn', $selesaiNidns);
        }
        $queryBase = $buildBaseQuery($k2_sub_selesai);
        if (empty($selesaiNidns)) {
            $queryBase->whereRaw('1 = 0');
        }
    } elseif ($type === 'lebih') {
        $search = $request->input('search_lebih');
        $queryBase = $buildBaseQuery($k2_sub_raw);
        if (empty($nidnsLebih)) {
            $queryBase->whereRaw('1 = 0');
        } else {
            $queryBase->where(function($q) use ($nidnsLebih) {
                $q->whereIn('k.NIDN', $nidnsLebih)->orWhereIn('k.NUPTK', $nidnsLebih);
            });
        }
        if (!empty($fullyPaidNidns)) {
            $queryBase->where(function ($q) use ($fullyPaidNidns) {
                $q->whereNotIn('k.NIDN', $fullyPaidNidns)->whereNotIn('k.NUPTK', $fullyPaidNidns);
            });
        }
        if (!empty($processedRekapNidnsLebih)) {
            $queryBase->where(function ($q) use ($processedRekapNidnsLebih) {
                $q->whereNotIn('k.NIDN', $processedRekapNidnsLebih)->whereNotIn('k.NUPTK', $processedRekapNidnsLebih);
            });
        }
    } else { // kurang
        $search = $request->input('search_kurang');
        $queryBase = $buildBaseQuery($k2_sub_raw);
        if (empty($nidnsKurang)) {
            $queryBase->whereRaw('1 = 0');
        } else {
            $queryBase->where(function($q) use ($nidnsKurang) {
                $q->whereIn('k.NIDN', $nidnsKurang)->orWhereIn('k.NUPTK', $nidnsKurang);
            });
        }
        if (!empty($fullyPaidNidns)) {
            $queryBase->where(function ($q) use ($fullyPaidNidns) {
                $q->whereNotIn('k.NIDN', $fullyPaidNidns)->whereNotIn('k.NUPTK', $fullyPaidNidns);
            });
        }
        if (!empty($processedRekapNidnsKurang)) {
            $queryBase->where(function ($q) use ($processedRekapNidnsKurang) {
                $q->whereNotIn('k.NIDN', $processedRekapNidnsKurang)->whereNotIn('k.NUPTK', $processedRekapNidnsKurang);
            });
        }
    }

    if ($search) {
        $applySearchFilter($queryBase, $search);
    }

    $results = $queryBase->get();

    $transformer = function ($row) use ($tarifMap, $paidKotorByNidnMonth) {
      $jenisRow = (string) ($row->Jenis ?? '');
      $jenisKey = trim($jenisRow);
      $sumDbKotorTPD = 0.0; $sumDbKotorTKGB = 0.0; $sumDbPajakTPD = 0.0; $sumDbPajakTKGB = 0.0; $sumDbBersih = 0.0;
      $sumAktKotorTPD = 0.0; $sumAktKotorTKGB = 0.0; $sumAktPajakTPD = 0.0; $sumAktPajakTKGB = 0.0; $sumAktBersih = 0.0;
      for ($i = 1; $i <= 12; $i++) {
        $noSp2d = trim((string) ($row->{'No_sp2d_' . $i} ?? ''));
        $tglSp2d = trim((string) ($row->{'Tgl_sp2d_' . $i} ?? ''));
        $sp2dOk = ($noSp2d !== '' && $tglSp2d !== '');
        
        $dbTPD = 0; $dbTKGB = 0; $aktTPD = 0; $aktTKGB = 0;
        if ($sp2dOk) {
          $aktKotorTPD = (float) $this->parseMoney($row->{'TPD' . $i} ?? 0);
          $aktKotorTKGB = (float) $this->parseMoney($row->{'TKGB' . $i} ?? 0);
          $gol = trim((string) ($row->{'Gol' . $i} ?? ''));
          $jabatan = (string) ($row->{'Jabatan' . $i} ?? ($row->Jabatan12 ?? ''));
          $kenaTKGB = $this->isGuruBesarAtauProfesor($jabatan);
          $k_tpd = (float) ($row->{'k_tpd' . $i} ?? 0);
          $k_tkgb = (float) ($row->{'k_tkgb' . $i} ?? 0);
          $dbKotorTPD = $aktKotorTPD + $k_tpd;
          $dbKotorTKGB = $aktKotorTKGB + $k_tkgb;
          $dbTPD = (int) round($dbKotorTPD);
          $dbTKGB = (int) round($dbKotorTKGB);
          
          $tarif = (float) (($tarifMap[$jenisKey][$gol] ?? 0) ?: 0);
          $ident = !empty($row->NIDN) ? $row->NIDN : ($row->NUPTK ?? '');
          $paidNet = (!empty($ident) && isset($paidKotorByNidnMonth[$ident][$i])) ? $paidKotorByNidnMonth[$ident][$i] : 0;
          if ($paidNet > 0) {
              $paidGross = $paidNet;
              $diffTPD = $dbKotorTPD - $aktKotorTPD;
              if ($diffTPD > 0 && $paidGross > 0) {
                  $addTPD = min($diffTPD, $paidGross); $aktKotorTPD += $addTPD; $paidGross -= $addTPD;
              } elseif ($diffTPD < 0 && $paidGross > 0) {
                  $subTPD = min(abs($diffTPD), $paidGross); $aktKotorTPD -= $subTPD; $paidGross -= $subTPD;
              }
              $diffTKGB = $dbKotorTKGB - $aktKotorTKGB;
              if ($diffTKGB > 0 && $paidGross > 0) {
                  $addTKGB = min($diffTKGB, $paidGross); $aktKotorTKGB += $addTKGB; $paidGross -= $addTKGB;
              } elseif ($diffTKGB < 0 && $paidGross > 0) {
                  $subTKGB = min(abs($diffTKGB), $paidGross); $aktKotorTKGB -= $subTKGB; $paidGross -= $subTKGB;
              }
          }
          $aktTPD = (int) round($aktKotorTPD);
          $aktTKGB = (int) round($aktKotorTKGB);
          
          $dbPajakTPD = $dbKotorTPD * $tarif;
          $dbPajakTKGB = $kenaTKGB ? ($dbKotorTKGB * $tarif) : 0.0;
          $dbBersih = ($dbKotorTPD - $dbPajakTPD) + ($dbKotorTKGB - $dbPajakTKGB);
          $aktPajakTPD = $aktKotorTPD * $tarif;
          $aktPajakTKGB = $kenaTKGB ? ($aktKotorTKGB * $tarif) : 0.0;
          $aktBersih = ($aktKotorTPD - $aktPajakTPD) + ($aktKotorTKGB - $aktPajakTKGB);
          
          $sumDbKotorTPD += $dbKotorTPD; $sumDbKotorTKGB += $dbKotorTKGB; $sumDbPajakTPD += $dbPajakTPD; $sumDbPajakTKGB += $dbPajakTKGB; $sumDbBersih += $dbBersih;
          $sumAktKotorTPD += $aktKotorTPD; $sumAktKotorTKGB += $aktKotorTKGB; $sumAktPajakTPD += $aktPajakTPD; $sumAktPajakTKGB += $aktPajakTKGB; $sumAktBersih += $aktBersih;
        }
        $row->{'db_tpd' . $i} = $dbTPD; $row->{'db_tkgb' . $i} = $dbTKGB;
        $row->{'exp_tpd' . $i} = $aktTPD; $row->{'exp_tkgb' . $i} = $aktTKGB;
      }
      $row->jml_tpd = $sumDbKotorTPD; $row->jml_tkgb = $sumDbKotorTKGB; $row->nilai_pjk_tpd = $sumDbPajakTPD; $row->nilai_pjk_tkgb = $sumDbPajakTKGB; $row->bersih = $sumDbBersih;
      $row->jml_tpd_akt = $sumAktKotorTPD; $row->jml_tkgb_akt = $sumAktKotorTKGB; $row->nilai_pjk_tpd_akt = $sumAktPajakTPD; $row->nilai_pjk_tkgb_akt = $sumAktPajakTKGB; $row->bersih_akt = $sumAktBersih;
      return $row;
    };

    $exportRows = [];
    $no = 1;
    foreach ($results as $row) {
        $transformer($row);
        
        $status = $row->Aktif == 1 ? 'Aktif' : 'Tidak Aktif';
        $kesimpulan = ((float) ($row->bersih_akt ?? 0)) - ((float) ($row->bersih ?? 0));
        
        $rowData = [
            $no++,
            $row->NIDN ?? '-',
            $row->NUPTK ?? '-',
            $row->Nama,
            $row->Jenis,
            $row->Jabatan12 ?? '-',
            $status,
            $row->Bank ?? '-',
        ];
        
        for ($i = 1; $i <= 12; $i++) {
            $rowData[] = $row->{'db_tpd'.$i} ?? 0;
            $rowData[] = $row->{'db_tkgb'.$i} ?? 0;
            $rowData[] = $row->{'exp_tpd'.$i} ?? 0;
            $rowData[] = $row->{'exp_tkgb'.$i} ?? 0;
        }
        
        $rowData = array_merge($rowData, [
            $row->jml_tpd ?? 0,
            $row->jml_tkgb ?? 0,
            $row->nilai_pjk_tpd ?? 0,
            $row->nilai_pjk_tkgb ?? 0,
            $row->bersih ?? 0,
            $row->jml_tpd_akt ?? 0,
            $row->jml_tkgb_akt ?? 0,
            $row->nilai_pjk_tpd_akt ?? 0,
            $row->nilai_pjk_tkgb_akt ?? 0,
            $row->bersih_akt ?? 0,
            $kesimpulan
        ]);

        $exportRows[] = $rowData;
    }

    $fileName = 'Data_' . ucfirst($type) . 'Bayar_' . $versi . '.xlsx';
    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\KekuranganBayarExport($exportRows), $fileName);
  }
}
