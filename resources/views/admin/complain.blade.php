@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Complain (Admin)</h5>
  <hr>
  <div class="table-responsive text-nowrap">
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

<!-- Modal Detail (View Only) -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Complain</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6 mb-2"><strong>Pelapor:</strong> <span id="dPelapor"></span></div>
          <div class="col-md-6 mb-2"><strong>Kode PTS:</strong> <span id="dKodePts"></span></div>
          <div class="col-md-6 mb-2"><strong>NIDN:</strong> <span id="dNidn"></span></div>
          <div class="col-md-6 mb-2"><strong>NUPTK:</strong> <span id="dNuptk"></span></div>
        </div>
        <div class="mb-2"><strong>Judul:</strong> <span id="dJudul"></span></div>
        <div class="mb-3">
          <strong>Pesan:</strong>
          <textarea class="form-control" id="pesan_view" rows="6" readonly></textarea>
        </div>
        <div class="mb-3"><strong>Lampiran:</strong><div class="border rounded p-2" id="dLampiran"></div></div>
        <div class="row">
          <div class="col-md-6 mb-2"><strong>Status:</strong> <span id="dStatus"></span></div>
          <div class="col-md-6 mb-2"><strong>Tanggal Handle:</strong> <span id="dHandledAt"></span></div>
        </div>
        <div class="mb-3">
          <strong>Balasan:</strong>
          <div class="border rounded p-2" id="dBalasan">-</div>
        </div>
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
      scrollX: true,
      scrollCollapse: true,
      serverSide: true,
      responsive: true,
      ajax: {
        url: "{{ route('admin.complain.index') }}",
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
      // Default sort: newest first by Created At (column index 10)
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

    // reload table when filter changes
    document.getElementById('filterStatus')?.addEventListener('change', function () {
      $('#complainTable').DataTable().ajax.reload();
    });

    $('#complainTable').on('click', '.view-complain', function() {
      const id = $(this).data('id');
      SptjmAlert.loading('Mohon Tunggu', 'Mengambil detail...');
      fetch(`/admin/complain/${id}`, {
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
        document.getElementById('dPelapor').innerText = (d.pelapor_tipe || '-').toString().toUpperCase();
        document.getElementById('dKodePts').innerText = d.kode_pts || '-';
        document.getElementById('dNidn').innerText = d.nidn || '-';
        document.getElementById('dNuptk').innerText = d.nuptk || '-';
        document.getElementById('dJudul').innerText = d.judul || '-';
        if (window.CKEDITOR && CKEDITOR.instances.pesan_view) {
          CKEDITOR.instances.pesan_view.setData(d.pesan || '-');
        } else {
          document.getElementById('pesan_view').value = d.pesan || '-';
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

        document.getElementById('dStatus').innerText = (d.status || '-').toString().toUpperCase();
        document.getElementById('dHandledAt').innerText = d.handled_at || '-';
        document.getElementById('dBalasan').innerHTML = d.admin_balasan ? d.admin_balasan : '-';
        $('#modalDetail').modal('show');
      }).catch(async (err) => {
        console.error(err);
        await SptjmAlert.close();
        SptjmAlert.error('Gagal', 'Terjadi kesalahan saat mengambil detail.');
      });
    });
  });
</script>
@endsection
