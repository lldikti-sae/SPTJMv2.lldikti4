@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online - Monitoring Usulan Dosen')

@section('page-style')
<style>
/* ── Variables & Setup ── */
:root {
    --md-primary: #0b3d91;
    --md-primary-hover: #082d6b;
    --md-bg-gray: #f8fafc;
    --md-border: #e2e8f0;
    --md-text-main: #1e293b;
    --md-text-muted: #64748b;
    --md-radius-lg: 12px;
    --md-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

/* ── Page Header ── */
.md-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    background: transparent;
    padding: 0;
    flex-wrap: wrap;
    gap: 12px;
}
.md-page-header .page-titles h4 {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--md-text-main);
    margin: 0 0 4px 0;
}
.md-page-header .breadcrumb { margin: 0; padding: 0; background: transparent; font-size: 0.85rem; }
.md-page-header .breadcrumb-item a { color: var(--md-text-muted); text-decoration: none; }
.md-page-header .breadcrumb-item.active { color: var(--md-primary); font-weight: 600; }
.md-page-header .breadcrumb-item + .breadcrumb-item::before { color: #cbd5e1; }

/* ── Header Action Buttons ── */
.btn-export-md {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: #fff;
    border: none;
    padding: 8px 18px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.btn-export-md:hover { opacity: 0.9; transform: translateY(-1px); color: #fff; }

.btn-tampilkan-md {
    background: linear-gradient(135deg, var(--md-primary) 0%, var(--md-primary-hover) 100%);
    color: #fff;
    border: none;
    padding: 8px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-tampilkan-md:hover { opacity: 0.9; transform: translateY(-1px); }

/* ── Card & Table Container ── */
.md-card {
    background: #fff;
    border-radius: var(--md-radius-lg);
    box-shadow: var(--md-shadow);
    border: 1px solid rgba(226, 232, 240, 0.8);
    overflow: hidden;
}
.md-card-inner { padding: 20px 24px; }

/* ── Filter Card ── */
.md-filter-card {
    background: var(--md-bg-gray);
    border: 1px solid var(--md-border);
    border-radius: 10px;
    padding: 20px 24px;
    margin-bottom: 20px;
}
.md-filter-card .filter-title {
    font-size: 0.82rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--md-text-muted);
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.md-filter-card label {
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--md-text-main);
    margin-bottom: 4px;
}
.md-filter-card .form-control,
.md-filter-card .form-select {
    border: 1px solid var(--md-border);
    border-radius: 8px;
    font-size: 0.88rem;
    padding: 8px 12px;
    color: var(--md-text-main);
    background-color: #fff;
    transition: border-color 0.2s;
}
.md-filter-card .form-control:focus,
.md-filter-card .form-select:focus {
    border-color: var(--md-primary);
    box-shadow: 0 0 0 2px rgba(11, 61, 145, 0.08);
}
.md-filter-card .separator-label {
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--md-text-muted);
    display: flex;
    align-items: flex-end;
    padding-bottom: 10px;
}

/* ── Toolbar ── */
.md-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}
.md-toolbar .entries-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.84rem;
    color: var(--md-text-muted);
}
.md-toolbar .entries-wrap select {
    border: 1px solid var(--md-border);
    border-radius: 6px;
    padding: 5px 10px;
    font-size: 0.84rem;
    color: var(--md-text-muted);
    background: var(--md-bg-gray);
    cursor: pointer;
    outline: none;
}



/* ── Custom Badges ── */
.badge-bulan {
    background-color: #fef3c7;
    color: #92400e;
    font-weight: 700;
    font-size: 0.78rem;
    padding: 5px 14px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    border: none;
}
.badge-bulan:hover {
    background-color: #fde68a;
    transform: scale(1.05);
}
.badge-bulan.high {
    background-color: #fee2e2;
    color: #b91c1c;
}
.badge-bulan.high:hover {
    background-color: #fecaca;
}
.badge-jenis {
    background-color: #e0e7ff;
    color: #3730a3;
    font-weight: 600;
    font-size: 0.75rem;
    padding: 3px 10px;
    border-radius: 20px;
    display: inline-block;
}



/* ── Modal ── */
.modal-content {
    border-radius: 12px;
    border: none;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}
.modal-header {
    background: var(--md-bg-gray);
    border-bottom: 1px solid var(--md-border);
    border-radius: 12px 12px 0 0;
    padding: 16px 20px;
}
.modal-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--md-text-main);
}
.modal-body {
    padding: 20px;
}
.modal-body .detail-name {
    font-size: 0.92rem;
    font-weight: 600;
    color: var(--md-text-main);
    margin-bottom: 12px;
}
.modal-body .detail-list {
    background: var(--md-bg-gray);
    border: 1px solid var(--md-border);
    border-radius: 8px;
    padding: 14px 16px;
    font-family: 'Courier New', monospace;
    font-size: 0.82rem;
    color: var(--md-text-main);
    line-height: 1.8;
    white-space: pre-wrap;
}
.modal-body .detail-badge {
    background-color: #fee2e2;
    color: #b91c1c;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
    display: inline-block;
}
</style>
@endsection

@section('content')

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h4>Monitoring Usulan Dosen</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Dosen</a></li>
                <li class="breadcrumb-item active">Monitoring Usulan Dosen</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.export-monitoring-usulan-dosen', request()->query()) }}" target="_blank" class="btn-export-md">
            <i class="bx bx-download"></i> Export XLS
        </a>
    </div>
</div>

{{-- Main Card --}}
<div class="md-card">
    <div class="md-card-inner">

        {{-- Filter Section --}}
        <div class="md-filter-card">
            <div class="filter-title">
                <i class="bx bx-filter-alt"></i> Filter Periode & Pencarian
            </div>
            <form class="row gx-3 gy-2 align-items-end" method="GET" action="{{ route('admin.monitoring-usulan-dosen') }}">
                <div class="col-md-3">
                    <label for="searchInput">NIDN / NUPTK / Nama</label>
                    <input type="text" class="form-control" id="searchInput" name="search"
                        placeholder="Cari data..." value="{{ request('search') }}">
                </div>

                <div class="col-md-2">
                    <label for="awalPeriode">Periode Awal</label>
                    <select id="awalPeriode" name="awalPeriode" class="form-select">
                        @foreach ($bulanIndonesia as $key => $bulan)
                        <option value="{{ $key }}" {{ request('awalPeriode') == $key ? 'selected' : '' }}>
                            {{ $bulan }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto separator-label">
                    s.d
                </div>

                <div class="col-md-2">
                    <label for="akhirPeriode">Periode Akhir</label>
                    <select id="akhirPeriode" name="akhirPeriode" class="form-select">
                        @foreach ($bulanIndonesia as $key => $bulan)
                        <option value="{{ $key }}" {{ request('akhirPeriode', now()->month) == $key ? 'selected' : '' }}>
                            {{ $bulan }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <button type="submit" class="btn-tampilkan-md">
                        <i class="bx bx-search-alt"></i> Tampilkan
                    </button>
                </div>
            </form>
        </div>

        {{-- Toolbar --}}
        <div class="md-toolbar">
            <div class="entries-wrap">
                <span>Show</span>
                <select id="perPageSelect">
                    @foreach ([15,25,50,100] as $pp)
                    <option value="{{ $pp }}" {{ request('perPage', 15) == $pp ? 'selected' : '' }}>{{ $pp }}</option>
                    @endforeach
                </select>
                <span>entries</span>
            </div>
        </div>

        {{-- Table --}}
        <div class="md-table-wrap table-responsive text-nowrap">
            <table class="table table-hover" id="monitoringTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>NIDN</th>
                        <th>NUPTK</th>
                        <th>Nama Dosen</th>
                        <th>Jenis</th>
                        <th>Kode PT</th>
                        <th>Nama PTS</th>
                        <th style="width: 120px;">Bulan Belum Usulan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dosenList as $data)
                    <tr>
                        <td class="text-center">{{ $dosenList->firstItem() ? $dosenList->firstItem() + $loop->index : $loop->iteration }}</td>
                        <td>{{ $data->NIDN }}</td>
                        <td>{{ $data->NUPTK ?: '-' }}</td>
                        <td style="white-space: normal; max-width: 220px;">{{ $data->Nama }}</td>
                        <td class="text-center"><span class="badge-jenis">{{ $data->Jenis }}</span></td>
                        <td class="text-center">{{ $data->Kode_PT }}</td>
                        <td style="white-space: normal; max-width: 220px;">{{ $data->PTS }}</td>
                        <td class="text-center">
                            <button type="button"
                                class="badge-bulan {{ $data->bulan_belum_usulan >= 3 ? 'high' : '' }}"
                                onclick="showDetailModal('{{ addslashes($data->Nama) }}', '{{ addslashes($data->kode_belum_usulan) }}')">
                                <i class="bx bx-calendar-x" style="font-size: 0.85rem;"></i>
                                {{ $data->bulan_belum_usulan }} Bulan
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 40px 16px; color: var(--md-text-muted);">
                            <i class="bx bx-check-circle" style="font-size: 2rem; display: block; margin-bottom: 8px; color: #059669;"></i>
                            Semua dosen aktif sudah memiliki usulan pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($dosenList->hasPages())
        <div class="md-pagination">
            {{ $dosenList->links('pagination::simple-bootstrap-5') }}
        </div>
        @endif

    </div>
</div>

{{-- Modal Detail Bulan --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-calendar-exclamation" style="color: var(--md-primary);"></i>
                    Detail Bulan Belum Diusulkan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="detail-name">
                    <i class="bx bx-user" style="color: var(--md-primary);"></i>
                    <span id="modalNamaDosen"></span>
                </div>
                <div class="detail-list" id="modalListBulan"></div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
// Tampilkan Detail Modal
function showDetailModal(nama, kodeBelum) {
    document.getElementById('modalNamaDosen').textContent = nama;
    const kodeList = kodeBelum.split(',').map(k => k.trim()).filter(k => k !== '');
    const nmBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober',
        'November', 'Desember'
    ];
    const formatted = kodeList.map((bulan) => {
        const index = nmBulan.indexOf(bulan);
        const kodeUsulan = index !== -1 ? `KodeUsulan${index+1}` : `????`;
        const padded = `${kodeUsulan} (${bulan})`.padEnd(28, ' ');
        return `${padded}  ⚠ Belum Diusulkan`;
    }).join('\n');
    document.getElementById('modalListBulan').innerHTML = formatted;
    const modal = new bootstrap.Modal(document.getElementById('modalDetail'));
    modal.show();
}
</script>
<script>
// Update perPage via URL (keeps other query params)
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('perPageSelect');
    if (!sel) return;
    sel.addEventListener('change', function () {
        const params = new URLSearchParams(window.location.search);
        params.set('perPage', this.value);
        params.delete('page'); // reset page to first
        const qs = params.toString();
        window.location.search = qs ? `?${qs}` : '';
    });
});
</script>
@endsection


