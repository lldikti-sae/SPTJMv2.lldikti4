@extends('layouts/contentNavbarLayout')

@section('title', 'Data Perguruan Tinggi - SPTJM Online')



@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- â”€â”€ Page Header â”€â”€ --}}
<div class="pt-page-header">
    <div class="page-titles">
        <h1>Data Perguruan Tinggi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Master Data</a></li>
                <li class="breadcrumb-item active">Perguruan Tinggi</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button class="btn-sinkron-md" id="addSyncPTBtn" data-bs-toggle="modal" data-bs-target="#modalSync">
            <i class="bx bx-transfer-alt"></i> Sinkronisasi
        </button>
        <button class="btn-tambah-md" id="addPTBtn" data-bs-toggle="modal" data-bs-target="#modalPTForm">
            <i class="bx bx-plus"></i> Tambah Data
        </button>
    </div>
</div>

{{-- â”€â”€ Card â”€â”€ --}}
<div class="pt-card">
    <div class="pt-card-inner">

        {{-- Toolbar --}}
        <div class="pt-toolbar">
            <div class="entries-wrap">
                Show
                <select id="entriesSelect">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="500">500</option>
                </select>
                entries
            </div>
            <div class="search-wrap">
                <input type="text" id="ptSearchInput" placeholder="Cari NPSN, Nama, atau Wilayah...">
            </div>
        </div>

        {{-- Table --}}
        <div>
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

{{-- â”€â”€ Modal Tambah/Edit PT â”€â”€ --}}
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
                        @if(Auth::check() && Auth::user()->role === 'pic')
                            <input type="text" class="form-control" name="wilayah" value="{{ Auth::user()->email }}" readonly style="background-color: #eceef1; font-size:0.85rem;">
                        @else
                            <select class="form-select" id="wilayah" name="wilayah" required style="font-size:0.85rem;">
                                <option value="">-- Pilih Wilayah --</option>
                                @foreach ($users as $user)
                                <option value="{{ $user->email }}">{{ $user->email }}</option>
                                @endforeach
                            </select>
                        @endif
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

{{-- â”€â”€ Modal Sync â”€â”€ --}}
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
                        @if(Auth::check() && Auth::user()->role === 'pic')
                            <input type="text" class="form-control" name="pemegang_wilayah_baru" value="{{ Auth::user()->email }}" readonly style="background-color: #eceef1; font-size:0.85rem;">
                        @else
                            <select name="pemegang_wilayah_baru" id="pemegang_wilayah_baru" class="form-select" required style="font-size:0.85rem;">
                                <option value="" selected>-- Pilih --</option>
                                @foreach ($users as $user)
                                <option value="{{ $user->email }}">{{ $user->email }}</option>
                                @endforeach
                            </select>
                        @endif
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

    // â”€â”€ SweetAlert helpers â”€â”€
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

    // â”€â”€ Reset form saat Tambah â”€â”€
    document.getElementById('addPTBtn').addEventListener('click', function () {
        document.getElementById('modalPTTitle').innerHTML = '<i class="bx bx-buildings me-2" style="color:#696cff;"></i>Tambah Perguruan Tinggi';
        document.getElementById('ptForm').reset();
        document.getElementById('ptForm').setAttribute('action', "{{ route('admin/daftar-pt.store') }}");
    });

    // â”€â”€ Validasi kode PT â”€â”€
    document.getElementById('kodePTS').addEventListener('input', function () {
        const pattern = /^[1-9][0-9]*$/;
        this.classList.toggle('is-invalid', !pattern.test(this.value));
    });

    // â”€â”€ Sync form â”€â”€
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

    // â”€â”€ Simpan PT form â”€â”€
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

    // â”€â”€ Custom toolbar â†’ DataTable â”€â”€
    const entriesSelect = document.getElementById('entriesSelect');
    const searchInput   = document.getElementById('ptSearchInput');

    // â”€â”€ DataTable init â”€â”€
    const table = $('#ptsTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: { url: "{{ route('admin.daftar-pt') }}" },
        columns: [
            { data: "kode_pts",  name: "kode_pts" },
            { data: "nama_pts",  name: "nama_pts" },
            { data: "wilayah",   name: "wilayah" },
            { data: "aktif",     name: "aktif",   orderable: false, searchable: false },
            { data: "aksi",      name: "aksi",    orderable: false, searchable: false }
        ],
        language: {
            paginate: { first: "Â«", last: "Â»", next: "â€º", previous: "â€¹" },
            zeroRecords: "Data tidak ditemukan",
            infoEmpty: "Tidak ada data tersedia",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
            infoFiltered: "(difilter dari _MAX_ total entri)"
        },
        dom: '<"table-responsive text-nowrap"t>rip'
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