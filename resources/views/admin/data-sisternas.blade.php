@php
    $layout = 'layouts/contentNavbarLayout';
    if(auth()->user() instanceof \App\Models\APts) {
        $layout = 'layouts/contentNavbarLayoutPts';
    } elseif(auth()->user() instanceof \App\Models\User && auth()->user()->isPIC()) {
        $layout = 'layouts/contentNavbarLayoutPic';
    }
@endphp
@extends($layout)

@section('title', 'SPTJM Online')

@section('content')
<style>
    /* Spacing simetris konten dari navbar */
    .content-wrapper > div.container-p-y {
        padding-top: 0.75rem !important;
    }
    /* Spacing simetris breadcrumb ke card */
    .md2-page-header {
        margin-top: 0 !important;
        margin-bottom: 8px !important;
    }

    /* ===== History Data Sisternas — Clean Minimal Design ===== */
    .hs-page-wrapper {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 14px 32px 32px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }

    /* --- Page Header inside card --- */
    .hs-page-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 2px;
        line-height: 1.3;
    }
    .hs-page-subtitle {
        font-size: 0.84rem;
        color: #6b7280;
        margin-bottom: 0;
    }

    /* --- Upload Form Panel (Admin only) --- */
    .hs-upload-panel {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 18px 20px 14px;
        margin-top: 20px;
        margin-bottom: 8px;
    }
    .hs-upload-panel-title {
        font-size: 0.78rem;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 0.055em;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .hs-upload-panel .form-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.045em;
        margin-bottom: 5px;
    }
    .hs-upload-panel .form-select,
    .hs-upload-panel .form-control {
        font-size: 0.84rem;
        border-color: #d1d5db;
        border-radius: 7px;
        color: #374151;
        background-color: #ffffff;
    }
    .hs-upload-panel .form-select:focus,
    .hs-upload-panel .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }
    .hs-btn-save {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background-color: #16a34a;
        color: #ffffff;
        border: none;
        border-radius: 7px;
        font-size: 0.84rem;
        font-weight: 600;
        padding: 8px 20px;
        cursor: pointer;
        transition: background 0.18s ease, transform 0.12s ease;
    }
    .hs-btn-save:hover {
        background-color: #15803d;
        transform: translateY(-1px);
    }
    .hs-btn-save:active {
        transform: translateY(0);
    }

    /* --- Toolbar (search) --- */
    .hs-toolbar {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 6px;
        margin-bottom: 0;
        flex-wrap: wrap;
    }
    .hs-search-wrap {
        position: relative;
    }
    .hs-search-wrap input {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 7px 14px 7px 36px;
        font-size: 0.84rem;
        color: #374151;
        min-width: 230px;
        outline: none;
        transition: border-color 0.18s, box-shadow 0.18s;
        background: #f9fafb;
    }
    .hs-search-wrap input::placeholder {
        color: #9ca3af;
    }
    .hs-search-wrap input:focus {
        border-color: #3b82f6;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
    }
    .hs-search-icon {
        position: absolute;
        left: 11px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 1rem;
        pointer-events: none;
    }

    /* --- Table --- */
    .hs-table-wrap {
        margin-top: 4px;
        overflow-x: auto;
    }
    .hs-table {
        width: 100%;
        border-collapse: collapse;
    }
    .hs-table thead tr {
        border-bottom: 1.5px solid #e5e7eb;
    }
    .hs-table thead th {
        font-size: 0.78rem;
        font-weight: 600;
        color: #6b7280;
        padding: 10px 14px;
        background: transparent;
        border: none;
        white-space: nowrap;
    }
    .hs-table tbody tr {
        border-bottom: 1px solid #f3f4f6;
        transition: background 0.12s ease;
    }
    .hs-table tbody tr:last-child {
        border-bottom: none;
    }
    .hs-table tbody tr:hover {
        background-color: #f9fafb;
    }
    .hs-table tbody td {
        padding: 13px 14px;
        font-size: 0.85rem;
        color: #374151;
        border: none;
        vertical-align: middle;
    }

    /* Periode cell */
    .hs-periode-label {
        font-weight: 700;
        font-size: 0.88rem;
        color: #111827;
        line-height: 1.3;
    }
    .hs-periode-sub {
        font-size: 0.78rem;
        color: #6b7280;
        margin-top: 1px;
    }

    /* Tanggal cut off */
    .hs-cutoff-date {
        font-size: 0.85rem;
        color: #374151;
        font-weight: 500;
    }

    /* Dokumen link */
    .hs-dokumen-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #2563eb;
        font-size: 0.84rem;
        font-weight: 500;
        text-decoration: none;
        transition: color 0.15s ease;
    }
    .hs-dokumen-link:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }
    .hs-dokumen-link i {
        font-size: 1.05rem;
        color: #6b7280;
        flex-shrink: 0;
    }

    /* Delete button */
    .hs-btn-delete {
        background: transparent;
        border: none;
        padding: 5px 8px;
        border-radius: 6px;
        color: #dc2626;
        font-size: 1.1rem;
        cursor: pointer;
        line-height: 1;
        transition: background 0.15s, color 0.15s, transform 0.12s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .hs-btn-delete:hover {
        background-color: #fef2f2;
        color: #b91c1c;
        transform: scale(1.1);
    }
    .hs-btn-delete:active {
        transform: scale(0.96);
    }

    /* Empty state */
    .hs-empty {
        text-align: center;
        padding: 48px 24px;
        color: #9ca3af;
        font-size: 0.88rem;
    }
    .hs-empty i {
        font-size: 2.5rem;
        display: block;
        margin-bottom: 10px;
        color: #d1d5db;
    }

    /* Pagination */
    .hs-pagination-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid #f3f4f6;
    }
    .hs-pagination-info {
        font-size: 0.80rem;
        color: #9ca3af;
    }
    .hs-pagination-btns {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .hs-pg-btn {
        width: 32px;
        height: 32px;
        border-radius: 7px;
        border: 1px solid #e5e7eb;
        background: #fff;
        font-size: 0.82rem;
        color: #374151;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s ease;
        text-decoration: none;
    }
    .hs-pg-btn:hover,
    .hs-pg-btn.active {
        background: #111827;
        border-color: #111827;
        color: #fff;
    }
    .hs-pg-btn.disabled {
        opacity: 0.4;
        pointer-events: none;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Breadcrumb Header --}}
<div class="md2-page-header mb-0" style="margin-bottom:4px !important;">
    <div class="page-titles">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Master Data</a></li>
                <li class="breadcrumb-item active">History Data Sisternas</li>
            </ol>
        </nav>
    </div>
</div>

<div class="hs-page-wrapper">
    {{-- Title & Subtitle --}}
    <div>
        <h1 class="hs-page-title">History data sisternas</h1>
        <p class="hs-page-subtitle">Riwayat dokumen yang sudah diunggah</p>
    </div>
    {{-- Toolbar: Search --}}
    <div class="hs-toolbar">
        <div class="hs-search-wrap">
            <i class="bx bx-search hs-search-icon"></i>
            <input type="text" id="sisternasSearchInput" placeholder="Cari periode, tahun, atau bulan...">
        </div>
    </div>

    {{-- Table --}}
    <div class="hs-table-wrap">
        <table class="hs-table" id="sisternasTable">
            <thead>
                <tr>
                    <th style="width: 18%;">Periode</th>
                    <th style="width: 15%;">Cut off</th>
                    <th>Dokumen</th>
                    @if(auth()->user() instanceof \App\Models\User && auth()->user()->isAdmin())
                    <th style="width: 80px; text-align: center;">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($dataSisternas as $item)
                @php
                    // Derive semester number from periode string
                    $isGanjil = stripos($item->periode, 'ganjil') !== false;
                    $semesterNum = $isGanjil ? '1' : '2';
                    $periodeLabel = $item->tahun . '/' . $semesterNum;
                    $jenisPeriode = $isGanjil ? 'ganjil' : 'genap';
                @endphp
                <tr>
                    <td>
                        <div class="hs-periode-label">{{ $periodeLabel }}</div>
                    </td>
                    <td>
                        <span class="hs-cutoff-date" style="display: block;">{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</span>
                        @if($item->created_at)
                            <span style="font-size: 0.75rem; color: #6b7280;"><i class="bx bx-time-five"></i> {{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }} WIB</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ asset('storage/File_Data_Sisternas2/' . $item->dokumen) }}" target="_blank" class="hs-dokumen-link">
                            <i class="bx bxs-file-pdf"></i>
                            {{ $item->dokumen }}
                        </a>
                    </td>
                    @if(auth()->user() instanceof \App\Models\User && auth()->user()->isAdmin())
                    <td style="text-align: center;">
                        <form action="{{ route('data-sisternas.destroy', $item->id) }}" method="POST"
                            class="delete-form d-inline" onsubmit="return false;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="hs-btn-delete delete-sisternas" title="Hapus data ini">
                                <i class="bx bx-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="hs-empty">
                            <i class="bx bx-folder-open"></i>
                            Belum ada data sisternas yang diunggah.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination & Info (DataTable controlled) --}}
    <div id="hs-dt-info-wrap" class="hs-pagination-wrap" style="display:none;">
        <div class="hs-pagination-info" id="hs-dt-info"></div>
        <div class="hs-pagination-btns" id="hs-dt-paging"></div>
    </div>

</div>

<script>
function updateFileName(input) {
    var fileName = input.files[0] ? input.files[0].name : "Tidak ada file terpilih";
    document.getElementById('file-name-val').value = fileName;
}

document.addEventListener('DOMContentLoaded', function() {

    // Initialize DataTable (hidden default controls, custom toolbar)
    const table = $('#sisternasTable').DataTable({
        dom: '<"d-none"l><"d-none"f>rt<"d-none"ip>',
        order: [[0, 'desc']],
        language: {
            zeroRecords: "",
            infoEmpty: "Tidak ada data tersedia",
            info: "Menampilkan _START_–_END_ dari _TOTAL_ entri",
        },
        drawCallback: function(settings) {
            const api = this.api();
            const info = api.page.info();
            const totalEntries = info.recordsDisplay;

            if (totalEntries === 0) {
                document.getElementById('hs-dt-info-wrap').style.display = 'none';
                return;
            }

            document.getElementById('hs-dt-info-wrap').style.display = 'flex';
            document.getElementById('hs-dt-info').textContent =
                'Menampilkan ' + (info.start + 1) + '–' + info.end + ' dari ' + totalEntries + ' entri';

            // Build pagination buttons
            const pagingEl = document.getElementById('hs-dt-paging');
            pagingEl.innerHTML = '';

            const prevBtn = document.createElement('a');
            prevBtn.className = 'hs-pg-btn' + (info.page === 0 ? ' disabled' : '');
            prevBtn.innerHTML = '<i class="bx bx-chevron-left"></i>';
            prevBtn.href = '#';
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (info.page > 0) api.page('previous').draw('page');
            });
            pagingEl.appendChild(prevBtn);

            for (let i = 0; i < info.pages; i++) {
                const pgBtn = document.createElement('a');
                pgBtn.className = 'hs-pg-btn' + (i === info.page ? ' active' : '');
                pgBtn.textContent = i + 1;
                pgBtn.href = '#';
                pgBtn.addEventListener('click', (function(page) {
                    return function(e) {
                        e.preventDefault();
                        api.page(page).draw('page');
                    };
                })(i));
                pagingEl.appendChild(pgBtn);
            }

            const nextBtn = document.createElement('a');
            nextBtn.className = 'hs-pg-btn' + (info.page >= info.pages - 1 ? ' disabled' : '');
            nextBtn.innerHTML = '<i class="bx bx-chevron-right"></i>';
            nextBtn.href = '#';
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (info.page < info.pages - 1) api.page('next').draw('page');
            });
            pagingEl.appendChild(nextBtn);
        }
    });

    // Custom search
    document.getElementById('sisternasSearchInput').addEventListener('input', function() {
        table.search(this.value).draw();
    });

    // SweetAlert helper
    const showAlert = (message, type) => {
        return Swal.fire({
            title: message,
            icon: type,
            timer: 2000,
            showConfirmButton: false,
        });
    };

    // Session success alert
    @if(session('success'))
        showAlert("{{ session('success') }}", "success");
    @endif

    // Konfirmasi Hapus
    document.body.addEventListener('click', function(event) {
        if (event.target.closest('.delete-sisternas')) {
            let button = event.target.closest('.delete-sisternas');
            let form = button.closest('.delete-form');
            Swal.fire({
                title: 'Hapus dokumen ini?',
                text: "Data yang dihapus tidak bisa dikembalikan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Mohon tunggu...',
                        html: `<div class="d-flex justify-content-center align-items-center flex-column">
                                   <div class="spinner-border text-danger" role="status"></div>
                                   <div class="mt-2 text-muted" style="font-size:0.85rem;">Sedang menghapus data</div>
                               </div>`,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    });
                    form.submit();
                }
            });
        }
    });

});
</script>
@endsection
