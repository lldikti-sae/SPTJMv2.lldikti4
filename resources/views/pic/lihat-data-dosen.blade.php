@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Data Dosen PTS</h5>
  <hr>
  <div>
    <table class="table table-sm table-hover table-bordered" id="dosenTable">
      <thead style="text-align: center; background-color: #dbdee0;">
        <tr>
          <th>No</th>
          <th>NIDN</th>
          <th>NUPTK</th>
          <th>Nama Dosen</th>
          <th>Golongan</th>
          <th>Masa Kerja</th>
          <th>Jabatan</th>
          <th>Kode PTS</th>
          <th>PTS</th>
          <th>BKD Genap TL<br>(P = Jan - Feb)</th>
          <th>BKD Ganjil TL<br>(P = Mar - Agu)</th>
          <th>BKD Genap BJ<br>(P = Sep - Des)</th>
          <th>Status</th>
          <th>Keterangan</th>
          <th>Aksi</th>
        </tr>
      </thead>

    </table>
  </div>
</div>

<script>
  //datatable
  $(document).ready(() => {
    const table = $('#dosenTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      searching: true,
      dom: "<'md-toolbar'<'entries-wrap'l><'search-wrap'f>><'table-responsive text-nowrap't><'row dt-bottom-row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
      ajax: {
        url: "{{ route('pic.lihat-data-dosen') }}"
      },
      columns: [{
          data: 'DT_RowIndex',
          name: '',
          orderable: false,
          searchable: false
        }, {
          data: 'NIDN',
          name: 'd.NIDN'
        }, {
          data: 'NUPTK',
          name: 'd.NUPTK'
        }, {
          data: 'Nama',
          name: 'd.Nama'
        },
        {
          data: 'gol',
          name: 'gol'
        },
        {
          data: 'masa_kerja',
          name: 'masa_kerja'
        },
        {
          data: 'jabatan',
          name: 'jabatan'
        },
        {
          data: 'Kode_PT',
          name: 'kode_pts',
          searchable: false
        },
        {
          data: 'PTS',
          name: 'pts',
          searchable: false
        },
        {
          data: 'bkd_genap_tl',
          name: 'bkd_genap_tl',
          searchable: false
        },
        {
          data: 'bkd_ganjil_tl',
          name: 'bkd_ganjil_tl',
          searchable: false
        },
        {
          data: 'bkd_genap_bj',
          name: 'bkd_genap_bj',
          searchable: false
        },
        {
          data: 'aktif',
          name: 'aktif',
          orderable: false,
          searchable: false
        },
        {
          data: 'Keterangan',
          name: 'keterangan',
          searchable: false
        },
        {
          data: 'aksi',
          name: 'aksi',
          orderable: false,
          searchable: false
        },
      ],
      order: [
        [1, 'asc']
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
      },
    })
  })

  // DataTables provides its own built-in search box when `searching: true` is enabled.
</script>

@endsection