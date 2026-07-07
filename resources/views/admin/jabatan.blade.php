@extends('layouts/contentNavbarLayout')

@section('title', 'Data Jabatan - SPTJM Online')

@section('page-style')
<style>
.md2-page-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
.md2-page-header .page-titles h4 { font-size:1.35rem; font-weight:700; color:#2c3e50; margin:0 0 4px; }
.md2-page-header .breadcrumb { margin:0; font-size:0.8rem; background:none; padding:0; }
.md2-page-header .breadcrumb-item a { color:#696cff; text-decoration:none; }
.md2-page-header .breadcrumb-item.active { color:#8592a3; }
.md2-page-header .breadcrumb-item+.breadcrumb-item::before { color:#8592a3; }
.md2-card { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(44,62,80,0.07); overflow:hidden; }
.md2-card-inner { padding:20px 24px 24px; }
.md2-toolbar { display:flex; align-items:center; justify-content:flex-end; flex-wrap:wrap; gap:12px; margin-bottom:16px; }
.md2-toolbar .right-wrap { display:flex; align-items:center; gap:12px; }
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
        <h4>Data Jabatan</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Master Data</a></li>
            <li class="breadcrumb-item active">Data Jabatan</li>
        </ol></nav>
    </div>
</div>

<div class="md2-card">
    <div class="md2-card-inner">
        <div class="md2-toolbar">
            <div class="right-wrap">
                <div class="search-wrap"><input type="text" id="searchInput" placeholder="Cari data jabatan..."></div>
                <button class="btn-md2-tambah" id="addJabatanBtn" type="button" data-bs-toggle="modal" data-bs-target="#modalJabatanForm">
                    <i class="bx bx-plus"></i> Tambah
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover md2-table" id="jabatanTable">
                <thead><tr>
                    <th>Kode</th><th>Jabatan</th><th>Nominal</th><th>Aksi</th>
                </tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($e_jabatan as $jabatan)
                    <tr>
                        <td><span class="fw-semibold text-primary">{{ $jabatan->kode }}</span></td>
                        <td><span class="fw-bold text-dark">{{ $jabatan->jabatan }}</span></td>
                        <td><span class="fw-bold text-dark">{{ number_format($jabatan->nominal) }}</span></td>
                        <td>
                            <button class="btn-aksi-edit edit-jabatan" data-id="{{ $jabatan->kode }}"
                              data-kode="{{ $jabatan->kode }}" data-jabatan="{{ $jabatan->jabatan }}"
                              data-nominal="{{ $jabatan->nominal }}" data-bs-toggle="modal" data-bs-target="#modalJabatanForm">
                                <i class="bx bx-edit"></i>
                            </button>
                            <form action="{{ route('admin/data-jabatan.destroy', $jabatan->kode) }}" method="POST" class="d-inline delete-form">
                                @csrf @method('DELETE')
                                <button type="button" class="btn-aksi-delete delete-jabatan" data-id="{{ $jabatan->kode }}">
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

<!-- Modal -->
<div class="modal fade" id="modalJabatanForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="modalJabatanTitle">Tambah Jabatan</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form id="jabatanForm" method="POST">
      @csrf
      <input type="hidden" name="_method" id="formMethod" value="POST">
      <input type="hidden" id="jabatanId" name="id">
      <div class="modal-body">
        <div class="mb-3"><label>Kode</label><input type="text" class="form-control" id="kode" name="kode" placeholder="Masukan Kode" required></div>
        <div class="mb-3"><label>Jabatan</label><input type="text" class="form-control" id="jabatan" name="jabatan" placeholder="Masukan Jabatan" required></div>
        <div class="mb-3"><label>Nominal</label><input type="text" class="form-control" id="nominal" name="nominal" placeholder="Masukan Nominal" required></div>
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
      return Swal.fire({ title: 'Mohon tunggu...', html: `<div class="d-flex justify-content-center align-items-center flex-column"><div class='spinner-border spinner-border-lg ${message? 'text-success':'text-danger'}' role="status"><span class="visually-hidden">Loading...</span></div><div class="mt-2">Mohon tunggu <br>${message ?? 'Sedang menghapus data!'}</div></div>`, showConfirmButton: false, allowOutsideClick: false, allowEscapeKey: false, backdrop: true });
    };

    const jabatanForm = document.getElementById('jabatanForm');
    jabatanForm.addEventListener('submit', function() {
      const method = document.getElementById('formMethod').value;
      const modalSync = document.getElementById('modalJabatanForm');
      const modalInstance = bootstrap.Modal.getInstance(modalSync);
      modalInstance.hide();
      method == "POST" ? loadingAlert("Sedang menyimpan data!") : loadingAlert("Sedang mengupdate data!");
    });

    @if(session('success'))
    Swal.fire({ title: 'Berhasil!', text: "{{ session('success') }}", icon: 'success', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
    @endif

    document.getElementById('addJabatanBtn').addEventListener('click', function() {
      document.getElementById('modalJabatanTitle').innerText = 'Tambah Jabatan';
      document.getElementById('jabatanForm').reset();
      document.getElementById('formMethod').value = 'POST';
      document.getElementById('jabatanForm').setAttribute('action', "{{ route('admin/data-jabatan.store') }}");
    });

    document.body.addEventListener('click', function(event) {
      if (event.target.closest('.edit-jabatan')) {
        let button = event.target.closest('.edit-jabatan');
        document.getElementById('modalJabatanTitle').innerText = 'Edit Jabatan';
        document.getElementById('jabatanId').value = button.dataset.id;
        document.getElementById('kode').value = button.dataset.kode;
        document.getElementById('jabatan').value = button.dataset.jabatan;
        document.getElementById('nominal').value = button.dataset.nominal;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('jabatanForm').setAttribute('action', `/admin/data-jabatan/${button.dataset.id}`);
      }
    });

    document.querySelectorAll('.delete-jabatan').forEach(button => {
      button.addEventListener('click', function() {
        let form = this.closest('.delete-form');
        Swal.fire({ title: 'Apakah Anda Yakin?', text: "Data yang dihapus tidak bisa dikembalikan!", icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal', customClass: { confirmButton: 'btn btn-danger me-1', cancelButton: 'btn btn-secondary' }, buttonsStyling: false }).then((result) => {
          if (result.isConfirmed) { loadingAlert(); form.submit(); }
        });
      });
    });

    document.getElementById("searchInput").addEventListener("keyup", function() {
      var filter = this.value.toLowerCase();
      document.querySelectorAll("#jabatanTable tbody tr").forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
      });
    });
  });
</script>
@endsection