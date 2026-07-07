@extends('layouts/contentNavbarLayout')

@section('title', 'Data Grade - SPTJM Online')

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
table.md2-table thead th { background:#f1f3f5!important; color:#374151!important; font-size:0.75rem!important; font-weight:700!important; text-transform:uppercase!important; letter-spacing:0.05em!important; border-bottom:2px solid #e5e7eb!important; padding:12px 14px!important; white-space:nowrap; }
table.md2-table tbody td { font-size:0.84rem; color:#374151; padding:10px 14px!important; vertical-align:middle; border-bottom:1px solid #f1f3f5; }
table.md2-table tbody tr:hover { background:#f8fafc!important; }
table.md2-table { border-collapse:collapse!important; }
.btn-aksi-edit   { background:#fd9f10; border:none; color:#fff; border-radius:6px; padding:5px 9px; font-size:0.82rem; cursor:pointer; }
.btn-aksi-delete { background:#dc3545; border:none; color:#fff; border-radius:6px; padding:5px 9px; font-size:0.82rem; cursor:pointer; }
.btn-aksi-edit:hover { background:#e68a00; } .btn-aksi-delete:hover { background:#bb2d3b; }
.dataTables_wrapper .dataTables_info { font-size:0.82rem; color:#8592a3; }
.dataTables_wrapper .dataTables_paginate .paginate_button.current,.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover { background:#1a56db!important; color:#fff!important; border:none!important; border-radius:6px!important; }
.dataTables_wrapper .dataTables_paginate .paginate_button:hover { background:#eef2ff!important; color:#1a56db!important; border:none!important; border-radius:6px!important; }
</style>
@endsection

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="md2-page-header">
    <div class="page-titles">
        <h4>Data Grade</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Master Data</a></li>
            <li class="breadcrumb-item active">Data Grade</li>
        </ol></nav>
    </div>
</div>

<div class="md2-card">
    <div class="md2-card-inner">
        <div class="md2-toolbar">
            <div class="entries-wrap">
                <span>Show</span>
                <select id="gradeLengthSelect">
                    <option value="10">10</option><option value="25">25</option>
                    <option value="50">50</option><option value="100">100</option>
                    <option value="500">500</option>
                </select>
                <span>entries</span>
            </div>
            <div class="right-wrap">
                <div class="search-wrap"><input type="text" id="gradeSearchInput" placeholder="Cari data..."></div>
                <button class="btn-md2-tambah" type="button" id="addGradeBtn" data-bs-toggle="modal" data-bs-target="#modalGradeForm">
                    <i class="bx bx-plus"></i> Tambah
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover md2-table" id="gradeTable" style="width:100%">
                <thead><tr>
                    <th>Kode</th><th>Golongan</th><th>Masa Kerja</th><th>Nominal</th><th>Aksi</th>
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
        <div class="mb-3"><label>Kode</label><input type="text" class="form-control" id="kode" name="kode" required></div>
        <div class="mb-3"><label>Golongan</label><input type="text" class="form-control" id="gol" name="gol" required></div>
        <div class="mb-3"><label>Masa Kerja</label><input type="number" class="form-control" id="masa_kerja" name="masa_kerja" required></div>
        <div class="mb-3"><label>Nominal</label><input type="number" class="form-control" id="nominal" name="nominal" required></div>
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
    const alert = (text = "Data berhasil tersimpan!", title = "Berhasil", icon = "success", warnaBtn = "btn btn-primary") => {
      return Swal.fire({ title, text, icon, confirmButtonText: 'OK', timer: 1500, timerProgressBar: true, customClass: { confirmButton: warnaBtn }, buttonsStyling: false });
    };
    const loadingAlert = (message) => {
      return Swal.fire({ title: 'Mohon tunggu...', html: `<div class="d-flex justify-content-center align-items-center flex-column"><div class="spinner-border spinner-border-lg text-danger" role="status"><span class="visually-hidden">Loading...</span></div><div class="mt-2">Mohon tunggu <br> ${message ?? 'Sedang menghapus data!'}</div></div>`, showConfirmButton: false, allowOutsideClick: false, allowEscapeKey: false, backdrop: true });
    };

    document.getElementById('addGradeBtn').addEventListener('click', function() {
      document.getElementById('modalGradeTitle').innerText = 'Tambah Data Grade';
      document.getElementById('gradeForm').reset();
      document.getElementById('formMethod').value = 'POST';
      document.getElementById('gradeForm').setAttribute('action', "{{ route('admin/data-grade.store') }}");
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
      fetch(gradeForm.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: dataForm })
        .then(res => res.json()).then(res => {
          Swal.close();
          if (!res.success) return Swal.fire('Gagal', res.message, 'error');
          Swal.fire({ title: 'Berhasil', text: res.message, icon: 'success', timer: 1500, showConfirmButton: false });
          table.ajax.reload();
        }).catch(err => console.error(err));
    });

    const table = $('#gradeTable').DataTable({
      processing: true, serverSide: true, responsive: true,
      pageLength: 10,
      dom: '<"d-none"l><"d-none"f>rtip',
      lengthMenu: [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']],
      ajax: { url: "{{ route('admin/data-grade') }}" },
      columns: [
        { 
          data: 'kode', name: 'kode',
          render: function(data) { return '<span class="fw-semibold text-primary">' + (data || '-') + '</span>'; }
        },
        { 
          data: 'gol', name: 'gol',
          render: function(data) { return '<span class="fw-bold text-dark">' + (data || '-') + '</span>'; }
        },
        { data: 'masa_kerja', name: 'masa_kerja' },
        { 
          data: 'nominal', name: 'nominal',
          render: function(data) { 
            let val = parseFloat(data);
            if (isNaN(val)) return data || '-';
            return '<span class="fw-bold text-dark">' + val.toLocaleString('id-ID') + '</span>'; 
          }
        },
        { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
      ],
      language: { paginate: { first: "«", last: "»", next: "›", previous: "‹" }, zeroRecords: 'Data tidak ditemukan', infoEmpty: 'Tidak ada data tersedia', info: 'Menampilkan _START_-_END_ dari _TOTAL_ entri' },
    });

    document.getElementById('gradeLengthSelect').addEventListener('change', function() { table.page.len(parseInt(this.value)).draw(); });
    let gradeSearchTimer;
    document.getElementById('gradeSearchInput').addEventListener('input', function() {
      clearTimeout(gradeSearchTimer); const val = this.value;
      gradeSearchTimer = setTimeout(() => { table.search(val).draw(); }, 400);
    });

    $('#gradeTable').on('click', '.edit-grade', function() {
      const kode = $(this).data('id');
      fetch(`/admin/data-grade/${kode}/edit`).then(res => res.json()).then(data => {
        $('#modalGradeTitle').text('Edit Data Grade'); $('#gradeId').val(data.kode); $('#kode').val(data.kode);
        $('#gol').val(data.gol); $('#masa_kerja').val(data.masa_kerja); $('#nominal').val(data.nominal);
        $('#formMethod').val('PUT'); $('#gradeForm').attr('action', `/admin/data-grade/${data.kode}`); $('#modalGradeForm').modal('show');
      });
    });

    $('#gradeTable').on('click', '.delete-grade', function() {
      const form = $(this).closest('.delete-form')[0];
      Swal.fire({ title: 'Apakah Anda Yakin?', text: "Data yang dihapus tidak bisa dikembalikan!", icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal', customClass: { confirmButton: 'btn btn-danger me-1', cancelButton: 'btn btn-secondary' }, buttonsStyling: false }).then((result) => {
        if (result.isConfirmed) {
          loadingAlert();
          fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: new FormData(form) })
            .then(res => res.json()).then(data => { Swal.close(); if (!data.success) return Swal.fire('Gagal', data.message, 'error'); Swal.fire({ title: 'Berhasil', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false }); table.ajax.reload(); }).catch(err => console.error(err));
        }
      });
    });
  });
</script>
@endsection