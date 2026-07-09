@extends('layouts/contentNavbarLayout')

@section('title', 'Status Pegawai - SPTJM Online')

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
/* Table header/body/pagination: dipindahkan ke global sptjm-datatable.css */
/* Action buttons now use sptjm-icon-btn from demo.css */
</style>
@endsection

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="md2-page-header">
    <div class="page-titles">
        <h4>Status Pegawai</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Master Data</a></li>
            <li class="breadcrumb-item active">Status Pegawai</li>
        </ol></nav>
    </div>
</div>

<div class="md2-card">
    <div class="md2-card-inner">
        <div class="md2-toolbar">
            <div class="right-wrap">
                <div class="search-wrap"><input type="text" id="searchInput" placeholder="Cari data..."></div>
                <button class="btn-md2-tambah" id="addPegawaiBtn" type="button" data-bs-toggle="modal" data-bs-target="#modalPegawaiForm">
                    <i class="bx bx-plus"></i> Tambah
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover md2-table" id="pegawaiTable">
                <thead><tr>
                    <th>Kode</th><th>Status</th><th>Aksi</th>
                </tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($g_pegawai as $pegawai)
                    <tr>
                        <td><span class="fw-semibold text-primary">{{ $pegawai->kode }}</span></td>
                        <td><span class="fw-bold text-dark">{{ $pegawai->jenis }}</span></td>
                        <td>
                            <button class="sptjm-icon-btn sptjm-btn-edit edit-pegawai" data-id="{{ $pegawai->kode }}"
                              data-kode="{{ $pegawai->kode }}" data-jenis="{{ $pegawai->jenis }}"
                              data-bs-toggle="modal" data-bs-target="#modalPegawaiForm" title="Edit">
                                <i class="bx bx-edit"></i>
                            </button>
                            <form action="{{ route('admin/data-pegawai.destroy', $pegawai->kode) }}" method="POST" class="d-inline delete-form">
                                @csrf @method('DELETE')
                                <button type="button" class="sptjm-icon-btn sptjm-btn-delete delete-pegawai" data-id="{{ $pegawai->kode }}" title="Hapus">
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
<div class="modal fade" id="modalPegawaiForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="modalPegawaiTitle">Tambah Status Pegawai</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form id="pegawaiForm" method="POST">
      @csrf
      <input type="hidden" name="_method" id="formMethod" value="POST">
      <input type="hidden" id="pegawaiId" name="id">
      <div class="modal-body">
        <div class="mb-3"><label>Kode</label><input type="text" class="form-control" id="kode" name="kode" required></div>
        <div class="mb-3"><label>Status</label><input type="text" class="form-control" id="jenis" name="jenis" required></div>
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

    document.getElementById('pegawaiForm').addEventListener('submit', function() {
      const method = document.getElementById('formMethod').value;
      const modalSync = document.getElementById('modalPegawaiForm');
      const modalInstance = bootstrap.Modal.getInstance(modalSync);
      if (modalInstance) modalInstance.hide();
      method === "POST" ? loadingAlert("Sedang menyimpan data!") : loadingAlert("Sedang mengupdate data!");
    });

    @if(session('success'))
    Swal.fire({ title: 'Berhasil!', text: "{{ session('success') }}", icon: 'success', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
    @endif

    document.getElementById('addPegawaiBtn').addEventListener('click', function() {
      document.getElementById('modalPegawaiTitle').innerText = 'Tambah Status Pegawai';
      document.getElementById('pegawaiForm').reset();
      document.getElementById('formMethod').value = 'POST';
      document.getElementById('pegawaiForm').setAttribute('action', "{{ route('admin/data-pegawai.store') }}");
    });

    document.body.addEventListener('click', function(event) {
      if (event.target.closest('.edit-pegawai')) {
        let button = event.target.closest('.edit-pegawai');
        document.getElementById('modalPegawaiTitle').innerText = 'Edit Status Pegawai';
        document.getElementById('pegawaiId').value = button.dataset.id;
        document.getElementById('kode').value = button.dataset.kode;
        document.getElementById('jenis').value = button.dataset.jenis;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('pegawaiForm').setAttribute('action', `/admin/data-pegawai/${button.dataset.id}`);
      }
    });

    document.querySelectorAll('.delete-pegawai').forEach(button => {
      button.addEventListener('click', function() {
        let form = this.closest('.delete-form');
        Swal.fire({ title: 'Apakah Anda Yakin?', text: "Data yang dihapus tidak bisa dikembalikan!", icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal', customClass: { confirmButton: 'btn btn-danger me-1', cancelButton: 'btn btn-secondary' }, buttonsStyling: false }).then((result) => {
          if (result.isConfirmed) { loadingAlert(); form.submit(); }
        });
      });
    });

    document.getElementById("searchInput").addEventListener("keyup", function() {
      var filter = this.value.toLowerCase();
      document.querySelectorAll("#pegawaiTable tbody tr").forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
      });
    });
  });
</script>
@endsection