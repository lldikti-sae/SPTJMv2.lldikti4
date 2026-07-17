@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="md2-page-header">
    <div class="page-titles">
        <h3>Lihat Data Dosen</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Dosen</a></li>
                <li class="breadcrumb-item active">Lihat Data Dosen</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md2-card">
  <div class="card-body px-4 pb-4 pt-4">
    <div class="table-responsive text-nowrap border rounded mb-4">
      <table class="table table-hover md2-table m-0" id="dosenTable">
        <thead style="background-color: #dbdee0;">
        <tr>
          <th>No</th>
          <th>NIDN</th>
          <th>NUPTK</th>
          <th>Nama Dosen</th>
          <th>Golongan</th>
          <th>Masa Kerja</th>
          <th>Jabatan</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
    </div>
  </div>
</div>


<script>
  //jquery
  $(document).ready(() => {
    const table = $('#dosenTable').DataTable({
      processing: true,
      serverSide: true,
      dom: "<'md-toolbar'<'entries-wrap'l><'search-wrap'f>><'table-responsive text-nowrap't><'row dt-bottom-row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
      ajax: {
        url: '{{ route("pts.lihat-data-dosen") }}'
      },
        columns: [{
          data: 'DT_RowIndex',
          name: 'DT_RowIndex',
          searchable: false,
          orderable: false
        }, {
          data: 'nidn',
          name: 'nidn',
          searchable: true
        },
        {
          data: 'nuptk',
          name: 'nuptk'
        },
        {
          data: 'nama',
          name: 'nama'
        },
        {
          data: 'gol',
          name: 'gol'
        },
        {
          data: 'masa_kerja',
          name: 'tahun'
        },
        {
          data: 'jabatan',
          name: 'jabatan'
        },
        {
          data: 'aksi',
          name: 'aksi',
          orderable: false,
          searchable: false
        }
      ],
      // Default order: status (aktif) desc so active rows appear first, then NIDN asc
      order: [
        [1, 'asc']
      ],
      responsive: true,
      language: {
        paginate: {
          first: "Awal",
          last: "Akhir",
          next: "→",
          previous: "←",
        },
        zeroRecords: "Data tidak ditemukan",
        infoEmpty: "Tidak ada data tersedia",
        searchPlaceholder: "Search NIDN...",
        search: "Search:"
      },
    });
  })
</script>
@endsection