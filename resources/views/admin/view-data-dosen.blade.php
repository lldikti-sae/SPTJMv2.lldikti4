@extends(
    \Illuminate\Support\Facades\Auth::guard('pts')->check()
        ? 'layouts/contentNavbarLayoutPts'
        : ((auth()->check() && method_exists(auth()->user(), 'isPIC') && auth()->user()->isPIC())
            ? 'layouts/contentNavbarLayoutPic'
            : 'layouts/contentNavbarLayout')
)

@section('title', 'SPTJM Online')

@section('page-style')
<style>
/* ── Page Header ── */
.vdd-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
}
.vdd-page-header .page-titles h4 {
    font-size: 1.35rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 4px 0;
    line-height: 1.2;
}
.vdd-page-header .breadcrumb {
    margin: 0;
    font-size: 0.8rem;
    background: none;
    padding: 0;
}
.vdd-page-header .breadcrumb-item a { color: #696cff; text-decoration: none; }
.vdd-page-header .breadcrumb-item.active { color: #8592a3; }
.vdd-page-header .breadcrumb-item + .breadcrumb-item::before { color: #8592a3; }

/* ── Card ── */
.vdd-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(44,62,80,0.07);
    overflow: hidden;
}
.vdd-card-inner { padding: 20px 24px 24px; }

/* ── Section Divider ── */
.vdd-section-title {
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #1a56db;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid #eef2ff;
    display: flex;
    align-items: center;
    gap: 8px;
}
.vdd-section-title i {
    font-size: 1rem;
}

/* ── Detail Field ── */
.vdd-field {
    margin-bottom: 14px;
}
.vdd-field-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #8592a3;
    margin-bottom: 4px;
}
.vdd-field-value {
    font-size: 0.88rem;
    font-weight: 600;
    color: #374151;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
    min-height: 38px;
    display: flex;
    align-items: center;
}
.vdd-field-value.vdd-na {
    color: #a0aec0;
    font-style: italic;
    font-weight: 500;
}

/* ── Status Badges ── */
.vdd-badge-aktif {
    background-color: rgba(40,199,111,0.12);
    color: #28c76f;
    font-weight: 700;
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    display: inline-block;
}
.vdd-badge-nonaktif {
    background-color: rgba(234,84,85,0.12);
    color: #ea5455;
    font-weight: 700;
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    display: inline-block;
}
.vdd-badge-ya {
    background-color: rgba(40,199,111,0.12);
    color: #28c76f;
    font-weight: 700;
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    display: inline-block;
}
.vdd-badge-tidak {
    background-color: rgba(234,84,85,0.12);
    color: #ea5455;
    font-weight: 700;
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    display: inline-block;
}
.vdd-badge-na {
    background-color: rgba(133,146,163,0.12);
    color: #8592a3;
    font-weight: 700;
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    display: inline-block;
}

/* ── Profile Header ── */
.vdd-profile-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 100%);
    border-radius: 8px;
    margin-bottom: 24px;
}
.vdd-profile-avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #1a56db;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.vdd-profile-info h5 {
    margin: 0 0 2px 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
}
.vdd-profile-info .vdd-profile-subtitle {
    font-size: 0.82rem;
    color: #8592a3;
    margin: 0;
}

/* ── Back Button ── */
.btn-kembali-vdd {
    background-color: #f1f3f5;
    border: 1px solid #e2e8f0;
    color: #4a5568;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 8px 18px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s, box-shadow 0.2s;
    white-space: nowrap;
    text-decoration: none;
}
.btn-kembali-vdd:hover {
    background-color: #e2e8f0;
    color: #2d3748;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
</style>
@endsection

@section('content')

@php
    // For display-only fields: replace empty / '-' with a friendly label.
    $__displayOrNA = function ($v) {
        $s = trim((string) $v);
        return ($s === '' || $s === '-') ? 'Data Tidak Tersedia' : $s;
    };

    $__isNA = function ($v) {
        $s = trim((string) $v);
        return ($s === '' || $s === '-');
    };

    // Format date values for display (d/m/Y) accepting various parseable strings
    $__formatDateDisplay = function($v) {
        if (empty($v)) return '';
        try {
            if (strpos($v, '/') !== false) {
                $d = \Carbon\Carbon::createFromFormat('d/m/Y', $v);
            } else {
                $d = \Carbon\Carbon::parse($v);
            }
            return $d->format('d/m/Y');
        } catch (\Exception $e) {
            return $v;
        }
    };
@endphp

@php
    $__tglLahir = trim((string)($dosen->Tanggal_Lahir ?? ''));
    $__ttl = trim((string)($dosen->TTL ?? ''));
    $__ttlDisplay = ($__tglLahir === '' || $__tglLahir === '-' || $__ttl === '' || $__ttl === '-')
        ? 'Data Tidak Tersedia'
        : ($__ttl . ', ' . $__tglLahir);

    $__kodePts = trim((string)($dosen->Kode_PT ?? ''));
    $__namaPts = trim((string)($dosen->PTS ?? ''));
    $__kodePtsDisplay = ($__kodePts === '' || $__kodePts === '-')
        ? 'Data Tidak Tersedia'
        : ($__kodePts . ' - ' . ($__namaPts !== '' && $__namaPts !== '-' ? $__namaPts : '-'));

    $__rawAktif = $dosen->aktif ?? ($dosen->Aktif ?? null);
    $__aktifStr = strtolower(trim((string)$__rawAktif));
    if ($__aktifStr === '') {
        $__statusDisplay = 'Data Tidak Tersedia';
        $__statusType = 'na';
    } elseif ($__aktifStr === 'aktif') {
        $__statusDisplay = 'AKTIF';
        $__statusType = 'aktif';
    } elseif ($__aktifStr === 'tidak aktif' || $__aktifStr === 'nonaktif' || $__aktifStr === 'tidak') {
        $__statusDisplay = 'TIDAK AKTIF';
        $__statusType = 'nonaktif';
    } else {
        $__statusDisplay = ((int)$__rawAktif) === 1 ? 'AKTIF' : 'TIDAK AKTIF';
        $__statusType = ((int)$__rawAktif) === 1 ? 'aktif' : 'nonaktif';
    }

    $__eligibleRaw = strtoupper(trim((string)($dosen->Eligible_span ?? '')));
    if (in_array($__eligibleRaw, ['YA','Y','1','TRUE'], true)) {
        $__eligibleDisplay = 'YA';
        $__eligibleType = 'ya';
    } elseif (in_array($__eligibleRaw, ['TIDAK','TDK','N','0','FALSE','NO'], true)) {
        $__eligibleDisplay = 'TIDAK';
        $__eligibleType = 'tidak';
    } else {
        $__eligibleDisplay = ($__eligibleRaw === '' || $__eligibleRaw === '-') ? 'Data Tidak Tersedia' : $__eligibleRaw;
        $__eligibleType = 'na';
    }

    $__tmtJadPertama = trim((string)($dosen->TMT_JAD_Pertama ?? ''));
    $__tmtJadAkhir = trim((string)($dosen->TMT_JAD_Akhir ?? ''));
    $__tmtInpassingAkhir = trim((string)($dosen->TMT_Inpassing_Akhir ?? ''));
@endphp

{{-- Page Header --}}
<div class="vdd-page-header">
    <div class="page-titles">
        <h4>Detail Data Dosen</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Dosen</a></li>
                <li class="breadcrumb-item active">Lihat Detail</li>
            </ol>
        </nav>
    </div>
    <a href="{{ url()->previous() }}" class="btn-kembali-vdd" onclick="event.preventDefault(); if (history.length > 1) { history.back(); } else { window.location.href = this.href; }">
        <i class="bx bx-arrow-back"></i> Kembali
    </a>
</div>

{{-- Main Card --}}
<div class="vdd-card">
    <div class="vdd-card-inner">

        {{-- Profile Header --}}
        <div class="vdd-profile-header">
            <div class="vdd-profile-avatar">
                <i class="bx bx-user"></i>
            </div>
            <div class="vdd-profile-info">
                <h5>{{ $__displayOrNA($dosen->Nama ?? null) }}</h5>
                <p class="vdd-profile-subtitle">
                    {{ $__displayOrNA($dosen->NIDN ?? null) }}
                    @if(!$__isNA($dosen->Jenis ?? null))
                        &nbsp;·&nbsp; {{ $dosen->Jenis }}
                    @endif
                    &nbsp;&nbsp;
                    <span class="vdd-badge-{{ $__statusType }}">{{ $__statusDisplay }}</span>
                </p>
            </div>
        </div>

        {{-- Section: Identitas --}}
        <div class="vdd-section-title"><i class="bx bx-id-card"></i> Identitas</div>
        <div class="row">
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">NIDN</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->NIDN ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->NIDN ?? null) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">NUPTK</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->NUPTK ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->NUPTK ?? null) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">NIK</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->NIK ?? ($dosen->nik ?? null)) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->NIK ?? ($dosen->nik ?? null)) }}</div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Nama</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->Nama ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->Nama ?? null) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Jenis</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->Jenis ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->Jenis ?? null) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Tempat, Tanggal Lahir</div>
                    <div class="vdd-field-value {{ ($__ttlDisplay === 'Data Tidak Tersedia') ? 'vdd-na' : '' }}">{{ $__ttlDisplay }}</div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Usia</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->Usia ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->Usia ?? null) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">NPWP</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->NPWP ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->NPWP ?? null) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Status</div>
                    <div class="vdd-field-value">
                        <span class="vdd-badge-{{ $__statusType }}">{{ $__statusDisplay }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: Kepegawaian --}}
        <div class="vdd-section-title mt-3"><i class="bx bx-briefcase"></i> Kepegawaian</div>
        <div class="row">
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Sertifikat Dosen</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->Sertifikat_Dosen ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->Sertifikat_Dosen ?? null) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Tahun Lulus</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->Tahun_Lulus ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->Tahun_Lulus ?? null) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Jabatan</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->jabatan ?? ($dosen->Jabatan ?? null)) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->jabatan ?? ($dosen->Jabatan ?? null)) }}</div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Golongan</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->gol ?? ($dosen->Gol ?? null)) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->gol ?? ($dosen->Gol ?? null)) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Masa Kerja</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->masa_kerja ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->masa_kerja ?? null) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Gaji</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->gaji ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->gaji ?? null) }}</div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">TMT JAD Pertama</div>
                    <div class="vdd-field-value {{ $__isNA($__tmtJadPertama) ? 'vdd-na' : '' }}">{{ $__isNA($__tmtJadPertama) ? 'Data Tidak Tersedia' : call_user_func($__formatDateDisplay, $__tmtJadPertama) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">TMT JAD Akhir</div>
                    <div class="vdd-field-value {{ $__isNA($__tmtJadAkhir) ? 'vdd-na' : '' }}">{{ $__isNA($__tmtJadAkhir) ? 'Data Tidak Tersedia' : call_user_func($__formatDateDisplay, $__tmtJadAkhir) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">TMT Inpassing Akhir</div>
                    <div class="vdd-field-value {{ $__isNA($__tmtInpassingAkhir) ? 'vdd-na' : '' }}">{{ $__isNA($__tmtInpassingAkhir) ? 'Data Tidak Tersedia' : call_user_func($__formatDateDisplay, $__tmtInpassingAkhir) }}</div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Inpassing</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->Inpassing ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->Inpassing ?? null) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Eligible Span</div>
                    <div class="vdd-field-value">
                        <span class="vdd-badge-{{ $__eligibleType }}">{{ $__eligibleDisplay }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Pemegang Wilayah</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->Pemegang_Wilayah ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->Pemegang_Wilayah ?? null) }}</div>
                </div>
            </div>
        </div>

        {{-- Section: Perguruan Tinggi --}}
        <div class="vdd-section-title mt-3"><i class="bx bxs-graduation"></i> Perguruan Tinggi</div>
        <div class="row">
            <div class="col-md-6">
                <div class="vdd-field">
                    <div class="vdd-field-label">Kode PTS / Nama PTS</div>
                    <div class="vdd-field-value {{ ($__kodePtsDisplay === 'Data Tidak Tersedia') ? 'vdd-na' : '' }}">{{ $__kodePtsDisplay }}</div>
                </div>
            </div>
        </div>

        {{-- Section: Informasi Keuangan --}}
        <div class="vdd-section-title mt-3"><i class="bx bx-wallet"></i> Informasi Keuangan</div>
        <div class="row">
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">No. Rekening</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->No_Rekening ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->No_Rekening ?? null) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Bank</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->Bank ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->Bank ?? null) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Nama Rekening</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->Nama_Rekening ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->Nama_Rekening ?? null) }}</div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="vdd-field">
                    <div class="vdd-field-label">Nama Supplier</div>
                    <div class="vdd-field-value {{ $__isNA($dosen->Nama_Penerima ?? null) ? 'vdd-na' : '' }}">{{ $__displayOrNA($dosen->Nama_Penerima ?? null) }}</div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection