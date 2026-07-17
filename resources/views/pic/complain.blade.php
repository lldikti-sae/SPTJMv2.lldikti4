@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h3>Complain</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                <li class="breadcrumb-item active">Complain</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md-card mb-4">
  <div class="md-card-inner">
    <div class="d-flex gap-2 mb-3 align-items-center">
      <label class="mb-0 fw-bold text-uppercase" style="font-size: 0.75rem; color: #64748b; letter-spacing: 0.05em;">Status:</label>
      <select id="filterStatus" class="form-select form-select-sm" style="width:220px; border: 1.5px solid #cbd5e1; border-radius: 8px;">
        <option value="">Semua</option>
        <option value="open">OPEN</option>
        <option value="setuju">SETUJU</option>
        <option value="tolak">TOLAK</option>
      </select>
    </div>

    <div class="table-responsive text-nowrap">
      <table class="table table-hover" id="complainTable" style="width: 100%;">
        <thead>
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
        <tbody></tbody>
      </table>
    </div>
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
          <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan</button>
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
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
      dom: "<'md-toolbar'<'entries-wrap'l><'search-wrap'f>><'table-responsive text-nowrap't><'row dt-bottom-row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
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
        lengthMenu: "Show _MENU_ entries",
        zeroRecords: "Tidak ada data yang cocok",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
        paginate: { previous: "Sebelumnya", next: "Berikutnya" }
      }
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
