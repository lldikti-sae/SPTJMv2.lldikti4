@extends('layouts/contentNavbarLayout')

@section('title', 'Data Akun Dosen - SPTJM Online')

@section('page-style')
<style>
/* ── Page Header ── */
.md-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
}
.md-page-header .page-titles h4 {
    font-size: 1.35rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 4px 0;
    line-height: 1.2;
}
.md-page-header .breadcrumb {
    margin: 0;
    font-size: 0.8rem;
    background: none;
    padding: 0;
}
.md-page-header .breadcrumb-item a { color: #696cff; text-decoration: none; }
.md-page-header .breadcrumb-item.active { color: #8592a3; }
.md-page-header .breadcrumb-item + .breadcrumb-item::before { color: #8592a3; }

/* ── Tambah Button ── */
.btn-tambah-md {
    background-color: #1a56db;
    border-color: #1a56db;
    color: #fff;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 8px 18px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s, box-shadow 0.2s;
    white-space: nowrap;
}
.btn-tambah-md:hover {
    background-color: #1648c0;
    border-color: #1648c0;
    color: #fff;
    box-shadow: 0 4px 12px rgba(26,86,219,0.35);
}

/* ── Card ── */
.md-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(44,62,80,0.07);
    overflow: hidden;
}
.md-card .md-card-inner { padding: 20px 24px 24px; }

/* ── Toolbar ── */
.md-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}
.md-toolbar .entries-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.84rem;
    color: #4a5568;
}
.md-toolbar .entries-wrap select {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 5px 10px;
    font-size: 0.84rem;
    color: #4a5568;
    background: #f8fafc;
    cursor: pointer;
    outline: none;
}
.md-toolbar .entries-wrap select:focus { border-color: #1a56db; }

.md-toolbar .search-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.md-toolbar .search-wrap input {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 6px 14px 6px 36px;
    font-size: 0.84rem;
    color: #2d3748;
    background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%238592a3' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat 10px center;
    min-width: 210px;
    outline: none;
    transition: border-color 0.2s;
}
.md-toolbar .search-wrap input:focus { border-color: #1a56db; background-color: #fff; }

/* ── Table ── */
.md-table-wrap table.dataTable thead th {
    background: #f1f3f5 !important;
    color: #374151 !important;
    font-size: 0.75rem !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    border-bottom: 2px solid #e5e7eb !important;
    padding: 12px 14px !important;
    white-space: nowrap;
}
.md-table-wrap table.dataTable tbody td {
    font-size: 0.84rem;
    color: #374151;
    padding: 10px 14px !important;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
}
.md-table-wrap table.dataTable tbody tr:hover { background-color: #f8fafc !important; }
.md-table-wrap table.dataTable { border-collapse: collapse !important; }

/* Badge status */
.badge-aktif {
    background-color: rgba(40,199,111,0.12);
    color: #28c76f;
    font-weight: 700;
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    display: inline-block;
}
.badge-nonaktif {
    background-color: rgba(234,84,85,0.12);
    color: #ea5455;
    font-weight: 700;
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    display: inline-block;
}

/* Aksi buttons */
.btn-aksi-edit   { background:#fd9f10; border:none; color:#fff; border-radius:6px; padding:5px 8px; font-size:0.82rem; }
.btn-aksi-reset  { background:#0d6efd; border:none; color:#fff; border-radius:6px; padding:5px 8px; font-size:0.82rem; }
.btn-aksi-delete { background:#dc3545; border:none; color:#fff; border-radius:6px; padding:5px 8px; font-size:0.82rem; }
.btn-aksi-edit:hover   { background:#e68a00; color:#fff; }
.btn-aksi-reset:hover  { background:#0b5ed7; color:#fff; }
.btn-aksi-delete:hover { background:#bb2d3b; color:#fff; }

/* Modal inputs readonly */
#modalDosenForm input.form-control[readonly] {
    background-color: #eceef1 !important;
    cursor: not-allowed;
}
#modalDosenForm select.form-select:disabled {
    background-color: #eceef1 !important;
    pointer-events: none;
    color: #495057;
}

/* DataTables overrides */
.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #1a56db !important;
    color: #fff !important;
    border: none !important;
    border-radius: 6px !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #eef2ff !important;
    color: #1a56db !important;
    border: none !important;
    border-radius: 6px !important;
}
.dataTables_wrapper .dataTables_info { font-size: 0.82rem; color: #8592a3; }
</style>
@endsection

@section('content')

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h4>Data Akun Dosen</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Master Data</a></li>
                <li class="breadcrumb-item active">Data Akun Dosen</li>
            </ol>
        </nav>
    </div>
</div>

{{-- Main Card --}}
<div class="md-card">
    <div class="md-card-inner">

        {{-- Toolbar: entries kiri, search & Tambah kanan --}}
        <div class="md-toolbar">
            <div class="entries-wrap">
                <span>Show</span>
                <select id="md-length-select">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="500">500</option>
                </select>
                <span>entries</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="search-wrap">
                    <input type="text" id="md-search-input" placeholder="Cari data dosen...">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn-tambah-md" type="button" id="addDosenBtn" data-bs-toggle="modal" data-bs-target="#modalDosenForm">
                        <i class="bx bx-plus"></i> Tambah
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="md-table-wrap table-responsive">
            <table class="table table-hover" id="dosenTable" style="width:100%">
                <thead>
                    <tr>
                        <th>NIDN</th>
                        <th>NUPTK</th>
                        <th>Kode PTS</th>
                        <th>Nama PTS</th>
                        <th>Nama Dosen</th>
                        <th>Aktif</th>
                        <th>Wilayah</th>
                        <th>Tanggal Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>


<!-- Modal Tambah/Edit Dosen -->
<div class="modal fade" id="modalDosenForm" tabindex="-1" aria-labelledby="modalDosenFormLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDosenTitle">Tambah Data Dosen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="dosenForm" method="POST">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" id="dosenId" name="id">
        <div class="modal-body">
          <div class="mb-3">
            <label>NIDN</label>
            <input type="text" class="form-control" id="nidn" name="nidn">
          </div>
          <div class="mb-3">
            <label>NUPTK</label>
            <input type="text" class="form-control" id="nuptk" name="nuptk">
          </div>
          <div class="mb-3">
            <label>Kode PTS</label>
            <select class="form-select" id="kode_pts" name="kode_pts">
              <option value="">- Pilih Kode PTS -</option>
              @foreach (($ptsOptions ?? []) as $pt)
                <option value="{{ data_get($pt, 'kode_pts') }}">{{ data_get($pt, 'kode_pts') }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label>Nama PTS</label>
            <input type="text" class="form-control" id="nama_pts" name="nama_pts" readonly style="background-color: #eceef1;">
          </div>
          <div class="mb-3">
            <label>Nama Dosen</label>
            <input type="text" class="form-control" id="nama_dosen" name="nama_dosen">
          </div>
          <div class="mb-3">
            <label>Alamat PT</label>
            <input type="text" class="form-control" id="alamat_pt" name="alamat_pt" readonly style="background-color: #eceef1;">
          </div>
          <div class="mb-3">
            <label>Wilayah</label>
            <select class="form-select" id="wilayah" name="wilayah">
              <option value="">- Pilih PIC -</option>
              @foreach (($picEmails ?? []) as $email)
                <option value="{{ $email }}">{{ $email }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label>Aktif</label>
            <select class="form-select" id="aktif" name="aktif">
              <option value="">-</option>
              <option value="1">Aktif</option>
              <option value="0">Tidak Aktif</option>
            </select>
          </div>
          <!-- Password will be auto-generated (NIDN or NUPTK) on save; input removed -->
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
  document.addEventListener('DOMContentLoaded', function() {
    // Use SptjmAlert helper (sptjm-alert.js) for notifications

    const ptsOptions = @json($ptsOptions ?? []);
    const ptsByKode = (Array.isArray(ptsOptions) ? ptsOptions : []).reduce((acc, pt) => {
      const kode = (pt && pt.kode_pts) ? String(pt.kode_pts) : '';
      if (kode) acc[kode] = pt;
      return acc;
    }, {});

    const applyPtsDetails = (fallback = null) => {
      const kode = String(document.getElementById('kode_pts')?.value || '');
      const pt = ptsByKode[kode];
      const fallbackNama = fallback && typeof fallback === 'object' ? (fallback.nama_pts || '') : '';
      const fallbackAlamat = fallback && typeof fallback === 'object' ? (fallback.alamat_pt || '') : '';
      document.getElementById('nama_pts').value = pt && pt.nama_pts ? pt.nama_pts : fallbackNama;
      document.getElementById('alamat_pt').value = pt && pt.alamat_pt ? pt.alamat_pt : fallbackAlamat;
    };

    document.getElementById('kode_pts')?.addEventListener('change', applyPtsDetails);

    const ensureSelectHasOption = (selectId, value) => {
      const selectEl = document.getElementById(selectId);
      const strValue = value === null || value === undefined ? '' : String(value);
      if (!selectEl || !strValue) return;
      const exists = Array.from(selectEl.options).some(opt => String(opt.value) === strValue);
      if (exists) return;
      const opt = document.createElement('option');
      opt.value = strValue;
      opt.textContent = strValue;
      selectEl.appendChild(opt);
    };

    // Reset modal saat tambah data
    document.getElementById('addDosenBtn').addEventListener('click', function() {
      document.getElementById('modalDosenTitle').innerText = 'Tambah Data Dosen';
      // Password is auto-generated on the server; no input required here.
      document.getElementById('dosenForm').reset();
      document.getElementById('formMethod').value = 'POST';
      document.getElementById('dosenForm').setAttribute('action', "{{ route('admin.master-dosen.store') }}");

      // ensure readonly fields cleared and make form editable for adding
      applyPtsDetails();
      setEditMode(false);
    });

    // Helper to toggle edit mode (true = edit/readonly, false = add/edit enabled)
    const setEditMode = (isEditMode) => {
      // inputs to set readonly (but still submit their values)
      const readonlyInputs = ['nidn', 'nuptk', 'nama_pts', 'nama_dosen', 'alamat_pt'];
      readonlyInputs.forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        if (isEditMode) {
          el.setAttribute('readonly', 'readonly');
          el.classList.add('bg-light');
        } else {
          el.removeAttribute('readonly');
          el.classList.remove('bg-light');
        }
      });

      // selects to disable (but we will create hidden inputs to preserve values)
      const selectIds = ['kode_pts', 'wilayah'];
      selectIds.forEach(id => {
        const sel = document.getElementById(id);
        if (!sel) return;
        // remove existing hidden mirror
        const hidId = `hid_${id}`;
        const existing = document.getElementById(hidId);
        if (existing) existing.remove();

        if (isEditMode) {
          // create hidden input to carry value since disabled selects are not submitted
          const hid = document.createElement('input');
          hid.type = 'hidden';
          hid.id = hidId;
          hid.name = sel.name;
          hid.value = sel.value;
          sel.parentNode.appendChild(hid);
          sel.setAttribute('disabled', 'disabled');
        } else {
          sel.removeAttribute('disabled');
        }
      });

      // Aktif should remain editable when in edit mode; otherwise leave as default
      const aktifEl = document.getElementById('aktif');
      if (aktifEl) {
        if (isEditMode) {
          aktifEl.removeAttribute('disabled');
          aktifEl.removeAttribute('readonly');
        }
      }
    };

    const dosenForm = document.getElementById('dosenForm');
    dosenForm.addEventListener('submit', function(e) {
      e.preventDefault();

      const modalSync = document.getElementById('modalDosenForm');
      const modalInstance = bootstrap.Modal.getInstance(modalSync);
      if (modalInstance) modalInstance.hide();

      const dataForm = new FormData(dosenForm);
      const method = document.getElementById('formMethod').value;
      method == 'POST' ? SptjmAlert.loading('Mohon Tunggu', 'Sedang menyimpan data!') : SptjmAlert.loading('Mohon Tunggu', 'Sedang mengupdate data!');

      fetch(dosenForm.action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          body: dataForm
        })
        .then(async (res) => {
          let data = {};
          try {
            data = await res.json();
          } catch (e) {
            data = {};
          }

          // close loading first, then show appropriate modal
          await SptjmAlert.close();

          if (res.ok && data && data.success) {
            await SptjmAlert.success('Berhasil', data.message || 'Berhasil menyimpan data.', { showConfirmButton: true });
            table.ajax.reload();
            return;
          }

          let msg = (data && data.message) ? data.message : 'Terjadi kesalahan.';
          if (data && data.errors) {
            const firstKey = Object.keys(data.errors)[0];
            if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) {
              msg = data.errors[firstKey][0];
            }
          }

          return SptjmAlert.error('Gagal', msg);
        })
        .catch(async (err) => {
          console.error(err);
          await SptjmAlert.close();
          SptjmAlert.error('Gagal', 'Terjadi kesalahan saat menyimpan data.');
        });
    });

    const table = $('#dosenTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      scrollX: true,
      scrollCollapse: true,
      pageLength: 10,
      dom: '<"d-none"l><"d-none"f>rtip', // sembunyikan length & search bawaan DT
      lengthMenu: [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']],
      ajax: {
        url: "{{ route('admin.master-dosen.index') }}"
      },
      columns: [
        { 
          data: 'nidn', name: 'nidn',
          render: function(data) { return '<span class="fw-semibold text-primary">' + (data || '-') + '</span>'; }
        },
        { data: 'nuptk', name: 'nuptk' },
        { 
          data: 'kode_pts', name: 'kode_pts',
          render: function(data) { return '<span class="fw-semibold text-primary">' + (data || '-') + '</span>'; }
        },
        { 
          data: 'nama_pts', name: 'nama_pts',
          render: function(data) { return '<span class="fw-semibold text-primary">' + (data || '-') + '</span>'; }
        },
        { 
          data: 'nama_dosen', name: 'nama_dosen',
          render: function(data) { return '<span class="fw-bold text-dark">' + (data || '-') + '</span>'; }
        },
        {
          data: 'aktif', name: 'aktif',
          render: function(data) {
            if (data == 1 || data == '1') {
              return '<span class="badge-aktif">Aktif</span>';
            }
            return '<span class="badge-nonaktif">Tidak Aktif</span>';
          }
        },
        { data: 'wilayah', name: 'wilayah' },
        { data: 'tanggal_update', name: 'tanggal_update' },
        { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
      ],
      language: {
        paginate: {
          first: 'Awal',
          last: 'Akhir',
          next: '→',
          previous: '←',
        },
        zeroRecords: 'Data tidak ditemukan',
        infoEmpty: 'Tidak ada data tersedia',
        info: 'Menampilkan _START_-_END_ dari _TOTAL_ entri',
      },
    });

    // Hubungkan custom length select ke DataTable
    document.getElementById('md-length-select')?.addEventListener('change', function() {
      table.page.len(parseInt(this.value)).draw();
    });

    // Hubungkan custom search input ke DataTable (debounce 400ms)
    let mdSearchTimer;
    document.getElementById('md-search-input')?.addEventListener('input', function() {
      clearTimeout(mdSearchTimer);
      const val = this.value;
      mdSearchTimer = setTimeout(() => { table.search(val).draw(); }, 400);
    });


    // edit
    $('#dosenTable').on('click', '.edit-dosen', function() {
      const id = $(this).data('id');
      fetch(`/admin/master-dosen/${id}/edit`).then(res => res.json()).then(data => {
        $('#modalDosenTitle').text('Edit Data Dosen');

        // ensure dropdown can show current DB value even if not in options
        ensureSelectHasOption('kode_pts', data.kode_pts);
        ensureSelectHasOption('wilayah', data.wilayah);

        $('#dosenId').val(data.id);
        $('#nidn').val(data.nidn);
        $('#nuptk').val(data.nuptk);
        $('#kode_pts').val(data.kode_pts);
        // default values from DB; if kode_pts exists in a_pts, it will override via applyPtsDetails
        applyPtsDetails({ nama_pts: data.nama_pts, alamat_pt: data.alamat_pt });
        $('#nama_pts').val(data.nama_pts);
        $('#nama_dosen').val(data.nama_dosen);
        $('#alamat_pt').val(data.alamat_pt);
        $('#wilayah').val(data.wilayah);
        $('#aktif').val(data.aktif === null ? '' : String(data.aktif));
        $('#formMethod').val('PUT');
        $('#dosenForm').attr('action', `/admin/master-dosen/${data.id}`);
        // make fields readonly for edit (only Aktif editable)
        setEditMode(true);
        $('#modalDosenForm').modal('show');
      });
    });

    // hapus
    $('#dosenTable').on('click', '.delete-dosen', function() {
      const form = $(this).closest('.delete-form')[0];
      SptjmAlert.question('Apakah Anda Yakin?', 'Data yang dihapus tidak bisa dikembalikan!', {
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          SptjmAlert.loading('Mohon Tunggu', 'Sedang menghapus data!');
          fetch(form.action, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
              },
              body: new FormData(form)
            })
            .then(async res => {
              const data = await res.json().catch(() => ({}));
              await SptjmAlert.close();
              if (!data.success) return SptjmAlert.error('Gagal', data.message || 'Terjadi kesalahan');
              await SptjmAlert.success('Berhasil', data.message, { showConfirmButton: true });
              table.ajax.reload();
            })
            .catch(async err => {
              console.error(err);
              await SptjmAlert.close();
              SptjmAlert.error('Gagal', 'Terjadi kesalahan saat menghapus data.');
            });
        }
      });
    });

    // reset password
    $('#dosenTable').on('click', '.reset-password', function() {
      const form = $(this).closest('.reset-form')[0];
      SptjmAlert.question('Reset Password?', 'Password akan diubah menjadi NIDN (atau NUPTK jika NIDN kosong).', {
        confirmButtonText: 'Ya, Reset!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          SptjmAlert.loading('Mohon Tunggu', 'Sedang mereset password...');
          fetch(form.action, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
              },
              body: new FormData(form)
            })
            .then(async res => {
              const data = await res.json().catch(() => ({}));
              await SptjmAlert.close();
              if (!res.ok || !data.success) return SptjmAlert.error('Gagal', data.message || 'Terjadi kesalahan');
              await SptjmAlert.success('Berhasil', data.message || 'Password berhasil direset.', { showConfirmButton: true });
              table.ajax.reload(null, false);
            })
            .catch(async err => {
              console.error(err);
              await SptjmAlert.close();
              SptjmAlert.error('Gagal', 'Terjadi kesalahan saat reset password.');
            });
        }
      });
    });
  });
</script>
@endsection

