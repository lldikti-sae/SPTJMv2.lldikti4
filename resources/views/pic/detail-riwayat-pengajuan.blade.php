@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h3>Riwayat Pengajuan</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Monitoring</a></li>
                <li class="breadcrumb-item"><a href="{{ url('pic/riwayat-pengajuan') }}">Riwayat Pengajuan</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md-card">
    <div class="md-card-inner">
        {{-- Informasi Usulan --}}
        <div class="mb-4">
            <div class="row mb-2">
                <div class="col-md-2 fw-semibold text-muted" style="font-size: 0.85rem;">Nomor Usulan</div>
                <div class="col-md-10">: {{ $pengajuan->id_usulan ?? '-' }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-2 fw-semibold text-muted" style="font-size: 0.85rem;">Tanggal Usulan</div>
                <div class="col-md-10">: {{ $pengajuan->tanggal_usulan ?? '-' }}</div>
            </div>
        </div>

        {{-- Tabel Dosen PNS --}}
        <h6 class="mb-2 fw-bold">Daftar Nama Dosen (PNS) :</h6>
        <div class="table-responsive mb-4">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIDN/NUPTK</th>
                        <th>Nama Dosen</th>
                        <th>SP2D</th>
                        <th>Tanggal Usulan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($usulanPns ?? collect()) as $index => $dosen)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $dosen->identifier ?? ($dosen->nidn ?? $dosen->nuptk ?? '-') }}</td>
                        <td>{{ $dosen->nama }}</td>
                        <td>{{ $dosen->no_sp2d ?? "-" }}</td>
                        <td>{{ $pengajuan->tanggal_usulan ?? '-' }}</td>
                        <td>{{ $pengajuan->status ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada dosen PNS yang diusulkan untuk bulan ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tabel Dosen NON PNS --}}
        <h6 class="mb-2 fw-bold">Daftar Nama Dosen (NON PNS) :</h6>
        <div class="table-responsive mb-4">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIDN/NUPTK</th>
                        <th>Nama Dosen</th>
                        <th>SP2D</th>
                        <th>Tanggal Usulan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($usulanNonPns ?? collect()) as $index => $dosen)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $dosen->identifier ?? ($dosen->nidn ?? $dosen->nuptk ?? '-') }}</td>
                        <td>{{ $dosen->nama }}</td>
                        <td>{{ $dosen->no_sp2d ?? "-" }}</td>
                        <td>{{ $pengajuan->tanggal_usulan ?? '-' }}</td>
                        <td>{{ $pengajuan->status ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada dosen NON PNS yang diusulkan untuk bulan ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-2">
            <a href="{{ url('pic/riwayat-pengajuan') }}" class="btn btn-secondary rounded-pill px-4">Kembali</a>
        </div>
    </div>
</div>
@endsection
