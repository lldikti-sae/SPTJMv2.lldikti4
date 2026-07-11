@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')



@section('content')
{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h1>Histori Data Dosen</h1>
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
        lengthChange: true, // Use native length
        searching: true, // Use native search
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
            next: "â†’",
            previous: "â†",
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


