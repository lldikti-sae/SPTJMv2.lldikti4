@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('page-style')
<style>
.dt-toolbar {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 10px;
    padding: 10px 0 12px;
}
.dt-toolbar .dt-search-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
}
.dt-toolbar .dt-search-wrap label {
    font-size: 0.83rem;
    font-weight: 600;
    color: #64748b;
    margin-bottom: 0;
    white-space: nowrap;
}
.dt-toolbar .dt-search-wrap input {
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    padding: 5px 10px;
    font-size: 0.85rem;
    height: 34px;
    width: 220px;
    outline: none;
    color: #374151;
}
.dt-toolbar .dt-search-wrap input:focus {
    border-color: #696cff;
    box-shadow: 0 0 0 2px rgba(105,108,255,0.1);
}
.dataTables_info { font-size: 0.82rem; color: #64748b; }
.dataTables_paginate { display: flex; align-items: center; gap: 4px; }
.dataTables_paginate .paginate_button {
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 6px !important;
    padding: 4px 10px !important;
    font-size: 0.82rem !important;
    color: #374151 !important;
    background: #f8fafc !important;
    cursor: pointer;
    transition: all 0.2s;
}
.dataTables_paginate .paginate_button:hover,
.dataTables_paginate .paginate_button.current {
    background: #696cff !important;
    color: #fff !important;
    border-color: #696cff !important;
}
.dataTables_paginate .paginate_button.disabled { opacity: 0.4; cursor: not-allowed; }
.dt-bottom-row {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 12px;
}
</style>
@endsection

@section('content')
<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Complain</h5>
  <hr>
  <div class="table-responsive text-nowrap">
    <!-- 'Buat Complain' button hidden for PTS -->

    <table class="table table-sm table-hover" id="complainTable">
      <thead style="background-color: #dbdee0;">
        <tr>
          <th>ID</th>
          <th>Judul</th>
          <th>PIC</th>
          <th>Status</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Buat Complain -->
<div class="modal fade" id="modalComplainForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Buat Complain</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="complainForm" method="POST" action="{{ route('pts.complain.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label>Judul</label>
            <input type="text" class="form-control" name="judul" id="judul" required>
          </div>
          <div class="mb-3">
            <label>Pesan</label>
            <textarea class="form-control" name="pesan" id="pesan" rows="4" required></textarea>
          </div>
          <div class="mb-3">
            <label>Lampiran</label>
            <input type="file" class="form-control" name="lampiran" id="lampiran">
            <small class="text-muted">Maksimal 1 file, maksimal 5MB. Format bebas.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Kirim</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalComplainDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Complain</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2"><strong>Judul:</strong> <span id="dJudul"></span></div>
        <div class="mb-2"><strong>Status:</strong> <span id="dStatus"></span></div>
        <div class="mb-3">
          <strong>Pesan:</strong>
          <textarea class="form-control" id="dPesan" rows="6" readonly></textarea>
        </div>
        <div class="mb-0">
          <strong>Balasan Admin:</strong>
          <textarea class="form-control" id="dBalasan" rows="4" readonly></textarea>
        </div>
        <div class="mt-3"><strong>Lampiran:</strong><div class="border rounded p-2" id="dLampiran"></div></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    $.fn.dataTable.ext.errMode = 'none';
    $('#complainTable').on('error.dt', function(e, settings, techNote, message) {
      console.error('DataTables error:', message);
    });

    if (window.CKEDITOR) {
      CKEDITOR.replace('pesan', {
        height: 220,
        allowedContent: true,
        extraAllowedContent: 'style;*[class];*[style];*(*);*{*}'
      });

      if (!CKEDITOR.instances.dPesan) {
        CKEDITOR.replace('dPesan', {
          readOnly: true,
          toolbar: [],
          removePlugins: 'elementspath',
          resize_enabled: false,
          height: 220,
          allowedContent: true,
          extraAllowedContent: 'style;*[class];*[style];*(*);*{*}'
        });
      }

      if (!CKEDITOR.instances.dBalasan) {
        CKEDITOR.replace('dBalasan', {
          readOnly: true,
          toolbar: [],
          removePlugins: 'elementspath',
          resize_enabled: false,
          height: 140,
          allowedContent: true,
          extraAllowedContent: 'style;*[class];*[style];*(*);*{*}'
        });
      }
    }

    const table = $('#complainTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      dom: "<'dt-toolbar'<'dt-search-wrap'f>>rt<'dt-bottom-row'ip>",
      ajax: {
        url: "{{ route('pts.complain') }}",
        error: function(xhr) {
          console.error('Ajax error:', xhr.status, xhr.responseText);
        }
      },
      columns: [
        { data: 'id', name: 'id' },
        { data: 'judul', name: 'judul' },
        { data: 'pic', name: 'pic' },
        { data: 'status', name: 'status', orderable: false, searchable: false },
        { data: 'created_at', name: 'created_at' },
        { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
      ],
      // Default sort: newest first by Created At (column index 4)
      order: [[4, 'desc']],
      language: {
        paginate: { first: 'Awal', last: 'Akhir', next: '→', previous: '←' },
        emptyTable: 'Data Tidak Tersedia',
        zeroRecords: 'Data Tidak Tersedia',
        infoEmpty: 'Data Tidak Tersedia',
        searchPlaceholder: 'Cari data...',
        search: 'Cari:'
      },
    });

    const addComplainBtn = document.getElementById('addComplainBtn');
    if (addComplainBtn) {
      addComplainBtn.addEventListener('click', function() {
        document.getElementById('complainForm').reset();
        if (window.CKEDITOR && CKEDITOR.instances.pesan) {
          CKEDITOR.instances.pesan.setData('');
        }
        const fileInput = document.getElementById('lampiran');
        if (fileInput) fileInput.value = '';
      });
    }

    document.getElementById('complainForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const form = e.target;

      if (window.CKEDITOR && CKEDITOR.instances.pesan) {
        CKEDITOR.instances.pesan.updateElement();
      }

      const fileInput = document.getElementById('lampiran');
      const files = fileInput ? Array.from(fileInput.files || []) : [];
      if (files.length > 1) {
        return SptjmAlert.warning('Peringatan', 'Maksimal upload 1 file.');
      }
      for (const f of files) {
        if (f.size > MAX_FILE_SIZE) {
          return SptjmAlert.warning('Peringatan', `File ${f.name} melebihi 5MB.`);
        }
      }

      const modalEl = document.getElementById('modalComplainForm');
      const modalInstance = bootstrap.Modal.getInstance(modalEl);
      if (modalInstance) modalInstance.hide();

      SptjmAlert.loading('Mohon Tunggu', 'Sedang mengirim complain...');
      fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: new FormData(form)
      }).then(async (res) => {
        const data = await res.json().catch(() => ({}));
        await SptjmAlert.close();
        if (!res.ok || !data.success) {
          const errMsg = (data && data.message) ? data.message : (res.status === 422 && data && data.errors ? Object.values(data.errors).flat().join('\n') : 'Terjadi kesalahan');
          return SptjmAlert.error('Gagal', errMsg);
        }
        await SptjmAlert.success('Berhasil', data.message || 'Complain terkirim.', { showConfirmButton: true });
        table.ajax.reload();
      }).catch(async (err) => {
        console.error(err);
        await SptjmAlert.close();
        SptjmAlert.error('Gagal', 'Terjadi kesalahan saat mengirim complain.');
      });
    });

    $('#complainTable').on('click', '.view-complain', function() {
      const id = $(this).data('id');
      SptjmAlert.loading('Mohon Tunggu', 'Mengambil detail...');
      fetch(`/pts/complain/${id}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      }).then(async (res) => {
        const payload = await res.json().catch(() => ({}));
        await SptjmAlert.close();
        if (!res.ok || !payload.success) {
          return SptjmAlert.error('Gagal', payload.message || 'Data tidak ditemukan');
        }
        const d = payload.data;
        document.getElementById('dJudul').innerText = d.judul || '-';
        document.getElementById('dStatus').innerText = (d.status || '-').toString().replace('_', ' ');
        if (window.CKEDITOR && CKEDITOR.instances.dPesan) {
          CKEDITOR.instances.dPesan.setData(d.pesan || '-');
        } else {
          document.getElementById('dPesan').value = d.pesan || '-';
        }
        if (window.CKEDITOR && CKEDITOR.instances.dBalasan) {
          CKEDITOR.instances.dBalasan.setData(d.admin_balasan || '-');
        } else {
          document.getElementById('dBalasan').value = d.admin_balasan || '-';
        }

        const lampiranEl = document.getElementById('dLampiran');
        lampiranEl.innerHTML = '-';
        try {
          const base = "{{ asset('storage') }}/";
          if (d.lampiran) {
            let paths = [];
            if (typeof d.lampiran === 'string' && d.lampiran.trim().startsWith('[')) {
              const arr = JSON.parse(d.lampiran);
              if (Array.isArray(arr)) paths = arr;
            } else {
              paths = [d.lampiran];
            }
            if (paths.length) {
              lampiranEl.innerHTML = paths.map(p => `<div><a href="${base}${p}" target="_blank" rel="noopener">${p}</a></div>`).join('');
            }
          }
        } catch (e) {
          // ignore
        }
        $('#modalComplainDetail').modal('show');
      }).catch(async (err) => {
        console.error(err);
        await SptjmAlert.close();
        SptjmAlert.error('Gagal', 'Terjadi kesalahan saat mengambil detail.');
      });
    });
  });
</script>
@endsection
