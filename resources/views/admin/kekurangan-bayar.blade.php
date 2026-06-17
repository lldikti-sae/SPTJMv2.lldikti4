@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

@php
    $formatInt = function ($value) {
        $num = (float) ($value ?? 0);
        return number_format((int) round($num), 0, ',', '.');
    };

    $diffBgClass = function ($dbValue, $aktualValue) {
        $db = (int) round((float) ($dbValue ?? 0));
        $akt = (int) round((float) ($aktualValue ?? 0));
        $d = $db - $akt;
        if ($d === 0) return '';
        return $d > 0 ? 'table-success' : 'table-danger';
    };

    // Panggil variabel yang dikirim dari controller (Gak butuh dipisah manual lagi)
    $kurangRows = $detailKurang ?? []; 
    $lebihRows  = $detailLebih ?? []; 
    $selesaiRows = $detailSelesai ?? []; 
    
    $rekapKurangRows = collect($rekapKurang ?? []);
    $rekapLebihRows = collect($rekapLebih ?? []);
    // Sorting: KURANG di atas, LEBIH di bawah, lalu urutkan by created_at desc
    $allRekap = $rekapKurangRows->merge($rekapLebihRows)->sort(function($a, $b) {
        $aIsKurang = strpos(strtolower($a->periode ?? ''), 'kurang') !== false;
        $bIsKurang = strpos(strtolower($b->periode ?? ''), 'kurang') !== false;
        if ($aIsKurang && !$bIsKurang) return -1;
        if (!$aIsKurang && $bIsKurang) return 1;
        return $a->created_at < $b->created_at ? 1 : -1;
    })->values();

    $flashInfo = $flashInfo ?? null;

    $rekapAssetUrl = function ($path) {
        $p = trim((string) ($path ?? ''));
        if ($p === '') return null;
        $p = ltrim(str_replace('\\', '/', $p), '/');
        if (strpos($p, 'storage/') === 0) return asset($p);
        return asset('storage/' . $p);
    };
@endphp

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <h5 class="card-header">Proses Kurang/Lebih Bayar - Tahun {{ $versi }}</h5>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.kekurangan-bayar.proses') }}" id="formProsesKekurangan">
                        @csrf
                        <div class="row mb-0">
                            <div class="col-lg-2 col-md-4 mb-2">
                                <label class="form-label">Pilih Periode</label>
                                <select class="form-select" name="periode">
                                    <option value="1" selected>Januari - Desember</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 mb-2">
                                <label class="form-label">Pilih Tipe</label>
                                <select class="form-select" name="tipe">
                                    <option value="Semua" selected>Semua</option>
                                    <option value="TPD">TPD</option>
                                    <option value="TKGB">TKGB</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 mb-2">
                                <label class="form-label">Pilih Jenis</label>
                                <select class="form-select" name="jenis" id="selectJenis">
                                    <option value="Semua" selected>Semua</option>
                                    <option value="PNS">PNS</option>
                                    <option value="NON PNS">NON PNS</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4 mb-2">
                                <label class="form-label">Pilih Bank</label>
                                <select class="form-select" name="bank" id="selectBank">
                                    <option value="Semua" selected>Semua</option>
                                    @foreach(($bankList ?? []) as $bank)
                                    <option value="{{ $bank }}">{{ $bank }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-4 mb-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-primary btn-sm me-2 px-2" id="btnCekDataKekurangan" style="min-width:130px;">
                                    <span class="tf-icons bx bx-search"></span>&nbsp; Cek Data
                                </button>
                                <button type="button" class="btn btn-warning btn-sm px-2" id="btnProsesKekurangan" style="min-width:130px;">
                                    <span class="tf-icons bx bx-loader"></span>&nbsp; Proses
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Navigasi Tabs --}}
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-kurang-btn" data-bs-toggle="tab" data-bs-target="#tab-data-kurang" type="button" role="tab" aria-selected="true">
                        Data Kurang Bayar
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-lebih-btn" data-bs-toggle="tab" data-bs-target="#tab-data-lebih" type="button" role="tab" aria-selected="false">
                        Data Lebih Bayar
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-selesai-btn" data-bs-toggle="tab" data-bs-target="#tab-data-selesai" type="button" role="tab" aria-selected="false">
                        Data Selesai
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-rekap-btn" data-bs-toggle="tab" data-bs-target="#tab-rekap" type="button" role="tab" aria-selected="false">
                        Rekap
                    </button>
                </li>
            </ul>

            <div class="tab-content px-0 py-0">
                
                {{-- TAB 1: DATA KURANG BAYAR --}}
                <div class="tab-pane fade show active" id="tab-data-kurang" role="tabpanel" tabindex="0">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Data Kurang Bayar</h5>
                            <div class="d-flex align-items-center gap-2">
                                <form action="" method="GET" class="m-0 d-flex gap-2">
                                    @if(request('lebih_page')) <input type="hidden" name="lebih_page" value="{{ request('lebih_page') }}"> @endif
                                    @if(request('search_lebih')) <input type="hidden" name="search_lebih" value="{{ request('search_lebih') }}"> @endif
                                    @if(request('selesai_page')) <input type="hidden" name="selesai_page" value="{{ request('selesai_page') }}"> @endif
                                    @if(request('search_selesai')) <input type="hidden" name="search_selesai" value="{{ request('search_selesai') }}"> @endif
                                    <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                                        <option value="1000" {{ request('per_page') == 1000 ? 'selected' : '' }}>1000</option>
                                    </select>
                                    <input type="text" name="search_kurang" class="form-control form-control-sm" placeholder="Cari NIDN / Nama..." value="{{ request('search_kurang') }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bx bx-search"></i></button>
                                </form>
                                <form action="{{ route('admin.kekurangan-bayar.destroy-kurang') }}" method="POST" id="formDestroyKurang" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <span class="tf-icons bx bx-trash"></span>&nbsp; Hapus Kurang Bayar
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="3" class="text-center align-middle">No</th>
                                            <th rowspan="3" class="text-center align-middle">NIDN / NUPTK</th>
                                            <th rowspan="3" class="text-center align-middle">Nama</th>
                                            <th rowspan="3" class="text-center align-middle">Jenis</th>
                                            <th rowspan="3" class="text-center align-middle">Jabatan</th>
                                            <th rowspan="3" class="text-center align-middle">Status</th>
                                            <th rowspan="3" class="text-center align-middle">Bank</th>
                                            <th colspan="48" class="text-center">Januari - Desember</th>
                                            <th colspan="11" class="text-center">Jumlah Kotor, Nilai Pajak, dan Bersih</th>
                                            <th rowspan="3" class="text-center align-middle">Aksi</th>
                                        </tr>
                                        <tr>
                                            <th colspan="2" class="text-center">Jan</th><th colspan="2" class="text-center">Jan (Aktual)</th>
                                            <th colspan="2" class="text-center">Feb</th><th colspan="2" class="text-center">Feb (Aktual)</th>
                                            <th colspan="2" class="text-center">Mar</th><th colspan="2" class="text-center">Mar (Aktual)</th>
                                            <th colspan="2" class="text-center">Apr</th><th colspan="2" class="text-center">Apr (Aktual)</th>
                                            <th colspan="2" class="text-center">Mei</th><th colspan="2" class="text-center">Mei (Aktual)</th>
                                            <th colspan="2" class="text-center">Jun</th><th colspan="2" class="text-center">Jun (Aktual)</th>
                                            <th colspan="2" class="text-center">Jul</th><th colspan="2" class="text-center">Jul (Aktual)</th>
                                            <th colspan="2" class="text-center">Ags</th><th colspan="2" class="text-center">Ags (Aktual)</th>
                                            <th colspan="2" class="text-center">Sep</th><th colspan="2" class="text-center">Sep (Aktual)</th>
                                            <th colspan="2" class="text-center">Okt</th><th colspan="2" class="text-center">Okt (Aktual)</th>
                                            <th colspan="2" class="text-center">Nov</th><th colspan="2" class="text-center">Nov (Aktual)</th>
                                            <th colspan="2" class="text-center">Des</th><th colspan="2" class="text-center">Des (Aktual)</th>
                                            <th colspan="2" class="text-center">Jumlah Kotor</th>
                                            <th colspan="2" class="text-center">Nilai Pajak</th>
                                            <th rowspan="2" class="text-center align-middle">Bersih</th>
                                            <th colspan="2" class="text-center">Jumlah Kotor (Aktual)</th>
                                            <th colspan="2" class="text-center">Nilai Pajak (Aktual)</th>
                                            <th rowspan="2" class="text-center align-middle">Bersih (Aktual)</th>
                                            <th rowspan="2" class="text-center align-middle">Kesimpulan</th>
                                        </tr>
                                        <tr>
                                            @for ($i = 1; $i <= 12; $i++)
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            @endfor
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($kurangRows as $row)
                                        @php
                                        $status = $row->Aktif == 1 ? 'Aktif' : 'Tidak Aktif';
                                        $kesimpulan = ((float) ($row->bersih_akt ?? 0)) - ((float) ($row->bersih ?? 0));
                                        $clsKesimpulan = $kesimpulan == 0.0 ? '' : ($kesimpulan < 0 ? 'table-danger' : 'table-success');
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ !empty($row->NIDN) ? $row->NIDN : ($row->NUPTK ?? '-') }}</td>
                                            <td>{{ $row->Nama }}</td>
                                            <td>{{ $row->Jenis }}</td>
                                            <td>{{ $row->Jabatan12 }}</td>
                                            <td>{{ $status }}</td>
                                            <td>{{ $row->Bank ?? '-' }}</td>
                                                @for ($i = 1; $i <= 12; $i++)
                                                @php
                                                    $dbTpd = $row->{'db_tpd'.$i} ?? 0;
                                                    $dbTkgb = $row->{'db_tkgb'.$i} ?? 0;
                                                    $aktTpd = $row->{'exp_tpd'.$i} ?? 0;
                                                    $aktTkgb = $row->{'exp_tkgb'.$i} ?? 0;
                                                    $clsTpd = $diffBgClass($dbTpd, $aktTpd);
                                                    $clsTkgb = $diffBgClass($dbTkgb, $aktTkgb);
                                                @endphp
                                                <td class="{{ $clsTpd }}">{{ $formatInt($dbTpd) }}</td>
                                                <td class="{{ $clsTkgb }}">{{ $formatInt($dbTkgb) }}</td>
                                                <td>{{ $formatInt($aktTpd) }}</td>
                                                <td>{{ $formatInt($aktTkgb) }}</td>
                                                @endfor
                                                @php
                                                    $clsSumTpd = $diffBgClass($row->jml_tpd ?? 0, $row->jml_tpd_akt ?? 0);
                                                    $clsSumTkgb = $diffBgClass($row->jml_tkgb ?? 0, $row->jml_tkgb_akt ?? 0);
                                                    $clsPjkTpd = $diffBgClass($row->nilai_pjk_tpd ?? 0, $row->nilai_pjk_tpd_akt ?? 0);
                                                    $clsPjkTkgb = $diffBgClass($row->nilai_pjk_tkgb ?? 0, $row->nilai_pjk_tkgb_akt ?? 0);
                                                    $clsBersih = $diffBgClass($row->bersih ?? 0, $row->bersih_akt ?? 0);
                                                @endphp
                                                <td class="{{ $clsSumTpd }}">{{ $formatInt($row->jml_tpd ?? 0) }}</td>
                                                <td class="{{ $clsSumTkgb }}">{{ $formatInt($row->jml_tkgb ?? 0) }}</td>
                                                <td class="{{ $clsPjkTpd }}">{{ $formatInt($row->nilai_pjk_tpd ?? 0) }}</td>
                                                <td class="{{ $clsPjkTkgb }}">{{ $formatInt($row->nilai_pjk_tkgb ?? 0) }}</td>
                                                <td class="{{ $clsBersih }}">{{ $formatInt($row->bersih ?? 0) }}</td>
                                                <td>{{ $formatInt($row->jml_tpd_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->jml_tkgb_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->nilai_pjk_tpd_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->nilai_pjk_tkgb_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->bersih_akt ?? 0) }}</td>
                                                <td class="{{ $clsKesimpulan }}">{{ $formatInt($kesimpulan) }}</td>
                                                <td class="text-center align-middle">
                                                    @if(in_array($row->NIDN, $processedRekapNidnsKurang ?? []))
                                                    <span class="badge bg-label-info d-inline-flex align-items-center" style="font-size:10px;padding:3px 8px;" title="Sudah diproses SP2D melalui Rekap">
                                                        <i class="bx bx-check-circle me-1"></i> SP2D
                                                    </span>
                                                    @elseif($kesimpulan != 0)
                                                    @php
                                                        $availMonths = [];
                                                        for($m=1; $m<=12; $m++) {
                                                            $dbK = ($row->{'db_tpd'.$m} ?? 0) + ($row->{'db_tkgb'.$m} ?? 0);
                                                            $akK = ($row->{'exp_tpd'.$m} ?? 0) + ($row->{'exp_tkgb'.$m} ?? 0);
                                                            // Kurang bayar: hak (db) > aktual yang dibayar (ak)
                                                            if($dbK > $akK) {
                                                                $availMonths[] = $m;
                                                            }
                                                        }
                                                    @endphp
                                                    <button type="button" class="btn btn-sm btn-primary btn-aksi-sp2d-individu py-0 px-2"
                                                        data-nidn="{{ $row->NIDN }}" data-nama="{{ $row->Nama }}" data-jenis="kurang" data-bulan="{{ json_encode($availMonths) }}" title="Input Pembayaran NIDN: {{ $row->NIDN }}" style="font-size:11px;">
                                                        <i class="bx bx-edit-alt"></i> Pembayaran
                                                    </button>
                                                    @else
                                                    <span class="badge bg-label-success d-inline-flex align-items-center" style="font-size:10px;padding:3px 8px;" title="Tidak ada selisih">
                                                        <i class="bx bx-check me-1"></i> Selesai
                                                    </span>
                                                    @endif
                                                </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="66" class="text-center">Tidak ada data kurang bayar ditemukan</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            @if($kurangRows instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="mt-4 d-flex justify-content-end">
                                {{ $kurangRows->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- TAB 2: DATA LEBIH BAYAR --}}
                <div class="tab-pane fade" id="tab-data-lebih" role="tabpanel" tabindex="0">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Data Lebih Bayar</h5>
                            <div class="d-flex align-items-center gap-2">
                                <form action="" method="GET" class="m-0 d-flex gap-2">
                                    @if(request('kurang_page')) <input type="hidden" name="kurang_page" value="{{ request('kurang_page') }}"> @endif
                                    @if(request('search_kurang')) <input type="hidden" name="search_kurang" value="{{ request('search_kurang') }}"> @endif
                                    @if(request('selesai_page')) <input type="hidden" name="selesai_page" value="{{ request('selesai_page') }}"> @endif
                                    @if(request('search_selesai')) <input type="hidden" name="search_selesai" value="{{ request('search_selesai') }}"> @endif
                                    <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                                        <option value="1000" {{ request('per_page') == 1000 ? 'selected' : '' }}>1000</option>
                                    </select>
                                    <input type="text" name="search_lebih" class="form-control form-control-sm" placeholder="Cari NIDN / Nama..." value="{{ request('search_lebih') }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bx bx-search"></i></button>
                                </form>
                                <form action="{{ route('admin.kekurangan-bayar.destroy-lebih') }}" method="POST" id="formDestroyLebih" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <span class="tf-icons bx bx-trash"></span>&nbsp; Hapus Lebih Bayar
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="3" class="text-center align-middle">No</th>
                                            <th rowspan="3" class="text-center align-middle">NIDN / NUPTK</th>
                                            <th rowspan="3" class="text-center align-middle">Nama</th>
                                            <th rowspan="3" class="text-center align-middle">Jenis</th>
                                            <th rowspan="3" class="text-center align-middle">Jabatan</th>
                                            <th rowspan="3" class="text-center align-middle">Status</th>
                                            <th rowspan="3" class="text-center align-middle">Bank</th>
                                            <th colspan="48" class="text-center">Januari - Desember</th>
                                            <th colspan="11" class="text-center">Jumlah Kotor, Nilai Pajak, dan Bersih</th>
                                            <th rowspan="3" class="text-center align-middle">Aksi</th>
                                        </tr>
                                        <tr>
                                            <th colspan="2" class="text-center">Jan</th><th colspan="2" class="text-center">Jan (Aktual)</th>
                                            <th colspan="2" class="text-center">Feb</th><th colspan="2" class="text-center">Feb (Aktual)</th>
                                            <th colspan="2" class="text-center">Mar</th><th colspan="2" class="text-center">Mar (Aktual)</th>
                                            <th colspan="2" class="text-center">Apr</th><th colspan="2" class="text-center">Apr (Aktual)</th>
                                            <th colspan="2" class="text-center">Mei</th><th colspan="2" class="text-center">Mei (Aktual)</th>
                                            <th colspan="2" class="text-center">Jun</th><th colspan="2" class="text-center">Jun (Aktual)</th>
                                            <th colspan="2" class="text-center">Jul</th><th colspan="2" class="text-center">Jul (Aktual)</th>
                                            <th colspan="2" class="text-center">Ags</th><th colspan="2" class="text-center">Ags (Aktual)</th>
                                            <th colspan="2" class="text-center">Sep</th><th colspan="2" class="text-center">Sep (Aktual)</th>
                                            <th colspan="2" class="text-center">Okt</th><th colspan="2" class="text-center">Okt (Aktual)</th>
                                            <th colspan="2" class="text-center">Nov</th><th colspan="2" class="text-center">Nov (Aktual)</th>
                                            <th colspan="2" class="text-center">Des</th><th colspan="2" class="text-center">Des (Aktual)</th>
                                            <th colspan="2" class="text-center">Jumlah Kotor</th>
                                            <th colspan="2" class="text-center">Nilai Pajak</th>
                                            <th rowspan="2" class="text-center align-middle">Bersih</th>
                                            <th colspan="2" class="text-center">Jumlah Kotor (Aktual)</th>
                                            <th colspan="2" class="text-center">Nilai Pajak (Aktual)</th>
                                            <th rowspan="2" class="text-center align-middle">Bersih (Aktual)</th>
                                            <th rowspan="2" class="text-center align-middle">Kesimpulan</th>
                                        </tr>
                                        <tr>
                                            @for ($i = 1; $i <= 12; $i++)
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            @endfor
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($lebihRows as $row)
                                        @php
                                        $status = $row->Aktif == 1 ? 'Aktif' : 'Tidak Aktif';
                                        $kesimpulan = ((float) ($row->bersih_akt ?? 0)) - ((float) ($row->bersih ?? 0));
                                        $clsKesimpulan = $kesimpulan == 0.0 ? '' : ($kesimpulan > 0 ? 'table-success' : 'table-danger');
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ !empty($row->NIDN) ? $row->NIDN : ($row->NUPTK ?? '-') }}</td>
                                            <td>{{ $row->Nama }}</td>
                                            <td>{{ $row->Jenis }}</td>
                                            <td>{{ $row->Jabatan12 }}</td>
                                            <td>{{ $status }}</td>
                                            <td>{{ $row->Bank ?? '-' }}</td>
                                                @for ($i = 1; $i <= 12; $i++)
                                                @php
                                                    $dbTpd = $row->{'db_tpd'.$i} ?? 0;
                                                    $dbTkgb = $row->{'db_tkgb'.$i} ?? 0;
                                                    $aktTpd = $row->{'exp_tpd'.$i} ?? 0;
                                                    $aktTkgb = $row->{'exp_tkgb'.$i} ?? 0;
                                                    $clsTpd = $diffBgClass($dbTpd, $aktTpd);
                                                    $clsTkgb = $diffBgClass($dbTkgb, $aktTkgb);
                                                @endphp
                                                <td class="{{ $clsTpd }}">{{ $formatInt($dbTpd) }}</td>
                                                <td class="{{ $clsTkgb }}">{{ $formatInt($dbTkgb) }}</td>
                                                <td>{{ $formatInt($aktTpd) }}</td>
                                                <td>{{ $formatInt($aktTkgb) }}</td>
                                                @endfor
                                                @php
                                                    $clsSumTpd = $diffBgClass($row->jml_tpd ?? 0, $row->jml_tpd_akt ?? 0);
                                                    $clsSumTkgb = $diffBgClass($row->jml_tkgb ?? 0, $row->jml_tkgb_akt ?? 0);
                                                    $clsPjkTpd = $diffBgClass($row->nilai_pjk_tpd ?? 0, $row->nilai_pjk_tpd_akt ?? 0);
                                                    $clsPjkTkgb = $diffBgClass($row->nilai_pjk_tkgb ?? 0, $row->nilai_pjk_tkgb_akt ?? 0);
                                                    $clsBersih = $diffBgClass($row->bersih ?? 0, $row->bersih_akt ?? 0);
                                                @endphp
                                                <td class="{{ $clsSumTpd }}">{{ $formatInt($row->jml_tpd ?? 0) }}</td>
                                                <td class="{{ $clsSumTkgb }}">{{ $formatInt($row->jml_tkgb ?? 0) }}</td>
                                                <td class="{{ $clsPjkTpd }}">{{ $formatInt($row->nilai_pjk_tpd ?? 0) }}</td>
                                                <td class="{{ $clsPjkTkgb }}">{{ $formatInt($row->nilai_pjk_tkgb ?? 0) }}</td>
                                                <td class="{{ $clsBersih }}">{{ $formatInt($row->bersih ?? 0) }}</td>
                                                <td>{{ $formatInt($row->jml_tpd_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->jml_tkgb_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->nilai_pjk_tpd_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->nilai_pjk_tkgb_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->bersih_akt ?? 0) }}</td>
                                                <td class="{{ $clsKesimpulan }}">{{ $formatInt($kesimpulan) }}</td>
                                                <td class="text-center align-middle">
                                                    @if(in_array($row->NIDN, $processedRekapNidnsLebih ?? []))
                                                    <span class="badge bg-label-info d-inline-flex align-items-center" style="font-size:10px;padding:3px 8px;" title="Sudah diproses SP2D melalui Rekap">
                                                        <i class="bx bx-check-circle me-1"></i> SP2D
                                                    </span>
                                                    @elseif($kesimpulan != 0)
                                                    @php
                                                        $availMonths = [];
                                                        for($m=1; $m<=12; $m++) {
                                                            $dbK = ($row->{'db_tpd'.$m} ?? 0) + ($row->{'db_tkgb'.$m} ?? 0);
                                                            $akK = ($row->{'exp_tpd'.$m} ?? 0) + ($row->{'exp_tkgb'.$m} ?? 0);
                                                            // Lebih bayar: aktual yang dibayar (ak) > hak (db)
                                                            if($akK > $dbK) {
                                                                $dbB = $row->{'db_bersih'.$m} ?? 0;
                                                                $akB = $row->{'akt_bersih'.$m} ?? 0;
                                                                $selisihBersih = $akB - $dbB;
                                                                $nominal = $selisihBersih > 0 ? $selisihBersih : ($akK - $dbK);
                                                                $availMonths[] = ['bulan' => $m, 'nominal' => $nominal];
                                                            }
                                                        }
                                                    @endphp
                                                    <button type="button" class="btn btn-sm btn-primary btn-aksi-sp2d-individu py-0 px-2"
                                                        data-nidn="{{ $row->NIDN }}" data-nama="{{ $row->Nama }}" data-jenis="lebih" data-bulan="{{ json_encode($availMonths) }}" title="Input Pembayaran NIDN: {{ $row->NIDN }}" style="font-size:11px;">
                                                        <i class="bx bx-edit-alt"></i> Pembayaran
                                                    </button>
                                                    @else
                                                    <span class="badge bg-label-success d-inline-flex align-items-center" style="font-size:10px;padding:3px 8px;" title="Tidak ada selisih">
                                                        <i class="bx bx-check me-1"></i> Selesai
                                                    </span>
                                                    @endif
                                                </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="66" class="text-center">Tidak ada data lebih bayar ditemukan</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            @if($lebihRows instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-4 d-flex justify-content-end">
            {{ $lebihRows->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    @endif
                        </div>
                    </div>
                </div>

                {{-- TAB 3: DATA SELESAI (LUNAS) --}}
                <div class="tab-pane fade" id="tab-data-selesai" role="tabpanel" tabindex="0">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Data Selesai</h5>
                            <div class="d-flex align-items-center gap-2">
                                <form action="{{ route('admin.kekurangan-bayar.sync-all-lunas') }}" method="POST" class="m-0 me-2 d-inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Sinkronkan ulang nilai selisih kotor/pajak/bersih ke tabel transaksi untuk semua dosen di tab ini">
                                        <i class="bx bx-sync"></i> Sinkronisasi ke Transaksi
                                    </button>
                                </form>
                                <form action="" method="GET" class="m-0 d-flex gap-2">
                                    @if(request('kurang_page')) <input type="hidden" name="kurang_page" value="{{ request('kurang_page') }}"> @endif
                                    @if(request('search_kurang')) <input type="hidden" name="search_kurang" value="{{ request('search_kurang') }}"> @endif
                                    @if(request('lebih_page')) <input type="hidden" name="lebih_page" value="{{ request('lebih_page') }}"> @endif
                                    @if(request('search_lebih')) <input type="hidden" name="search_lebih" value="{{ request('search_lebih') }}"> @endif
                                    <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                                        <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                        <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                                        <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                                    </select>
                                    <input type="text" name="search_selesai" class="form-control form-control-sm" placeholder="Cari NIDN / Nama..." value="{{ request('search_selesai') }}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bx bx-search"></i></button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th rowspan="3" class="text-center align-middle">No</th>
                                            <th rowspan="3" class="text-center align-middle">NIDN / NUPTK</th>
                                            <th rowspan="3" class="text-center align-middle">Nama</th>
                                            <th rowspan="3" class="text-center align-middle">Status Selisih</th>
                                            <th rowspan="3" class="text-center align-middle">Jenis</th>
                                            <th rowspan="3" class="text-center align-middle">Jabatan</th>
                                            <th rowspan="3" class="text-center align-middle">Status</th>
                                            <th rowspan="3" class="text-center align-middle">Bank</th>
                                            <th colspan="48" class="text-center">Januari - Desember</th>
                                            <th colspan="11" class="text-center">Jumlah Kotor, Nilai Pajak, dan Bersih</th>
                                            <th rowspan="3" class="text-center align-middle">Aksi</th>
                                        </tr>
                                        <tr>
                                            <th colspan="2" class="text-center">Jan</th><th colspan="2" class="text-center">Jan (Aktual)</th>
                                            <th colspan="2" class="text-center">Feb</th><th colspan="2" class="text-center">Feb (Aktual)</th>
                                            <th colspan="2" class="text-center">Mar</th><th colspan="2" class="text-center">Mar (Aktual)</th>
                                            <th colspan="2" class="text-center">Apr</th><th colspan="2" class="text-center">Apr (Aktual)</th>
                                            <th colspan="2" class="text-center">Mei</th><th colspan="2" class="text-center">Mei (Aktual)</th>
                                            <th colspan="2" class="text-center">Jun</th><th colspan="2" class="text-center">Jun (Aktual)</th>
                                            <th colspan="2" class="text-center">Jul</th><th colspan="2" class="text-center">Jul (Aktual)</th>
                                            <th colspan="2" class="text-center">Ags</th><th colspan="2" class="text-center">Ags (Aktual)</th>
                                            <th colspan="2" class="text-center">Sep</th><th colspan="2" class="text-center">Sep (Aktual)</th>
                                            <th colspan="2" class="text-center">Okt</th><th colspan="2" class="text-center">Okt (Aktual)</th>
                                            <th colspan="2" class="text-center">Nov</th><th colspan="2" class="text-center">Nov (Aktual)</th>
                                            <th colspan="2" class="text-center">Des</th><th colspan="2" class="text-center">Des (Aktual)</th>
                                            <th colspan="2" class="text-center">Jumlah Kotor</th>
                                            <th colspan="2" class="text-center">Nilai Pajak</th>
                                            <th rowspan="2" class="text-center align-middle">Bersih</th>
                                            <th colspan="2" class="text-center">Jumlah Kotor (Aktual)</th>
                                            <th colspan="2" class="text-center">Nilai Pajak (Aktual)</th>
                                            <th rowspan="2" class="text-center align-middle">Bersih (Aktual)</th>
                                            <th rowspan="2" class="text-center align-middle">Kesimpulan</th>
                                        </tr>
                                        <tr>
                                            @for ($i = 1; $i <= 12; $i++)
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            @endfor
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                            <th class="text-center">TPD</th><th class="text-center">TKGB</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($selesaiRows as $row)
                                        @php
                                        $status = $row->Aktif == 1 ? 'Aktif' : 'Tidak Aktif';
                                        $kesimpulan = ((float) ($row->bersih ?? 0)) - ((float) ($row->bersih_akt ?? 0));
                                        $clsKesimpulan = $kesimpulan == 0.0 ? '' : ($kesimpulan > 0 ? 'table-success' : 'table-danger');
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ !empty($row->NIDN) ? $row->NIDN : ($row->NUPTK ?? '-') }}</td>
                                            <td>{{ $row->Nama }}</td>
                                            @php
                                                $deltaBersih = (float)($row->delta_bersih ?? 0);
                                                if ($deltaBersih == 0) {
                                                    // Fallback: Jika data asli (K_TPD/L_TPD) terhapus tapi riwayat pembayaran (PEMBAYARAN_) masih ada
                                                    $totalPembayaran = (float)($row->total_pembayaran ?? 0);
                                                    $deltaBersih = $totalPembayaran * -1;
                                                }

                                                if ($deltaBersih < 0) {
                                                    $badgeStatus = '<span class="badge bg-danger" style="font-size:10px;padding:3px 8px;">Kurang</span><br><small class="text-danger fw-bold mt-1 d-block" style="font-size:11px; white-space:nowrap;">Rp ' . number_format(abs($deltaBersih), 0, ',', '.') . '</small>';
                                                } elseif ($deltaBersih > 0) {
                                                    $badgeStatus = '<span class="badge bg-success" style="font-size:10px;padding:3px 8px;">Lebih</span><br><small class="text-success fw-bold mt-1 d-block" style="font-size:11px; white-space:nowrap;">Rp ' . number_format(abs($deltaBersih), 0, ',', '.') . '</small>';
                                                } else {
                                                    $badgeStatus = '-';
                                                }
                                            @endphp
                                            <td class="text-center">{!! $badgeStatus !!}</td>
                                            <td>{{ $row->Jenis }}</td>
                                            <td>{{ $row->Jabatan12 }}</td>
                                            <td>{{ $status }}</td>
                                            <td>{{ $row->Bank ?? '-' }}</td>
                                                @for ($i = 1; $i <= 12; $i++)
                                                @php
                                                    $dbTpd = $row->{'db_tpd'.$i} ?? 0;
                                                    $dbTkgb = $row->{'db_tkgb'.$i} ?? 0;
                                                    $aktTpd = $row->{'exp_tpd'.$i} ?? 0;
                                                    $aktTkgb = $row->{'exp_tkgb'.$i} ?? 0;
                                                    $clsTpd = $diffBgClass($dbTpd, $aktTpd);
                                                    $clsTkgb = $diffBgClass($dbTkgb, $aktTkgb);
                                                @endphp
                                                <td class="{{ $clsTpd }}">{{ $formatInt($dbTpd) }}</td>
                                                <td class="{{ $clsTkgb }}">{{ $formatInt($dbTkgb) }}</td>
                                                <td>{{ $formatInt($aktTpd) }}</td>
                                                <td>{{ $formatInt($aktTkgb) }}</td>
                                                @endfor
                                                @php
                                                    $clsSumTpd = $diffBgClass($row->jml_tpd ?? 0, $row->jml_tpd_akt ?? 0);
                                                    $clsSumTkgb = $diffBgClass($row->jml_tkgb ?? 0, $row->jml_tkgb_akt ?? 0);
                                                    $clsPjkTpd = $diffBgClass($row->nilai_pjk_tpd ?? 0, $row->nilai_pjk_tpd_akt ?? 0);
                                                    $clsPjkTkgb = $diffBgClass($row->nilai_pjk_tkgb ?? 0, $row->nilai_pjk_tkgb_akt ?? 0);
                                                    $clsBersih = $diffBgClass($row->bersih ?? 0, $row->bersih_akt ?? 0);
                                                @endphp
                                                <td class="{{ $clsSumTpd }}">{{ $formatInt($row->jml_tpd ?? 0) }}</td>
                                                <td class="{{ $clsSumTkgb }}">{{ $formatInt($row->jml_tkgb ?? 0) }}</td>
                                                <td class="{{ $clsPjkTpd }}">{{ $formatInt($row->nilai_pjk_tpd ?? 0) }}</td>
                                                <td class="{{ $clsPjkTkgb }}">{{ $formatInt($row->nilai_pjk_tkgb ?? 0) }}</td>
                                                <td class="{{ $clsBersih }}">{{ $formatInt($row->bersih ?? 0) }}</td>
                                                <td>{{ $formatInt($row->jml_tpd_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->jml_tkgb_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->nilai_pjk_tpd_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->nilai_pjk_tkgb_akt ?? 0) }}</td>
                                                <td>{{ $formatInt($row->bersih_akt ?? 0) }}</td>
                                                <td class="{{ $clsKesimpulan }}">{{ $formatInt($kesimpulan) }}</td>
                                                <td class="text-center align-middle">
                                                    @if(in_array($row->NIDN, $processedRekapNidnsLebih ?? []))
                                                    <span class="badge bg-label-info d-inline-flex align-items-center" style="font-size:10px;padding:3px 8px;" title="Sudah diproses SP2D melalui Rekap">
                                                        <i class="bx bx-check-circle me-1"></i> SP2D
                                                    </span>
                                                    @elseif($kesimpulan != 0)
                                                    @php
                                                        $availMonths = [];
                                                        for($m=1; $m<=12; $m++) {
                                                            $dbK = ($row->{'db_tpd'.$m} ?? 0) + ($row->{'db_tkgb'.$m} ?? 0);
                                                            $akK = ($row->{'exp_tpd'.$m} ?? 0) + ($row->{'exp_tkgb'.$m} ?? 0);
                                                            // Lebih bayar: aktual yang dibayar (ak) > hak (db)
                                                            if($akK > $dbK) {
                                                                $dbB = $row->{'db_bersih'.$m} ?? 0;
                                                                $akB = $row->{'akt_bersih'.$m} ?? 0;
                                                                $selisihBersih = $akB - $dbB;
                                                                $nominal = $selisihBersih > 0 ? $selisihBersih : ($akK - $dbK);
                                                                $availMonths[] = ['bulan' => $m, 'nominal' => $nominal];
                                                            }
                                                        }
                                                    @endphp
                                                    <button type="button" class="btn btn-sm btn-primary btn-aksi-sp2d-individu py-0 px-2"
                                                        data-nidn="{{ $row->NIDN }}" data-nama="{{ $row->Nama }}" data-jenis="lebih" data-bulan="{{ json_encode($availMonths) }}" title="Input Pembayaran NIDN: {{ $row->NIDN }}" style="font-size:11px;">
                                                        <i class="bx bx-edit-alt"></i> Pembayaran
                                                    </button>
                                                    @else
                                                    <span class="badge bg-label-success d-inline-flex align-items-center" style="font-size:10px;padding:3px 8px;" title="Tidak ada selisih">
                                                        <i class="bx bx-check me-1"></i> Selesai
                                                    </span>
                                                    @endif
                                                </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="67" class="text-center">Tidak ada data selesai (lunas) ditemukan</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            @if($selesaiRows instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-4 d-flex justify-content-end">
            {{ $selesaiRows->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    @endif
                        </div>
                    </div>
                </div>

                {{-- TAB 3: REKAP GABUNGAN --}}
                <div class="tab-pane fade" id="tab-rekap" role="tabpanel" tabindex="0">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Rekap Kurang & Lebih Bayar</h5>
                            <form action="{{ route('admin.kekurangan-bayar.destroy-semua-rekap') }}" method="POST" class="d-inline form-hapus-semua-rekap">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-dark btn-hapus-semua-rekap" title="Hapus Semua Rekap beserta Data Dosen">
                                    <span class="tf-icons bx bx-trash"></span>&nbsp; Hapus Semua Rekap
                                </button>
                            </form>
                        </div>
                        <div class="card-body pt-2">
                            <style>
                                .rekap-tbl { font-size: 12px; line-height: 1.35; }
                                .rekap-tbl th, .rekap-tbl td { padding: 4px 8px !important; vertical-align: middle; }
                                .rekap-tbl th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; }
                                .badge-kurang { background-color: #d74d4dff; color: #fff; font-size: 10.5px; padding: 3px 8px; border-radius: 4px; }
                                .badge-lebih { background-color: #34cc4bff; color: #fff; font-size: 10.5px; padding: 3px 8px; border-radius: 4px; }
                            </style>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-bordered table-sm rekap-tbl mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th class="text-center">Jenis</th>
                                            <th class="text-center">Periode</th>
                                            <th class="text-center">Pegawai</th>
                                            <th class="text-center">Tipe</th>
                                            <th class="text-center">PNS/Non PNS</th>
                                            <th class="text-center">Bank</th>
                                            <th class="text-center">Total Pembayaran</th>
                                            <th class="text-center">Tanggal Rekap</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($allRekap as $rekap)
                                        @php
                                            $periodeText = strtolower(trim($rekap->periode ?? ''));
                                            $isKurangRekap = (strpos($periodeText, 'kurang') !== false);
                                            $isLebihRekap = (strpos($periodeText, 'lebih') !== false);
                                            $sudahSp2d = !empty($rekap->sp2d);
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-center">
                                                @if($isKurangRekap)
                                                    <span class="badge-kurang">Kurang</span>
                                                @elseif($isLebihRekap)
                                                    <span class="badge-lebih">Lebih</span>
                                                @else
                                                    <span class="badge bg-secondary" style="font-size:10.5px;">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $rekap->periode }}</td>
                                            <td class="text-center">{{ $rekap->pegawai }}</td>
                                            <td class="text-center">{{ $rekap->tipe }}</td>
                                            <td class="text-center">{{ $rekap->jenis ?? '-' }}</td>
                                            <td>{{ $rekap->bank }}</td>
                                            <td class="text-end fw-semibold">Rp {{ number_format((float) ($rekap->total_nominal ?? 0), 0, ',', '.') }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($rekap->created_at)->format('d-M-Y H:i') }}</td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center flex-nowrap">
                                                    @if(!empty($rekap->pdf) && ($u = $rekapAssetUrl($rekap->pdf)))
                                                    <a href="{{ $u }}" class="btn btn-sm btn-danger py-0 px-2" target="_blank" title="Download PDF" style="font-size:11px;">PDF</a>
                                                    @endif
                                                    @if(!empty($rekap->excel) && ($u = $rekapAssetUrl($rekap->excel)))
                                                    <a href="{{ $u }}" class="btn btn-sm btn-success py-0 px-2" target="_blank" title="Download XLSX" style="font-size:11px;">XLSX</a>
                                                    @endif
                                                    
                                                    <a href="{{ route('admin.kekurangan-bayar.detail-rekap', $rekap->id) }}" class="btn btn-sm btn-info py-0 px-2" title="Lihat detail dan pilah dosen" style="font-size:11px;">
                                                        <i class="bx bx-list-ul"></i> Detail
                                                    </a>

                                                    @if(!$isLebihRekap)
                                                        @if($sudahSp2d)
                                                            <span class="badge bg-label-info d-inline-flex align-items-center" style="font-size:10px;padding:3px 8px;" title="SP2D: {{ $rekap->sp2d }}">
                                                                <i class="bx bx-check-circle me-1"></i> SP2D
                                                            </span>
                                                        @else
                                                            <button type="button"
                                                                class="btn btn-sm btn-primary btn-aksi-sp2d py-0 px-2"
                                                                data-rekap-id="{{ $rekap->id }}"
                                                                data-rekap-periode="{{ $rekap->periode }}"
                                                                title="Input No SP2D"
                                                                style="font-size:11px;">
                                                                <i class="bx bx-edit-alt"></i> Proses
                                                            </button>
                                                        @endif
                                                    @endif

                                                    <form action="{{ route('admin.kekurangan-bayar.destroy-rekap-single', $rekap->id) }}" method="POST" class="d-inline form-hapus-rekap">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-rekap" title="Hapus Rekap & Data Dosen" style="font-size:11px;">
                                                            <i class="bx bx-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center">Belum ada data rekap yang diproses.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

{{-- Modal Input SP2D --}}
<div class="modal fade" id="modalSp2d" tabindex="-1" aria-labelledby="modalSp2dLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSp2dLabel">Input No SP2D</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <span class="text-muted small">Rekap:</span>
                    <strong id="sp2dRekapPeriode" class="d-block"></strong>
                </div>
                <div class="alert alert-warning small py-2 mb-3">
                    <i class="bx bx-info-circle me-1"></i>
                    <span id="sp2dWarningText">Data SP2D hanya bisa diinput <b>satu kali</b>. Pastikan nomor dan tanggal sudah benar.</span>
                </div>
                <input type="hidden" id="sp2dRekapId">
                <div class="mb-3">
                    <label for="sp2dUraian" class="form-label">Uraian Pembayaran <span class="text-muted">(Opsional)</span></label>
                    <input type="text" class="form-control" id="sp2dUraian" placeholder="Cth: Pembayaran kekurangan bayar">
                </div>
                <div class="mb-3">
                    <label id="lblSp2dNomor" for="sp2dNomor" class="form-label">No SP2D <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="sp2dNomor" placeholder="Masukkan No SP2D" required>
                </div>
                <div class="mb-3">
                    <label id="lblSp2dTanggal" for="sp2dTanggal" class="form-label">Tanggal SP2D <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="sp2dTanggal" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSubmitSp2d">
                    <span class="tf-icons bx bx-send"></span>&nbsp; Proses SP2D
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Input SP2D Individu (Kurang Bayar) --}}
<div class="modal fade" id="modalSp2dIndividu" tabindex="-1" aria-labelledby="modalSp2dIndividuLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSp2dIndividuLabel">Input SP2D Individu (Kurang Bayar)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <span class="text-muted small">NIDN - Nama:</span>
                    <strong id="sp2dIndividuNidnLabel" class="d-block"></strong>
                </div>
                <div class="alert alert-warning small py-2 mb-3">
                    <i class="bx bx-info-circle me-1"></i>
                    <span id="sp2dIndividuWarningText">Data SP2D ini akan disimpan untuk dosen dengan NIDN di atas secara spesifik.</span>
                </div>
                <input type="hidden" id="sp2dIndividuNidn">
                <input type="hidden" id="sp2dIndividuJenis" value="kurang">
                
                <div class="mb-3">
                    <label for="sp2dIndividuUraian" class="form-label">Uraian Pembayaran <span class="text-muted">(Opsional)</span></label>
                    <input type="text" class="form-control" id="sp2dIndividuUraian" placeholder="Cth: Pembayaran kekurangan bayar tahun 2026">
                </div>
                <div class="mb-3">
                    <label id="lblSp2dIndividuNomor" for="sp2dIndividuNomor" class="form-label">No SP2D <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="sp2dIndividuNomor" placeholder="Masukkan No SP2D" required>
                </div>
                <div class="mb-3">
                    <label id="lblSp2dIndividuTanggal" for="sp2dIndividuTanggal" class="form-label">Tanggal SP2D <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="sp2dIndividuTanggal" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSubmitSp2dIndividu">
                    <span class="tf-icons bx bx-send"></span>&nbsp; Proses SP2D
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Input NTPN Individu (Lebih Bayar) --}}
<div class="modal fade" id="modalNtpnIndividu" tabindex="-1" aria-labelledby="modalNtpnIndividuLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNtpnIndividuLabel">Input Data Pembayaran (Lebih Bayar)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <span class="text-muted small">NIDN - Nama:</span>
                    <strong id="ntpnIndividuNidnLabel" class="d-block"></strong>
                </div>
                <div class="alert alert-warning small py-2 mb-3">
                    <i class="bx bx-info-circle me-1"></i>
                    <span>Data ini akan disimpan untuk dosen dengan NIDN di atas secara spesifik.</span>
                </div>
                <input type="hidden" id="ntpnIndividuNidn">
                <input type="hidden" id="ntpnIndividuJenis" value="lebih">
                <input type="hidden" id="ntpnIndividuJenisTransaksi" value="">

                <div class="mb-3" id="wrapperNtpnIndividuNomor">
                    <label id="lblNtpnIndividuNomor" for="ntpnIndividuNomor" class="form-label">No Bukti <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="ntpnIndividuNomor" placeholder="Masukkan Nomor">
                </div>
                
                <div class="mb-3">
                    <label id="lblNtpnIndividuBulan" for="ntpnIndividuBulan" class="form-label">Bulan <span class="text-danger">*</span></label>
                    <select class="form-select" id="ntpnIndividuBulan" required>
                        <option value="">-- Pilih Bulan --</option>
                    </select>
                    <small class="text-muted">Bulan yang muncul hanya bulan yang lebih saja.</small>
                </div>

                <div class="mb-3">
                    <label id="lblNtpnIndividuNominal" for="ntpnIndividuNominal" class="form-label">Dibayar Berapa (Nominal Cicilan) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" class="form-control" id="ntpnIndividuNominal" placeholder="Cth: 1500000" required>
                    </div>
                    <small class="text-muted">Masukkan angka saja. Bisa untuk pembayaran penuh atau cicilan.</small>
                </div>

                <div class="mb-3">
                    <label id="lblNtpnIndividuTanggal" for="ntpnIndividuTanggal" class="form-label">Tanggal Bukti <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="ntpnIndividuTanggal" required>
                </div>

                <div class="mb-3">
                    <label id="lblNtpnIndividuUraian" for="ntpnIndividuUraian" class="form-label">Catatan Khusus (Uraian Pembayaran) <span class="text-muted">(Opsional)</span></label>
                    <input type="text" class="form-control" id="ntpnIndividuUraian" placeholder="Cth: Pembayaran kelebihan bayar">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSubmitNtpnIndividu">
                    <span class="tf-icons bx bx-send"></span>&nbsp; Proses
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Riwayat Pembayaran --}}
<div class="modal fade" id="modalEditRiwayat" tabindex="-1" aria-labelledby="modalEditRiwayatLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditRiwayatLabel">Edit Riwayat Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <span class="text-muted small">NIDN - Nama:</span>
                    <strong id="editRiwayatNidnLabel" class="d-block"></strong>
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-sm" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th>Nominal Bersih</th>
                                <th>Uraian</th>
                                <th>No Bukti/SP2D</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRiwayat">
                            <tr><td colspan="6" class="text-center">Memuat data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnProses = document.getElementById('btnProsesKekurangan');
        const btnCekData = document.getElementById('btnCekDataKekurangan');
        const formProses = document.getElementById('formProsesKekurangan');

        const formDestroyKurang = document.getElementById('formDestroyKurang');
        const formDestroyLebih = document.getElementById('formDestroyLebih');

        const alertApi = window.SptjmAlert;

        // TAMBAHAN: MENGINGAT POSISI TAB SAAT RELOAD
        const tabElements = document.querySelectorAll('button[data-bs-toggle="tab"]');
        tabElements.forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                localStorage.setItem('activeKekuranganTab', event.target.id);
            });
        });

        const urlParams = new URLSearchParams(window.location.search);
        let activeTabId = null;
        
        if (urlParams.has('lebih_page')) {
            activeTabId = 'tab-lebih-btn';
        } else if (urlParams.has('kurang_page')) {
            activeTabId = 'tab-kurang-btn';
        } else {
            activeTabId = localStorage.getItem('activeKekuranganTab');
        }

        if (activeTabId) {
            const tabToActivate = document.getElementById(activeTabId);
            if (tabToActivate) tabToActivate.click();
        }
        // ==========================================

        const getSelectedText = (sel) => {
            if (!sel) return '';
            const opt = sel.options[sel.selectedIndex];
            return opt ? opt.text : '';
        };

        const buildSummaryHtml = () => {
            const periode = document.querySelector('select[name="periode"]');
            const tipe = document.querySelector('select[name="tipe"]');
            const jenis = document.querySelector('select[name="jenis"]');
            const bank = document.querySelector('select[name="bank"]');

            if (!periode || !tipe || !jenis || !bank) return null;

            return `
                <div class="text-start">
                  <div>Periode: <b>${getSelectedText(periode)}</b></div>
                  <div>Tipe: <b>${getSelectedText(tipe)}</b></div>
                  <div>Jenis: <b>${getSelectedText(jenis)}</b></div>
                  <div>Bank: <b>${getSelectedText(bank)}</b></div>
                </div>
            `;
        };

        const showFlash = () => {
            const validationErrors = @json($errors->all());
            const flash = {
                success: @json(session('success')),
                error: @json(session('error')),
                warning: @json(session('warning')),
                info: @json(session('info') ?? ($flashInfo ?? null)),
            };

            if (!alertApi) {
                const msg = (validationErrors && validationErrors.length)
                    ? validationErrors.join('\n')
                    : (flash.error || flash.warning || flash.success || flash.info);
                if (msg) {
                    try { alert(String(msg)); } catch (e) {}
                }
                return;
            }

            if (validationErrors && validationErrors.length) {
                alertApi.warning('Validasi', validationErrors.join('<br>'));
                return;
            }

            if (flash.error) return alertApi.error('Gagal', String(flash.error));
            if (flash.warning) return alertApi.warning('Peringatan', String(flash.warning));
            if (flash.success) return alertApi.success('Berhasil', String(flash.success));
            if (flash.info) return alertApi.info('Info', String(flash.info));
        };

        showFlash();

        try {
            const _flashInfo = @json($flashInfo ?? null);
            if (_flashInfo && typeof _flashInfo === 'string' && _flashInfo.toLowerCase().includes('cek data')) {
                // Cegah pindah tab jika user sengaja di tab lain
                if (!urlParams.has('lebih_page') && !urlParams.has('kurang_page')) {
                     const tabBtn = document.getElementById('tab-kurang-btn');
                     if (tabBtn && localStorage.getItem('activeKekuranganTab') !== 'tab-lebih-btn') tabBtn.click();
                }
            }
        } catch (e) {}

        const confirmAndSubmit = async (actionUrl, title, html, confirmText) => {
            if (!formProses) return;

            if (!actionUrl) {
                if (alertApi) {
                    await alertApi.warning('Peringatan', 'Form tidak lengkap. Silakan refresh halaman.');
                }
                return;
            }

            if (alertApi) {
                const r = await alertApi.question(title, html, {
                    confirmButtonText: confirmText || 'Ya',
                });
                if (!r || !r.isConfirmed) return;
                formProses.setAttribute('action', actionUrl);
                formProses.submit();
                return;
            }

            if (confirm(title)) {
                formProses.setAttribute('action', actionUrl);
                formProses.submit();
            }
        };

        if (btnProses && formProses) {
            btnProses.addEventListener('click', async function () {
                const summary = buildSummaryHtml();
                if (!summary) {
                    if (alertApi) await alertApi.warning('Peringatan', 'Form tidak lengkap. Silakan refresh halaman.');
                    return;
                }

                const html = `
                    <div class="text-start">
                      <div class="mb-2">Proses akan menghitung dan menyimpan data kurang/lebih bayar untuk Tahun <b>{{ $versi }}</b>.</div>
                      ${summary}
                      <div class="mt-3 alert alert-warning mb-0">Bulan dianggap dibayar jika <b>No SP2D</b> dan <b>Tgl SP2D</b> terisi.</div>
                    </div>
                `;
                await confirmAndSubmit("{{ route('admin.kekurangan-bayar.proses') }}", 'Konfirmasi Proses', html, 'Ya, Proses');
            });
        }

        if (btnCekData && formProses) {
            btnCekData.addEventListener('click', async function () {
                const summary = buildSummaryHtml();
                if (!summary) {
                    if (alertApi) await alertApi.warning('Peringatan', 'Form tidak lengkap. Silakan refresh halaman.');
                    return;
                }

                const html = `
                    <div class="text-start">
                      <div class="mb-2">Cek Data akan menghitung data seperti Proses, namun <b>tanpa menyimpan</b> ke database.</div>
                      ${summary}
                      <div class="mt-3 alert alert-info mb-0">Jika hasil sudah benar, klik <b>Proses</b> untuk menyimpan.</div>
                    </div>
                `;
                await confirmAndSubmit("{{ route('admin.kekurangan-bayar.cek') }}", 'Konfirmasi Cek Data', html, 'Ya, Cek Data');
            });
        }

        const wireDeleteConfirm = (form, title, text) => {
            if (!form) return;
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (alertApi) {
                    const r = await alertApi.question(title, text, { confirmButtonText: 'Ya, Hapus' });
                    if (r && r.isConfirmed) form.submit();
                    return;
                }
                if (confirm(text)) form.submit();
            });
        };

        wireDeleteConfirm(formDestroyKurang, 'Konfirmasi', 'Yakin ingin menghapus data Kurang Bayar pada tahun ini?');
        wireDeleteConfirm(formDestroyLebih, 'Konfirmasi', 'Yakin ingin menghapus data Lebih Bayar pada tahun ini?');

        // Helper date
        const getTodayDate = () => {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        };

        // SP2D MODAL HANDLERS
        const modalSp2dEl = document.getElementById('modalSp2d');
        const sp2dModal = modalSp2dEl ? new bootstrap.Modal(modalSp2dEl) : null;

        // Bind all Aksi buttons
        document.querySelectorAll('.btn-aksi-sp2d').forEach(btn => {
            btn.addEventListener('click', function () {
                const rekapId = this.dataset.rekapId;
                const rekapPeriode = this.dataset.rekapPeriode;
                const isLebih = (rekapPeriode || '').toLowerCase().includes('lebih');
                const labelText = isLebih ? 'NTPN' : 'SP2D';

                document.getElementById('sp2dRekapId').value = rekapId;
                document.getElementById('sp2dRekapPeriode').textContent = rekapPeriode;
                document.getElementById('sp2dUraian').value = '';
                document.getElementById('sp2dNomor').value = '';
                document.getElementById('sp2dTanggal').value = getTodayDate();
                
                // Set Labels dynamically
                document.getElementById('modalSp2dLabel').textContent = `Input No ${labelText}`;
                document.getElementById('lblSp2dNomor').innerHTML = `No ${labelText} <span class="text-danger">*</span>`;
                document.getElementById('sp2dNomor').placeholder = `Masukkan No ${labelText}`;
                document.getElementById('lblSp2dTanggal').innerHTML = `Tanggal ${labelText} <span class="text-danger">*</span>`;
                document.getElementById('sp2dWarningText').innerHTML = `Data ${labelText} hanya bisa diinput <b>satu kali</b>. Pastikan nomor dan tanggal sudah benar.`;
                document.getElementById('btnSubmitSp2d').innerHTML = `<span class="tf-icons bx bx-send"></span>&nbsp; Proses ${labelText}`;
                document.getElementById('btnSubmitSp2d').dataset.labelText = labelText;

                if (sp2dModal) sp2dModal.show();
            });
        });

        // Submit SP2D
        const btnSubmitSp2d = document.getElementById('btnSubmitSp2d');
        if (btnSubmitSp2d) {
            btnSubmitSp2d.addEventListener('click', async function () {
                const rekapId = document.getElementById('sp2dRekapId').value;
                const uraianPembayaran = document.getElementById('sp2dUraian').value.trim();
                const noSp2d = document.getElementById('sp2dNomor').value.trim();
                const tglSp2d = document.getElementById('sp2dTanggal').value;

                const labelText = btnSubmitSp2d.dataset.labelText || 'SP2D';

                if (!noSp2d || !tglSp2d) {
                    // Sembunyikan modal dulu agar peringatan tampil di depan
                    if (sp2dModal) sp2dModal.hide();
                    if (alertApi) {
                        await alertApi.warning('Peringatan', `No ${labelText} dan Tanggal wajib diisi.`);
                    } else {
                        alert(`No ${labelText} dan Tanggal wajib diisi.`);
                    }
                    // Tampilkan kembali modal
                    if (sp2dModal) sp2dModal.show();
                    return;
                }

                // Sembunyikan modal agar dialog konfirmasi tampil di depan
                if (sp2dModal) sp2dModal.hide();

                // Konfirmasi sebelum submit
                const konfHtml = `
                    <div class="text-start">
                        <div>No ${labelText}: <b>${noSp2d}</b></div>
                        <div>Tanggal: <b>${tglSp2d}</b></div>
                        <div class="mt-2 alert alert-warning mb-0 small">Data ${labelText} tidak bisa diubah setelah diproses.</div>
                    </div>
                `;

                let confirmed = false;
                if (alertApi) {
                    const r = await alertApi.question(`Konfirmasi Proses ${labelText}`, konfHtml, { confirmButtonText: 'Ya, Proses' });
                    confirmed = r && r.isConfirmed;
                } else {
                    confirmed = confirm(`Yakin ingin memproses ${labelText} ini? Data tidak bisa diubah setelahnya.`);
                }

                if (!confirmed) {
                    // Jika user batal, tampilkan kembali modal
                    if (sp2dModal) sp2dModal.show();
                    return;
                }

                // Disable button
                btnSubmitSp2d.disabled = true;
                btnSubmitSp2d.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content
                               || document.querySelector('input[name="_token"]')?.value;

                    const response = await fetch("{{ route('admin.kekurangan-bayar.aksi-sp2d') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            rekap_id: rekapId,
                            no_sp2d: noSp2d,
                            tanggal_sp2d: tglSp2d,
                            uraian_pembayaran: uraianPembayaran,
                        }),
                    });

                    const data = await response.json();

                    if (data.success) {
                        if (sp2dModal) sp2dModal.hide();
                        if (alertApi) {
                            await alertApi.success('Berhasil', data.message || `${labelText} berhasil diproses.`);
                        } else {
                            alert(data.message || `${labelText} berhasil diproses.`);
                        }
                        // Reload halaman untuk refresh data
                        window.location.reload();
                    } else {
                        if (alertApi) {
                            await alertApi.error('Gagal', data.message || 'Terjadi kesalahan.');
                        } else {
                            alert(data.message || 'Terjadi kesalahan.');
                        }
                    }
                } catch (err) {
                    console.error('SP2D submit error:', err);
                    if (alertApi) {
                        await alertApi.error('Error', 'Gagal menghubungi server. Silakan coba lagi.');
                    } else {
                        alert('Gagal menghubungi server.');
                    }
                } finally {
                    btnSubmitSp2d.disabled = false;
                    btnSubmitSp2d.innerHTML = `<span class="tf-icons bx bx-send"></span>&nbsp; Proses ${labelText}`;
                }
            });
        }
        // SP2D INDIVIDU MODAL HANDLERS
        const modalSp2dIndividuEl = document.getElementById('modalSp2dIndividu');
        const sp2dIndividuModal = modalSp2dIndividuEl ? new bootstrap.Modal(modalSp2dIndividuEl) : null;

        const modalNtpnIndividuEl = document.getElementById('modalNtpnIndividu');
        const ntpnIndividuModal = modalNtpnIndividuEl ? new bootstrap.Modal(modalNtpnIndividuEl) : null;

        document.querySelectorAll('.btn-aksi-sp2d-individu').forEach(btn => {
            btn.addEventListener('click', async function () {
                const nidn = this.dataset.nidn;
                const nama = this.dataset.nama;
                const jenis = this.dataset.jenis;
                const isLebih = (jenis === 'lebih');

                if (isLebih) {
                    let trxType = '';
                    if (alertApi && typeof alertApi.close === 'function') {
                        await alertApi.close(); 
                    }
                    if (window.Swal) {
                        const result = await window.Swal.fire({
                            title: 'Pilih Jenis Transaksi',
                            text: 'Apakah transaksi ini berupa Pemotongan atau Pengembalian?',
                            icon: 'question',
                            showCancelButton: true,
                            showDenyButton: true,
                            confirmButtonText: 'Pemotongan',
                            denyButtonText: 'Pengembalian',
                            cancelButtonText: 'Batal',
                            customClass: {
                                confirmButton: 'btn btn-primary',
                                denyButton: 'btn btn-info ms-2',
                                cancelButton: 'btn btn-outline-secondary ms-2',
                            },
                            buttonsStyling: false
                        });

                        if (result.isConfirmed) {
                            trxType = 'Pemotongan';
                        } else if (result.isDenied) {
                            trxType = 'Pengembalian';
                        } else {
                            return;
                        }
                    } else {
                        const isPemotongan = confirm("Klik OK untuk Pemotongan, Batal untuk Pengembalian.");
                        trxType = isPemotongan ? 'Pemotongan' : 'Pengembalian';
                    }

                    document.getElementById('ntpnIndividuJenisTransaksi').value = trxType;

                    // Set labels dynamically based on trxType
                    if (trxType === 'Pemotongan') {
                        document.getElementById('wrapperNtpnIndividuNomor').style.display = 'none';
                        document.getElementById('lblNtpnIndividuBulan').innerHTML = 'Bulan <span class="text-danger">*</span>';
                        document.getElementById('lblNtpnIndividuNominal').innerHTML = 'Jumlah Potongan <span class="text-danger">*</span>';
                        document.getElementById('lblNtpnIndividuTanggal').innerHTML = 'Tgl <span class="text-danger">*</span>';
                        document.getElementById('lblNtpnIndividuUraian').innerHTML = 'Uraian Pembayaran <span class="text-muted">(Opsional)</span>';
                    } else {
                        document.getElementById('wrapperNtpnIndividuNomor').style.display = 'block';
                        document.getElementById('lblNtpnIndividuNomor').innerHTML = 'NTPN <span class="text-danger">*</span>';
                        document.getElementById('lblNtpnIndividuBulan').innerHTML = 'Bulan apa <span class="text-danger">*</span>';
                        document.getElementById('lblNtpnIndividuNominal').innerHTML = 'Dibayar Berapa (Nominal) <span class="text-danger">*</span>';
                        document.getElementById('lblNtpnIndividuTanggal').innerHTML = 'Tanggal <span class="text-danger">*</span>';
                        document.getElementById('lblNtpnIndividuUraian').innerHTML = 'Catatan Khusus (Uraian Pembayaran) <span class="text-muted">(Opsional)</span>';
                    }

                    const availMonthsStr = this.dataset.bulan || '[]';
                    let availMonths = [];
                    try {
                        availMonths = JSON.parse(availMonthsStr);
                    } catch(e) {}

                    document.getElementById('ntpnIndividuNidn').value = nidn;
                    document.getElementById('ntpnIndividuNidnLabel').textContent = nidn + ' - ' + nama + ' (LEBIH BAYAR - ' + trxType.toUpperCase() + ')';
                    document.getElementById('ntpnIndividuNomor').value = '';
                    document.getElementById('ntpnIndividuTanggal').value = getTodayDate();
                    document.getElementById('ntpnIndividuNominal').value = '';
                    document.getElementById('ntpnIndividuUraian').value = '';

                    // Populate month dropdown
                    const bulanSelect = document.getElementById('ntpnIndividuBulan');
                    bulanSelect.innerHTML = '<option value="">-- Pilih Bulan --</option>';
                    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                    availMonths.forEach(m => {
                        const opt = document.createElement('option');
                        // m bisa berupa objek {bulan: X, nominal: Y} atau angka biasa jika format lama
                        const mVal = typeof m === 'object' ? m.bulan : m;
                        const mNom = typeof m === 'object' ? m.nominal : 0;
                        opt.value = mVal;
                        opt.dataset.nominal = mNom;
                        opt.textContent = monthNames[mVal-1];
                        bulanSelect.appendChild(opt);
                    });

                    document.getElementById('btnSubmitNtpnIndividu').innerHTML = `<span class="tf-icons bx bx-send"></span>&nbsp; Proses`;

                    if (ntpnIndividuModal) ntpnIndividuModal.show();
                } else {
                    document.getElementById('sp2dIndividuNidn').value = nidn;
                    document.getElementById('sp2dIndividuNidnLabel').textContent = nidn + ' - ' + nama + ' (KURANG BAYAR)';
                    document.getElementById('sp2dIndividuNomor').value = '';
                    document.getElementById('sp2dIndividuTanggal').value = getTodayDate();
                    document.getElementById('sp2dIndividuUraian').value = '';

                    document.getElementById('btnSubmitSp2dIndividu').innerHTML = `<span class="tf-icons bx bx-send"></span>&nbsp; Proses SP2D`;

                    if (sp2dIndividuModal) sp2dIndividuModal.show();
                }
            });
        });

        // Event listener for month dropdown to auto-fill Nominal and Uraian placeholder
        const ntpnIndividuBulanEl = document.getElementById('ntpnIndividuBulan');
        if (ntpnIndividuBulanEl) {
            ntpnIndividuBulanEl.addEventListener('change', function() {
                const trxType = document.getElementById('ntpnIndividuJenisTransaksi').value;
                if (this.value) {
                    const selectedOpt = this.options[this.selectedIndex];
                    const nominal = selectedOpt.dataset.nominal || '';
                    const monthName = selectedOpt.text;
                    const prefix = trxType === 'Pemotongan' ? 'Pemotongan Kelebihan' : 'Pengembalian Pemotongan';
                    document.getElementById('ntpnIndividuUraian').placeholder = 'Cth: '+ prefix;
                    
                    if (trxType === 'Pemotongan' || trxType === 'Pengembalian') {
                        if(nominal) {
                            document.getElementById('ntpnIndividuNominal').value = nominal;
                        }
                    }
                } else {
                    document.getElementById('ntpnIndividuUraian').placeholder = 'Cth: Pemotongan Kelebihan';
                    if (trxType === 'Pemotongan' || trxType === 'Pengembalian') {
                        document.getElementById('ntpnIndividuNominal').value = '';
                    }
                }
            });
        }

        // Handler untuk Kurang Bayar (SP2D Individu)
        const btnSubmitSp2dIndividu = document.getElementById('btnSubmitSp2dIndividu');
        if (btnSubmitSp2dIndividu) {
            btnSubmitSp2dIndividu.addEventListener('click', async function () {
                const nidn = document.getElementById('sp2dIndividuNidn').value;
                const jenis = 'kurang';
                let uraianPembayaran = document.getElementById('sp2dIndividuUraian').value.trim();
                const noSp2d = document.getElementById('sp2dIndividuNomor').value.trim();
                const tglSp2d = document.getElementById('sp2dIndividuTanggal').value;

                if (uraianPembayaran === '') {
                    uraianPembayaran = 'Kekurangan Pembayaran';
                }

                if (!noSp2d || !tglSp2d) {
                    if (sp2dIndividuModal) sp2dIndividuModal.hide();
                    if (alertApi) {
                        await alertApi.warning('Peringatan', `No SP2D dan Tanggal wajib diisi.`);
                    } else {
                        alert(`No SP2D dan Tanggal wajib diisi.`);
                    }
                    if (sp2dIndividuModal) sp2dIndividuModal.show();
                    return;
                }

                if (sp2dIndividuModal) sp2dIndividuModal.hide();

                const konfHtml = `
                    <div class="text-start">
                        <p class="mb-2">Anda akan memproses <strong>SP2D</strong> untuk NIDN: <strong>${nidn}</strong>.</p>
                        <table class="table table-sm table-borderless">
                            <tr><td style="width:100px;">SP2D</td><td>: ${noSp2d}</td></tr>
                            <tr><td>Tanggal</td><td>: ${tglSp2d}</td></tr>
                        </table>
                        <div class="mt-2 alert alert-warning mb-0 small">Data SP2D tidak bisa diubah setelah diproses.</div>
                    </div>
                `;

                let confirmed = false;
                if (alertApi) {
                    const r = await alertApi.question(`Konfirmasi Proses SP2D Individu`, konfHtml, { confirmButtonText: 'Ya, Proses' });
                    confirmed = r && r.isConfirmed;
                } else {
                    confirmed = confirm(`Yakin ingin memproses data ini?\nNIDN: ${nidn}\nSP2D: ${noSp2d}`);
                }

                if (!confirmed) {
                    if (sp2dIndividuModal) sp2dIndividuModal.show();
                    return;
                }

                btnSubmitSp2dIndividu.disabled = true;
                btnSubmitSp2dIndividu.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content
                               || document.querySelector('input[name="_token"]')?.value;

                    const response = await fetch("{{ route('admin.kekurangan-bayar.aksi-sp2d') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            nidn: nidn,
                            jenis_sp2d: jenis,
                            no_sp2d: noSp2d,
                            tanggal_sp2d: tglSp2d,
                            uraian_pembayaran: uraianPembayaran,
                        }),
                    });

                    const data = await response.json();
                    if (data.success) {
                        if (alertApi) await alertApi.success('Berhasil', data.message);
                        else alert('Berhasil memproses SP2D.');
                        
                        if (data.is_lunas) {
                            const btn = document.querySelector(`button.btn-aksi-sp2d-individu[data-nidn="${nidn}"]`);
                            if (btn) {
                                const tr = btn.closest('tr');
                                if (tr) {
                                    const tdAksi = tr.querySelector('td:last-child');
                                    if (tdAksi) {
                                        tdAksi.innerHTML = '<span class="badge bg-label-success d-inline-flex align-items-center" style="font-size:10px;padding:3px 8px;" title="Tidak ada selisih"><i class="bx bx-check me-1"></i> Selesai</span>';
                                    }
                                    const selesaiTbody = document.querySelector('#tab-data-selesai tbody');
                                    if (selesaiTbody) {
                                        const emptyTr = selesaiTbody.querySelector('td[colspan="65"]');
                                        if (emptyTr) emptyTr.closest('tr').remove();
                                        selesaiTbody.appendChild(tr);
                                    }
                                }
                            }
                        } else {
                            window.location.reload();
                        }
                    } else {
                        if (alertApi) await alertApi.error('Gagal', data.message || 'Terjadi kesalahan.');
                        else alert(data.message || 'Terjadi kesalahan.');
                        if (sp2dIndividuModal) sp2dIndividuModal.show();
                    }
                } catch (err) {
                    console.error('Submit error:', err);
                    if (alertApi) await alertApi.error('Error', 'Gagal menghubungi server.');
                    else alert('Gagal menghubungi server.');
                    if (sp2dIndividuModal) sp2dIndividuModal.show();
                } finally {
                    btnSubmitSp2dIndividu.disabled = false;
                    btnSubmitSp2dIndividu.innerHTML = `<span class="tf-icons bx bx-send"></span>&nbsp; Proses SP2D`;
                }
            });
        }

        // Handler untuk Lebih Bayar (NTPN Individu)
        const btnSubmitNtpnIndividu = document.getElementById('btnSubmitNtpnIndividu');
        if (btnSubmitNtpnIndividu) {
            btnSubmitNtpnIndividu.addEventListener('click', async function () {
                const nidn = document.getElementById('ntpnIndividuNidn').value;
                const jenis = 'lebih';
                let uraianPembayaran = document.getElementById('ntpnIndividuUraian').value.trim();
                const noSp2d = document.getElementById('ntpnIndividuNomor').value.trim();
                const tglSp2d = document.getElementById('ntpnIndividuTanggal').value;
                const bulan = document.getElementById('ntpnIndividuBulan').value;
                const nominalBayar = document.getElementById('ntpnIndividuNominal').value.trim();

                const trxType = document.getElementById('ntpnIndividuJenisTransaksi').value;
                const isPemotongan = (trxType === 'Pemotongan');
                const jenisTrx = isPemotongan ? 'Pemotongan Kelebihan' : 'Pengembalian Pemotongan';
                
                if (uraianPembayaran === '') {
                    uraianPembayaran = jenisTrx;
                }

                if ((!isPemotongan && !noSp2d) || !tglSp2d || !bulan || !nominalBayar) {
                    if (ntpnIndividuModal) ntpnIndividuModal.hide();
                    if (alertApi) {
                        await alertApi.warning('Peringatan', `Semua field ber-bintang merah wajib diisi.`);
                    } else {
                        alert(`Semua field wajib diisi.`);
                    }
                    if (ntpnIndividuModal) ntpnIndividuModal.show();
                    return;
                }

                if (ntpnIndividuModal) ntpnIndividuModal.hide();

                const konfHtml = `
                    <div class="text-start">
                        <p class="mb-2">Anda akan memproses cicilan/pembayaran untuk NIDN: <strong>${nidn}</strong>.</p>
                        <table class="table table-sm table-borderless">
                            <tr><td style="width:100px;">Bulan</td><td>: ${bulan}</td></tr>
                            <tr><td>Nominal</td><td>: Rp ${new Intl.NumberFormat('id-ID').format(nominalBayar)}</td></tr>
                            ${!isPemotongan ? `<tr><td>No Bukti</td><td>: ${noSp2d}</td></tr>` : ''}
                            <tr><td>Tanggal</td><td>: ${tglSp2d}</td></tr>
                        </table>
                        <div class="mt-2 alert alert-warning mb-0 small">Data tidak bisa diubah setelah diproses.</div>
                    </div>
                `;

                let confirmed = false;
                if (alertApi) {
                    const r = await alertApi.question(`Konfirmasi Proses Pembayaran`, konfHtml, { confirmButtonText: 'Ya, Proses' });
                    confirmed = r && r.isConfirmed;
                } else {
                    confirmed = confirm(`Yakin ingin memproses data ini?\nNIDN: ${nidn}\nBulan: ${bulan}\nNominal: Rp ${nominalBayar}`);
                }

                if (!confirmed) {
                    if (ntpnIndividuModal) ntpnIndividuModal.show();
                    return;
                }

                btnSubmitNtpnIndividu.disabled = true;
                btnSubmitNtpnIndividu.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content
                               || document.querySelector('input[name="_token"]')?.value;

                    const response = await fetch("{{ route('admin.kekurangan-bayar.aksi-sp2d') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            nidn: nidn,
                            jenis_sp2d: jenis,
                            no_sp2d: noSp2d,
                            tanggal_sp2d: tglSp2d,
                            uraian_pembayaran: uraianPembayaran,
                            bulan: bulan,
                            nominal_bayar: nominalBayar,
                            trx_type: trxType
                        }),
                    });

                    const data = await response.json();
                    if (data.success) {
                        if (alertApi) await alertApi.success('Berhasil', data.message);
                        else alert('Berhasil memproses Pembayaran.');
                        
                        if (data.is_lunas) {
                            const btn = document.querySelector(`button.btn-aksi-sp2d-individu[data-nidn="${nidn}"]`);
                            if (btn) {
                                const tr = btn.closest('tr');
                                if (tr) {
                                    const tdAksi = tr.querySelector('td:last-child');
                                    if (tdAksi) {
                                        tdAksi.innerHTML = '<span class="badge bg-label-success d-inline-flex align-items-center" style="font-size:10px;padding:3px 8px;" title="Tidak ada selisih"><i class="bx bx-check me-1"></i> Selesai</span>';
                                    }
                                    
                                    // Inject missing "STATUS SELISIH" column to prevent DOM shifting
                                    const namaTd = tr.querySelector('td:nth-child(3)');
                                    if (namaTd) {
                                        const newTd = document.createElement('td');
                                        newTd.className = 'text-center';
                                        
                                        const isKurangTab = tr.closest('.tab-pane').id === 'tab-data-kurang';
                                        if (isKurangTab) {
                                            newTd.innerHTML = '<span class="badge bg-danger" style="font-size:10px;padding:3px 8px;">Kurang</span><br><small class="text-danger fw-bold mt-1 d-block" style="font-size:11px; white-space:nowrap;">Selesai</small>';
                                        } else {
                                            newTd.innerHTML = '<span class="badge bg-success" style="font-size:10px;padding:3px 8px;">Lebih</span><br><small class="text-success fw-bold mt-1 d-block" style="font-size:11px; white-space:nowrap;">Selesai</small>';
                                        }
                                        
                                        namaTd.after(newTd);
                                    }

                                    const selesaiTbody = document.querySelector('#tab-data-selesai tbody');
                                    if (selesaiTbody) {
                                        const emptyTr = selesaiTbody.querySelector('td[colspan="65"]');
                                        if (emptyTr) emptyTr.closest('tr').remove();
                                        selesaiTbody.appendChild(tr);
                                    }
                                }
                            }
                        } else {
                            window.location.reload();
                        }
                    } else {
                        if (alertApi) await alertApi.error('Gagal', data.message || 'Terjadi kesalahan.');
                        else alert(data.message || 'Terjadi kesalahan.');
                        if (ntpnIndividuModal) ntpnIndividuModal.show();
                    }
                } catch (err) {
                    console.error('Submit error:', err);
                    if (alertApi) await alertApi.error('Error', 'Gagal menghubungi server.');
                    else alert('Gagal menghubungi server.');
                    if (ntpnIndividuModal) ntpnIndividuModal.show();
                } finally {
                    btnSubmitNtpnIndividu.disabled = false;
                    btnSubmitNtpnIndividu.innerHTML = `<span class="tf-icons bx bx-send"></span>&nbsp; Proses`;
                }
            });
        }
        // Handler Edit Riwayat
        const editRiwayatModalEl = document.getElementById('modalEditRiwayat');
        const editRiwayatModal = editRiwayatModalEl ? new bootstrap.Modal(editRiwayatModalEl) : null;
        const tbodyRiwayat = document.getElementById('tbodyRiwayat');

        document.querySelectorAll('.btn-edit-riwayat').forEach(btn => {
            btn.addEventListener('click', async function () {
                const nidn = this.dataset.nidn;
                const nama = this.dataset.nama;
                
                document.getElementById('editRiwayatNidnLabel').textContent = `${nidn} - ${nama}`;
                tbodyRiwayat.innerHTML = '<tr><td colspan="6" class="text-center">Memuat data...</td></tr>';
                
                if (editRiwayatModal) editRiwayatModal.show();

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.content
                               || document.querySelector('input[name="_token"]')?.value;

                    const response = await fetch("{{ route('admin.kekurangan-bayar.get-riwayat') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ nidn: nidn }),
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        if (data.data.length === 0) {
                            tbodyRiwayat.innerHTML = '<tr><td colspan="6" class="text-center">Tidak ada riwayat pembayaran.</td></tr>';
                            return;
                        }
                        
                        let html = '';
                        const months = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
                        
                        data.data.forEach(r => {
                            html += `
                                <tr id="row-riwayat-${r.id}">
                                    <td class="align-middle text-center">${months[parseInt(r.bulan)-1] || r.bulan}</td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm nominal-riwayat" value="${parseInt(r.bersih)}" data-id="${r.id}" style="width:100px;">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm uraian-riwayat" value="${r.uraian_pembayaran || ''}" data-id="${r.id}" style="width:150px;">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm nomor-riwayat" value="${r.nomor || ''}" data-id="${r.id}" style="width:120px;">
                                    </td>
                                    <td>
                                        <input type="date" class="form-control form-control-sm tanggal-riwayat" value="${r.tanggal ? r.tanggal.split(' ')[0] : ''}" data-id="${r.id}" style="width:120px;">
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" class="btn btn-sm btn-success btn-simpan-riwayat py-0 px-2" data-id="${r.id}" title="Simpan Perubahan"><i class="bx bx-save"></i></button>
                                    </td>
                                </tr>
                            `;
                        });
                        tbodyRiwayat.innerHTML = html;
                        
                        // Bind Save buttons
                        document.querySelectorAll('.btn-simpan-riwayat').forEach(btnSave => {
                            btnSave.addEventListener('click', async function() {
                                const id = this.dataset.id;
                                const tr = document.getElementById(`row-riwayat-${id}`);
                                
                                const nominal = tr.querySelector('.nominal-riwayat').value;
                                const uraian = tr.querySelector('.uraian-riwayat').value;
                                const nomor = tr.querySelector('.nomor-riwayat').value;
                                const tanggal = tr.querySelector('.tanggal-riwayat').value;
                                
                                this.disabled = true;
                                this.innerHTML = '<i class="bx bx-loader bx-spin"></i>';
                                
                                try {
                                    const resUpdate = await fetch("{{ route('admin.kekurangan-bayar.update-riwayat') }}", {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': token,
                                            'Accept': 'application/json',
                                        },
                                        body: JSON.stringify({
                                            id: id,
                                            nidn: nidn,
                                            nominal: nominal,
                                            uraian: uraian,
                                            nomor: nomor,
                                            tanggal: tanggal
                                        }),
                                    });
                                    
                                    const datUpdate = await resUpdate.json();
                                    if (datUpdate.success) {
                                        if (alertApi) await alertApi.success('Berhasil', 'Data riwayat berhasil diperbarui');
                                        else alert('Berhasil');
                                    } else {
                                        if (alertApi) await alertApi.error('Gagal', datUpdate.message || 'Gagal update riwayat');
                                        else alert(datUpdate.message || 'Gagal');
                                    }
                                } catch (e) {
                                    console.error(e);
                                    if (alertApi) await alertApi.error('Error', 'Gagal memproses ke server.');
                                    else alert('Gagal');
                                } finally {
                                    this.disabled = false;
                                    this.innerHTML = '<i class="bx bx-save"></i>';
                                }
                            });
                        });
                    } else {
                        tbodyRiwayat.innerHTML = `<tr><td colspan="6" class="text-center text-danger">${data.message || 'Gagal mengambil data'}</td></tr>`;
                    }
                } catch (err) {
                    console.error('Fetch riwayat error:', err);
                    tbodyRiwayat.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Gagal terhubung ke server.</td></tr>';
                }
            });
        });
        
        editRiwayatModalEl.addEventListener('hidden.bs.modal', function () {
            // Refresh to show updated data when modal closed
            window.location.reload();
        });

        document.querySelectorAll('.btn-hapus-rekap').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                const form = this.closest('form');
                if (alertApi) {
                    const r = await alertApi.question('Konfirmasi Hapus', 'Apakah Anda yakin ingin menghapus rekap ini beserta data kurang bayar untuk dosen terkait yang ada di dalamnya?', { confirmButtonText: 'Ya, Hapus' });
                    if (r && r.isConfirmed) form.submit();
                } else {
                    if (confirm('Apakah Anda yakin ingin menghapus rekap ini beserta data kurang/lebih bayar untuk dosen terkait yang ada di dalamnya?')) {
                        form.submit();
                    }
                }
            });
        });

        document.querySelectorAll('.btn-hapus-semua-rekap').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                const form = this.closest('form');
                if (alertApi) {
                    const r = await alertApi.question('Konfirmasi Hapus Semua', 'Apakah Anda yakin ingin menghapus SELURUH rekap beserta data kurang/lebih bayar untuk dosen terkait yang ada di dalamnya?', { confirmButtonText: 'Ya, Hapus Semua' });
                    if (r && r.isConfirmed) form.submit();
                } else {
                    if (confirm('Apakah Anda yakin ingin menghapus SELURUH rekap beserta data kurang/lebih bayar untuk dosen terkait yang ada di dalamnya?')) {
                        form.submit();
                    }
                }
            });
        });

    });
</script>
@endpush
