@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')

<div class="md2-page-header">
    <div class="page-titles">
        <h3>Data Grade</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Dosen</a></li>
                <li class="breadcrumb-item active">Data Grade</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md2-card">
  <div class="card-body px-4 pb-4 pt-4">
    <div class="table-responsive text-nowrap border rounded mb-4">
      <table class="table table-hover md2-table m-0" id="gradeTable">
        <thead style="background-color: #dbdee0;">
        <tr>
          <th>Kode</th>
          <th>Golongan</th>
          <th>Masa Kerja</th>
          <th>Nominal</th>
        </tr>
      </thead>
    </table>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
      return;
    }

    $('#gradeTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      ajax: {
        url: "{{ route('pts.data-grade') }}"
      },
      columns: [{
          data: 'kode',
          name: 'kode'
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
          data: 'nominal',
          name: 'nominal'
        }
      ],
      language: {
        paginate: {
          first: 'Awal',
          last: 'Akhir',
          next: '→',
          previous: '←',
        },
        zeroRecords: 'Data tidak ditemukan',
        infoEmpty: 'Tidak ada data tersedia',
        searchPlaceholder: 'Cari data...',
        search: 'Cari:'
      },
    });
  });
</script>

@endsection
