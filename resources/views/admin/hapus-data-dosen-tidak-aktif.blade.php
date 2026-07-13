@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online - Hapus Dosen Tidak Aktif')

@section('page-style')
<style>
/* â”€â”€ Variables & Setup â”€â”€ */
:root {
    --md-primary: #0b3d91;
    --md-bg-gray: #f8fafc;
    --md-border: #e2e8f0;
    --md-text-main: #1e293b;
    --md-text-muted: #64748b;
    --md-radius-lg: 12px;
    --md-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

/* â”€â”€ Page Header â”€â”€ */
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

.md-page-header .breadcrumb { margin: 0; padding: 0; background: transparent; font-size: 0.85rem; }
.md-page-header .breadcrumb-item a { color: var(--md-text-muted); text-decoration: none; }
.md-page-header .breadcrumb-item.active { color: var(--md-primary); font-weight: 600; }
.md-page-header .breadcrumb-item + .breadcrumb-item::before { color: #cbd5e1; }

/* â”€â”€ Card & Table Container â”€â”€ */
.md-card {
    background: #fff;
    border-radius: var(--md-radius-lg);
    box-shadow: var(--md-shadow);
    border: 1px solid rgba(226, 232, 240, 0.8);
    overflow: hidden;
}
.md-card-inner { padding: 20px 24px; }

/* â”€â”€ Filter Box â”€â”€ */
.md-filter-box {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--md-bg-gray);
    border: 1px solid var(--md-border);
    padding: 10px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.md-filter-box label {
    font-size: 0.84rem;
    font-weight: 600;
    color: var(--md-text-main);
    margin: 0;
}
.md-filter-box select {
    border: 1px solid var(--md-border);
    border-radius: 6px;
    font-size: 0.88rem;
    padding: 6px 12px;
    color: var(--md-text-main);
    background-color: #fff;
    min-width: 250px;
    outline: none;
    transition: border-color 0.2s;
}
.md-filter-box select:focus {
    border-color: var(--md-primary);
}

/* â”€â”€ Toolbar â”€â”€ */
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
@endsection

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h1>Hapus Data Dosen Tidak Aktif</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Dosen</a></li>
                <li class="breadcrumb-item active">Hapus Dosen Tidak Aktif</li>
            </ol>
        </nav>
    </div>
</div>

<div class="md-card">
    <div class="md-card-inner">
        {{-- Filter Box --}}
        <div class="md-filter-box">
            <i class="bx bx-filter-alt" style="color: var(--md-primary); font-size: 1.1rem;"></i>
            <label for="filterKeterangan">Filter Keterangan:</label>
            <select id="filterKeterangan">
                <option value="all">-- Semua Keterangan --</option>
                @isset($keteranganOptions)
                @foreach ($keteranganOptions as $opt)
                <option value="{{ $opt }}">{{ $opt }}</option>
                @endforeach
                @endisset
            </select>
        </div>

        <div class="md-table-wrap">
            <table id="dosenTable" class="table table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th>NIDN</th>
                        <th>NUPTK</th>
                        <th>Nama Dosen</th>
                        <th>Kode PTS</th>
                        <th>Nama PTS</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Data di load pakai ajax --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    @if(session('success'))
    Swal.fire({
        title: 'Berhasil!',
        text: @json(session('success')),
        icon: 'success',
        customClass: {
            confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false
    });
    @endif
    @if(session('error'))
    Swal.fire({
        title: 'Gagal!',
        text: @json(session('error')),
        icon: 'error',
        customClass: {
            confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false
    });
    @endif

    $('#dosenTable').DataTable({
        serverSide: true,
        processing: true,
        responsive: true,
        dom: "<'md-toolbar'<'entries-wrap'l><'search-wrap'f>><'table-responsive text-nowrap't><'row dt-bottom-row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        ajax: {
            url: "{{ route('admin.data-dosen.tidak-aktif.data') }}",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data: function(d) {
                d.keterangan = $('#filterKeterangan').val();
            }
        },
        columns: [
            {
                data: 'nidn',
                name: 'nidn',
                render: function(data) { return data ? data : '-'; }
            },
            {
                data: 'nuptk',
                name: 'nuptk',
                render: function(data) { return data ? data : '-'; }
            },
            {
                data: 'Nama',
                name: 'nama'
            },
            {
                data: 'Kode_PT',
                name: 'kode_pt'
            },
            {
                data: 'PTS',
                name: 'pts',
                render: function(data) {
                    return `<div style="white-space: normal; max-width: 250px;">${data || '-'}</div>`;
                }
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                searchable: false,
                render: function() {
                    return '<span class="badge-tidak-aktif">TIDAK AKTIF</span>';
                }
            },
            {
                data: 'Keterangan',
                name: 'keterangan',
                render: function(data) {
                    return data ? `<span class="badge-keterangan">${data}</span>` : '-';
                }
            },
            {
                data: null,
                name: 'aksi',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const identifier = row.nidn || row.NIDN || row.nuptk || row.NUPTK;
                    if (!identifier) {
                        return '';
                    }

                    // Jika can_delete == 1 (tidak ada kode usulan/kode cair) â†’ tampilkan tombol hapus
                    if (parseInt(row.can_delete ?? 0) === 1) {
                        let url = "{{ route('admin.data-dosen.tidak-aktif.hapus', ':id') }}";
                        url = url.replace(':id', identifier);

                        return `
                            <form action="${url}" method="POST" class="form-delete-dosen d-inline">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="button" class="sptjm-icon-btn sptjm-btn-delete btn-can-delete" data-id="${identifier}" title="Hapus Data">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        `;
                    }

                    // Jika punya kode usulan/kode cair â†’ tidak boleh dihapus, tampilkan tombol info yang memunculkan modal
                    return `
                        <button type="button" class="sptjm-icon-btn sptjm-btn-info btn-cannot-delete" data-id="${identifier}" title="Informasi Penghapusan">
                            <i class="bx bx-info-circle"></i>
                        </button>
                    `;
                }
            }
        ],
        language: {
            search: "Cari Data:",
            searchPlaceholder: "Cari disini...",
            lengthMenu: "Show _MENU_ entries",
            paginate: {
                "first": "Awal",
                "last": "Akhir",
                "next": "â†’",
                "previous": "â†"
            },
            zeroRecords: "Data tidak ditemukan",
            infoEmpty: "Tidak ada data tersedia",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri"
        }
    });

    // reload ketika filter berubah
    $('#filterKeterangan').on('change', function() {
        $('#dosenTable').DataTable().ajax.reload();
    });

    // Modal (SweetAlert) ketika baris tidak dapat dihapus karena masih punya kode usulan / kode cair
    $('#dosenTable').on('click', '.btn-cannot-delete', function() {
        const id = $(this).data('id') || '-';
        Swal.fire({
            title: 'Tidak dapat dihapus',
            html: `Data dosen dengan NIDN/NUPTK <b>${id}</b> masih memiliki <br>kode cair sehingga tidak dapat dihapus.`,
            icon: 'info',
            confirmButtonText: 'OK',
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false
        });
    });

    // Modal konfirmasi hapus ketika data boleh dihapus (can_delete == 1)
    $('#dosenTable').on('click', '.btn-can-delete', function(e) {
        e.preventDefault();
        const id = $(this).data('id') || '-';
        const form = $(this).closest('form');

        Swal.fire({
            title: 'Yakin hapus data?',
            html: `Data dosen dengan NIDN/NUPTK <b>${id}</b> akan dihapus dan tidak dapat dikembalikan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                form.trigger('submit');
            }
        });
    });
});
</script>
@endsection


