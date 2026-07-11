@extends('layouts/contentNavbarLayout')

@section('title', 'Status Keaktifan - SPTJM Online')

@section('page-style')
<style>
.md2-page-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }

.md2-page-header .breadcrumb { margin:0; font-size:0.8rem; background:none; padding:0; }
.md2-page-header .breadcrumb-item a { color:#696cff; text-decoration:none; }
.md2-page-header .breadcrumb-item.active { color:#8592a3; }
.md2-page-header .breadcrumb-item+.breadcrumb-item::before { color:#8592a3; }
.md2-card { background:#fff; border-radius:10px; box-shadow:0 2px 12px rgba(44,62,80,0.07); overflow:hidden; }
.md2-card-inner { padding:20px 24px 24px; }
.md2-toolbar { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px; }
.md2-toolbar .entries-wrap { display:flex; align-items:center; gap:8px; font-size:0.84rem; color:#4a5568; }
.md2-toolbar .entries-wrap select { border:1px solid #e2e8f0; border-radius:6px; padding:5px 10px; font-size:0.84rem; color:#4a5568; background:#f8fafc; cursor:pointer; outline:none; }
.md2-toolbar .entries-wrap select:focus { border-color:#1a56db; }
.md2-toolbar .right-wrap { display:flex; align-items:center; gap:12px; }
.md2-toolbar .search-wrap input { border:1px solid #e2e8f0; border-radius:6px; padding:6px 14px 6px 36px; font-size:0.84rem; color:#2d3748; min-width:210px; outline:none; transition:border-color 0.2s; background:#f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%238592a3' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 10px center; }
.md2-toolbar .search-wrap input:focus { border-color:#1a56db; background-color:#fff; }
.btn-md2-tambah { background:#1a56db; border:none; color:#fff; font-weight:600; font-size:0.82rem; padding:8px 18px; border-radius:6px; display:inline-flex; align-items:center; gap:6px; transition:background 0.2s; cursor:pointer; }
.btn-md2-tambah:hover { background:#1648c0; color:#fff; box-shadow:0 4px 12px rgba(26,86,219,0.35); }
/* Table header/body/pagination: dipindahkan ke global sptjm-datatable.css */
.badge-status { display:inline-block; padding:3px 12px; border-radius:20px; font-size:0.75rem; font-weight:700; }
.badge-aktif   { background:rgba(26, 86, 219, 0.1); color:#1a56db; }
.badge-nonaktif { background:rgba(234,84,85,0.12); color:#ea5455; }
.badge-belajar  { background:rgba(40,199,111,0.12); color:#28c76f; }
</style>
@endsection

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="md2-page-header">
    <div class="page-titles">
        <h1>Status Keaktifan</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Master Data</a></li>
            <li class="breadcrumb-item active">Status Keaktifan</li>
        </ol></nav>
    </div>
</div>

<div class="md2-card">
    <div class="md2-card-inner">
        <div class="md2-toolbar">
            <div class="entries-wrap">
                <span>Show</span>
                <select id="keaktifanLengthSelect">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="500">500</option>
                </select>
                <span>entries</span>
            </div>
            <div class="right-wrap">
                <div class="search-wrap"><input type="text" id="searchInput" placeholder="Cari data..."></div>
                <button class="btn-md2-tambah" id="addKeaktifanBtn" type="button" data-bs-toggle="modal" data-bs-target="#modalKeaktifanForm">
                    <i class="bx bx-plus"></i> Tambah
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover md2-table" id="keaktifanTable">
                <thead><tr>
                    <th>Kode</th><th>Status</th><th>Aksi</th>
                </tr></thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($f_keaktifan as $keaktifan)
                    <tr>
                        <td><span class="fw-semibold text-primary">{{ $keaktifan->kode }}</span></td>
                        <td>
                            @php
                                $aktifLower = strtolower($keaktifan->aktif);
                                $badgeClass = $aktifLower === 'aktif' ? 'badge-aktif' : ($aktifLower === 'tidak aktif' ? 'badge-nonaktif' : 'badge-belajar');
                            @endphp
                            <span class="badge-status {{ $badgeClass }}">{{ $keaktifan->aktif }}</span>
                        </td>
                        <td>
                            <button class="sptjm-icon-btn sptjm-btn-edit edit-keaktifan" data-id="{{ $keaktifan->kode }}"
                              data-kode="{{ $keaktifan->kode }}" data-aktif="{{ $keaktifan->aktif }}"
                              data-bs-toggle="modal" data-bs-target="#modalKeaktifanForm" title="Edit">
                                <i class="bx bx-edit"></i>
                            </button>
                            <form action="{{ route('admin/data-keaktifan.destroy', $keaktifan->kode) }}" method="POST" class="d-inline delete-form">
                                @csrf @method('DELETE')
                                <button type="button" class="sptjm-icon-btn sptjm-btn-delete delete-keaktifan" data-id="{{ $keaktifan->kode }}" title="Hapus">
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
<div class="modal fade" id="modalKeaktifanForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="modalKeaktifanTitle">Tambah Status Keaktifan</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form id="keaktifanForm" method="POST">
      @csrf
      <input type="hidden" name="_method" id="formMethod" value="POST">
      <input type="hidden" id="keaktifanId" name="id">
      <div class="modal-body">
        <div class="mb-3"><label>Kode</label><input type="text" class="form-control" id="kode" name="kode" required></div>
        <div class="mb-3"><label>Status</label><input type="text" class="form-control" id="aktif" name="aktif" required></div>
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

    document.getElementById('keaktifanForm').addEventListener('submit', function() {
      const method = document.getElementById('formMethod').value;
      const modalSync = document.getElementById('modalKeaktifanForm');
      const modalInstance = bootstrap.Modal.getInstance(modalSync);
      if (modalInstance) modalInstance.hide();
      method === "POST" ? loadingAlert("Sedang menyimpan data!") : loadingAlert("Sedang mengupdate data!");
    });

    @if(session('success'))
    Swal.fire({ title: 'Berhasil!', text: "{{ session('success') }}", icon: 'success', customClass: { confirmButton: 'btn btn-primary' }, buttonsStyling: false });
    @endif

    const table = $('#keaktifanTable').DataTable({
      dom: '<"d-none"l><"d-none"f>rtip',
      language: {
        paginate: { first: "Â«", last: "Â»", next: "â€º", previous: "â€¹" },
        zeroRecords: "Data tidak ditemukan",
        infoEmpty: "Tidak ada data tersedia",
        info: "Menampilkan _START_-_END_ dari _TOTAL_ entri",
      }
    });

    document.getElementById('keaktifanLengthSelect').addEventListener('change', function() {
      table.page.len(parseInt(this.value)).draw();
    });

    document.getElementById("searchInput").addEventListener("input", function() {
      table.search(this.value).draw();
    });

    document.getElementById('addKeaktifanBtn').addEventListener('click', function() {
      document.getElementById('modalKeaktifanTitle').innerText = 'Tambah Status Keaktifan';
      document.getElementById('keaktifanForm').reset();
      document.getElementById('formMethod').value = 'POST';
      document.getElementById('keaktifanForm').setAttribute('action', "{{ route('admin/data-keaktifan.store') }}");
    });

    document.body.addEventListener('click', function(event) {
      if (event.target.closest('.edit-keaktifan')) {
        let button = event.target.closest('.edit-keaktifan');
        document.getElementById('modalKeaktifanTitle').innerText = 'Edit Status Keaktifan';
        document.getElementById('keaktifanId').value = button.dataset.id;
        document.getElementById('kode').value = button.dataset.kode;
        document.getElementById('aktif').value = button.dataset.aktif;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('keaktifanForm').setAttribute('action', `/admin/data-keaktifan/${button.dataset.id}`);
      }
    });

    document.body.addEventListener('click', function(event) {
      if (event.target.closest('.delete-keaktifan')) {
        let button = event.target.closest('.delete-keaktifan');
        let form = button.closest('.delete-form');
        Swal.fire({ title: 'Apakah Anda Yakin?', text: "Data yang dihapus tidak bisa dikembalikan!", icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal', customClass: { confirmButton: 'btn btn-danger me-1', cancelButton: 'btn btn-secondary' }, buttonsStyling: false }).then((result) => {
          if (result.isConfirmed) { loadingAlert(); form.submit(); }
        });
      }
    });
  });
</script>
@endsection