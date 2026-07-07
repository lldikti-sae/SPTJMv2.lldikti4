@extends('layouts/contentNavbarLayout')

@section('title', 'Data Pajak - SPTJM Online')

@section('page-style')
<style>
.md2-page-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
.md2-page-header .page-titles h4 { font-size:1.35rem; font-weight:700; color:#2c3e50; margin:0 0 4px; }
.md2-page-header .breadcrumb { margin:0; font-size:0.8rem; background:none; padding:0; }
.md2-page-header .breadcrumb-item a { color:#696cff; text-decoration:none; }
.md2-page-header .breadcrumb-item.active { color:#8592a3; }
.md2-page-header .breadcrumb-item+.breadcrumb-item::before { color:#8592a3; }

.md2-card { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(44,62,80,0.07); overflow:hidden; margin-bottom:24px; }
.md2-card-inner { padding:20px 24px 24px; }

.md2-toolbar { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px; }
.md2-toolbar .right-wrap { display:flex; align-items:center; gap:12px; margin-left:auto; }
.md2-toolbar .search-wrap input { border:1px solid #e2e8f0; border-radius:6px; padding:6px 14px 6px 36px; font-size:0.84rem; color:#2d3748; min-width:210px; outline:none; transition:border-color 0.2s; background:#f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%238592a3' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 10px center; }
.md2-toolbar .search-wrap input:focus { border-color:#1a56db; background-color:#fff; }

.btn-md2-tambah { background:#1a56db; border:none; color:#fff; font-weight:600; font-size:0.82rem; padding:8px 18px; border-radius:6px; display:inline-flex; align-items:center; gap:6px; transition:background 0.2s; cursor:pointer; }
.btn-md2-tambah:hover { background:#1648c0; color:#fff; box-shadow:0 4px 12px rgba(26,86,219,0.35); }

table.md2-table thead th { background:#f1f3f5!important; color:#374151!important; font-size:0.75rem!important; font-weight:700!important; text-transform:uppercase!important; letter-spacing:0.05em!important; border-bottom:2px solid #e5e7eb!important; padding:12px 14px!important; white-space:nowrap; }
table.md2-table tbody td { font-size:0.84rem; color:#374151; padding:10px 14px!important; vertical-align:middle; border-bottom:1px solid #f1f3f5; }
table.md2-table tbody tr:hover { background:#f8fafc!important; }
table.md2-table { border-collapse:collapse!important; width:100%; }

.btn-aksi-edit   { background:#fd9f10; border:none; color:#fff; border-radius:6px; padding:5px 9px; font-size:0.82rem; cursor:pointer; }
.btn-aksi-delete { background:#dc3545; border:none; color:#fff; border-radius:6px; padding:5px 9px; font-size:0.82rem; cursor:pointer; }
.btn-aksi-edit:hover { background:#e68a00; } .btn-aksi-delete:hover { background:#bb2d3b; }
</style>
@endsection

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="md2-page-header">
    <div class="page-titles">
        <h4>Data Pajak & Identitas Pemotong</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Master Data</a></li>
            <li class="breadcrumb-item active">Data Pajak</li>
        </ol></nav>
    </div>
</div>

{{-- Card 1: Data Pajak --}}
<div class="md2-card">
    <div class="md2-card-inner">
        <h5 class="mb-3 text-dark fw-bold" style="font-size:1.1rem;">Data Pajak</h5>
        <div class="md2-toolbar">
            <div class="right-wrap">
                <div class="search-wrap"><input type="text" id="pajakSearchInput" placeholder="Cari data pajak..."></div>
                <button class="btn-md2-tambah" type="button" id="addPajakBtn" data-bs-toggle="modal" data-bs-target="#modalPajakForm">
                    <i class="bx bx-plus"></i> Tambah
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover md2-table" id="pajakTable">
                <thead><tr>
                    <th>No</th><th>Status</th><th>Akumulasi</th><th>Tarif Pajak</th><th>Aksi</th>
                </tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($d_pajak as $index => $pajak)
                    <tr>
                        <td><span class="fw-semibold text-primary">{{ $index + 1 }}</span></td>
                        <td><span class="fw-bold text-dark">{{ $pajak->status }}</span></td>
                        <td><span class="fw-bold text-dark">{{ $pajak->akumulasi }}</span></td>
                        <td><span class="fw-semibold text-primary">{{ number_format($pajak->tarif_pajak, 2) }}%</span></td>
                        <td>
                            <button class="btn-aksi-edit edit-pajak" data-id="{{ $pajak->no }}"
                                data-status="{{ $pajak->status }}" data-akumulasi="{{ $pajak->akumulasi }}"
                                data-tarif_pajak="{{ $pajak->tarif_pajak }}" data-bs-toggle="modal"
                                data-bs-target="#modalPajakForm">
                                <i class="bx bx-edit"></i>
                            </button>
                            <form action="{{ route('admin/data-pajak.destroy', $pajak->no) }}" method="POST" class="d-inline delete-form">
                                @csrf @method('DELETE')
                                <button type="button" class="btn-aksi-delete delete-pajak">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Card 2: Identitas Pemotong --}}
<div class="md2-card">
    <div class="md2-card-inner">
        <h5 class="mb-3 text-dark fw-bold" style="font-size:1.1rem;">Identitas Pemotong</h5>
        <div class="md2-toolbar">
            <div class="right-wrap">
                <button class="btn-md2-tambah" type="button" id="addPemotongBtn" data-bs-toggle="modal" data-bs-target="#modalPemotongForm">
                    <i class="bx bx-plus"></i> Tambah Identitas
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover md2-table" id="pemotongTable">
                <thead><tr>
                    <th>No</th><th>NPWP</th><th>Nama</th><th>Tanggal</th><th>Tanda Tangan</th><th>Cap</th><th>Aksi</th>
                </tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach (($identitas_pemotong ?? []) as $index => $item)
                    <tr>
                        <td><span class="fw-semibold text-primary">{{ $index + 1 }}</span></td>
                        <td><span class="fw-semibold text-primary">{{ $item['npwp'] ?? '-' }}</span></td>
                        <td><span class="fw-bold text-dark">{{ $item['nama'] ?? '-' }}</span></td>
                        <td>{{ $item['tanggal'] ?? '-' }}</td>
                        <td>
                            @if(!empty($item['tanda_tangan_path']))
                            <img src="{{ asset('storage/' . $item['tanda_tangan_path']) }}" alt="Tanda Tangan" style="height: 40px; width: 80px; object-fit: contain;" />
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            @if(!empty($item['cap_path']))
                            <img src="{{ asset('storage/' . $item['cap_path']) }}" alt="Cap" style="height: 40px; width: 40px; object-fit: contain;" />
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            <button class="btn-aksi-edit edit-pemotong" data-id="{{ $item['id'] ?? '' }}"
                                data-npwp="{{ $item['npwp'] ?? '' }}" data-nama="{{ $item['nama'] ?? '' }}"
                                data-tanggal="{{ $item['tanggal'] ?? '' }}"
                                data-ttd="{{ !empty($item['tanda_tangan_path']) ? asset('storage/' . $item['tanda_tangan_path']) : '' }}"
                                data-cap="{{ !empty($item['cap_path']) ? asset('storage/' . $item['cap_path']) : '' }}">
                                <i class="bx bx-edit"></i>
                            </button>
                            <form action="{{ route('admin/data-pajak.identitas-pemotong.destroy', $item['id'] ?? '') }}" method="POST" class="d-inline delete-form-pemotong">
                                @csrf @method('DELETE')
                                <button type="button" class="btn-aksi-delete delete-pemotong">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Pajak -->
<div class="modal fade" id="modalPajakForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalPajakTitle">Tambah Data Pajak</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="pajakForm" method="POST" action="{{ route('admin/data-pajak.store') }}">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" id="pajakId" name="no">
            <div class="modal-body">
                <div class="mb-3"><label>Status</label><input type="text" class="form-control" id="status" name="status" required></div>
                <div class="mb-3"><label>Akumulasi</label><input type="text" class="form-control" id="akumulasi" name="akumulasi" required></div>
                <div class="mb-3"><label>Tarif Pajak</label><input type="number" class="form-control" id="tarif_pajak" name="tarif_pajak" step="0.01" required></div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div></div>
</div>

<!-- Modal Identitas Pemotong -->
<div class="modal fade" id="modalPemotongForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalPemotongTitle">Tambah Identitas Pemotong</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="pemotongForm" method="POST" action="{{ route('admin/data-pajak.identitas-pemotong.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="pemotongFormMethod" value="POST">
            <input type="hidden" id="pemotongId" name="id">
            <div class="modal-body">
                <div class="mb-3"><label>NPWP</label><input type="text" class="form-control" id="pemotong_npwp" name="npwp" required></div>
                <div class="mb-3"><label>Nama</label><input type="text" class="form-control" id="pemotong_nama" name="nama" required></div>
                <div class="mb-3"><label>Tanggal</label><input type="date" class="form-control" id="pemotong_tanggal" name="tanggal"></div>
                <div class="mb-3">
                    <label>Tanda Tangan (PNG)</label>
                    <input type="file" class="form-control" id="pemotong_ttd" name="tanda_tangan" accept="image/png" required>
                    <div class="form-text" id="pemotong_ttd_help">Format PNG.</div>
                </div>
                <div class="mb-2" id="pemotong_ttd_preview_wrapper" style="display:none;">
                    <label class="form-label">Preview</label>
                    <div><img id="pemotong_ttd_preview" src="" alt="Preview" style="height: 60px; width: 120px; object-fit: contain;" /></div>
                </div>
                <div class="mb-3">
                    <label>Cap (PNG)</label>
                    <input type="file" class="form-control" id="pemotong_cap" name="cap" accept="image/png" required>
                    <div class="form-text" id="pemotong_cap_help">Format PNG.</div>
                </div>
                <div class="mb-2" id="pemotong_cap_preview_wrapper" style="display:none;">
                    <label class="form-label">Preview</label>
                    <div><img id="pemotong_cap_preview" src="" alt="Preview" style="height: 60px; width: 60px; object-fit: contain;" /></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadingAlert = (message) => {
        return Swal.fire({
            title: 'Mohon tunggu...',
            html: `<div class="d-flex justify-content-center align-items-center flex-column"><div class='spinner-border spinner-border-lg ${message? 'text-success':'text-danger'}' role="status"><span class="visually-hidden">Loading...</span></div><div class="mt-2">Mohon tunggu <br>${message ?? 'Sedang menghapus data!'}</div></div>`,
            showConfirmButton: false, allowOutsideClick: false, allowEscapeKey: false, backdrop: true
        });
    }

    const pajakForm = document.getElementById('pajakForm');
    pajakForm.addEventListener('submit', function() {
        const method = document.getElementById('formMethod').value
        const modalSync = document.getElementById('modalPajakForm');
        const modalInstance = bootstrap.Modal.getInstance(modalSync);
        if (modalInstance) modalInstance.hide();
        method == "POST" ? loadingAlert("Sedang menyimpan data!") : loadingAlert("Sedang mengupdate data!");
    });

    @if(session('success'))
    Swal.close()
    Swal.fire({ title: 'Berhasil!', text: "{{ session('success') }}", icon: 'success' });
    @endif

    @if(session('error'))
    Swal.close()
    Swal.fire({ title: 'Gagal!', text: "{{ session('error') }}", icon: 'error' });
    @endif

    const openModal = (modalId) => {
        const el = document.getElementById(modalId);
        const instance = new bootstrap.Modal(el);
        instance.show();
    };

    document.getElementById('addPajakBtn').addEventListener('click', function() {
        document.getElementById('modalPajakTitle').innerText = 'Tambah Data Pajak';
        document.getElementById('pajakForm').reset();
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('pajakForm').setAttribute('action', "{{ route('admin/data-pajak.store') }}");
    });

    document.getElementById('addPemotongBtn').addEventListener('click', function() {
        document.getElementById('modalPemotongTitle').innerText = 'Tambah Identitas Pemotong';
        document.getElementById('pemotongForm').reset();
        document.getElementById('pemotongFormMethod').value = 'POST';
        document.getElementById('pemotongForm').setAttribute('action', "{{ route('admin/data-pajak.identitas-pemotong.store') }}");
        document.getElementById('pemotong_ttd_help').innerText = 'Format PNG.';
        document.getElementById('pemotong_ttd_preview_wrapper').style.display = 'none';
        document.getElementById('pemotong_ttd').required = true;
        document.getElementById('pemotong_cap_help').innerText = 'Format PNG.';
        document.getElementById('pemotong_cap_preview_wrapper').style.display = 'none';
        document.getElementById('pemotong_cap').required = true;
        openModal('modalPemotongForm');
    });

    document.body.addEventListener('click', function(event) {
        if (event.target.closest('.edit-pajak')) {
            let button = event.target.closest('.edit-pajak');
            document.getElementById('modalPajakTitle').innerText = 'Edit Data Pajak';
            document.getElementById('pajakId').value = button.dataset.id;
            document.getElementById('status').value = button.dataset.status;
            document.getElementById('akumulasi').value = button.dataset.akumulasi;
            document.getElementById('tarif_pajak').value = button.dataset.tarif_pajak;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('pajakForm').setAttribute('action', `/admin/data-pajak/${button.dataset.id}`);
        }
    });

    const pemotongForm = document.getElementById('pemotongForm');
    pemotongForm.addEventListener('submit', function() {
        const method = document.getElementById('pemotongFormMethod').value;
        const modalEl = document.getElementById('modalPemotongForm');
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) { modalInstance.hide(); }
        method === 'POST' ? loadingAlert('Sedang menyimpan data!') : loadingAlert('Sedang mengupdate data!');
    });

    document.body.addEventListener('click', function(event) {
        if (event.target.closest('.edit-pemotong')) {
            const button = event.target.closest('.edit-pemotong');
            const id = button.dataset.id;
            document.getElementById('modalPemotongTitle').innerText = 'Edit Identitas Pemotong';
            document.getElementById('pemotongId').value = id;
            document.getElementById('pemotong_npwp').value = button.dataset.npwp || '';
            document.getElementById('pemotong_nama').value = button.dataset.nama || '';
            document.getElementById('pemotong_tanggal').value = button.dataset.tanggal || '';
            document.getElementById('pemotongFormMethod').value = 'PUT';
            document.getElementById('pemotongForm').setAttribute('action', `/admin/data-pajak/identitas-pemotong/${id}`);
            document.getElementById('pemotong_ttd').required = false;
            document.getElementById('pemotong_cap').required = false;
            document.getElementById('pemotong_ttd_help').innerText = 'Kosongkan jika tidak diganti (PNG).';
            const ttdUrl = button.dataset.ttd || '';
            if (ttdUrl) {
                document.getElementById('pemotong_ttd_preview').src = ttdUrl;
                document.getElementById('pemotong_ttd_preview_wrapper').style.display = 'block';
            } else {
                document.getElementById('pemotong_ttd_preview_wrapper').style.display = 'none';
            }
            document.getElementById('pemotong_cap_help').innerText = 'Kosongkan jika tidak diganti (PNG).';
            const capUrl = button.dataset.cap || '';
            if (capUrl) {
                document.getElementById('pemotong_cap_preview').src = capUrl;
                document.getElementById('pemotong_cap_preview_wrapper').style.display = 'block';
            } else {
                document.getElementById('pemotong_cap_preview_wrapper').style.display = 'none';
            }
            openModal('modalPemotongForm');
        }
    });

    document.getElementById('pemotong_ttd').addEventListener('change', function() {
        const file = this.files && this.files[0];
        if (!file) return;
        document.getElementById('pemotong_ttd_preview').src = URL.createObjectURL(file);
        document.getElementById('pemotong_ttd_preview_wrapper').style.display = 'block';
    });

    document.getElementById('pemotong_cap').addEventListener('change', function() {
        const file = this.files && this.files[0];
        if (!file) return;
        document.getElementById('pemotong_cap_preview').src = URL.createObjectURL(file);
        document.getElementById('pemotong_cap_preview_wrapper').style.display = 'block';
    });

    document.getElementById("pajakSearchInput").addEventListener("keyup", function() {
        var filter = this.value.toLowerCase();
        document.querySelectorAll("#pajakTable tbody tr").forEach(function(row) {
            row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
        });
    });

    document.querySelectorAll('.delete-pajak').forEach(button => {
        button.addEventListener('click', function() {
            let form = this.closest('.delete-form');
            Swal.fire({
                title: 'Apakah Anda Yakin?', text: "Data yang dihapus tidak bisa dikembalikan!", icon: 'warning',
                showCancelButton: true, confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal',
                customClass: { confirmButton: 'btn btn-danger me-1', cancelButton: 'btn btn-secondary' }, buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) { loadingAlert(); form.submit(); }
            });
        });
    });

    document.querySelectorAll('.delete-pemotong').forEach(button => {
        button.addEventListener('click', function() {
            let form = this.closest('.delete-form-pemotong');
            Swal.fire({
                title: 'Apakah Anda Yakin?', text: "Data yang dihapus tidak bisa dikembalikan!", icon: 'warning',
                showCancelButton: true, confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal',
                customClass: { confirmButton: 'btn btn-danger me-1', cancelButton: 'btn btn-secondary' }, buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) { loadingAlert(); form.submit(); }
            });
        });
    });
});
</script>
@endsection
