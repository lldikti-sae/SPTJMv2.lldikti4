<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorAlias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\RekapPencairanExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\RekapPencairan;
use Illuminate\Support\Facades\Log;

class RekapPencairanController extends Controller
{
  public function index(Request $request)
  {
    $status = $request->input('status'); // "Proses" atau "Selesai"
    $pencairanKe = $request->input('pencairan_ke');
    $tipeSptjm = $request->input('tipe_sptjm', 'SPTJM');

    $data = [];

    // Tampilkan data hanya jika pencairan_ke dipilih dan tombol status ditekan
    if ($pencairanKe && $status) {
      $query = DB::table('r_proses_cair');

      // jika pencairanKe bukan 'Semua', tambahkan filter pencairan_ke
      if ($pencairanKe !== 'Semua') {
        $query->where('pencairan_ke', $pencairanKe);
      }

      if ($status === 'Proses') {
        $query->whereNull('no_sp2d');
      } elseif ($status === 'Selesai') {
        $query->whereNotNull('no_sp2d');
      }

      // Filter berdasarkan tipe_sptjm jika kolom tersedia
      if ($tipeSptjm) {
        $query->where('tipe_sptjm', $tipeSptjm);
      }

      $data = $query->get();
    }

    return view('admin.rekap-pencairan', compact('data', 'status', 'pencairanKe', 'tipeSptjm'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'no' => 'required|integer',
      'no_sp2d' => 'required|string',
      'tanggal_sp2d' => 'required|date',
    ]);
    $no = $request->no;
    try {
      // Update no_sp2d di tabel r_proses_cair
      DB::table('r_proses_cair')
        ->where('no', $request->no)
        ->update([
          'no_sp2d' => $request->no_sp2d,
        ]);

      // Update tanggal_sp2d di tabel s_transaksi
      // DB::table('s_transaksi')
      //   ->where('tgl_sp2d_1', $request->tanggal_sp2d)
      //   ->update([
      //     'tgl_sp2d_1' => $request->tanggal_sp2d,
      //   ]);

      //kode baru
      $prosesCair = DB::table('r_proses_cair')
        ->where('no', $no)
        ->first();
      if (!$prosesCair) {
        abort(404, 'Data proses cair tidak ditemukan.');
      }
      $pencairan_ke = $prosesCair->pencairan_ke;

      // Pastikan update hanya untuk NIDN yang ada di kolom nidns
      $nidns = array_values(array_filter(array_map('trim', explode(',', (string) $prosesCair->nidns))));

      // Nama bulan pendek (kolom di s_transaksi_2)
      $bulanPendek = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

      // Base filter (tahun/bank/jenis/eligible) supaya tidak nyasar ke data lain
      $baseFilter = function () use ($nidns, $prosesCair) {
        $q = DB::table('s_transaksi_2')
          ->whereIn('nidn', $nidns)
          ->where('bank', $prosesCair->bank)
          ->where('jenis', $prosesCair->status_pegawai)
          ->where('eligible_span', $prosesCair->eligible_span)
          ->where('tahun_versi', $prosesCair->tahun);

        // filter jika jenis prosesnya TKGB
        if ($prosesCair->jenis == 'TKGB') {
          $q->where('Jabatan1', 'Guru Besar');
        }
        return $q;
      };

      // Format tanggal SP2D ke DD/MM/YYYY
      try {
        $formattedDate = \Carbon\Carbon::parse($request->tanggal_sp2d)->format('d/m/Y');
      } catch (\Exception $e) {
        // jika parsing gagal, gunakan nilai mentah sebagai fallback
        $formattedDate = $request->tanggal_sp2d;
      }

      // Update kolom No_sp2d_{n} dan Tgl_sp2d_{n} sesuai bulan yang berisi pencairan_ke.
      // Contoh: jika pencairan_ke=5 ada di kolom Jan, maka update No_sp2d_1 & Tgl_sp2d_1.
      for ($i = 1; $i <= 12; $i++) {
        $bulanField = $bulanPendek[$i - 1];
        $baseFilter()
          ->where($bulanField, $pencairan_ke)
          ->update([
            'No_sp2d_' . $i => $request->no_sp2d,
            'Tgl_sp2d_' . $i => $formattedDate,
          ]);
      }

      // Ambil kode PT yang terkait (untuk update status q_sptjm)
      $kodePTList = DB::table('s_transaksi_2')
        ->whereIn('nidn', $nidns)
        ->where('bank', $prosesCair->bank)
        ->where('jenis', $prosesCair->status_pegawai)
        ->where('eligible_span', $prosesCair->eligible_span)
        ->where('tahun_versi', $prosesCair->tahun)
        ->where(function ($q) use ($pencairan_ke, $bulanPendek) {
          for ($i = 1; $i <= 12; $i++) {
            $q->orWhere($bulanPendek[$i - 1], $pencairan_ke);
          }
        })
        ->distinct()
        ->pluck('Kode_PT');

      //update selesai
      DB::table('q_sptjm')
        ->whereIn('kode_pts', $kodePTList)
        ->where('status', 'Proses')
        ->update(['status' => 'Selesai']);

      // --- TRIGGER JADWAL PINDAH PTS ---
      // Setelah update SP2D, cek apakah ada jadwal pindah PTS untuk NIDN/NUPTK yang baru saja selesai.
      foreach ($nidns as $identifier) {
          $jadwal = DB::table('j_jadwal_pindah_pts')
              ->where(function($q) use ($identifier) {
                  $q->where('nidn', $identifier)
                    ->orWhere('nuptk', $identifier);
              })
              ->where('status', 'pending')
              ->first();

          if ($jadwal) {
              // Cek apakah masih ada usulan aktif LAINNYA untuk dosen ini di TAHUN ini
              $masihAdaUsulanAktif = false;
              $transaksi = DB::table('s_transaksi_2')
                  ->where(function($q) use ($identifier) {
                      $q->where('NIDN', $identifier)
                        ->orWhere('NUPTK', $identifier);
                  })
                  ->where('Tahun_Versi', $prosesCair->tahun)
                  ->first();

              if ($transaksi) {
                  for ($j = 1; $j <= 12; $j++) {
                      $kodeUsulan = $transaksi->{'KodeUsulan' . $j} ?? null;
                      $noSp2d = $transaksi->{'No_sp2d_' . $j} ?? null;
                      if (!empty($kodeUsulan) && empty($noSp2d)) {
                          $masihAdaUsulanAktif = true;
                          break;
                      }
                  }
              }

              if (!$masihAdaUsulanAktif) {
                  // Eksekusi perpindahan PTS secara global (untuk semua tahun versi dosen ini)
                  DB::table('s_transaksi_2')
                      ->where(function($q) use ($identifier) {
                          $q->where('NIDN', $identifier)
                            ->orWhere('NUPTK', $identifier);
                      })
                      ->update([
                          'Kode_PT' => $jadwal->kode_pt_baru,
                          'PTS' => $jadwal->nama_pts_baru,
                          'Pemegang_Wilayah' => $jadwal->pemegang_wilayah_baru,
                      ]);

                  // Update status jadwal menjadi selesai
                  DB::table('j_jadwal_pindah_pts')
                      ->where('id', $jadwal->id)
                      ->update([
                          'status' => 'selesai',
                          'dieksekusi_pada' => \Carbon\Carbon::now(),
                          'updated_at' => \Carbon\Carbon::now()
                      ]);
              }
          }
      }
      // ---------------------------------

      return redirect()
        ->back()
        ->with('success', 'Data SP2D berhasil disimpan.');
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-PENCAIRAN');
      Log::error('RekapPencairanController@store failed', [
        'alias' => $alias['code'],
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return redirect()
        ->back()
        ->with('error', 'Gagal menyimpan data. (Kode: ' . $alias['code'] . ')');
    }
  }

  public function print(Request $request, $id)
  {
    // Ambil data dari r_proses_cair berdasarkan ID
    $prosesCair = DB::table('r_proses_cair')
      ->where('no', $id)
      ->first();
    if (!$prosesCair) {
      abort(404, 'Data proses cair tidak ditemukan.');
    }
    $pencairan_ke = $prosesCair->pencairan_ke;

    // Ambil list NIDN dari kolom 'nidns' (misal: "123456,789012")
    $nidns = explode(',', $prosesCair->nidns);
    // Ambil data dari s_transaksi dengan filter NIDN, bank, jenis pegawai, eligible, dan tahun
    $bulanPendek = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

    $dataTransaksi = DB::table('s_transaksi_2 as s')
      ->whereIn('nidn', $nidns)
      ->where(function ($q) use ($pencairan_ke, $bulanPendek) {
        for ($i = 1; $i <= 12; $i++) {
          $q->orWhere($bulanPendek[$i - 1], $pencairan_ke);
        }
      })
      ->where('bank', $prosesCair->bank)
      ->where('jenis', $prosesCair->status_pegawai)
      ->where('eligible_span', $prosesCair->eligible_span)
      ->where('tahun_versi', $prosesCair->tahun)
      ->get();



    // Nama bulan
    $months = [
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

    // Kirim ke view
    return view('admin.print-pencairan', [
      'prosesCair' => $prosesCair,
      'dataKeuangan' => $dataTransaksi,
      'months' => $months,
      'bulanPendek' => $bulanPendek
    ]);
  }

  public function destroy($no)
  {
    try {
      DB::table('r_proses_cair')
        ->where('no', $no)
        ->delete();
      return redirect()
        ->back()
        ->with('success', 'Data berhasil dihapus.');
    } catch (\Exception $e) {
      $alias = ErrorAlias::fromThrowable($e, 'ADM-PENCAIRAN');
      Log::error('RekapPencairanController@destroy failed', [
        'alias' => $alias['code'],
        'no' => (string) $no,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return redirect()
        ->back()
        ->with('error', 'Gagal menghapus data. (Kode: ' . $alias['code'] . ')');
    }
  }

  public function exportExcel(Request $request, $id)
  {
    // Ambil data dari r_proses_cair berdasarkan ID
    // $prosesCair = DB::table('r_proses_cair')
    //   ->where('no', $id)
    //   ->first();

    // if (!$prosesCair) {
    //   abort(404, 'Data proses cair tidak ditemukan.');
    // }

    // Ambil list NIDN dari kolom 'nidns' (misal: "123456,789012")
    // $nidns = explode(',', $prosesCair->nidns);

    // Ambil data dari s_transaksi dengan filter NIDN, bank, jenis pegawai, eligible, dan tahun
    // $dataTransaksi = DB::table('s_transaksi as s')
    //   ->join('s_transaksi_2 as d', 's.nidn', '=', 'd.nidn')
    //   ->select(
    //     's.*',
    //     'd.nama as nama_dosen',
    //     'd.jabatan12 as jabatan_dosen',
    //     'd.gol12 as golongan_dosen',
    //     'd.jenis as status_dosen',
    //     'd.no_rekening',
    //     'd.npwp'
    //   )
    //   ->whereIn('s.nidn', $nidns)
    //   ->where('d.bank', $prosesCair->bank)
    //   ->where('s.type', $prosesCair->status_pegawai)
    //   ->where('d.eligible_span', $prosesCair->eligible_span)
    //   ->where('s.tahun', $prosesCair->tahun)
    //   ->get();
    // // dd($dataTransaksi);
    // // Nama bulan
    // $months = [
    //   1 => 'Januari',
    //   2 => 'Februari',
    //   3 => 'Maret',
    //   4 => 'April',
    //   5 => 'Mei',
    //   6 => 'Juni',
    //   7 => 'Juli',
    //   8 => 'Agustus',
    //   9 => 'September',
    //   10 => 'Oktober',
    //   11 => 'November',
    //   12 => 'Desember',
    // ];

    // Ambil data pejabat dari view
    // $pejabat = DB::table('v_pejabat')->first();

    // return Excel::download(
    //   new RekapPencairanExport($dataTransaksi, $prosesCair, $months, $pejabat),
    //   'rekap_pencairan_' . $prosesCair->tahun . '.xlsx'
    // );

    //kode baru
    // Ambil data dari r_proses_cair berdasarkan ID
    // Ambil data dari r_proses_cair berdasarkan ID
    $prosesCair = DB::table('r_proses_cair')->where('no', $id)->first();
    if (!$prosesCair) {
      abort(404, 'Data proses cair tidak ditemukan.');
    }

    $pencairan_ke = $prosesCair->pencairan_ke;

    // Ambil list NIDN
    $nidns = explode(',', $prosesCair->nidns);

    // Nama bulan pendek
    $bulanPendek = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];

    // Ambil data keuangan
    $dataTransaksi = DB::table('s_transaksi_2 as s')
      ->whereIn('nidn', $nidns)
      ->where(function ($q) use ($pencairan_ke, $bulanPendek) {
        for ($i = 1; $i <= 12; $i++) {
          $q->orWhere($bulanPendek[$i - 1], $pencairan_ke);
        }
      })
      ->where('bank', $prosesCair->bank)
      ->where('jenis', $prosesCair->status_pegawai)
      ->where('eligible_span', $prosesCair->eligible_span)
      ->where('tahun_versi', $prosesCair->tahun)
      ->get();

    // Nama bulan lengkap
    $months = [
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

    // 🔹 Ambil data pejabat di sini, bukan di view
    $m_pejabat = DB::table('m_pejabat')->get();
    $pejabat = new \stdClass();
    foreach ($m_pejabat as $p) {
        $pejabat->{"pejabat{$p->urutan}"} = $p->nama;
        $pejabat->{"nip_pejabat{$p->urutan}"} = $p->nip;
        $pejabat->{"jabatan{$p->urutan}"} = $p->jabatan;
    }

    // Determine visible months (same logic as print view)
    $showMonths = [];
    for ($m = 1; $m <= 12; $m++) {
      $has = false;
      foreach ($dataTransaksi as $d) {
        if ($prosesCair->jenis == 'TPD') {
          $v = (float) ($d->{'TPD' . $m} ?? 0);
        } elseif ($prosesCair->jenis == 'TKGB') {
          $v = (float) ($d->{'TKGB' . $m} ?? 0);
        } else {
          $v = (float) ($d->{'TPD' . $m} ?? 0) + (float) ($d->{'TKGB' . $m} ?? 0);
        }
        if ($v != 0) {
          $has = true;
          break;
        }
      }
      if ($has) $showMonths[] = $m;
    }

    // Export ke Excel
    return Excel::download(
      new RekapPencairanExport($dataTransaksi, $prosesCair, $months, $bulanPendek, $pejabat, $showMonths),
      'rekap_pencairan_' . $prosesCair->tahun . '.xlsx'
    );
  }
}
