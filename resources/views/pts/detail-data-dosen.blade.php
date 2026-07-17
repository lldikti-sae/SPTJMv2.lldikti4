@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')

<div class="md2-page-header">
    <div class="page-titles">
        <h3>Detail Data Dosen</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Dosen</a></li>
                <li class="breadcrumb-item active">Detail Data Dosen</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md2-card">
    <div class="card-body px-4 pb-4 pt-4">
                {{-- Jabatan/Golongan/Masa Kerja/Biaya disediakan oleh controller sebagai aliases: jabatan, gol, masa_kerja, gaji --}}
            <form>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">NIDN</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->NIDN }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">NUPTK</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->NUPTK ?? '-' }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <!--
        <div class="row mb-3">
          <label class="col-sm-2 col-form-label" for="basic-default-name">NIP</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" value="{{ $dosen->NIK }}" readonly
              style="background-color: #eceef1;">
          </div>
        </div> -->
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Nama</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->Nama }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Tempat Lahir</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->TTL }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Tanggal Lahir</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->Tanggal_Lahir }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Usia</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->Usia }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">PTS</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->PTS }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Jenis</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->Jenis }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Jabatan</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->jabatan ?? $dosen->Jabatan12 ?? '-' }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Golongan</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->gol ?? $dosen->Gol12 ?? '-' }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Masa Kerja</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->masa_kerja ?? $dosen->Tahun12 ?? '-' }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Biaya</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->gaji ?? $dosen->Gaji12 ?? '-' }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Rekening</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->No_Rekening }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Bank</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->Bank }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Rekening</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->Nama_Rekening }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">NPWP</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="{{ $dosen->NPWP }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-2 col-form-label" for="basic-default-name">Status</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control"
                            value="{{ $dosen->Aktif == 1 ? 'Aktif' : 'Tidak Aktif' }}" readonly
                            style="background-color: #eceef1;">
                    </div>


                    @php
                        $referer = request()->headers->get('referer') ?? '';
                        if (strpos($referer, '/pts/cek-data-dosen') !== false) {
                            $backUrl = url('/pts/cek-data-dosen');
                        } elseif (strpos($referer, '/pts/lihat-data-dosen') !== false) {
                            $backUrl = route('pts.lihat-data-dosen');
                        } else {
                            $backUrl = url()->previous();
                        }
                    @endphp

                    <div class="demo-inline-spacing">
                        <div class="d-flex justify-content-center">
                            <a href="{{ $backUrl }}" class="btn btn-secondary mx-2">Kembali</a>
                        </div>
                    </div>



                </div>
            </form>

        </div>
    </div>
</div>



@endsection
