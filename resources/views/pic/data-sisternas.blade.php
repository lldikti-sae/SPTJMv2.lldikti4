@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h3>Data Sisternas</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Sisternas</a></li>
                <li class="breadcrumb-item active">Lihat Data</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md-card">
    <div class="md-card-inner">
        <div class="table-responsive text-nowrap">
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
                                class="sptjm-icon-btn sptjm-btn-view" title="Lihat Data">
                                <i class="bx bx-file"></i>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Ganjil Tahun Lalu [September - Desember]</td>
                        <td>Maret - Agustus Berjalan</td>
                        <td>
                            <a href="{{ route('pic.data-sisternas-export',['sisternas' => 'p_sister_ganjil_tl']) }}"
                                class="sptjm-icon-btn sptjm-btn-view" title="Lihat Data">
                                <i class="bx bx-file"></i>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Genap Berjalan [Maret - Agustus]</td>
                        <td>September - Desember Berjalan</td>
                        <td>
                            <a href="{{ route('pic.data-sisternas-export',['sisternas' => 'n_sister_genap_bj']) }}"
                                class="sptjm-icon-btn sptjm-btn-view" title="Lihat Data">
                                <i class="bx bx-file"></i>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection