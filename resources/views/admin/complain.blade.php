@extends('layouts/contentNavbarLayout')

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
.dataTables_info {
    font-size: 0.82rem;
    color: #64748b;
}
.dataTables_paginate {
    display: flex;
    align-items: center;
    gap: 4px;
}
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
.dataTables_paginate .paginate_button.disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
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
<div class="md2-page-header">
    <div class="page-titles">
        <h3>Complain (Admin)</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Pusat Bantuan</a></li>
                <li class="breadcrumb-item active">Complain</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md2-card mb-4">
    <div class="card-body px-4 pb-4 pt-0">
        <!-- Filter Status & Toolbar Wrapper -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3 pt-3">
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">Filter Status:</label>
                <select id="filterStatus" class="form-select form-select-sm" style="width:180px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.84rem; color: #374151; height: 32px;">
                    <option value="">Semua Status</option>
                    <option value="open">OPEN</option>
                    <option value="setuju">SETUJU</option>
                    <option value="tolak">TOLAK</option>
                </select>
            </div>
        </div>

        <div>
            <table class="table table-hover md2-table text-center" id="complainTable" style="width:100%">
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
                <tbody>
                </tbody>
            </table>
        </div>
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
        versionCheck: false,
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
        paginate: {
          first: "Awal",
          last: "Akhir",
          next: "→",
          previous: "←",
        },
        zeroRecords: "Data tidak ditemukan",
        infoEmpty: "Tidak ada data tersedia",
        info: "Menampilkan _START_–_END_ dari _TOTAL_ entri",
        search: "Filter Data:",
        searchPlaceholder: "Cari data...",
        lengthMenu: "Show _MENU_ entries"
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
