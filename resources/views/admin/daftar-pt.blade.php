@extends('layouts/contentNavbarLayout')

@section('title', 'Data Perguruan Tinggi - SPTJM Online')

@section('page-style')
<style>
/* ── Page Header ── */
.pt-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
}
.pt-page-header .page-titles h4 {
    font-size: 1.35rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 4px 0;
    line-height: 1.2;
}
.pt-page-header .breadcrumb {
    margin: 0;
    font-size: 0.8rem;
    background: none;
    padding: 0;
}
.pt-page-header .breadcrumb-item a {
    color: #696cff;
    text-decoration: none;
}
.pt-page-header .breadcrumb-item.active {
    color: #8592a3;
}
.pt-page-header .breadcrumb-item + .breadcrumb-item::before {
    color: #8592a3;
}

/* ── Header Buttons ── */
.btn-sync {
    background-color: #ff9f43;
    border-color: #ff9f43;
    color: #fff;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 8px 16px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s, box-shadow 0.2s;
}
.btn-sync:hover {
    background-color: #f08030;
    border-color: #f08030;
    color: #fff;
    box-shadow: 0 4px 12px rgba(255,159,67,0.35);
}
.btn-tambah {
    background-color: #1a56db;
    border-color: #1a56db;
    color: #fff;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 8px 16px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s, box-shadow 0.2s;
}
.btn-tambah:hover {
    background-color: #1648c0;
    border-color: #1648c0;
    color: #fff;
    box-shadow: 0 4px 12px rgba(26,86,219,0.35);
}

/* ── Card ── */
.pt-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(44,62,80,0.07);
    overflow: hidden;
}
.pt-card .pt-card-inner {
    padding: 20px 24px 24px;
}

/* ── Toolbar (entries + search) ── */
.pt-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 16px;
}
.pt-toolbar .entries-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    color: #5c6877;
}
.pt-toolbar .entries-wrap select {
    width: 70px;
    padding: 4px 8px;
    border: 1px solid #d9dee3;
    border-radius: 5px;
    font-size: 0.82rem;
    color: #2c3e50;
    background: #f8f9fa;
}
.pt-toolbar .search-wrap {
    display: flex;
    align-items: center;
    border: 1px solid #d9dee3;
    border-radius: 6px;
    overflow: hidden;
    background: #fff;
}
.pt-toolbar .search-wrap input {
    border: none;
    outline: none;
    padding: 6px 12px;
    font-size: 0.82rem;
    min-width: 220px;
    color: #2c3e50;
}
.pt-toolbar .search-wrap .search-icon {
    padding: 6px 10px;
    color: #8592a3;
    font-size: 1rem;
    background: #f8f9fa;
    border-left: 1px solid #d9dee3;
}

/* ── Table ── */
#ptsTable {
    font-size: 0.83rem;
    color: #2c3e50;
    border-collapse: separate;
    border-spacing: 0;
}
#ptsTable thead tr th {
    background-color: #eef0f3;
    color: #5c6877;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 10px 14px;
    border-bottom: 2px solid #dee2e6;
    white-space: nowrap;
}
#ptsTable tbody tr {
    transition: background 0.15s;
}
#ptsTable tbody tr:hover {
    background-color: #f5f7fa;
}
#ptsTable tbody td {
    padding: 10px 14px;
    border-bottom: 1px solid #f0f2f5;
    vertical-align: middle;
}
#ptsTable tbody tr:last-child td {
    border-bottom: none;
}

/* ── Badge Status ── */
.badge-aktif {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    background: rgba(40,199,111,0.12);
    color: #28c76f;
    border: 1px solid rgba(40,199,111,0.3);
}
.badge-nonaktif {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.03em;
    background: rgba(234,84,85,0.1);
    color: #ea5455;
    border: 1px solid rgba(234,84,85,0.25);
}

/* ── Edit Button ── */
.btn-edit-pt {
    background: #ff9f43;
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s;
}
.btn-edit-pt:hover {
    background: #f08030;
    transform: scale(1.07);
}

/* ── DataTables Override ── */
div.dataTables_wrapper div.dataTables_info {
    font-size: 0.78rem;
    color: #8592a3;
    padding-top: 8px;
}
div.dataTables_wrapper div.dataTables_paginate {
    padding-top: 6px;
}
div.dataTables_wrapper div.dataTables_paginate .pagination {
    margin: 0;
}
div.dataTables_wrapper div.dataTables_paginate .page-link {
    font-size: 0.8rem;
    padding: 4px 10px;
    color: #696cff;
    border-color: #d9dee3;
}
div.dataTables_wrapper div.dataTables_paginate .page-item.active .page-link {
    background: #696cff;
    border-color: #696cff;
    color: #fff;
}
div.dataTables_wrapper div.dataTables_filter,
div.dataTables_wrapper div.dataTables_length {
    display: none; /* kita pakai toolbar custom */
}
</style>
@endsection

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- ── Page Header ── --}}
<div class="pt-page-header">
    <div class="page-titles">
        <h4>Data Perguruan Tinggi</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Master Data</a></li>
                <li class="breadcrumb-item active">Perguruan Tinggi</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sync" id="addSyncPTBtn" data-bs-toggle="modal" data-bs-target="#modalSync">
            <i class="bx bx-sync"></i> Sync
        </button>
        <button class="btn btn-tambah" id="addPTBtn" data-bs-toggle="modal" data-bs-target="#modalPTForm">
            <i class="bx bx-plus"></i> Tambah Perguruan Tinggi
        </button>
    </div>
</div>

{{-- ── Card ── --}}
<div class="pt-card">
    <div class="pt-card-inner">

        {{-- Toolbar --}}
        <div class="pt-toolbar">
            <div class="entries-wrap">
                Tampilkan
                <select id="entriesSelect">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                data per halaman
            </div>
            <div class="search-wrap">
                <input type="text" id="ptSearchInput" placeholder="Cari NPSN, Nama, atau Wilayah...">
                <span class="search-icon"><i class="bx bx-search"></i></span>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table" id="ptsTable" style="width:100%">
                <thead>
                    <tr>
                        <th>Kode PT</th>
                        <th>Nama PT</th>
                        <th>Wilayah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>

{{-- ── Modal Tambah/Edit PT ── --}}
<div class="modal fade" id="modalPTForm" tabindex="-1" aria-labelledby="modalPTFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:10px; border:none; box-shadow:0 8px 30px rgba(0,0,0,0.12);">
            <div class="modal-header" style="background:#f8f9fa; border-radius:10px 10px 0 0; border-bottom:1px solid #e9ecef;">
                <h5 class="modal-title fw-bold" id="modalPTTitle" style="font-size:1rem; color:#2c3e50;">
                    <i class="bx bx-buildings me-2" style="color:#696cff;"></i>Tambah Perguruan Tinggi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="ptForm" method="POST" action="{{ route('admin/daftar-pt.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="padding:20px 24px;">
                    <div class="mb-3">
                        <label for="kodePTS" class="form-label fw-semibold" style="font-size:0.82rem; color:#5c6877;">Kode Perguruan Tinggi</label>
                        <input type="text" id="kodePTS" class="form-control @error('kode_pts') is-invalid @enderror"
                            name="kode_pts" placeholder="Masukkan Kode PT" required pattern="^[1-9][0-9]*$"
                            title="Hanya angka, tidak boleh diawali 0 atau mengandung spasi"
                            style="font-size:0.85rem;">
                        @error('kode_pts')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.82rem; color:#5c6877;">Nama Perguruan Tinggi</label>
                        <input type="text" class="form-control" id="nama_pts" name="nama_pts"
                            placeholder="Masukkan Nama PT" required style="font-size:0.85rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.82rem; color:#5c6877;">Nama Pimpinan</label>
                        <input type="text" class="form-control" id="nama_pimpinan" name="nama_pimpinan"
                            placeholder="Masukkan Nama Pimpinan" required style="font-size:0.85rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.82rem; color:#5c6877;">Jabatan Pimpinan</label>
                        <input type="text" class="form-control" id="jabatan_pimpinan" name="jabatan_pimpinan"
                            placeholder="Masukkan Jabatan Pimpinan" required style="font-size:0.85rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.82rem; color:#5c6877;">Alamat Perguruan Tinggi</label>
                        <input type="text" class="form-control" id="alamat_pt" name="alamat_pt"
                            placeholder="Masukkan Alamat PT" required style="font-size:0.85rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.82rem; color:#5c6877;">Wilayah</label>
                        <select class="form-select" id="wilayah" name="wilayah" required style="font-size:0.85rem;">
                            <option value="">-- Pilih Wilayah --</option>
                            @foreach ($users as $user)
                            <option value="{{ $user->email }}">{{ $user->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.82rem; color:#5c6877;">Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Masukkan Password" required style="font-size:0.85rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.82rem; color:#5c6877;">Status</label>
                        <select class="form-select" id="aktif" name="aktif" required style="font-size:0.85rem;">
                            <option value="1">Aktif</option>
                            <option value="0">Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" style="font-size:0.82rem; color:#5c6877;">Upload Dokumen</label>
                        <input type="file" class="form-control" id="dokumen" name="dokumen" accept=".pdf,.doc,.docx" style="font-size:0.85rem;">
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa; border-top:1px solid #e9ecef; border-radius:0 0 10px 10px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-size:0.83rem;">Batal</button>
                    <button type="submit" class="btn" style="background:#696cff; color:#fff; font-size:0.83rem; font-weight:600; padding:7px 20px;">
                        <i class="bx bx-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Modal Sync ── --}}
<div class="modal fade" id="modalSync" tabindex="-1" aria-labelledby="modalSyncFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content" style="border-radius:10px; border:none; box-shadow:0 8px 30px rgba(0,0,0,0.12);">
            <div class="modal-header" style="background:#f8f9fa; border-radius:10px 10px 0 0; border-bottom:1px solid #e9ecef;">
                <h5 class="modal-title fw-bold" id="modalSyncTitle" style="font-size:1rem; color:#2c3e50;">
                    <i class="bx bx-sync me-2" style="color:#ff9f43;"></i>Sinkronisasi Perguruan Tinggi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="syncPtForm" method="POST" action="{{ route('admin.daftar-pt.updateWilayah') }}">
                @csrf
                @method('PUT')
                <div class="modal-body" style="padding:20px 24px;">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.82rem; color:#5c6877;">Kode Perguruan Tinggi</label>
                        <select name="kode_pts" id="kode_pts_sync" class="form-select" required style="font-size:0.85rem;">
                            <option value="" selected>--- Pilih ---</option>
                            @foreach ($kode_pts as $kode)
                            <option value="{{ $kode->kode_pts }}" data-nama="{{ $kode->nama_pts ?? '' }}">
                                {{ $kode->kode_pts }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:0.82rem; color:#5c6877;">Nama Perguruan Tinggi</label>
                        <input type="text" readonly class="form-control" id="nama_pts_sync" name="nama_pts"
                            placeholder="Nama PT akan terisi otomatis" required style="font-size:0.85rem; background:#f8f9fa;">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold" style="font-size:0.82rem; color:#5c6877;">Pemegang Wilayah Baru</label>
                        <select name="pemegang_wilayah_baru" id="pemegang_wilayah_baru" class="form-select" required style="font-size:0.85rem;">
                            <option value="" selected>-- Pilih --</option>
                            @foreach ($users as $user)
                            <option value="{{ $user->email }}">{{ $user->email }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8f9fa; border-top:1px solid #e9ecef; border-radius:0 0 10px 10px;">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="font-size:0.83rem;">Batal</button>
                    <button type="submit" class="btn" style="background:#ff9f43; color:#fff; font-size:0.83rem; font-weight:600; padding:7px 20px;">
                        <i class="bx bx-sync me-1"></i> Sinkronkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── SweetAlert helpers ──
    const alert = (text = "Data berhasil tersimpan!", title = "Berhasil", icon = "success", warnaBtn = "btn btn-primary") => {
        return Swal.fire({
            title, text, icon,
            confirmButtonText: 'OK',
            timer: 1500,
            timerProgressBar: true,
            customClass: { confirmButton: warnaBtn },
            buttonsStyling: false
        });
    };

    const loadingAlert = () => Swal.fire({
        title: 'Mohon tunggu...',
        html: `<div class="d-flex justify-content-center align-items-center flex-column">
                    <div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>
                    <div class="mt-2">Sedang menyimpan data!</div>
               </div>`,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        backdrop: true
    });

    // ── Reset form saat Tambah ──
    document.getElementById('addPTBtn').addEventListener('click', function () {
        document.getElementById('modalPTTitle').innerHTML = '<i class="bx bx-buildings me-2" style="color:#696cff;"></i>Tambah Perguruan Tinggi';
        document.getElementById('ptForm').reset();
        document.getElementById('ptForm').setAttribute('action', "{{ route('admin/daftar-pt.store') }}");
    });

    // ── Validasi kode PT ──
    document.getElementById('kodePTS').addEventListener('input', function () {
        const pattern = /^[1-9][0-9]*$/;
        this.classList.toggle('is-invalid', !pattern.test(this.value));
    });

    // ── Sync form ──
    const syncForm = document.getElementById('syncPtForm');
    syncForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const modalSync = document.getElementById('modalSync');
        bootstrap.Modal.getInstance(modalSync).hide();
        loadingAlert();

        const formData = new FormData(syncForm);
        (async () => {
            try {
                const data = await fetch(syncForm.action, {
                    method: "POST",
                    headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    body: formData
                });
                const response = await data.json();
                Swal.close();
                if (!data.ok) { alert(response.message, 'Gagal!', 'error', 'btn btn-danger'); return; }
                alert(response.message);
                syncForm.reset();
                $('#ptsTable').DataTable().ajax.reload();
            } catch (error) {
                console.log(`err: ${error.message}`);
            }
        })();
    });

    // Nama PT sync otomatis
    const kodePtsSync = document.getElementById("kode_pts_sync");
    const namaPtsSyncInput = document.getElementById("nama_pts_sync");
    const setNamaFromSelected = () => {
        const sel = kodePtsSync.options[kodePtsSync.selectedIndex];
        namaPtsSyncInput.value = sel ? (sel.getAttribute('data-nama') || '') : '';
    };
    setNamaFromSelected();
    kodePtsSync.addEventListener('change', setNamaFromSelected);

    // ── Simpan PT form ──
    const ptForm = document.getElementById('ptForm');
    ptForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const modalPTForm = document.getElementById('modalPTForm');
        const modalInstancePTForm = bootstrap.Modal.getOrCreateInstance(modalPTForm);
        modalInstancePTForm.hide();

        modalPTForm.addEventListener('hidden.bs.modal', function onHidden() {
            modalPTForm.removeEventListener('hidden.bs.modal', onHidden);
            loadingAlert();
        });

        const formData = new FormData(ptForm);
        (async () => {
            try {
                const data = await fetch(ptForm.action, {
                    method: "POST",
                    headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    body: formData
                });
                const response = await data.json();
                Swal.close();
                if (!response.success) {
                    alert(response.message ?? 'Terjadi kesalahan!', 'Gagal!', 'error', 'btn btn-danger');
                    return;
                }
                alert(response.message, 'Berhasil!');
                $('#ptsTable').DataTable().ajax.reload();
            } catch (error) {
                console.log(error.message);
            }
        })();
    });

    // ── Custom toolbar → DataTable ──
    const entriesSelect = document.getElementById('entriesSelect');
    const searchInput   = document.getElementById('ptSearchInput');

    // ── DataTable init ──
    const table = $('#ptsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        pageLength: 10,
        ajax: { url: "{{ route('admin.daftar-pt') }}" },
        columns: [
            { data: "kode_pts",  name: "kode_pts" },
            { data: "nama_pts",  name: "nama_pts" },
            { data: "wilayah",   name: "wilayah" },
            { data: "aktif",     name: "aktif",   orderable: false, searchable: false },
            { data: "aksi",      name: "aksi",    orderable: false, searchable: false }
        ],
        language: {
            paginate: { first: "«", last: "»", next: "›", previous: "‹" },
            zeroRecords: "Data tidak ditemukan",
            infoEmpty: "Tidak ada data tersedia",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
            infoFiltered: "(difilter dari _MAX_ total entri)"
        },
        dom: 'rtip'  // hide default length + search, tampilkan hanya table + info + pagination
    });

    // Hook custom entries selector
    entriesSelect.addEventListener('change', function () {
        table.page.len(parseInt(this.value)).draw();
    });

    // Hook custom search input (debounce 400ms)
    let searchTimer;
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { table.search(this.value).draw(); }, 400);
    });
});
</script>
@endsection