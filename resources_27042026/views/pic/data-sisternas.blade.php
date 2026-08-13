@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="row">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Data Sisternas</a></li>
      <li class="breadcrumb-item active" aria-current="page">Lihat Data</li>
    </ol>
  </nav>
  <div class="col-12">
    <!-- coba -->
    <div class="row">
      <div class="col-12">
        <div class="card mb-4">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Data Sisternas</h5>
          </div>
          <div class="card-body">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Pelaporan</th>
                  <th>Untuk Pembayaran</th>
                  <th>Data Sisternas</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td>Genap Tahun Lalu [Januari - Februari]</td>
                  <td>Januari - Februari Berjalan</td>
                  <td>
                    <a href="{{ route('pic.data-sisternas-export', ['sisternas' => 'o_sister_genap_tl']) }}"
                      class="btn btn-sm btn-primary">Lihat</a>
                  </td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>Ganjil Tahun Lalu [September - Desember]</td>
                  <td>Maret - Agustus Berjalan</td>
                  <td>
                    <a href="{{ route('pic.data-sisternas-export',['sisternas' => 'p_sister_ganjil']) }}"
                      class="btn btn-sm btn-primary">Lihat</a>
                  </td>
                </tr>
                <tr>
                  <td>3</td>
                  <td>Genap Berjalan [Maret - Agustus]</td>
                  <td>September - Desember Berjalan</td>
                  <td>
                    <a href="{{ route('pic.data-sisternas-export',['sisternas' => 'n_sister_genap_bj']) }}"
                      class="btn btn-sm btn-primary">Lihat</a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection