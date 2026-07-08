@extends('layouts/contentNavbarLayout')

@section('title', 'Data Grade Serdos - SPTJM Online')

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
.md2-toolbar { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:16px; }
.md2-toolbar .entries-wrap { display:flex; align-items:center; gap:8px; font-size:0.84rem; color:#4a5568; }
.md2-toolbar .entries-wrap select { border:1px solid #e2e8f0; border-radius:6px; padding:5px 10px; font-size:0.84rem; color:#4a5568; background:#f8fafc; cursor:pointer; outline:none; }
.md2-toolbar .right-wrap { display:flex; align-items:center; gap:12px; }
.md2-toolbar .search-wrap input { border:1px solid #e2e8f0; border-radius:6px; padding:6px 14px 6px 36px; font-size:0.84rem; color:#2d3748; min-width:210px; outline:none; transition:border-color 0.2s; background:#f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%238592a3' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 10px center; }
.md2-toolbar .search-wrap input:focus { border-color:#1a56db; background-color:#fff; }
.btn-md2-tambah { background:#1a56db; border:none; color:#fff; font-weight:600; font-size:0.82rem; padding:8px 18px; border-radius:6px; display:inline-flex; align-items:center; gap:6px; transition:background 0.2s; cursor:pointer; }
.btn-md2-tambah:hover { background:#1648c0; color:#fff; box-shadow:0 4px 12px rgba(26,86,219,0.35); }
table.md2-table { border-collapse:collapse!important; }
/* Table header/body/pagination: dipindahkan ke global sptjm-datatable.css */
</style>
@endsection

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="md2-page-header">
    <div class="page-titles">
        <h4>Data Grade Serdos</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Master Data</a></li>
            <li class="breadcrumb-item active">Data Grade Serdos</li>
        </ol></nav>
    </div>
</div>

<div class="md2-card">
    <div class="md2-card-inner">
        <div class="md2-toolbar">
            <div class="entries-wrap">
                <span>Show</span>
                <select id="serdosLengthSelect">
                    <option value="10">10</option><option value="25" selected>25</option>
                    <option value="50">50</option><option value="100">100</option><option value="500">500</option>
                </select>
                <span>entries</span>
            </div>
            <div class="right-wrap">
                <div class="search-wrap"><input type="text" id="serdosSearchInput" placeholder="Cari data..."></div>
                <button class="btn-md2-tambah" type="button" id="addGradeBtn" data-bs-toggle="modal" data-bs-target="#modalGradeForm">
                    <i class="bx bx-plus"></i> Tambah
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover md2-table" id="gradeTable" style="width:100%">
                <thead><tr>
                    <th>Jabatan</th><th>Masa Kerja Bawah</th><th>Masa Kerja Atas</th><th>Golongan</th><th>Aksi</th>
                </tr></thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalGradeForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="modalGradeTitle">Tambah Data Grade</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form id="gradeForm" method="POST">
      @csrf
      <input type="hidden" name="_method" id="formMethod" value="POST">
      <input type="hidden" id="gradeId" name="id">
      <div class="modal-body">
        <div class="mb-3"><label>Jabatan</label><input type="text" class="form-control" id="jabatan" name="jabatan" required></div>
        <div class="mb-3"><label>Masa Kerja Bawah</label><input type="number" class="form-control" id="masa_kerja_bawah" name="masa_kerja_bawah" required></div>
        <div class="mb-3"><label>Masa Kerja Atas</label><input type="number" class="form-control" id="masa_kerja_atas" name="masa_kerja_atas" required></div>
        <div class="mb-3"><label>Golongan</label><input type="text" class="form-control" id="golongan" name="golongan" required></div>
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
      return Swal.fire({ title: 'Mohon tunggu...', html: `<div class="d-flex justify-content-center align-items-center flex-column"><div class="spinner-border spinner-border-lg text-danger" role="status"><span class="visually-hidden">Loading...</span></div><div class="mt-2">Mohon tunggu <br> ${message ?? 'Sedang menghapus data!'}</div></div>`, showConfirmButton: false, allowOutsideClick: false, allowEscapeKey: false, backdrop: true });
    };

    document.getElementById('addGradeBtn').addEventListener('click', function() {
      document.getElementById('modalGradeTitle').innerText = 'Tambah Data Grade Serdos';
      document.getElementById('gradeForm').reset();
      document.getElementById('formMethod').value = 'POST';
      document.getElementById('gradeForm').setAttribute('action', "{{ route('admin.grade-serdos.store') }}");
    });

    const gradeForm = document.getElementById('gradeForm');
    gradeForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const modalSync = document.getElementById('modalGradeForm');
      const modalInstance = bootstrap.Modal.getInstance(modalSync);
      if (modalInstance) modalInstance.hide();
      const dataForm = new FormData(gradeForm);
      const method = document.getElementById('formMethod').value;
      method == "POST" ? loadingAlert('Sedang menyimpan data!') : loadingAlert('Sedang mengupdate data!');
      fetch(gradeForm.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: dataForm })
        .then(async (res) => {
          let data = {}; try { data = await res.json(); } catch (e) { data = {}; }
          Swal.close();
          if (res.ok && data && data.success) { await Swal.fire({ title: 'Berhasil', text: data.message || 'Berhasil menyimpan data.', icon: 'success', timer: 1500, showConfirmButton: false }); table.ajax.reload(); return; }
          let msg = (data && data.message) ? data.message : 'Terjadi kesalahan.';
          if (data && data.errors) { const firstKey = Object.keys(data.errors)[0]; if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) { msg = data.errors[firstKey][0]; } }
          return Swal.fire({ title: 'Gagal', text: msg, icon: 'error' });
        }).catch(err => { console.error(err); Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan data.', 'error'); }).finally(() => { Swal.close(); });
    });

    const table = $('#gradeTable').DataTable({
      processing: true, serverSide: true, responsive: true,
      pageLength: 25,
      dom: '<"d-none"l><"d-none"f>rtip',
      lengthMenu: [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']],
      ajax: { url: "{{ route('admin.grade-serdos.index') }}" },
      columns: [
        { 
          data: 'jabatan', name: 'jabatan',
          render: function(data) { return '<span class="fw-bold text-dark">' + (data || '-') + '</span>'; }
        },
        { data: 'masa_kerja_bawah', name: 'masa_kerja_bawah' },
        { data: 'masa_kerja_atas', name: 'masa_kerja_atas' },
        { 
          data: 'golongan', name: 'golongan',
          render: function(data) { return '<span class="fw-semibold text-primary">' + (data || '-') + '</span>'; }
        },
        { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
      ],
      language: { paginate: { first: "«", last: "»", next: "›", previous: "‹" }, zeroRecords: 'Data tidak ditemukan', infoEmpty: 'Tidak ada data tersedia', info: 'Menampilkan _START_-_END_ dari _TOTAL_ entri' },
    });

    document.getElementById('serdosLengthSelect').addEventListener('change', function() { table.page.len(parseInt(this.value)).draw(); });
    let serdosSearchTimer;
    document.getElementById('serdosSearchInput').addEventListener('input', function() {
      clearTimeout(serdosSearchTimer); const val = this.value;
      serdosSearchTimer = setTimeout(() => { table.search(val).draw(); }, 400);
    });

    $('#gradeTable').on('click', '.edit-grade', function() {
      const id = $(this).data('id');
      fetch(`/admin/grade-serdos/${id}/edit`).then(res => res.json()).then(data => {
        $('#modalGradeTitle').text('Edit Data Grade Serdos'); $('#gradeId').val(data.id); $('#jabatan').val(data.jabatan);
        $('#masa_kerja_bawah').val(data.masa_kerja_bawah); $('#masa_kerja_atas').val(data.masa_kerja_atas); $('#golongan').val(data.golongan);
        $('#formMethod').val('PUT'); $('#gradeForm').attr('action', `/admin/grade-serdos/${data.id}`); $('#modalGradeForm').modal('show');
      });
    });

    $('#gradeTable').on('click', '.delete-grade', function() {
      const form = $(this).closest('.delete-form')[0];
      Swal.fire({ title: 'Apakah Anda Yakin?', text: "Data yang dihapus tidak bisa dikembalikan!", icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal', customClass: { confirmButton: 'btn btn-danger me-1', cancelButton: 'btn btn-secondary' }, buttonsStyling: false }).then((result) => {
        if (result.isConfirmed) {
          loadingAlert();
          fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: new FormData(form) })
            .then(res => res.json()).then(data => {
              if (!data.success) return Swal.fire('Gagal', data.message || 'Terjadi kesalahan', 'error');
              Swal.fire({ title: 'Berhasil', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false }); table.ajax.reload();
            }).catch(err => { console.error(err); Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus data.', 'error'); }).finally(() => { Swal.close(); });
        }
      });
    });
  });
</script>
@endsection