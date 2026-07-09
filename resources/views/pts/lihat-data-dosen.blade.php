@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Lihat Data Dosen</h5>
  <hr>

  <div class="table-responsive text-nowrap">
    <table class="table table-sm table-hover" id="dosenTable">
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


<script>
  //jquery
  $(document).ready(() => {
    const table = $('#dosenTable').DataTable({
      processing: true,
      serverSide: true,
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