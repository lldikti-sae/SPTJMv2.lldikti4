@extends('layouts/contentNavbarLayoutPic')

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
  <h5 class="card-header text-start p-2">Complain (PIC)</h5>
  <hr>
  <div>
    <div class="d-flex gap-2 mb-2 align-items-center">
      <label class="mb-0"><strong>Status:</strong></label>
      <select id="filterStatus" class="form-select form-select-sm" style="width:220px;">
        <option value="">Semua</option>
        <option value="open">OPEN</option>
        <option value="setuju">SETUJU</option>
        <option value="tolak">TOLAK</option>
      </select>
    </div>

    <table class="table table-sm table-hover" id="complainTable">
      <thead style="background-color: #dbdee0;">
        <tr>
          <th>ID</th>
          <th>Tipe</th>
          <th>Kode PTS</th>
          <th>NIDN</th>
          <th>NUPTK</th>
          <th>PIC</th>
          <th>Nama</th>
          <th>Judul</th>
          <th>Status</th>
          <th>Tanggal Handle</th>
          <th>Tanggal</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modal Tanggapi -->
<div class="modal fade" id="modalReply" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tanggapi Complain</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="replyForm" method="POST">
        @csrf
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" id="replyId">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-2"><strong>Pelapor:</strong> <span id="rPelapor"></span></div>
            <div class="col-md-6 mb-2"><strong>Kode PTS:</strong> <span id="rKodePts"></span></div>
            <div class="col-md-6 mb-2"><strong>NIDN:</strong> <span id="rNidn"></span></div>
            <div class="col-md-6 mb-2"><strong>NUPTK:</strong> <span id="rNuptk"></span></div>
          </div>
          <div class="mb-2"><strong>Judul:</strong> <span id="rJudul"></span></div>
          <div class="mb-3">
            <strong>Pesan:</strong>
            <textarea class="form-control" id="pesan_view" rows="6" readonly></textarea>
          </div>
          <div class="mb-3"><strong>Lampiran:</strong><div class="border rounded p-2" id="rLampiran"></div></div>

          <div class="mb-3">
            <label>Status</label>
            <select class="form-select" name="status" id="status" required>
              <option value="open">OPEN</option>
              <option value="setuju">SETUJU</option>
              <option value="tolak">TOLAK</option>
            </select>
          </div>
          <div class="mb-3">
            <label>Balasan</label>
            <textarea class="form-control" name="admin_balasan" id="admin_balasan" rows="4"></textarea>
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

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (window.CKEDITOR && !CKEDITOR.instances.admin_balasan) {
      CKEDITOR.replace('admin_balasan');
    }

    if (window.CKEDITOR && !CKEDITOR.instances.pesan_view) {
      CKEDITOR.replace('pesan_view', {
        readOnly: true,
        toolbar: [],
        removePlugins: 'elementspath',
        resize_enabled: false,
        height: 400,
        allowedContent: true,
        extraAllowedContent: 'style;*[class];*[style];*(*);*{*}'
      });
    }

    $.fn.dataTable.ext.errMode = 'none';
    $('#complainTable').on('error.dt', function(e, settings, techNote, message) {
      console.error('DataTables error:', message);
    });

    const table = $('#complainTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      dom: "<'dt-toolbar'<'dt-search-wrap'f>><'table-responsive text-nowrap't><'dt-bottom-row'ip>",
      ajax: {
        url: "{{ route('pic.complain.index') }}",
        data: function (d) {
          d.status = document.getElementById('filterStatus') ? document.getElementById('filterStatus').value : '';
        },
        error: function(xhr) {
          console.error('Ajax error:', xhr.status, xhr.responseText);
        }
      },
      columns: [
        { data: 'id', name: 'id' },
        { data: 'pelapor_tipe', name: 'pelapor_tipe' },
        { data: 'kode_pts', name: 'kode_pts' },
        { data: 'nidn', name: 'nidn' },
        { data: 'nuptk', name: 'nuptk' },
        { data: 'pic', name: 'pic' },
        { data: 'nama', name: 'nama' },
        { data: 'judul', name: 'judul' },
        { data: 'status', name: 'status', orderable: false, searchable: false },
        { data: 'handled_at', name: 'handled_at' },
        { data: 'created_at', name: 'created_at' },
        { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
      ],
      order: [[10, 'desc']],
      language: {
        paginate: { first: 'Awal', last: 'Akhir', next: '→', previous: '←' },
        emptyTable: 'Data Tidak Tersedia',
        zeroRecords: 'Data Tidak Tersedia',
        infoEmpty: 'Data Tidak Tersedia',
        searchPlaceholder: 'Cari data...',
        search: 'Cari:'
      },
    });

    document.getElementById('filterStatus')?.addEventListener('change', function () {
      $('#complainTable').DataTable().ajax.reload();
    });

    $('#complainTable').on('click', '.reply-complain', function() {
      const id = $(this).data('id');
      SptjmAlert.loading('Mohon Tunggu', 'Mengambil detail...');
      fetch(`/pic/complain/${id}`, {
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
        document.getElementById('replyId').value = d.id;
        document.getElementById('rPelapor').innerText = (d.pelapor_tipe || '-').toString().toUpperCase();
        document.getElementById('rKodePts').innerText = d.kode_pts || '-';
        document.getElementById('rNidn').innerText = d.nidn || '-';
        document.getElementById('rNuptk').innerText = d.nuptk || '-';
        document.getElementById('rJudul').innerText = d.judul || '-';

        if (window.CKEDITOR && CKEDITOR.instances.pesan_view) {
          CKEDITOR.instances.pesan_view.setData(d.pesan || '-');
        } else {
          document.getElementById('pesan_view').value = d.pesan || '-';
        }

        const lampiranEl = document.getElementById('rLampiran');
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
              lampiranEl.innerHTML = paths.map(p => {
                let url = `${base}${p}`;
                if (d.jenis_pengajuan === 'Surat Keterangan' || d.jenis_pengajuan === 'Surat SKPP') {
                    if (!p.includes('/')) {
                        url = `${base}Dokumen_Histori_Dosen2/${p}`;
                    }
                }
                return `<div><a href="${url}" target="_blank" rel="noopener">${p}</a></div>`;
              }).join('');
            }
          }
        } catch (e) {
          // ignore
        }

        document.getElementById('status').value = d.status || 'open';
        if (window.CKEDITOR && CKEDITOR.instances.admin_balasan) {
          CKEDITOR.instances.admin_balasan.setData(d.admin_balasan || '');
        } else {
          document.getElementById('admin_balasan').value = d.admin_balasan || '';
        }

        document.getElementById('replyForm').setAttribute('action', `/pic/complain/${d.id}`);
        $('#modalReply').modal('show');
      }).catch(async (err) => {
        console.error(err);
        await SptjmAlert.close();
        SptjmAlert.error('Gagal', 'Terjadi kesalahan saat mengambil detail.');
      });
    });

    document.getElementById('replyForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const form = e.target;
      const modalEl = document.getElementById('modalReply');
      const modalInstance = bootstrap.Modal.getInstance(modalEl);
      if (modalInstance) modalInstance.hide();

      if (window.CKEDITOR && CKEDITOR.instances.admin_balasan) {
        CKEDITOR.instances.admin_balasan.updateElement();
      }

      SptjmAlert.loading('Mohon Tunggu', 'Menyimpan balasan...');
      fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: new FormData(form)
      }).then(async (res) => {
        const rawText = await res.text().catch(() => '');
        let data = {};
        try {
          data = rawText ? JSON.parse(rawText) : {};
        } catch (e) {
          data = {};
        }
        await SptjmAlert.close();
        if (!res.ok || !data.success) {
          let msg = '';
          if (data && typeof data === 'object') {
            if (typeof data.message === 'string' && data.message.trim() !== '') {
              msg = data.message;
            } else if (data.errors && typeof data.errors === 'object') {
              try {
                const first = Object.values(data.errors).flat().find(v => typeof v === 'string' && v.trim() !== '');
                if (first) msg = first;
              } catch (e) {}
            }
          }
          if (!msg && rawText && rawText.trim() !== '' && !rawText.trim().startsWith('<')) {
            msg = rawText;
          }
          if (!msg) msg = 'Terjadi kesalahan saat menyimpan.';
          return SptjmAlert.error('Gagal', msg);
        }
        await SptjmAlert.success('Berhasil', data.message || 'Berhasil disimpan.', { showConfirmButton: true });
        table.ajax.reload();
      }).catch(async (err) => {
        console.error(err);
        await SptjmAlert.close();
        SptjmAlert.error('Gagal', (err && err.message) ? err.message : 'Terjadi kesalahan saat menyimpan.');
      });
    });
  });
</script>
@endsection
