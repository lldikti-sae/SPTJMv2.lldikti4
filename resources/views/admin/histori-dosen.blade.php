@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

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

/* ── Buttons ── */
.btn-filter-md {
    background-color: #fff;
    border: 1px solid #e2e8f0;
    color: #4a5568;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 8px 18px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
    white-space: nowrap;
}
.btn-filter-md:hover { background-color: #f8fafc; color: #2d3748; border-color: #cbd5e1; }

.btn-export-md {
    background-color: #0b3d91; /* Dark blue matching Figma */
    border-color: #0b3d91;
    color: #fff;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 8px 18px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
    white-space: nowrap;
}
.btn-export-md:hover { background-color: #082f73; color: #fff; }

/* ── Card ── */
.md-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(44,62,80,0.07);
    overflow: hidden;
}
.md-card-inner { padding: 20px 24px 24px; }

/* ── Toolbar ── */
.md-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}
.dataTables_length {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.84rem;
    color: #4a5568;
}
.dataTables_length select {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 5px 10px;
    font-size: 0.84rem;
    color: #4a5568;
    background: #f8fafc;
    cursor: pointer;
    outline: none;
}
.dataTables_filter {
    display: flex;
    align-items: center;
    gap: 8px;
}
.dataTables_filter label {
    font-size: 0.84rem;
    color: #4a5568;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dataTables_filter input {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 6px 36px 6px 14px;
    font-size: 0.84rem;
    color: #2d3748;
    background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='%238592a3' viewBox='0 0 16 16'%3E%3Cpath d='M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398l3.85 3.85a1 1 0 0 0 1.415-1.415l-3.868-3.833zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z'/%3E%3C/svg%3E") no-repeat calc(100% - 10px) center;
    min-width: 240px;
    outline: none;
    transition: border-color 0.2s;
}
.dataTables_filter input:focus { border-color: #0b3d91; background-color: #fff; }



</style>
@endsection

@section('content')
{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h4>Histori Data Dosen</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Dosen</a></li>
                <li class="breadcrumb-item active">Histori Dosen</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
            <button type="button" class="btn-filter-md dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="border:none;">
                <i class="bx bx-filter-alt"></i> Filter Status
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item md-filter-status" href="#" data-status="">Semua Status</a></li>
                <li><a class="dropdown-item md-filter-status" href="#" data-status="AKTIF">Aktif</a></li>
                <li><a class="dropdown-item md-filter-status" href="#" data-status="TIDAK AKTIF">Tidak Aktif</a></li>
            </ul>
        </div>
        <button type="button" class="btn-export-md" id="btnExportCSV">
            <i class="bx bx-export"></i> Export Report
        </button>
    </div>
</div>

{{-- Main Card --}}
<div class="md-card">
    <div class="md-card-inner">
        {{-- Toolbar --}}
        <!-- DataTables will inject length menu and search filter here via DOM option -->

        {{-- Table --}}
        <div class="md-table-wrap table-responsive text-nowrap">
            <table class="table table-hover" id="dosenTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>NIDN</th>
                        <th>NUPTK</th>
                        <th>NAMA DOSEN</th>
                        <th>NAMA PTS</th>
                        <th>STATUS</th>
                        <th>PENGGUNA</th>
                        <th>TMT</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
  // DataTables server-side pagination + search via AJAX
  (function() {
    document.addEventListener('DOMContentLoaded', function() {
      const $ = window.jQuery;
      if (!$) {
        console.error('jQuery is not loaded. DataTables cannot initialize.');
        return;
      }
      if (!$.fn || !$.fn.DataTable) {
        console.error('DataTables is not loaded. Please ensure DataTables scripts are included in the layout.');
        return;
      }

      const table = $('#dosenTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '{{ route("admin.histori-dosen.data") }}',
          error: function(xhr) {
            console.error('Histori Dosen AJAX error:', xhr.status, xhr.responseText);
          }
        },
        pageLength: 15,
        lengthChange: true, // Use native length
        searching: true, // Use native search
        lengthMenu: [[15, 25, 50, 100], [15, 25, 50, 100]],
        dom: "<'md-toolbar'<'entries-wrap'l><'search-wrap'f>>" +
             "rt<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        order: [[6, 'desc']],
        columns: [
          { data: 'nidn', name: 'nidn', className: 'text-start' },
          { data: 'nuptk', name: 'nuptk', className: 'text-start', render: function(data) { return data ? data : '-'; } },
          { data: 'nama', name: 'nama' },
          { data: 'pts', name: 'pts' },
          { 
              data: 'aktif', 
              name: 'aktif', 
              orderable: false, 
              searchable: true,
              render: function(data, type, row) {
                  let strData = data ? data.toString() : '';
                  if (strData.includes('Tidak Aktif') || strData.includes('TIDAK AKTIF') || strData === '0') {
                      return '<span class="badge-tidak-aktif">TIDAK AKTIF</span>';
                  } else if (strData.includes('Aktif') || strData.includes('AKTIF') || strData === '1' || strData.includes('bg-label-primary')) {
                      return '<span class="badge-aktif">AKTIF</span>';
                  }
                  return data;
              }
          },
          { data: 'pengguna', name: 'pengguna' },
          { data: 'tgl_dokumen_ubah', name: 'tgl_dokumen_ubah' },
          { 
              data: 'aksi', 
              name: 'aksi', 
              orderable: false, 
              searchable: false
          },
        ],
        language: {
          paginate: {
            first: "Awal",
            last: "Akhir",
            next: "→",
            previous: "←",
          },
          zeroRecords: "Data tidak ditemukan",
          infoEmpty: "Tidak ada data tersedia",
          info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
          search: "Cari Data:",
          searchPlaceholder: "Cari histori...",
          lengthMenu: "Show _MENU_ entries"
        },
      });

      // Handle Status Filter
      $('.md-filter-status').on('click', function(e) {
          e.preventDefault();
          const statusLabel = $(this).data('status');
          let searchVal = '';
          
          if(statusLabel === '') {
              $('.btn-filter-md').html('<i class="bx bx-filter-alt"></i> Filter Status');
              searchVal = '';
          } else if(statusLabel === 'AKTIF') {
              $('.btn-filter-md').html('<i class="bx bx-filter-alt"></i> Status: Aktif');
              searchVal = '1';
          } else if(statusLabel === 'TIDAK AKTIF') {
              $('.btn-filter-md').html('<i class="bx bx-filter-alt"></i> Status: Tidak Aktif');
              searchVal = '0';
          }
          // The Status column is at index 4
          table.column(4).search(searchVal).draw();
      });

      // Handle Export CSV
      $('#btnExportCSV').on('click', function() {
          let csv = [];
          let headers = [];
          
          $('#dosenTable thead th').each(function() {
              const thText = $(this).text().trim();
              if (thText !== 'Aksi' && thText !== 'AKSI') {
                  headers.push('"' + thText + '"');
              }
          });
          csv.push(headers.join(','));
          
          const rows = $('#dosenTable tbody tr');
          if (rows.length === 1 && rows.find('.dataTables_empty').length > 0) {
              if (window.Swal) Swal.fire('Perhatian', 'Tidak ada data untuk diexport.', 'warning');
              else alert('Tidak ada data untuk diexport.');
              return;
          }

          rows.each(function() {
              let row = [];
              $(this).find('td').each(function(index) {
                  if (index < headers.length) {
                      row.push('"' + $(this).text().trim().replace(/"/g, '""') + '"');
                  }
              });
              if(row.length > 0) csv.push(row.join(','));
          });
          
          let csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
          let encodedUri = encodeURI(csvContent);
          let link = document.createElement("a");
          link.setAttribute("href", encodedUri);
          link.setAttribute("download", "Histori_Data_Dosen_Report.csv");
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
      });
    });
  })();
</script>
@endsection


