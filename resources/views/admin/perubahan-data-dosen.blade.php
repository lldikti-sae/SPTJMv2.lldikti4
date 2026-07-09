@extends(
    \Illuminate\Support\Facades\Auth::guard('pts')->check()
        ? 'layouts/contentNavbarLayoutPts'
        : ((\Illuminate\Support\Facades\Auth::guard('web')->check()
            && ($__u = \Illuminate\Support\Facades\Auth::guard('web')->user())
            && method_exists($__u, 'isPIC')
            && $__u->isPIC())
            ? 'layouts/contentNavbarLayoutPic'
            : 'layouts/contentNavbarLayout')
)

@php
    $isPts = \Illuminate\Support\Facades\Auth::guard('pts')->check();
    $webUser = \Illuminate\Support\Facades\Auth::guard('web')->user();
    $isPic = $webUser && method_exists($webUser, 'isPIC') && $webUser->isPIC();
    $routePrefix = $isPts ? 'pts' : ($isPic ? 'pic' : 'admin');
    $showPengaktifanTab = !$isPts;
@endphp

@section('title', 'SPTJM Online')

@section('content')

<style>
/* Modern tab and toggle styling */
.nav-tabs.custom-tabs .nav-link { border: none; padding: 0.5rem 0.9rem; color: #495057; background: transparent !important; }
.nav-tabs.custom-tabs .nav-link:not(.active) { background: transparent !important; color: #6c757d; }
.nav-tabs.custom-tabs .nav-link:hover, .nav-tabs.custom-tabs .nav-link:focus { background: transparent !important; }
.nav-tabs.custom-tabs .nav-link.active { color: #0d6efd; border-bottom: 3px solid #0d6efd; background: transparent; }
.tab-header-row { display: flex; justify-content: flex-start; align-items: center; gap: 1rem; margin-bottom: 1rem; width: 100%; }
.tab-header-row > div:first-child { flex: 1 1 auto; }
.toggle-switch { display: flex; align-items: center; gap: 0.6rem; margin-left: auto; }
.toggle-label { margin: 0; font-weight: 600; color: #495057; }

/* Modern custom toggle */
.modern-toggle { display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; user-select: none; }
.modern-toggle input { display: none; }
.modern-toggle .track { width: 44px; height: 22px; background: #e9ecef; border-radius: 999px; position: relative; display: inline-block; transition: background .18s ease; }
.modern-toggle .track .thumb { position: absolute; top: 2px; left: 2px; width: 18px; height: 18px; background: #fff; border-radius: 50%; box-shadow: 0 1px 4px rgba(0,0,0,0.12); transition: transform .18s ease; }
.modern-toggle input:checked + .track { background: #0d6efd; }
.modern-toggle input:checked + .track .thumb { transform: translateX(22px); }
.modern-toggle .label { font-weight: 600; color: #495057; font-size: 0.9rem; }
/* Remove horizontal padding around tab panes (outside cards) */
.tab-content, .tab-content > .tab-pane { padding-left: 0 !important; padding-top: 0 !important; padding-right: 0 !important; }
/* Smaller calendar icon inside Jabatan input */
.btnToggleTmtJabatan { padding: 0.55rem 0.9rem; border-radius: 4px; }
.btnToggleTmtJabatan i { font-size: 0.95rem; line-height: 1; }
.jabatan-block .input-group .form-control { min-height: 36px; }
</style>

@php
    // For display-only fields in Detail Data Dosen: replace empty / '-' with a friendly label.
    $__displayOrNA = function ($v) {
        $s = trim((string) $v);
        return ($s === '' || $s === '-') ? 'Data Tidak Tersedia' : $s;
    };

    // Route identifier can be NIDN or NUPTK. Use trimmed values; empty string should fallback.
    $__identifier = trim((string)($dosen->NIDN ?? ''));
    if ($__identifier === '') {
        $__identifier = trim((string)($dosen->NUPTK ?? ''));
    }
    if ($__identifier === '') {
        $__identifier = trim((string) request()->route('nidn'));
    }
@endphp

@php
    // Format date values for HTML input[type=date] (Y-m-d) accepting d/m/Y or other parseable strings
    $__formatForInput = function($v) {
        if (empty($v)) return '';
        try {
            if (strpos($v, '/') !== false) {
                $d = \Carbon\Carbon::createFromFormat('d/m/Y', $v);
            } else {
                $d = \Carbon\Carbon::parse($v);
            }
            return $d->format('Y-m-d');
        } catch (\Exception $e) {
            return '';
        }
    };
@endphp

<div class="tab-header-row">
    <div>
        <h5 class="mb-0">Data Dosen</h5>
        <ul class="nav nav-tabs custom-tabs mt-2" role="tablist">
            @if($showPengaktifanTab)
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-pengaktifan-btn" data-bs-toggle="tab" data-bs-target="#tab-pengaktifan" type="button" role="tab" aria-controls="tab-pengaktifan" aria-selected="true">Pengaktifan</button>
                </li>
            @endif
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $showPengaktifanTab ? '' : 'active' }}" id="tab-perubahan-btn" data-bs-toggle="tab" data-bs-target="#tab-perubahan" type="button" role="tab" aria-controls="tab-perubahan" aria-selected="{{ $showPengaktifanTab ? 'false' : 'true' }}">Perubahan Data</button>
            </li>
        </ul>
    </div>

    <div class="toggle-switch">
        <label class="modern-toggle">
            <input type="checkbox" id="toggleEdit">
            <span class="track"><span class="thumb"></span></span>
            <span class="label">Edit Mode</span>
        </label>
    </div>
</div>

<div class="tab-content">

    @if($showPengaktifanTab)
    <div class="tab-pane fade show active" id="tab-pengaktifan" role="tabpanel" aria-labelledby="tab-pengaktifan-btn">

        <form id="formPengaktifan" action="{{ route($routePrefix . '.perubahan-data-dosen.pengaktifan', ['nidn' => $__identifier]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="mode" value="{{ ($mode ?? 'edit') }}">
            <input type="hidden" name="tanggal_update_terakhir" value="{{ now()->format('d/m/Y') }}">

                <div class="card mb-4">
                    <div class="card-header">Informasi Perubahan</div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">No Dokumen</label>
                                <input type="text" name="no_dokumen_ubah" class="form-control js-editable" value="{{ $dosen->no_dokumen_ubah ?? '' }}" readonly style="background-color: #eceef1;" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                @php
                                    $__rawAktifInfo = $dosen->Aktif ?? ($dosen->aktif ?? 0);
                                    $__aktifValInfo = ((int)$__rawAktifInfo) === 1 ? '1' : '0';
                                @endphp
                                <div class="input-group keaktifan-block">
                                    <select name="Aktif" class="form-select js-editable-select" disabled style="background-color: #eceef1;" required>
                                        <option value="1" {{ $__aktifValInfo === '1' ? 'selected' : '' }}>AKTIF</option>
                                        <option value="0" {{ $__aktifValInfo === '0' ? 'selected' : '' }}>TIDAK AKTIF</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary btnToggleTmtKeaktifan" disabled title="Tampilkan TMT Keaktifan">
                                        <i class="bx bx-calendar"></i>
                                    </button>
                                </div>
                                <div class="tmtKeaktifanWrap mt-2 d-none">
                                    <label class="form-label">TMT Keaktifan</label>
                                    <input type="date" name="tmt_keaktifan" class="form-control js-editable-date tmt_keaktifan" value="{{ call_user_func($__formatForInput, $dosen->TMT_Keaktifan ?? null) }}" disabled style="background-color: #eceef1;" required>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Dokumen</label>
                                <input type="date" name="tgl_dokumen_ubah" class="form-control js-editable" value="{{ $dosen->tgl_dokumen_ubah ?? '' }}" readonly style="background-color: #eceef1;" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alasan Perubahan</label>
                                @php
                                    $__alasanCurrent = trim((string)($dosen->alasan_perubahan ?? ''));
                                    $__alasanList = $statusPerubahan ?? collect();
                                    $__alasanInList = $__alasanCurrent !== '' && $__alasanList->contains($__alasanCurrent);
                                @endphp
                                <select name="alasan_perubahan" class="form-select js-editable-select" disabled style="background-color: #eceef1;" required>
                                    <option value="" {{ !($__alasanInList) ? 'selected' : '' }}>Pilih</option>
                                    @foreach($__alasanList as $__a)
                                        @php $__a = trim((string)$__a); @endphp
                                        @if($__a !== '')
                                            <option value="{{ $__a }}" {{ $__a === $__alasanCurrent ? 'selected' : '' }}>{{ $__a }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Dokumen (PDF, max 10MB)</label>
                                @if(!empty($dosen->dokumen_path))
                                    <div class="mb-2">
                                        <a href="{{ $dosen->dokumen_path }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Dokumen Saat Ini</a>
                                    </div>
                                @endif
                                <input type="file" class="form-control dokumen-input" name="dokumen" accept="application/pdf" required />
                                <small class="text-muted">Upload file PDF untuk dokumen perubahan.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="keterangan" class="form-control js-editable" value="{{ $dosen->keterangan ?? '' }}" readonly style="background-color: #eceef1;" maxlength="100" required>
                            </div>
                        </div>
                    </div>
                </div>

<div class="card">
    <h5 class="card-header">Detail Data Dosen</h5>
    <div class="table-responsive text-nowrap">

        <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">NIDN</label>
                        <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->NIDN ?? null) }}" readonly style="background-color: #eceef1;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NUPTK</label>
                        <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->NUPTK ?? null) }}" readonly style="background-color: #eceef1;">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->NIK ?? ($dosen->nik ?? null)) }}" readonly style="background-color: #eceef1;">
                    </div>
                    <div class="col-md-6"></div>
                </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama</label>
                                <input type="text" name="nama" class="form-control js-editable js-always-readonly" value="{{ $dosen->Nama ?? '' }}" readonly style="background-color: #eceef1;" placeholder="Data Tidak Tersedia">
                            </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis</label>
                        @php
                            $__jenisCurrent = trim((string)($dosen->Jenis ?? ''));
                            $__jenisList = $jenisList ?? collect();
                        @endphp
                        <select name="jenis" class="form-select js-editable-select js-always-readonly" disabled style="background-color: #eceef1;" required>
                            @if($__jenisCurrent !== '' && $__jenisCurrent !== '-')
                                <option value="{{ $__jenisCurrent }}" selected>{{ $__jenisCurrent }}</option>
                            @else
                                <option value="" selected>Data Tidak Tersedia</option>
                            @endif
                            @foreach($__jenisList as $__j)
                                @php $__j = trim((string)$__j); @endphp
                                @if($__j !== '' && $__j !== $__jenisCurrent)
                                    <option value="{{ $__j }}">{{ $__j }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Sertifikat Dosen</label>
                                        <input type="text" name="sertifikat_dosen" class="form-control js-editable js-always-readonly" value="{{ $dosen->Sertifikat_Dosen ?? '' }}" readonly style="background-color: #eceef1;" placeholder="Data Tidak Tersedia">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tahun Lulus</label>
                                <input type="text" name="tahun_lulus" class="form-control js-editable js-always-readonly" value="{{ $dosen->Tahun_Lulus ?? '' }}" readonly style="background-color: #eceef1;" placeholder="Data Tidak Tersedia">
                                    </div>
                                </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">TTL</label>
                                @php
                                    $__tglLahir = trim((string)($dosen->Tanggal_Lahir ?? ''));
                                    $__ttl = trim((string)($dosen->TTL ?? ''));
                                    $__ttlDisplay = ($__tglLahir === '' || $__tglLahir === '-' || $__ttl === '' || $__ttl === '-')
                                        ? 'Data Tidak Tersedia'
                                        : ($__tglLahir . ' - ' . $__ttl);
                                @endphp
                                <input type="text" name="ttl" class="form-control js-editable js-ttl js-always-readonly" value="{{ $__ttlDisplay }}" readonly style="background-color: #eceef1;" placeholder="Data Tidak Tersedia">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Usia</label>
                                <input type="text" name="usia" class="form-control js-editable js-usia js-always-readonly" value="{{ $__displayOrNA($dosen->Usia ?? null) }}" readonly style="background-color: #eceef1;">
                            </div>
                        </div>

                <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode PTS / PTS</label>
                        @php
                            $__kodePtsCurrent = trim((string)($dosen->Kode_PT ?? ''));
                            $__namaPtsCurrent = trim((string)($dosen->PTS ?? ''));
                            $__ptsList = collect($ptsList ?? [])
                                ->filter(fn($p) => isset($p->kode_pts, $p->nama_pts))
                                ->sortBy(fn($p) => (int) $p->kode_pts) // ← URUT TERKECIL
                                ->values();
                            $__kodePtsInList = $__kodePtsCurrent !== '' && $__ptsList->contains(function($p) use ($__kodePtsCurrent) {
                                return isset($p->kode_pts) && (string)$p->kode_pts === (string)$__kodePtsCurrent;
                            });
                        @endphp
                        <input type="hidden" name="pts" value="{{ $dosen->PTS ?? '' }}" data-sync-pts>
                        <select name="kode_pt" class="form-select js-editable-select js-always-readonly" disabled style="background-color: #eceef1;" required>
                            <option value="" {{ $__kodePtsCurrent === '' ? 'selected' : '' }}>Data Tidak Tersedia</option>
                            @if($__kodePtsCurrent !== '' && !$__kodePtsInList)
                                <option value="{{ $__kodePtsCurrent }}" data-pts="{{ $__namaPtsCurrent }}" selected>{{ $__kodePtsCurrent }} - {{ $__namaPtsCurrent !== '' ? $__namaPtsCurrent : '-' }}</option>
                            @endif
                            @foreach($__ptsList as $__p)
                                @php
                                    $__k = isset($__p->kode_pts) ? (string)$__p->kode_pts : '';
                                    $__n = isset($__p->nama_pts) ? (string)$__p->nama_pts : '';
                                @endphp
                                @if($__k !== '' && $__n !== '')
                                    <option value="{{ $__k }}" data-pts="{{ $__n }}" {{ $__k === $__kodePtsCurrent ? 'selected' : '' }}>{{ $__k }} - {{ $__n }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        @php
                            $__rawAktif = $dosen->aktif ?? ($dosen->Aktif ?? 0);
                            $__aktifStr = strtolower(trim((string)$__rawAktif));
                            if ($__aktifStr === 'aktif') {
                                $__aktifVal = '1';
                            } elseif ($__aktifStr === 'tidak aktif' || $__aktifStr === 'nonaktif' || $__aktifStr === 'tidak') {
                                $__aktifVal = '0';
                            } else {
                                $__aktifVal = ((int)$__rawAktif) === 1 ? '1' : '0';
                            }
                        @endphp
                        <select class="form-select js-editable-select js-always-readonly" disabled style="background-color: #eceef1;">
                            <option value="1" {{ $__aktifVal === '1' ? 'selected' : '' }}>AKTIF</option>
                            <option value="0" {{ $__aktifVal === '0' ? 'selected' : '' }}>TIDAK AKTIF</option>
                        </select>
                    </div>
                </div>

                @php
                    $__formatForInput = function($v) {
                        if (empty($v)) return '';
                        try {
                            if (strpos($v, '/') !== false) {
                                $d = \Carbon\Carbon::createFromFormat('d/m/Y', $v);
                            } else {
                                $d = \Carbon\Carbon::parse($v);
                            }
                            return $d->format('Y-m-d');
                        } catch (\Exception $e) {
                            return '';
                        }
                    };
                @endphp

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">TMT JAD Pertama</label>
                        <input type="date" name="tmt_jad_pertama" class="form-control js-editable-date"
                            value="{{ call_user_func($__formatForInput, $dosen->TMT_JAD_Pertama ?? null) }}" disabled style="background-color: #eceef1;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">TMT JAD Akhir</label>
                        <input type="date" name="tmt_jad_akhir" class="form-control js-editable-date"
                            value="{{ call_user_func($__formatForInput, $dosen->TMT_JAD_Akhir ?? null) }}" disabled style="background-color: #eceef1;">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">TMT Inpassing Akhir</label>
                        <div class="input-group">
                            <input type="date" name="tmt_inpassing_akhir" class="form-control js-editable-date js-inpassing-date"
                                value="{{ call_user_func($__formatForInput, $dosen->TMT_Inpassing_Akhir ?? null) }}" disabled style="background-color: #eceef1;">
                            <span class="input-group-text" title="Centang untuk update Golongan berdasarkan TMT Inpassing">
                                <div class="form-check mb-0">
                                    <input class="form-check-input mt-0 js-editable-check js-apply-gol-by-tmt" type="checkbox" name="apply_gol_by_tmt_inpassing" value="1" disabled>
                                    <label class="form-check-label ms-1 small">Update Gol</label>
                                </div>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Inpassing</label>
                        @php
                            $__rawInpassing = trim((string)($dosen->Inpassing ?? ''));
                            $__rawInpassingNorm = strtolower($__rawInpassing);
                        @endphp
                        <select name="inpassing" class="form-select js-editable-select js-inpassing-select" data-initial="{{ $__rawInpassing }}">
                            @if($__rawInpassing !== '' && $__rawInpassing !== '-')
                                <option value="{{ $__rawInpassing }}" selected>{{ $__rawInpassing }}</option>
                            @else
                                <option value="" selected>Data Tidak Tersedia</option>
                            @endif
                            @if($__rawInpassingNorm !== strtolower('Berdasarkan Inpassing'))
                                <option value="Berdasarkan Inpassing">Berdasarkan Inpassing</option>
                            @endif
                            @if($__rawInpassingNorm !== strtolower('Sesuai TMT Awal dan Akhir'))
                                <option value="Sesuai TMT Awal dan Akhir">Sesuai TMT Awal dan Akhir</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="jabatan-block">
                            <label class="form-label">Jabatan</label>
                            <div class="input-group">
                                <select name="jabatan" class="form-select js-editable-select" data-initial="{{ $dosen->jabatan ?? '' }}" disabled style="background-color: #eceef1;" required>
                                    @if(trim((string)($dosen->jabatan ?? '')) !== '')
                                        <option value="{{ $dosen->jabatan }}" selected>{{ $dosen->jabatan }}</option>
                                    @else
                                        <option value="" selected>Data Tidak Tersedia</option>
                                    @endif
                                    @foreach($jabatanList ?? [] as $__jab)
                                        @php $__jab = trim((string)$__jab); @endphp
                                        @if($__jab !== '' && $__jab !== ($dosen->jabatan ?? ''))
                                            <option value="{{ $__jab }}">{{ $__jab }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary btnToggleTmtJabatan" disabled title="Tampilkan TMT Jabatan">
                                    <i class="bx bx-calendar"></i>
                                </button>
                            </div>

                            <div class="tmtJabatanWrap mt-2 d-none">
                                <label class="form-label">TMT Jabatan</label>
                                <input type="date" name="tmt_jabatan" class="form-control js-editable-date tmt_jabatan" value="{{ call_user_func($__formatForInput, $dosen->TMT_Jabatan ?? null) }}" disabled style="background-color: #eceef1;">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Golongan</label>
                        @php
                            $__golCurrent = trim((string)($dosen->gol ?? ''));
                            $__golList = $golonganList ?? collect();
                        @endphp
                        <select name="gol" class="form-select js-editable-select" disabled style="background-color: #eceef1;" required>
                            @if($__golCurrent !== '' && $__golCurrent !== '-')
                                <option value="{{ $__golCurrent }}" selected>{{ $__golCurrent }}</option>
                            @else
                                <option value="" selected>-</option>
                            @endif
                            @foreach($__golList as $__g)
                                @php $__g = trim((string)$__g); @endphp
                                @if($__g !== '' && $__g !== $__golCurrent)
                                    <option value="{{ $__g }}">{{ $__g }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Masa Kerja</label>
                        <input type="number" name="tahun" class="form-control js-editable js-always-readonly" value="{{ is_numeric($dosen->masa_kerja ?? null) ? (int)$dosen->masa_kerja : '' }}" readonly style="background-color: #eceef1;" min="0" max="32" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gaji</label>
                        <input type="number" name="gaji" class="form-control js-editable js-always-readonly" value="{{ is_numeric($dosen->gaji ?? null) ? (int)$dosen->gaji : '' }}" readonly style="background-color: #eceef1;" min="0" step="1" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Rekening</label>
                        <input type="text" name="no_rekening" class="form-control js-editable" value="{{ $dosen->No_Rekening ?? '' }}" readonly style="background-color: #eceef1;" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bank</label>
                        @php
                            $__bankCurrent = trim((string)($dosen->Bank ?? ''));
                            $__bankList = $bankList ?? collect();
                        @endphp
                        <select name="bank" class="form-select js-editable-select" disabled style="background-color: #eceef1;" required>
                            @if($__bankCurrent !== '' && $__bankCurrent !== '-')
                                <option value="{{ $__bankCurrent }}" selected>{{ $__bankCurrent }}</option>
                            @else
                                <option value="" selected>Data Tidak Tersedia</option>
                            @endif
                            @foreach($__bankList as $__b)
                                @php $__b = trim((string)$__b); @endphp
                                @if($__b !== '' && $__b !== $__bankCurrent)
                                    <option value="{{ $__b }}">{{ $__b }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Rekening</label>
                        <input type="text" name="nama_rekening" class="form-control js-editable" value="{{ $dosen->Nama_Rekening ?? '' }}" readonly style="background-color: #eceef1;" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nama Supplier</label>
                        <input type="text" name="nama_penerima" class="form-control js-editable" value="{{ $dosen->Nama_Penerima ?? '' }}" readonly style="background-color: #eceef1;" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">NPWP</label>
                        <input type="text" name="npwp" class="form-control js-editable" value="{{ $dosen->NPWP ?? '' }}" readonly style="background-color: #eceef1;" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Eligible Span</label>
                        @php
                            $__eligibleRaw = strtoupper(trim((string)($dosen->Eligible_span ?? '')));
                            if (in_array($__eligibleRaw, ['YA','Y','1','TRUE'], true)) {
                                $__eligibleVal = 'YA';
                            } elseif (in_array($__eligibleRaw, ['TIDAK','TDK','N','0','FALSE','NO'], true)) {
                                $__eligibleVal = 'TIDAK';
                            } else {
                                $__eligibleVal = $__eligibleRaw;
                            }
                        @endphp
                        <select name="eligible_span" class="form-select js-editable-select {{ $isPts ? 'js-always-readonly' : '' }}" disabled style="background-color: #eceef1;" required>
                            <option value="" {{ $__eligibleVal === '' ? 'selected' : '' }}>Data Tidak Tersedia</option>
                            <option value="YA" {{ $__eligibleVal === 'YA' ? 'selected' : '' }}>YA</option>
                            <option value="TIDAK" {{ $__eligibleVal === 'TIDAK' ? 'selected' : '' }}>TIDAK</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Pemegang Wilayah</label>
                        @php
                            $__pemegangCurrent = trim((string)($dosen->Pemegang_Wilayah ?? ''));
                            $__pics = $pics ?? collect();
                        @endphp
                        <select name="pemegang_wilayah" class="form-select js-editable-select {{ $isPts ? 'js-always-readonly' : '' }}" disabled style="background-color: #eceef1;" required>
                            @if($__pemegangCurrent !== '' && $__pemegangCurrent !== '-')
                                <option value="{{ $__pemegangCurrent }}" selected>{{ $__pemegangCurrent }}</option>
                            @else
                                <option value="" selected>Data Tidak Tersedia</option>
                            @endif
                            @foreach($__pics as $__p)
                                @php $__p = trim((string)$__p); @endphp
                                @if($__p !== '' && $__p !== $__pemegangCurrent)
                                    <option value="{{ $__p }}">{{ $__p }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="demo-inline-spacing">
                    <div class="d-flex justify-content-center">
                        <div>
                            <button type="button" class="btn btn-success" id="btnSimpanPerubahan">
                                <span class="tf-icons bx bx-save"></span>&nbsp;Simpan
                            </button>
                        </div>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary mx-2" onclick="event.preventDefault(); if (history.length > 1) { history.back(); } else { window.location.href = this.href; }">Batal</a>
                    </div>
                </div>


                </div>

        </div>
    </div>
    @endif
</div>

      </form>

    <div class="tab-pane fade {{ $showPengaktifanTab ? '' : 'show active' }}" id="tab-perubahan" role="tabpanel" aria-labelledby="tab-perubahan-btn">

            <form id="formPerubahan" action="{{ route($routePrefix . '.perubahan-data-dosen.perubahan', ['nidn' => $__identifier]) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="mode" value="{{ ($mode ?? 'edit') }}">
        <input type="hidden" name="tanggal_update_terakhir" value="{{ now()->format('d/m/Y') }}">

                <div class="card mb-4">
                    <div class="card-header">Informasi Perubahan</div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">No Dokumen</label>
                                <input type="text" name="no_dokumen_ubah" class="form-control js-editable" value="{{ $dosen->no_dokumen_ubah ?? '' }}" readonly style="background-color: #eceef1;" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                @php
                                    $__rawAktifInfo = $dosen->Aktif ?? ($dosen->aktif ?? 0);
                                    $__aktifValInfo = ((int)$__rawAktifInfo) === 1 ? '1' : '0';
                                @endphp

                                {{-- Hidden mirror so "Aktif" value is always submitted even when the select is disabled/read-only --}}
                                <input type="hidden" name="Aktif" value="{{ $__aktifValInfo }}">

                                <select name="Aktif" class="form-select js-editable-select js-always-readonly" disabled style="background-color: #eceef1;" required>
                                    <option value="1" {{ $__aktifValInfo === '1' ? 'selected' : '' }}>AKTIF</option>
                                    <option value="0" {{ $__aktifValInfo === '0' ? 'selected' : '' }}>TIDAK AKTIF</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Dokumen</label>
                                <input type="date" name="tgl_dokumen_ubah" class="form-control js-editable" value="{{ $dosen->tgl_dokumen_ubah ?? '' }}" readonly style="background-color: #eceef1;" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alasan Perubahan</label>
                                @php
                                    $__alasanCurrent = trim((string)($dosen->alasan_perubahan ?? ''));
                                    $__alasanList = $statusPerubahan ?? collect();
                                    $__alasanInList = $__alasanCurrent !== '' && $__alasanList->contains($__alasanCurrent);
                                @endphp
                                <select name="alasan_perubahan" class="form-select js-editable-select" disabled style="background-color: #eceef1;" required>
                                    <option value="" {{ !($__alasanInList) ? 'selected' : '' }}>Pilih</option>
                                    @foreach($__alasanList as $__a)
                                        @php $__a = trim((string)$__a); @endphp
                                        @if($__a !== '')
                                            <option value="{{ $__a }}" {{ $__a === $__alasanCurrent ? 'selected' : '' }}>{{ $__a }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Dokumen (PDF, max 10MB)</label>
                                @if(!empty($dosen->dokumen_path))
                                    <div class="mb-2">
                                        <a href="{{ $dosen->dokumen_path }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Dokumen Saat Ini</a>
                                    </div>
                                @endif
                                <input type="file" class="form-control dokumen-input" name="dokumen" accept="application/pdf" required />
                                <small class="text-muted">Upload file PDF untuk dokumen perubahan.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="keterangan" class="form-control js-editable" value="{{ $dosen->keterangan ?? '' }}" readonly style="background-color: #eceef1;" maxlength="100" required>
                            </div>
                            <!-- Terhitung Mulai Tanggal dihapus sesuai permintaan -->
                        </div>
                    </div>
                </div>

<div class="card">
    <h5 class="card-header">Detail Data Dosen</h5>
    <div class="table-responsive text-nowrap">

        <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">NIDN</label>
                        <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->NIDN ?? null) }}" readonly style="background-color: #eceef1;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NUPTK</label>
                        <input type="text" class="form-control" value="{{ $__displayOrNA($dosen->NUPTK ?? null) }}" readonly style="background-color: #eceef1;">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control js-editable" value="{{ $__displayOrNA($dosen->NIK ?? ($dosen->nik ?? null)) }}" readonly style="background-color: #eceef1;">
                    </div>
                    <div class="col-md-6"></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control js-editable" value="{{ $dosen->Nama ?? '' }}" readonly style="background-color: #eceef1;" placeholder="Data Tidak Tersedia" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Jenis</label>
                        @php
                            $__jenisCurrent = trim((string)($dosen->Jenis ?? ''));
                            $__jenisList = $jenisList ?? collect();
                        @endphp
                        <select name="jenis" class="form-select js-editable-select {{ $isPts ? 'js-always-readonly' : '' }}" disabled style="background-color: #eceef1;" required>
                            @if($__jenisCurrent !== '' && $__jenisCurrent !== '-')
                                <option value="{{ $__jenisCurrent }}" selected>{{ $__jenisCurrent }}</option>
                            @else
                                <option value="" selected>Data Tidak Tersedia</option>
                            @endif
                            @foreach($__jenisList as $__j)
                                @php $__j = trim((string)$__j); @endphp
                                @if($__j !== '' && $__j !== $__jenisCurrent)
                                    <option value="{{ $__j }}">{{ $__j }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Sertifikat Dosen</label>
                                <input type="text" name="sertifikat_dosen" class="form-control js-editable" value="{{ $dosen->Sertifikat_Dosen ?? '' }}" placeholder="Data Tidak Tersedia">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tahun Lulus</label>
                        <input type="text" name="tahun_lulus" class="form-control js-editable" value="{{ $dosen->Tahun_Lulus ?? '' }}" placeholder="Data Tidak Tersedia">
                            </div>
                        </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">TTL</label>
                        @php
                            $__tglLahir = trim((string)($dosen->Tanggal_Lahir ?? ''));
                            $__ttl = trim((string)($dosen->TTL ?? ''));
                            $__ttlDisplay = ($__tglLahir === '' || $__tglLahir === '-' || $__ttl === '' || $__ttl === '-')
                                ? 'Data Tidak Tersedia'
                                : ($__tglLahir . ' - ' . $__ttl);
                        @endphp
                        <input type="text" name="ttl" class="form-control js-editable js-ttl" value="{{ $__ttlDisplay }}" placeholder="Data Tidak Tersedia">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Usia</label>
                        <input type="text" name="usia" class="form-control js-editable js-usia js-always-readonly" value="{{ $__displayOrNA($dosen->Usia ?? null) }}" readonly style="background-color: #eceef1;">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Kode PTS / PTS</label>
                        @php
                            $__kodePtsCurrent = trim((string)($dosen->Kode_PT ?? ''));
                            $__namaPtsCurrent = trim((string)($dosen->PTS ?? ''));
                            $__ptsList = collect($ptsList ?? [])
                                ->filter(fn($p) => isset($p->kode_pts, $p->nama_pts))
                                ->sortBy(fn($p) => (int) $p->kode_pts) // ← URUT TERKECIL
                                ->values();
                            $__kodePtsInList = $__kodePtsCurrent !== '' && $__ptsList->contains(function($p) use ($__kodePtsCurrent) {
                                return isset($p->kode_pts) && (string)$p->kode_pts === (string)$__kodePtsCurrent;
                            });
                        @endphp
                        <input type="hidden" name="pts" value="{{ $dosen->PTS ?? '' }}" data-sync-pts>
                        <select name="kode_pt" class="form-select js-editable-select" disabled style="background-color: #eceef1;" required>
                            <option value="" {{ $__kodePtsCurrent === '' ? 'selected' : '' }}>Data Tidak Tersedia</option>
                            @if($__kodePtsCurrent !== '' && !$__kodePtsInList)
                                <option value="{{ $__kodePtsCurrent }}" data-pts="{{ $__namaPtsCurrent }}" selected>{{ $__kodePtsCurrent }} - {{ $__namaPtsCurrent !== '' ? $__namaPtsCurrent : '-' }}</option>
                            @endif
                            @foreach($__ptsList as $__p)
                                @php
                                    $__k = isset($__p->kode_pts) ? (string)$__p->kode_pts : '';
                                    $__n = isset($__p->nama_pts) ? (string)$__p->nama_pts : '';
                                @endphp
                                @if($__k !== '' && $__n !== '')
                                    <option value="{{ $__k }}" data-pts="{{ $__n }}" {{ $__k === $__kodePtsCurrent ? 'selected' : '' }}>{{ $__k }} - {{ $__n }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        @php
                            $__rawAktif = $dosen->aktif ?? ($dosen->Aktif ?? 0);
                            $__aktifStr = strtolower(trim((string)$__rawAktif));
                            if ($__aktifStr === 'aktif') {
                                $__aktifVal = '1';
                            } elseif ($__aktifStr === 'tidak aktif' || $__aktifStr === 'nonaktif' || $__aktifStr === 'tidak') {
                                $__aktifVal = '0';
                            } else {
                                $__aktifVal = ((int)$__rawAktif) === 1 ? '1' : '0';
                            }
                        @endphp
                        <select class="form-select js-editable-select js-always-readonly" disabled style="background-color: #eceef1;">
                            <option value="1" {{ $__aktifVal === '1' ? 'selected' : '' }}>AKTIF</option>
                            <option value="0" {{ $__aktifVal === '0' ? 'selected' : '' }}>TIDAK AKTIF</option>
                        </select>
                    </div>
                </div>

                @php
                    $__formatForInput = function($v) {
                        if (empty($v)) return '';
                        try {
                            if (strpos($v, '/') !== false) {
                                $d = \Carbon\Carbon::createFromFormat('d/m/Y', $v);
                            } else {
                                $d = \Carbon\Carbon::parse($v);
                            }
                            return $d->format('Y-m-d');
                        } catch (\Exception $e) {
                            return '';
                        }
                    };
                @endphp

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">TMT JAD Pertama</label>
                        <input type="date" name="tmt_jad_pertama" class="form-control js-editable-date"
                            value="{{ call_user_func($__formatForInput, $dosen->TMT_JAD_Pertama ?? null) }}" disabled style="background-color: #eceef1;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">TMT JAD Akhir</label>
                        <input type="date" name="tmt_jad_akhir" class="form-control js-editable-date"
                            value="{{ call_user_func($__formatForInput, $dosen->TMT_JAD_Akhir ?? null) }}" disabled style="background-color: #eceef1;">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">TMT Inpassing Akhir</label>
                        <div class="input-group">
                            <input type="date" name="tmt_inpassing_akhir" class="form-control js-editable-date js-inpassing-date"
                                value="{{ call_user_func($__formatForInput, $dosen->TMT_Inpassing_Akhir ?? null) }}" disabled style="background-color: #eceef1;">
                            <span class="input-group-text" title="Centang untuk update Golongan berdasarkan TMT Inpassing">
                                <div class="form-check mb-0">
                                    <input class="form-check-input mt-0 js-editable-check js-apply-gol-by-tmt" type="checkbox" name="apply_gol_by_tmt_inpassing" value="1" disabled>
                                    <label class="form-check-label ms-1 small">Update Gol</label>
                                </div>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Inpassing</label>
                        @php
                            $__rawInpassing = trim((string)($dosen->Inpassing ?? ''));
                            $__rawInpassingNorm = strtolower($__rawInpassing);
                        @endphp
                        <select name="inpassing" class="form-select js-editable-select js-inpassing-select" data-initial="{{ $__rawInpassing }}">
                            @if($__rawInpassing !== '' && $__rawInpassing !== '-')
                                <option value="{{ $__rawInpassing }}" selected>{{ $__rawInpassing }}</option>
                            @else
                                <option value="" selected>Data Tidak Tersedia</option>
                            @endif
                            @if($__rawInpassingNorm !== strtolower('Berdasarkan Inpassing'))
                                <option value="Berdasarkan Inpassing">Berdasarkan Inpassing</option>
                            @endif
                            @if($__rawInpassingNorm !== strtolower('Sesuai TMT Awal dan Akhir'))
                                <option value="Sesuai TMT Awal dan Akhir">Sesuai TMT Awal dan Akhir</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="jabatan-block">
                            <label class="form-label">Jabatan</label>
                            <div class="input-group">
                                <select name="jabatan" class="form-select js-editable-select" data-initial="{{ $dosen->jabatan ?? '' }}" disabled style="background-color: #eceef1;" required>
                                    @if(trim((string)($dosen->jabatan ?? '')) !== '')
                                        <option value="{{ $dosen->jabatan }}" selected>{{ $dosen->jabatan }}</option>
                                    @else
                                        <option value="" selected>Data Tidak Tersedia</option>
                                    @endif
                                    @foreach($jabatanList ?? [] as $__jab)
                                        @php $__jab = trim((string)$__jab); @endphp
                                        @if($__jab !== '' && $__jab !== ($dosen->jabatan ?? ''))
                                            <option value="{{ $__jab }}">{{ $__jab }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary btnToggleTmtJabatan" disabled title="Tampilkan TMT Jabatan">
                                    <i class="bx bx-calendar"></i>
                                </button>
                            </div>

                            <div class="tmtJabatanWrap mt-2 d-none">
                                <label class="form-label">TMT Jabatan</label>
                                <input type="date" name="tmt_jabatan" class="form-control js-editable-date tmt_jabatan" value="{{ call_user_func($__formatForInput, $dosen->TMT_Jabatan ?? null) }}" disabled style="background-color: #eceef1;">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Golongan</label>
                        @php
                            $__golCurrent = trim((string)($dosen->gol ?? ''));
                            $__golList = $golonganList ?? collect();
                        @endphp
                        <select name="gol" class="form-select js-editable-select" disabled style="background-color: #eceef1;" required>
                            @if($__golCurrent !== '' && $__golCurrent !== '-')
                                <option value="{{ $__golCurrent }}" selected>{{ $__golCurrent }}</option>
                            @else
                                <option value="" selected>-</option>
                            @endif
                            @foreach($__golList as $__g)
                                @php $__g = trim((string)$__g); @endphp
                                @if($__g !== '' && $__g !== $__golCurrent)
                                    <option value="{{ $__g }}">{{ $__g }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Masa Kerja</label>
                        <input type="number" name="tahun" class="form-control js-editable js-always-readonly" value="{{ is_numeric($dosen->masa_kerja ?? null) ? (int)$dosen->masa_kerja : '' }}" readonly style="background-color: #eceef1;" min="0" max="32" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gaji</label>
                        <input type="number" name="gaji" class="form-control js-editable js-always-readonly" value="{{ is_numeric($dosen->gaji ?? null) ? (int)$dosen->gaji : '' }}" readonly style="background-color: #eceef1;" min="0" step="1" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Rekening</label>
                        <input type="text" name="no_rekening" class="form-control js-editable" value="{{ $dosen->No_Rekening ?? '' }}" readonly style="background-color: #eceef1;" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Bank</label>
                        @php
                            $__bankCurrent = trim((string)($dosen->Bank ?? ''));
                            $__bankList = $bankList ?? collect();
                        @endphp
                        <select name="bank" class="form-select js-editable-select" disabled style="background-color: #eceef1;" required>
                            @if($__bankCurrent !== '' && $__bankCurrent !== '-')
                                <option value="{{ $__bankCurrent }}" selected>{{ $__bankCurrent }}</option>
                            @else
                                <option value="" selected>Data Tidak Tersedia</option>
                            @endif
                            @foreach($__bankList as $__b)
                                @php $__b = trim((string)$__b); @endphp
                                @if($__b !== '' && $__b !== $__bankCurrent)
                                    <option value="{{ $__b }}">{{ $__b }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Rekening</label>
                        <input type="text" name="nama_rekening" class="form-control js-editable" value="{{ $dosen->Nama_Rekening ?? '' }}" readonly style="background-color: #eceef1;" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nama Supplier</label>
                        <input type="text" name="nama_penerima" class="form-control js-editable" value="{{ $dosen->Nama_Penerima ?? '' }}" readonly style="background-color: #eceef1;" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">NPWP</label>
                        <input type="text" name="npwp" class="form-control js-editable" value="{{ $dosen->NPWP ?? '' }}" readonly style="background-color: #eceef1;" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Eligible Span</label>
                        @php
                            $__eligibleRaw = strtoupper(trim((string)($dosen->Eligible_span ?? '')));
                            if (in_array($__eligibleRaw, ['YA','Y','1','TRUE'], true)) {
                                $__eligibleVal = 'YA';
                            } elseif (in_array($__eligibleRaw, ['TIDAK','TDK','N','0','FALSE','NO'], true)) {
                                $__eligibleVal = 'TIDAK';
                            } else {
                                $__eligibleVal = $__eligibleRaw;
                            }
                        @endphp
                        <select name="eligible_span" class="form-select js-editable-select {{ $isPts ? 'js-always-readonly' : '' }}" disabled style="background-color: #eceef1;" required>
                            <option value="" {{ $__eligibleVal === '' ? 'selected' : '' }}>Data Tidak Tersedia</option>
                            <option value="YA" {{ $__eligibleVal === 'YA' ? 'selected' : '' }}>YA</option>
                            <option value="TIDAK" {{ $__eligibleVal === 'TIDAK' ? 'selected' : '' }}>TIDAK</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Pemegang Wilayah</label>
                        @php
                            $__pemegangCurrent = trim((string)($dosen->Pemegang_Wilayah ?? ''));
                            $__pics = $pics ?? collect();
                        @endphp
                        <select name="pemegang_wilayah" class="form-select js-editable-select {{ $isPts ? 'js-always-readonly' : '' }}" disabled style="background-color: #eceef1;" required>
                            @if($__pemegangCurrent !== '' && $__pemegangCurrent !== '-')
                                <option value="{{ $__pemegangCurrent }}" selected>{{ $__pemegangCurrent }}</option>
                            @else
                                <option value="" selected>Data Tidak Tersedia</option>
                            @endif
                            @foreach($__pics as $__p)
                                @php $__p = trim((string)$__p); @endphp
                                @if($__p !== '' && $__p !== $__pemegangCurrent)
                                    <option value="{{ $__p }}">{{ $__p }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="demo-inline-spacing">
                    <div class="d-flex justify-content-center">
                        <div>
                            <button type="button" class="btn btn-success" id="btnSimpanPerubahanTab">
                                <span class="tf-icons bx bx-save"></span>&nbsp;Simpan
                            </button>
                        </div>
                        <a href="{{ url()->previous() }}" class="btn btn-secondary mx-2" onclick="event.preventDefault(); if (history.length > 1) { history.back(); } else { window.location.href = this.href; }">Batal</a>
                    </div>
                </div>


        </form>

        </div>
    </div>
</div>

    </div>
</div>

<script>
(() => {
    const __isNewMode = {!! json_encode((($mode ?? 'edit') === 'new')) !!};
    const toggle = document.getElementById('toggleEdit');
    const editableText = Array.from(document.querySelectorAll('.js-editable'));
    const editableDate = Array.from(document.querySelectorAll('.js-editable-date'));
    const editableChecks = Array.from(document.querySelectorAll('.js-editable-check'));
    const editableSelect = Array.from(document.querySelectorAll('.js-editable-select'));
    const btnToggleTmtJabatan = Array.from(document.querySelectorAll('.btnToggleTmtJabatan'));
    const tmtJabatanWraps = Array.from(document.querySelectorAll('.tmtJabatanWrap'));
    const btnToggleTmtKeaktifan = Array.from(document.querySelectorAll('.btnToggleTmtKeaktifan'));
    const tmtKeaktifanWraps = Array.from(document.querySelectorAll('.tmtKeaktifanWrap'));
    const dokumenInputs = Array.from(document.querySelectorAll('.dokumen-input'));

    const isBerdasarkanInpassing = (v) => {
        const s = String(v || '').trim().toLowerCase();
        return s === 'berdasarkan inpassing' || s === 'inpassing' || s.includes('berdasarkan');
    };

    const isSesuaTMTAwaldanAkhir = (v) => {
        const s = String(v || '').trim().toLowerCase();
        return s === 'Sesuai TMT Awal dan Akhir' || s === 'Sesuai TMT Awal dan Akhir' || s.includes('tanpa');
    };

    const syncPtsForForm = (form) => {
        if (!form) return;
        const kodePt = form.querySelector('select[name="kode_pt"]');
        const ptsHidden = form.querySelector('input[data-sync-pts][name="pts"]');
        if (!kodePt || !ptsHidden) return;
        const apply = () => {
            const opt = kodePt.options && kodePt.selectedIndex >= 0 ? kodePt.options[kodePt.selectedIndex] : null;
            const pts = opt ? (opt.getAttribute('data-pts') || '') : '';
            ptsHidden.value = pts;
        };
        kodePt.addEventListener('change', apply);
        apply();
    };

    const applyInpassingRules = (enabled) => {
        const selects = Array.from(document.querySelectorAll('.js-inpassing-select'));
        selects.forEach((sel) => {
            // Inpassing select should be editable when edit mode is ON
            sel.disabled = !enabled;
            sel.style.backgroundColor = enabled ? '' : '#eceef1';

            // If current value is Tanpa, lock related date/checkbox; otherwise date editable in edit mode
            const row = sel.closest('.row') || sel.closest('.card-body') || document;
            const dateEl = row ? row.querySelector('.js-inpassing-date') : null;

            // Rule: Golongan editability depends on 'Berdasarkan Inpassing'.
            // However TMT Inpassing date should remain editable in Edit Mode regardless of the Inpassing selection.
            const berdasarkan = isBerdasarkanInpassing(sel.value);
            if (dateEl) {
                // date input editable when Edit Mode is enabled
                dateEl.disabled = !enabled;
                dateEl.style.backgroundColor = (!dateEl.disabled) ? '' : '#eceef1';
            }

            // Golongan (select[name="gol"]) follows Inpassing rule:
            // - Tanpa/other: locked + grey
            // - Berdasarkan: editable (only if edit mode enabled)
            const form = sel.closest('form') || document;
            const golEl = form ? form.querySelector('select[name="gol"]') : null;
            if (golEl) {
                const shouldEnableGol = enabled && berdasarkan;
                if (shouldEnableGol) {
                    golEl.removeAttribute('disabled');
                    golEl.disabled = false;
                    golEl.style.backgroundColor = '';
                } else {
                    golEl.setAttribute('disabled', 'disabled');
                    golEl.disabled = true;
                    golEl.style.backgroundColor = '#eceef1';
                }
            }
        });
    };

    const bindInpassingSelectChanges = () => {
        const selects = Array.from(document.querySelectorAll('.js-inpassing-select'));
        selects.forEach((sel) => {
            sel.addEventListener('change', () => {
                const isEdit = !!(toggle && toggle.checked);
                applyInpassingRules(isEdit);
            });
        });
    };

    const setEditMode = (enabled) => {
        editableText.forEach((el) => {
            // keep always-readonly elements readonly regardless of edit mode
            if (el.classList && el.classList.contains('js-always-readonly')) {
                el.setAttribute('readonly', 'readonly');
                el.style.backgroundColor = '#eceef1';
                return;
            }
            if (enabled) {
                el.removeAttribute('readonly');
                el.removeAttribute('disabled');
                el.style.backgroundColor = '';
            } else {
                el.setAttribute('readonly', 'readonly');
                el.style.backgroundColor = '#eceef1';
            }
        });

        editableDate.forEach((el) => {
            if (el.classList && el.classList.contains('js-always-readonly')) {
                el.setAttribute('disabled', 'disabled');
                el.disabled = true;
                el.style.backgroundColor = '#eceef1';
                return;
            }
            if (enabled) {
                el.removeAttribute('disabled');
                el.removeAttribute('readonly');
                el.disabled = false;
                el.style.backgroundColor = '';
            } else {
                el.setAttribute('disabled', 'disabled');
                el.disabled = true;
                el.style.backgroundColor = '#eceef1';
            }
        });

        editableChecks.forEach((el) => {
            if (el.classList && el.classList.contains('js-always-readonly')) {
                el.disabled = true;
            } else {
                el.disabled = !enabled;
            }
        });

        editableSelect.forEach((el) => {
            if (el.classList && el.classList.contains('js-always-readonly')) {
                el.setAttribute('disabled', 'disabled');
                el.disabled = true;
                el.style.backgroundColor = '#eceef1';
                return;
            }
            if (enabled) {
                el.removeAttribute('disabled');
                el.disabled = false;
                el.style.backgroundColor = '';
            } else {
                el.setAttribute('disabled', 'disabled');
                el.disabled = true;
                el.style.backgroundColor = '#eceef1';
            }
        });

        // Enable/disable dokumen file inputs
        dokumenInputs.forEach((el) => {
            try {
                if (enabled) { el.removeAttribute('disabled'); el.disabled = false; el.style.backgroundColor = ''; }
                else { el.setAttribute('disabled', 'disabled'); el.disabled = true; el.style.backgroundColor = '#eceef1'; }
            } catch (e) {}
        });

        // enable/disable all TMT Jabatan buttons
        btnToggleTmtJabatan.forEach((b) => { b.disabled = !enabled; });
        // enable/disable all TMT Keaktifan buttons
        btnToggleTmtKeaktifan.forEach((b) => { b.disabled = !enabled; });

        // When edit mode is off, hide all TMT Jabatan and Keaktifan blocks
        if (!enabled) {
            tmtJabatanWraps.forEach((w) => w.classList.add('d-none'));
            tmtKeaktifanWraps.forEach((w) => w.classList.add('d-none'));
        }

        // Apply special rules after general enable/disable
        applyInpassingRules(enabled);
        enableSpecialFields(enabled);
    };

    const enableSpecialFields = (enabled) => {
        // Explicitly enable/disable Status selects (name=Aktif) across both tabs
        const statusSelects = Array.from(document.querySelectorAll('select[name="Aktif"]'));
        statusSelects.forEach((s) => {
            // respect js-always-readonly: never enable these
            if (s.classList && s.classList.contains('js-always-readonly')) {
                s.setAttribute('disabled', 'disabled'); s.disabled = true; s.style.backgroundColor = '#eceef1';
                return;
            }
            if (enabled) { s.removeAttribute('disabled'); s.disabled = false; s.style.backgroundColor = ''; }
            else { s.setAttribute('disabled', 'disabled'); s.disabled = true; s.style.backgroundColor = '#eceef1'; }
        });
    };

    // TMT Jabatan reveal (per-block)
    if (btnToggleTmtJabatan.length) {
        btnToggleTmtJabatan.forEach((btn) => {
            btn.addEventListener('click', () => {
                if (!toggle || !toggle.checked) return;
                const block = btn.closest('.jabatan-block');
                if (!block) return;
                const wrap = block.querySelector('.tmtJabatanWrap');
                if (!wrap) return;
                wrap.classList.toggle('d-none');
                const input = wrap.querySelector('.tmt_jabatan');
                if (input && !wrap.classList.contains('d-none')) {
                    input.disabled = false;
                    input.style.backgroundColor = '';
                }
            });
        });
    }

    // TMT Keaktifan reveal (per-block)
    if (btnToggleTmtKeaktifan.length) {
        btnToggleTmtKeaktifan.forEach((btn) => {
            btn.addEventListener('click', () => {
                if (!toggle || !toggle.checked) return;
                const block = btn.closest('.keaktifan-block');
                if (!block) return;
                const wrap = document.querySelector('.tmtKeaktifanWrap');
                if (!wrap) return;
                wrap.classList.toggle('d-none');
                const input = wrap.querySelector('.tmt_keaktifan');
                if (input && !wrap.classList.contains('d-none')) {
                    input.disabled = false;
                    input.style.backgroundColor = '';
                }
            });
        });
    }

    if (toggle) {
        toggle.addEventListener('change', () => {
            setEditMode(toggle.checked);
        });
    }

    // default state: view-only (except mode=new)
    if (__isNewMode) {
        if (toggle) toggle.checked = true;
        setEditMode(true);
    } else {
        setEditMode(false);
    }
    bindInpassingSelectChanges();
    applyInpassingRules(__isNewMode ? true : false);

    // sync PTS hidden fields for forms (Pengaktifan may be hidden for PTS)
    const formPengaktifan = document.getElementById('formPengaktifan');
    if (formPengaktifan) syncPtsForForm(formPengaktifan);
    const formPerubahan = document.getElementById('formPerubahan');
    if (formPerubahan) syncPtsForForm(formPerubahan);

        // Auto-calculate Usia from TTL (per-row). Listens on blur/change of TTL inputs.
        const ttlInputs = Array.from(document.querySelectorAll('.js-ttl'));
        const computeAgeForTtl = (ttlEl) => {
            if (!ttlEl) return;
            const val = (ttlEl.value || '').trim();
            const row = ttlEl.closest('.row') || ttlEl.parentElement;
            const usiaEl = row ? row.querySelector('.js-usia') : null;

            const parseYear = (s) => {
                if (!s) return null;
                s = s.trim();
                // ISO yyyy-mm-dd
                const iso = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (iso) return parseInt(iso[1], 10);
                // dd/mm/yyyy
                const dmy = s.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
                if (dmy) return parseInt(dmy[3], 10);
                // any 4-digit year
                const m = s.match(/(\d{4})/);
                if (m) return parseInt(m[1], 10);
                // try Date parse fallback
                const dt = new Date(s);
                if (!isNaN(dt)) return dt.getFullYear();
                return null;
            };

            const year = parseYear(val);
            if (usiaEl) {
                if (year) {
                    const age = new Date().getFullYear() - year;
                    usiaEl.value = age >= 0 ? age : 0;
                } else {
                    // leave existing value if cannot parse
                    usiaEl.value = usiaEl.value || '';
                }
            }
        };

        if (ttlInputs.length) {
            ttlInputs.forEach((el) => {
                el.addEventListener('blur', () => computeAgeForTtl(el));
                el.addEventListener('change', () => computeAgeForTtl(el));
            });
            // initial compute on load
            ttlInputs.forEach((el) => computeAgeForTtl(el));
        }
})();

// Validate dokumen inputs (PDF, max 10MB)
document.addEventListener('change', function (e) {
    const el = e.target;
    if (!el || !el.classList) return;
    if (!el.classList.contains('dokumen-input')) return;
    const file = el.files && el.files[0];
    if (!file) return;
    const maxBytes = 10 * 1024 * 1024; // 10MB
    if (file.size > maxBytes) {
        if (window.SptjmAlert) {
            window.SptjmAlert.warning('Peringatan', 'File terlalu besar. Maksimum 10 MB.');
        } else {
            alert('File terlalu besar. Maksimum 10 MB.');
        }
        el.value = '';
        return;
    }
    const type = file.type || '';
    if (type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        if (window.SptjmAlert) {
            window.SptjmAlert.warning('Peringatan', 'Hanya file PDF yang diperbolehkan.');
        } else {
            alert('Hanya file PDF yang diperbolehkan.');
        }
        el.value = '';
        return;
    }
});

// SweetAlert hooks: flash messages + Simpan confirmation
document.addEventListener('DOMContentLoaded', function () {
    const __isNewMode = {!! json_encode((($mode ?? 'edit') === 'new')) !!};
    // SweetAlert helper with safe fallback (avoid "click Simpan tidak terjadi apa apa")
    const Alert = (function () {
        const has = (window.SptjmAlert && typeof window.SptjmAlert.warning === 'function');
        if (has) return window.SptjmAlert;

        // fallback: browser dialogs
        return {
            warning: async (title, textOrHtml) => { try { alert((title ? (title + "\n") : "") + String(textOrHtml || '')); } catch (e) {} },
            error: async (title, textOrHtml) => { try { alert((title ? (title + "\n") : "") + String(textOrHtml || '')); } catch (e) {} },
            success: async (title, textOrHtml) => { try { alert((title ? (title + "\n") : "") + String(textOrHtml || '')); } catch (e) {} },
            info: async (title, textOrHtml) => { try { alert((title ? (title + "\n") : "") + String(textOrHtml || '')); } catch (e) {} },
            question: async (title, textOrHtml) => {
                let ok = false;
                try { ok = confirm((title ? (title + "\n") : "") + String(textOrHtml || '')); } catch (e) {}
                return { isConfirmed: ok };
            },
            fromFlash: () => {},
        };
    })();

    const __identifier = {!! json_encode(($dosen->NIDN ?? $dosen->NUPTK) ?? '') !!};
    @if (\Illuminate\Support\Facades\Route::has($routePrefix . '.sinkronisasi'))
        const __sinkronisasiUrl = {!! json_encode(route($routePrefix . '.sinkronisasi')) !!};
    @else
        const __sinkronisasiUrl = null;
    @endif
    const __urlDataDosen = {!! json_encode(route($routePrefix . '.data-dosen')) !!};
    const __urlUbahDataTemplate = {!! json_encode(route($routePrefix . '.edit-data-dosen', ['nidn' => '__IDENT__'])) !!};
    const __urlUbahMkGolTemplate = {!! json_encode(route($routePrefix . '.edit-mk-gol', ['nidn' => '__IDENT__'])) !!};
    const __urlUpdateDataTemplate = {!! json_encode(route($routePrefix . '.update-data-dosen', ['nidn' => '__IDENT__'])) !!};

    try {
        const __sptjm_flash = {!! json_encode([
            'success' => session('success'),
            'error' => session('error'),
            'warning' => session('warning'),
            'info' => session('info'),
            'question' => session('question'),
        ]) !!};

        const successMsg = __sptjm_flash && __sptjm_flash.success ? String(__sptjm_flash.success) : '';
        const shouldRedirectAfterSuccess = !!successMsg && /(tersimpan|berhasil diubah)/i.test(successMsg);

        const __redirectToSinkronisasi = () => {
            if (!__sinkronisasiUrl) return; // route not available for this guard
            const id = String(__identifier || '').trim();
            const qs = new URLSearchParams();
            qs.set('open', 'golmasa');
            qs.set('tab', 'golmasa');
            qs.set('autofill', '1');
            if (id) qs.set('identifier', id);
            window.location.href = __sinkronisasiUrl + '?' + qs.toString();
        };

        // Custom handling for requires_scheduling
        const __requires_scheduling = {!! json_encode(session('requires_scheduling') ?? false) !!};
        if (__requires_scheduling) {
            const jadwalUrl = {!! json_encode(route($routePrefix . '.jadwal-pindah-pts.simpan' ?? 'admin.jadwal-pindah-pts.simpan')) !!};
            const postData = {
                nidn: __identifier,
                kode_pt_baru: {!! json_encode(session('kode_pt_baru') ?? '') !!},
                nama_pts_baru: {!! json_encode(session('nama_pts_baru') ?? '') !!},
                pemegang_wilayah_baru: {!! json_encode(session('pemegang_wilayah_baru') ?? '') !!},
                _token: {!! json_encode(csrf_token()) !!}
            };
            
            Swal.fire({
                title: 'Perhatian!',
                text: __sptjm_flash.warning || 'Dosen ini tidak bisa dipindah PTS sekarang karena dalam proses usulan pencairan aktif.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Jadwalkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = jadwalUrl;
                    for (const key in postData) {
                        const hiddenField = document.createElement('input');
                        hiddenField.type = 'hidden';
                        hiddenField.name = key;
                        hiddenField.value = postData[key];
                        form.appendChild(hiddenField);
                    }
                    document.body.appendChild(form);
                    form.submit();
                }
            });
            // Hapus warning dari flash standar agar tidak muncul dua kali
            __sptjm_flash.warning = null;
        }

        // Show success modal and redirect immediately after it is closed
        if (shouldRedirectAfterSuccess) {
            // Avoid double-success by not passing success into fromFlash
            const flashWithoutSuccess = Object.assign({}, __sptjm_flash, { success: null });
            if (Alert && typeof Alert.fromFlash === 'function') {
                Alert.fromFlash(flashWithoutSuccess);
            }

            // Show success message
            Promise.resolve(Alert.success('Berhasil', successMsg, { confirmButtonText: 'Tutup' }))
                .then(() => {
                    __redirectToSinkronisasi();
                })
                .catch(() => {
                    // If modal fails, still redirect immediately
                    __redirectToSinkronisasi();
                });
        } else {
            if (Alert && typeof Alert.fromFlash === 'function') {
                Alert.fromFlash(__sptjm_flash);
            }
        }
    } catch (e) { console.error('Flash handling error', e); }

    // Show Laravel validation errors (server-side) in a modal
    try {
        const __errors = {!! json_encode($errors->all() ?? []) !!};
        if (Array.isArray(__errors) && __errors.length) {
            const html = '<div style="text-align:left">' + __errors.map(e => '<div>• ' + String(e) + '</div>').join('') + '</div>';
            Alert.warning('Validasi gagal', html, { confirmButtonText: 'Tutup' });
        }
    } catch (e) {}

    const btnSimpan = document.getElementById('btnSimpanPerubahan');
    const btnSimpanTab = document.getElementById('btnSimpanPerubahanTab');

    // Global submit lock to prevent duplicate requests (e.g. multiple listeners, double clicks, slow network)
    const __acquireSubmitLock = (key) => {
        try {
            if (!window.__sptjmSubmitLocks) window.__sptjmSubmitLocks = {};
            if (window.__sptjmSubmitLocks[key]) return false;
            window.__sptjmSubmitLocks[key] = true;
            return true;
        } catch (e) {
            // If window is not writable for some reason, fall back to allowing submission.
            return true;
        }
    };
    const __releaseSubmitLock = (key) => {
        try {
            if (window.__sptjmSubmitLocks && window.__sptjmSubmitLocks[key]) {
                delete window.__sptjmSubmitLocks[key];
            }
        } catch (e) {}
    };
    const __disableButton = (btn, disabled) => {
        if (!btn) return;
        try {
            btn.disabled = !!disabled;
            btn.setAttribute('aria-disabled', disabled ? 'true' : 'false');
        } catch (e) {}
    };

    const getFieldLabel = (el) => {
        if (!el) return '';
        const wrap = el.closest('.col-md-12, .col-md-6, .col-md-4, .col-md-3') || el.parentElement;
        const lbl = wrap ? wrap.querySelector('label.form-label') : null;
        const labelText = lbl ? String(lbl.textContent || '').trim() : '';
        if (labelText) return labelText;
        const name = el.getAttribute('name') || el.id || 'Field';
        return name;
    };

    const showFormInvalidModal = async (form) => {
        try {
            const invalid = form ? Array.from(form.querySelectorAll(':invalid')) : [];
            if (invalid.length) {
                const html = '<div style="text-align:left">' + invalid.map((el) => '• ' + getFieldLabel(el)).join('<br>') + '</div>';
                await Alert.warning('Periksa input', html, { confirmButtonText: 'Tutup' });
            } else {
                await Alert.warning('Periksa input', 'Pastikan semua field wajib sudah diisi.', { confirmButtonText: 'Tutup' });
            }
        } catch (e) {
            try { await Alert.warning('Periksa input', 'Pastikan semua field wajib sudah diisi.', { confirmButtonText: 'Tutup' }); } catch (e2) {}
        }
        try { if (form && typeof form.reportValidity === 'function') form.reportValidity(); } catch (e) {}
    };

    // Submit helper: convert all enabled <input type="date"> values to DD/MM/YYYY
    // by creating hidden mirror inputs with the same name.
    const ensureDmyDateSubmission = (form) => {
        if (!form) return;

        // remove old mirrors (if any)
        try {
            Array.from(form.querySelectorAll('input.__date_mirror')).forEach((el) => el.remove());
        } catch (e) {}

        const toDmy = (raw) => {
            const v = String(raw || '').trim();
            if (!v) return '';
            // yyyy-mm-dd -> dd/mm/yyyy
            const m = v.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (m) return `${m[3]}/${m[2]}/${m[1]}`;

            // dd/mm/yyyy (ensure 2-digit)
            const dmy = v.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (dmy) {
                const dd = String(dmy[1]).padStart(2, '0');
                const mm = String(dmy[2]).padStart(2, '0');
                const yyyy = dmy[3];
                return `${dd}/${mm}/${yyyy}`;
            }
            return v;
        };

        const dateInputs = Array.from(form.querySelectorAll('input[type="date"][name]'))
            .filter((el) => el && !el.disabled);

        dateInputs.forEach((el) => {
            const name = el.getAttribute('name');
            if (!name) return;
            const dmyVal = toDmy(el.value);

            // Mirror only when user actually has a value
            if (String(dmyVal || '').trim() === '') return;

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = name;
            hidden.value = dmyVal;
            hidden.className = '__date_mirror';
            form.appendChild(hidden);

            // Prevent the original date input from submitting its yyyy-mm-dd value
            el.setAttribute('data-original-name', name);
            el.removeAttribute('name');
        });
    };

    // mode=new helper: mirror ALL disabled fields (including js-always-readonly) so they still submit.
    const ensureDisabledFieldSubmission = (form) => {
        if (!form) return;
        try {
            Array.from(form.querySelectorAll('input.__disabled_mirror')).forEach((el) => el.remove());
        } catch (e) {}

        const toDmy = (raw) => {
            const v = String(raw || '').trim();
            if (!v) return '';
            const m = v.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (m) return `${m[3]}/${m[2]}/${m[1]}`;
            const dmy = v.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (dmy) {
                const dd = String(dmy[1]).padStart(2, '0');
                const mm = String(dmy[2]).padStart(2, '0');
                return `${dd}/${mm}/${dmy[3]}`;
            }
            return v;
        };

        const disabled = Array.from(form.querySelectorAll('[name]:disabled'))
            .filter((el) => el && el.getAttribute && el.getAttribute('name'));

        disabled.forEach((el) => {
            const name = el.getAttribute('name');
            if (!name) return;
            // never mirror file inputs
            if (el.tagName === 'INPUT' && String(el.type || '').toLowerCase() === 'file') return;

            let value = '';
            const type = (el.tagName === 'INPUT') ? String(el.type || '').toLowerCase() : '';

            if (type === 'checkbox' || type === 'radio') {
                if (!el.checked) return;
                value = el.value || '1';
            } else if (type === 'date') {
                value = toDmy(el.value);
            } else {
                value = (el.value != null) ? String(el.value) : '';
            }

            // Mirror even empty? For required disabled fields, keep current value.
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = name;
            hidden.value = value;
            hidden.className = '__disabled_mirror';
            form.appendChild(hidden);
        });
    };

    if (btnSimpan) {
        btnSimpan.addEventListener('click', async function () {
            const __lockKey = 'perubahan-data-dosen:' + (this && this.id ? this.id : 'btnSimpanPerubahan');
            if (!__acquireSubmitLock(__lockKey)) return;
            let __willSubmit = false;
            const toggle = document.getElementById('toggleEdit');
            if (!__isNewMode && (!toggle || !toggle.checked)) {
                await Alert.warning('Periksa input', 'Aktifkan Edit Mode terlebih dahulu untuk melakukan perubahan.', { confirmButtonText: 'Tutup' });
                __releaseSubmitLock(__lockKey);
                return;
            }
            const form = document.getElementById('formPengaktifan');
            if (form) {
                // TMT Keaktifan is mandatory for Pengaktifan save
                const tmtKeaktifan = form.querySelector('input[name="tmt_keaktifan"]');
                if (!tmtKeaktifan || !String(tmtKeaktifan.value || '').trim()) {
                    await Alert.warning('Periksa input', 'Isi TMT Keaktifan sebelum menyimpan.', { confirmButtonText: 'Tutup' });
                    // ensure native validation bubble is shown if possible
                    try { if (tmtKeaktifan && typeof tmtKeaktifan.reportValidity === 'function') tmtKeaktifan.reportValidity(); } catch (e) {}
                    __releaseSubmitLock(__lockKey);
                    return;
                }

                // Conditional rule: if "Update Gol" checked, TMT Inpassing Akhir must be filled
                const applyGol = form.querySelector('input[name="apply_gol_by_tmt_inpassing"].js-apply-gol-by-tmt');
                const tmtInpassing = form.querySelector('input[name="tmt_inpassing_akhir"]');
                if (applyGol && applyGol.checked) {
                    if (!tmtInpassing || !String(tmtInpassing.value || '').trim()) {
                        await Alert.warning('Periksa input', 'Isi TMT Inpassing Akhir jika ingin update Golongan berdasarkan TMT.', { confirmButtonText: 'Tutup' });
                        __releaseSubmitLock(__lockKey);
                        return;
                    }
                }

                // Conditional rule: to update Jabatan, TMT Jabatan must be filled
                const jabatan = form.querySelector('[name="jabatan"]');
                const tmtJabatan = form.querySelector('input[name="tmt_jabatan"]');
                if (jabatan && tmtJabatan) {
                    const cur = String(jabatan.value || '').trim();
                    const initial = String(jabatan.getAttribute('data-initial') || '').trim();
                    if (cur !== initial && !String(tmtJabatan.value || '').trim()) {
                        await Alert.warning('Periksa input', 'Isi TMT Jabatan jika ingin melakukan update Jabatan.', { confirmButtonText: 'Tutup' });
                        __releaseSubmitLock(__lockKey);
                        return;
                    }
                }

                // Block submit when there is no change at all
                const __hasAnyChange = (form) => {
                    if (!form) return false;
                    const els = Array.from(form.querySelectorAll('[name]'))
                        .filter((el) => {
                            if (!el) return false;
                            if (el.tagName === 'INPUT' && String(el.type || '').toLowerCase() === 'file') return false;
                            // ignore mirrors
                            if (el.classList && (el.classList.contains('__date_mirror') || el.classList.contains('__disabled_mirror'))) return false;
                            return true;
                        });

                    for (const el of els) {
                        const tag = String(el.tagName || '').toUpperCase();
                        const type = tag === 'INPUT' ? String(el.type || '').toLowerCase() : '';

                        if (type === 'checkbox') {
                            // consider change if checkbox state differs from its defaultChecked
                            if (!!el.checked !== !!el.defaultChecked) return true;
                            continue;
                        }

                        // Prefer data-initial when available (used by several selects)
                        const initialAttr = el.getAttribute('data-initial');
                        if (initialAttr !== null) {
                            if (String(el.value || '').trim() !== String(initialAttr || '').trim()) return true;
                            continue;
                        }

                        // Fallbacks for inputs/selects
                        if (typeof el.defaultValue !== 'undefined') {
                            if (String(el.value || '').trim() !== String(el.defaultValue || '').trim()) return true;
                        }
                    }
                    return false;
                };

                if (!__hasAnyChange(form)) {
                    await Alert.warning('Periksa input', 'Detail Data Dosen tidak ada perubahan. Tidak dapat melakukan simpan.', { confirmButtonText: 'Tutup' });
                    __releaseSubmitLock(__lockKey);
                    return;
                }

                if (!form.checkValidity()) {
                    await showFormInvalidModal(form);
                    __releaseSubmitLock(__lockKey);
                    return;
                }
            }

            const res = await Alert.question('Konfirmasi', 'Simpan perubahan data dosen?', {
                confirmButtonText: 'Simpan',
            });

            if (res && res.isConfirmed) {
                if (form) {
                    if (__isNewMode) ensureDisabledFieldSubmission(form);
                    ensureDmyDateSubmission(form);
                    __willSubmit = true;
                    __disableButton(btnSimpan, true);
                    try { form.submit(); return; }
                    catch (e) {
                        __disableButton(btnSimpan, false);
                        __releaseSubmitLock(__lockKey);
                        await Alert.error('Gagal', 'Submit gagal: ' + (e && e.message ? e.message : 'Unknown error'));
                        return;
                    }
                }
                await Alert.success('Berhasil', 'Konfirmasi diterima.');
            }

            // if user cancels or form not found
            if (!__willSubmit) {
                __releaseSubmitLock(__lockKey);
            }
        });
    }

    if (btnSimpanTab) {
        btnSimpanTab.addEventListener('click', async function () {
            const __lockKey = 'perubahan-data-dosen:' + (this && this.id ? this.id : 'btnSimpanPerubahanTab');
            if (!__acquireSubmitLock(__lockKey)) return;
            let __willSubmit = false;
            const toggle = document.getElementById('toggleEdit');
            if (!__isNewMode && (!toggle || !toggle.checked)) {
                await Alert.warning('Periksa input', 'Aktifkan Edit Mode terlebih dahulu untuk melakukan perubahan.', { confirmButtonText: 'Tutup' });
                __releaseSubmitLock(__lockKey);
                return;
            }
            const form = document.getElementById('formPerubahan');
            if (form) {
                // Conditional rule: if "Update Gol" checked, TMT Inpassing Akhir must be filled
                const applyGol = form.querySelector('input[name="apply_gol_by_tmt_inpassing"].js-apply-gol-by-tmt');
                const tmtInpassing = form.querySelector('input[name="tmt_inpassing_akhir"]');
                if (applyGol && applyGol.checked) {
                    if (!tmtInpassing || !String(tmtInpassing.value || '').trim()) {
                        await Alert.warning('Periksa input', 'Isi TMT Inpassing Akhir jika ingin update Golongan berdasarkan TMT.', { confirmButtonText: 'Tutup' });
                        __releaseSubmitLock(__lockKey);
                        return;
                    }
                }

                // Conditional rule: to update Jabatan, TMT Jabatan must be filled
                const jabatan = form.querySelector('[name="jabatan"]');
                const tmtJabatan = form.querySelector('input[name="tmt_jabatan"]');
                if (jabatan && tmtJabatan) {
                    const cur = String(jabatan.value || '').trim();
                    const initial = String(jabatan.getAttribute('data-initial') || '').trim();
                    if (cur !== initial && !String(tmtJabatan.value || '').trim()) {
                        await Alert.warning('Periksa input', 'Isi TMT Jabatan jika ingin melakukan update Jabatan.', { confirmButtonText: 'Tutup' });
                        __releaseSubmitLock(__lockKey);
                        return;
                    }
                }

                // Block submit when there is no change at all
                const __hasAnyChange = (form) => {
                    if (!form) return false;
                    const els = Array.from(form.querySelectorAll('[name]'))
                        .filter((el) => {
                            if (!el) return false;
                            if (el.tagName === 'INPUT' && String(el.type || '').toLowerCase() === 'file') return false;
                            if (el.classList && (el.classList.contains('__date_mirror') || el.classList.contains('__disabled_mirror'))) return false;
                            return true;
                        });

                    for (const el of els) {
                        const tag = String(el.tagName || '').toUpperCase();
                        const type = tag === 'INPUT' ? String(el.type || '').toLowerCase() : '';

                        if (type === 'checkbox') {
                            if (!!el.checked !== !!el.defaultChecked) return true;
                            continue;
                        }

                        const initialAttr = el.getAttribute('data-initial');
                        if (initialAttr !== null) {
                            if (String(el.value || '').trim() !== String(initialAttr || '').trim()) return true;
                            continue;
                        }

                        if (typeof el.defaultValue !== 'undefined') {
                            if (String(el.value || '').trim() !== String(el.defaultValue || '').trim()) return true;
                        }
                    }
                    return false;
                };

                if (!__hasAnyChange(form)) {
                    await Alert.warning('Periksa input', 'Detail Data Dosen tidak ada perubahan. Tidak dapat melakukan simpan.', { confirmButtonText: 'Tutup' });
                    __releaseSubmitLock(__lockKey);
                    return;
                }

                if (!form.checkValidity()) {
                    await showFormInvalidModal(form);
                    __releaseSubmitLock(__lockKey);
                    return;
                }
            }

            const res = await Alert.question('Konfirmasi', 'Simpan perubahan data dosen?', {
                confirmButtonText: 'Simpan',
            });

            if (res && res.isConfirmed) {
                if (form) {
                    if (__isNewMode) ensureDisabledFieldSubmission(form);
                    ensureDmyDateSubmission(form);
                    __willSubmit = true;
                    __disableButton(btnSimpanTab, true);
                    try { form.submit(); return; }
                    catch (e) {
                        __disableButton(btnSimpanTab, false);
                        __releaseSubmitLock(__lockKey);
                        await Alert.error('Gagal', 'Submit gagal: ' + (e && e.message ? e.message : 'Unknown error'));
                        return;
                    }
                }
                await Alert.success('Berhasil', 'Konfirmasi diterima.');
            }

            if (!__willSubmit) {
                __releaseSubmitLock(__lockKey);
            }
        });
    }
});

function redirectToDataDosen(identifier) {
    if (!identifier) {
        // fallback to data-dosen list if no identifier
        window.location.href = __urlDataDosen;
        return;
    }
    window.location.href = __urlUbahDataTemplate.replace('__IDENT__', encodeURIComponent(identifier));
}

function redirectToMKDosen(identifier) {
    if (!identifier) { window.location.href = __urlDataDosen; return; }
    window.location.href = __urlUbahMkGolTemplate.replace('__IDENT__', encodeURIComponent(identifier));
}

function redirectToUpdateDataDosen(identifier) {
    if (!identifier) { window.location.href = __urlDataDosen; return; }
    window.location.href = __urlUpdateDataTemplate.replace('__IDENT__', encodeURIComponent(identifier));
}

// Ensure default tab ('Pengaktifan') is shown on page load.
// This prevents the fragment/hash from auto-opening the 'Perubahan Data' tab.
document.addEventListener('DOMContentLoaded', function () {
    try {
        const params = new URLSearchParams(window.location.search);
        const open = (params.get('open') || '').toLowerCase();
        const pengaktifanBtn = document.getElementById('tab-pengaktifan-btn');
        const perubahanBtn = document.getElementById('tab-perubahan-btn');

        // If caller explicitly requested the Perubahan tab (via ?open=perubahan), open it.
        if (open === 'perubahan' && perubahanBtn) {
            setTimeout(() => {
                try { if (window.bootstrap && bootstrap.Tab) { bootstrap.Tab.getOrCreateInstance(perubahanBtn).show(); } else { perubahanBtn.click(); } } catch(e) {}
                // optionally remove query param to avoid re-trigger on reload
                try { history.replaceState(null, '', window.location.pathname + window.location.hash); } catch (e) {}
            }, 50);
            return;
        }

        // Otherwise ensure default tab remains 'Pengaktifan' (ignore URL hash)
        if (pengaktifanBtn) {
            setTimeout(() => {
                try { if (window.bootstrap && bootstrap.Tab) { bootstrap.Tab.getOrCreateInstance(pengaktifanBtn).show(); } else { pengaktifanBtn.click(); } } catch(e) {}
            }, 50);
        }
    } catch (e) {
        // ignore
    }
});
</script>

@endsection