@extends('layouts/contentNavbarLayout')

@section('title', 'Data Bank - SPTJM Online')

@section('page-style')
<style>
/* ── Shared Master Data Card Style ── */
.md2-page-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
.md2-page-header .page-titles h4 { font-size:1.35rem; font-weight:700; color:#2c3e50; margin:0 0 4px; line-height:1.2; }
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
.md2-toolbar .search-wrap input {
    border:1px solid #e2e8f0; border-radius:6px; padding:6px 14px 6px 36px; font-size:0.84rem;
    color:#2d3748; min-width:210px; outline:none; transition:border-color 0.2s;
    background:#f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%238592a3' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 10px center;
}
.md2-toolbar .search-wrap input:focus { border-color:#1a56db; background-color:#fff; }

.btn-md2-tambah { background:#1a56db; border:none; color:#fff; font-weight:600; font-size:0.82rem; padding:8px 18px; border-radius:6px; display:inline-flex; align-items:center; gap:6px; transition:background 0.2s,box-shadow 0.2s; white-space:nowrap; cursor:pointer; }
.btn-md2-tambah:hover { background:#1648c0; color:#fff; box-shadow:0 4px 12px rgba(26,86,219,0.35); }

/* Table header/body/pagination: dipindahkan ke global sptjm-datatable.css */
</style>
@endsection

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Page Header --}}
<div class="md2-page-header">
    <div class="page-titles">
        <h4>Data Bank</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Master Data</a></li>
                <li class="breadcrumb-item active">Data Bank</li>
            </ol>
        </nav>
    </div>
</div>

{{-- Main Card --}}
<div class="md2-card">
    <div class="md2-card-inner">
        {{-- Toolbar --}}
        <div class="md2-toolbar">
            <div class="entries-wrap">
                <span>Show</span>
                <select id="bankLengthSelect">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="500">500</option>
                </select>
                <span>entries</span>
            </div>
            <div class="right-wrap">
                <div class="search-wrap">
                    <input type="text" id="bankSearchInput" placeholder="Cari data...">
                </div>
                <button class="btn-md2-tambah" id="addBankBtn" data-bs-toggle="modal" data-bs-target="#modalBankForm">
                    <i class="bx bx-plus"></i> Tambah
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover md2-table" id="bankTable" style="width:100%">
                <thead>
                    <tr>
                        <th>Kode Bank</th>
                        <th>Nama Bank</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalBankForm" tabindex="-1" aria-labelledby="modalBankFormLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalBankTitle">Tambah Data Bank</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="bankForm" method="POST">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" id="bankId" name="id">
        <div class="modal-body">
          <div class="mb-3">
            <label>Kode Bank</label>
            <input type="text" class="form-control" id="kode_bank" name="kode_bank" required>
          </div>
          <div class="mb-3">
            <label>Nama Bank</label>
            <input type="text" class="form-control" id="nama_bank" name="nama_bank" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.getElementById('addBankBtn').addEventListener('click', function() {
    document.getElementById('modalBankTitle').innerText = 'Tambah Data Bank';
    document.getElementById('bankForm').reset();
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('bankForm').setAttribute('action', "{{ route('admin/data-bank.store') }}");
  });

  $(document).ready(function() {
    const bankForm = document.getElementById('bankForm');
    bankForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const modalSync = document.getElementById('modalBankForm');
      const modalInstance = bootstrap.Modal.getInstance(modalSync);
      modalInstance.hide();
      loadingAlert();
      const dataForm = new FormData(bankForm);
      const fetchingData = async () => {
        const data = await fetch(bankForm.action, { method: "POST", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }, body: dataForm });
        const res = await data.json();
        Swal.close();
        if (!res.success) { alert(res.message, 'Gagal!', 'error', 'btn btn-danger'); }
        alert(res.message);
        table.ajax.reload();
      };
      fetchingData();
    });

    const table = $('#bankTable').DataTable({
      processing: true, serverSide: true, responsive: true,
      pageLength: 10,
      dom: '<"d-none"l><"d-none"f>rtip',
      lengthMenu: [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']],
      ajax: { url: "{{ route('admin.data-bank') }}" },
      columns: [
        { 
          data: "kode_bank", name: "kode_bank",
          render: function(data) { return '<span class="fw-semibold text-primary">' + (data || '-') + '</span>'; }
        },
        { 
          data: "nama_bank", name: "nama_bank",
          render: function(data) { return '<span class="fw-bold text-dark">' + (data || '-') + '</span>'; }
        },
        { data: "aksi",      name: "aksi", orderable: false, searchable: false }
      ],
      language: {
        paginate: { first: "«", last: "»", next: "›", previous: "‹" },
        zeroRecords: "Data tidak ditemukan",
        infoEmpty: "Tidak ada data tersedia",
        info: "Menampilkan _START_-_END_ dari _TOTAL_ entri",
      },
    });

    document.getElementById('bankLengthSelect').addEventListener('change', function() {
      table.page.len(parseInt(this.value)).draw();
    });
    let bankSearchTimer;
    document.getElementById('bankSearchInput').addEventListener('input', function() {
      clearTimeout(bankSearchTimer);
      const val = this.value;
      bankSearchTimer = setTimeout(() => { table.search(val).draw(); }, 400);
    });

    const alert = (text = "Data berhasil tersimpan!", title = "Berhasil", icon = "success", warnaBtn = "btn btn-primary") => {
      return Swal.fire({ title, text, icon, confirmButtonText: 'OK', timer: 1500, timerProgressBar: true, customClass: { confirmButton: warnaBtn }, buttonsStyling: false });
    };
    const loadingAlert = () => {
      return Swal.fire({ title: 'Mohon tunggu...', html: `<div class="d-flex justify-content-center align-items-center flex-column"><div class="spinner-border spinner-border-lg text-danger" role="status"><span class="visually-hidden">Loading...</span></div><div class="mt-2">Mohon tunggu <br> Sedang mengupdate data!</div></div>`, showConfirmButton: false, allowOutsideClick: false, allowEscapeKey: false, backdrop: true });
    };

    $('#bankTable').on('click', '.edit-bank', function() {
      const bankKode = $(this).data('id');
      fetch(`/admin/data-bank/${bankKode}/edit`).then(r => r.json()).then(data => {
        $('#modalBankTitle').text('Edit Data Bank');
        $('#bankId').val(data.kode_bank); $('#kode_bank').val(data.kode_bank); $('#nama_bank').val(data.nama_bank);
        $('#formMethod').val('PUT'); $('#bankForm').attr('action', `/admin/data-bank/${data.kode_bank}`);
        $('#modalBankForm').modal('show');
      });
    });

    $('#bankTable').on('click', '.delete-bank', function() {
      let form = $(this).closest('.delete-form')[0];
      Swal.fire({ title: 'Apakah Anda Yakin?', text: "Data yang dihapus tidak bisa dikembalikan!", icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal', customClass: { confirmButton: 'btn btn-danger me-1', cancelButton: 'btn btn-secondary' }, buttonsStyling: false }).then((result) => {
        if (result.isConfirmed) {
          loadingAlert();
          fetch(form.action, { method: "POST", headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }, body: new FormData(form) })
            .then(res => res.json()).then(data => { Swal.close(); if (!data.success) { alert(data.message, 'Gagal!', 'error', 'btn btn-danger'); } else { alert(data.message); } table.ajax.reload(); }).catch(err => console.error(err));
        }
      });
    });
  });
</script>
@endsection