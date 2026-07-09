@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online - Lihat Histori Dosen')

@section('content')
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

.btn-back-md {
    background-color: #fff;
    color: var(--md-text-main);
    border: 1px solid var(--md-border);
    padding: 8px 16px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-back-md:hover { background-color: var(--md-bg-gray); color: var(--md-primary); }

/* ── Card & Table Container ── */
.md-card {
    background: #fff;
    border-radius: var(--md-radius-lg);
    box-shadow: var(--md-shadow);
    border: 1px solid rgba(226, 232, 240, 0.8);
    overflow: hidden;
}
.md-card-inner { padding: 20px 24px; }

/* ── Toolbar ── */
.md-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}
.dataTables_length {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.84rem;
    color: var(--md-text-muted);
}
.dataTables_length select {
    border: 1px solid var(--md-border);
    border-radius: 6px;
    padding: 5px 10px;
    font-size: 0.84rem;
    color: var(--md-text-muted);
    background: var(--md-bg-gray);
    cursor: pointer;
    outline: none;
}
.dataTables_filter {
    display: flex;
    align-items: center;
    gap: 8px;
}
.dataTables_filter label {
    font-size: 0.84rem;
    color: var(--md-text-muted);
    display: flex;
    align-items: center;
    gap: 8px;
}
.dataTables_filter input {
    border: 1px solid var(--md-border);
    border-radius: 6px;
    padding: 6px 36px 6px 14px;
    font-size: 0.84rem;
    color: var(--md-text-main);
    background: var(--md-bg-gray) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%238592a3' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat calc(100% - 10px) center;
    min-width: 240px;
    outline: none;
    transition: border-color 0.2s;
}
.dataTables_filter input:focus { border-color: var(--md-primary); background-color: #fff; }



</style>

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h4>Lihat Histori Data Dosen</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Dosen</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.histori-dosen') }}">Histori Data Dosen</a></li>
                <li class="breadcrumb-item active">Lihat Histori</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.histori-dosen') }}" class="btn-back-md">
            <i class="bx bx-arrow-back"></i> Kembali
        </a>
    </div>
</div>

{{-- Main Card --}}
<div class="md-card">
    <div class="md-card-inner">
        {{-- Toolbar akan di-inject oleh DataTables --}}

        {{-- Table --}}
        <div class="md-table-wrap table-responsive text-nowrap">
            <table class="table table-hover" id="lihatHistoriTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>No Dokumen</th>
                        <th>Tanggal Dokumen</th>
                        <th>Tgl Update Terbaru</th>
                        <th>Dokumen</th>
                        <th>NIDN</th>
                        <th>Nama</th>
                        <th>Alasan Perubahan</th>
                        <th>Keterangan</th>
                        <th>Pengguna</th>
                        <th>TMT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dosen as $d)
                    <tr>
                        <td>{{ $d->no_dokumen_ubah }}</td>
                        <td>{{ $d->tgl_dokumen_ubah }}</td>
                        <td>{{ $d->tanggal_update_terbaru ?? '-' }}</td>
                        <td>
                            @if($d->dokumen)
                            <a href="{{ asset('storage/Dokumen_Histori_Dosen2/' . $d->dokumen) }}" target="_blank" class="btn-link-dokumen">
                                <i class="bx bx-file"></i> Lihat Dokumen
                            </a>
                            @else
                            -
                            @endif
                        </td>
                        <td>{{ Str::mask($d->nidn, '*', 0, 0) }}</td>
                        <td>{{ $d->nama }}</td>
                        <td>{{ $d->alasan_perubahan }}</td>
                        <td>{{ $d->keterangan }}</td>
                        <td>{{ $d->pengguna }}</td>
                        <td>{{ $d->tanggal_update_terakhir }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- jQuery (asumsi diload di layout, jika belum maka butuh script tag jquery) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery && window.jQuery.fn.DataTable) {
        window.jQuery('#lihatHistoriTable').DataTable({
            pageLength: 15,
            lengthMenu: [[15, 25, 50, 100, 500], [15, 25, 50, 100, 500]],
            dom: "<'md-toolbar'<'entries-wrap'l><'search-wrap'f>>" +
                 "rt<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            language: {
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "→",
                    previous: "←",
                },
                zeroRecords: "Data tidak ditemukan",
                infoEmpty: "Tidak ada data tersedia",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                search: "Cari Data:",
                searchPlaceholder: "Search...",
                lengthMenu: "Show _MENU_ entries"
            },
        });
    } else {
        // Fallback filter jika DataTables gagal diload
        console.warn('DataTables not available, using fallback filter.');
        const searchHtml = document.createElement('div');
        searchHtml.className = 'md-toolbar';
        searchHtml.innerHTML = `<div class="search-wrap" style="margin-left: auto;"><label style="font-size:0.84rem;color:#4a5568;display:flex;align-items:center;gap:8px;">Cari Data: <input type="text" id="fallbackSearch" class="form-control" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 14px; min-width: 240px;"></label></div>`;
        document.querySelector('.md-table-wrap').parentNode.insertBefore(searchHtml, document.querySelector('.md-table-wrap'));
        
        document.getElementById("fallbackSearch").addEventListener("keyup", function() {
            var filter = this.value.toLowerCase();
            var rows = document.querySelectorAll("#lihatHistoriTable tbody tr");
            rows.forEach(function(row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            });
        });
    }
});
</script>
@endsection

