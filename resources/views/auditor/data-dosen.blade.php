@extends('layouts/contentNavbarLayoutAuditor')

@section('title', 'SPTJM Online')

@section('content')
  <div class="card" style="width: 100%; padding: 10px;">
    <div class="d-flex justify-content-between align-items-center">
      <h5 class="card-header text-start p-2 mb-0">Audit Data Dosen</h5>
      <div class="text-muted small pe-2">Tahun {{ session('tahun') }}</div>
    </div>
    <hr>

    @if (session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif

    <div class="table-responsive text-nowrap">
      <table class="table table-sm table-hover" id="dosenTable">
        <thead style="background-color: #dbdee0;">
          <tr>
            <th>NIDN</th>
            <th>NUPTK</th>
            <th>Nama Dosen</th>
            <th>Kode PTS</th>
            <th>Nama PTS</th>
            <th>Status</th>
            <th>Eligible Span</th>
            <th>Aksi</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

  @push('scripts')
    <script>
      (function () {
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
          return;
        }

        $('#dosenTable').DataTable({
          processing: true,
          serverSide: true,
          paging: true,
          deferRender: true,
          pageLength: 25,
          lengthChange: true,
          lengthMenu: [[25, 50, 100, 500], [25, 50, 100, 500]],
          dom: "<'row align-items-center mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-start mt-2 mt-md-0'f>>" +
               "rt<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
          ajax: {
            url: '{{ route('auditor.data-dosen') }}'
          },
          columns: [
            { data: 'nidn', name: 'nidn', searchable: true, className: 'text-start' },
            { data: 'nuptk', name: 'nuptk', className: 'text-start' },
            { data: 'nama', name: 'nama' },
            { data: 'kode_pt', name: 'kode_pt' },
            { data: 'pts', name: 'pts' },
            { data: 'aktif', name: 'aktif', orderable: false, searchable: false },
            { data: 'eligible_span', name: 'eligible_span' },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
          ],
          order: [],
          responsive: true,
          pagingType: 'simple_numbers',
          language: {
            paginate: { first: 'Awal', last: 'Akhir', next: '→', previous: '←' },
            zeroRecords: 'Data tidak ditemukan',
            infoEmpty: 'Tidak ada data tersedia',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            lengthMenu: 'Show _MENU_ entries',
            search: 'Cari:'
          }
        });
      })();
    </script>
  @endpush
@endsection
