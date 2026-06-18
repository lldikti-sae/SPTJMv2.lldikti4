@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')
@section('content')

@if(isset($pendingComplainCount) && $pendingComplainCount > 0)
<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
    <div class="d-flex align-items-center">
        <i class="bx bx-error-circle bx-md me-2"></i>
        <div>
            <strong>Perhatian!</strong> Terdapat <strong>{{ $pendingComplainCount }}</strong> pengajuan yang harus segera Anda periksa di menu <a href="{{ route('pic.complain.index') }}" class="alert-link">Complain</a>.
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Dosen PNS Aktif</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahDosenPNSAktif }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-success p-4">
                            <i class="bx bx-user-check bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Dosen PNS Tidak Aktif</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahDosenPNSTidakAktif }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-danger p-4">
                            <i class="bx bx-user-x bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Dosen PNS</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahDosenPNS }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary p-4">
                            <i class="bx bx-group bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Dosen Non-PNS Aktif</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahDosenNonPNSAktif }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-success p-4">
                            <i class="bx bx-user-check bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Dosen Non-PNS Tidak Aktif</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahDosenNonPNSTidakAktif }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-danger p-4">
                            <i class="bx bx-user-x bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Dosen Non-PNS</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahDosenNonPNS }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary p-4">
                            <i class="bx bx-group bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="card ">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Dosen Wilayah : {{ Auth::user()->email }}</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $jumlahDosen }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary p-4">
                            <i class="bx bx-group bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr class="my-4">

@endsection
