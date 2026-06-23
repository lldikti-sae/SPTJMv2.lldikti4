@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

    <div class="card mb-4">
        <h5 class="card-header">Lengkapi Data</h5>
        <div class="card-body">
            <input type="hidden" name="pk" value="{{ old('pk', $dosen->id ?? '') }}">

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">NIDN</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="nidn" readonly style="background-color: #eceef1;"
                        value="{{ old('nidn', $dosen->nidn ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">NIK</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="nik" readonly style="background-color: #eceef1;"
                        value="{{ old('nik', $dosen->nik ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Nama</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="nama" readonly style="background-color: #eceef1;"
                        value="{{ old('nama', $dosen->nama ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Tempat Lahir</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="ttl" readonly style="background-color: #eceef1;"
                        value="{{ old('ttl', $dosen->tempat_lahir ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Tanggal Lahir</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="tanggal_lahir" readonly
                        style="background-color: #eceef1;" value="{{ old('tanggal_lahir', $dosen->tanggal_lahir ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Usia</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="usia" readonly style="background-color: #eceef1;"
                        value="{{ old('usia', $dosen->usia ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Kode PTS</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="kode_pt" readonly style="background-color: #eceef1;" value="{{ old('kode_pt', $dosen->kode_pt ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">PTS</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="pts" readonly style="background-color: #eceef1;" value="{{ old('pts', $dosen->pts ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Jenis</label>
                <div class="col-sm-10">
                    <select class="form-select" name="jenis" style="pointer-events: none; background-color: #eceef1;" readonly>
                        <option value="NON PNS" {{ old('jenis', $dosen->jenis ?? '') == 'NON PNS' ? 'selected' : '' }}>NON PNS</option>
                        <option value="PNS" {{ old('jenis', $dosen->jenis ?? '') == 'PNS' ? 'selected' : '' }}>PNS</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Jabatan</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="jabatan" readonly style="background-color: #eceef1;" value="{{ old('jabatan', $dosen->jabatan ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Golongan</label>
                <div class="col-sm-10">
                    <select class="form-select" name="gol" id="golongan" required style="pointer-events: none; background-color: #eceef1;" readonly>
                        @foreach (['III/a', 'III/b', 'III/c', 'III/d', 'IV/a', 'IV/b', 'IV/c'] as $gol)
                            <option value="{{ $gol }}" {{ old('gol', $dosen->gol ?? '') == $gol ? 'selected' : '' }}>
                                {{ $gol }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Masa Kerja (tahun)</label>
                <div class="col-sm-10">
                    <input type="number" class="form-control" name="tahun" value="{{ old('tahun', $dosen->tahun ?? '') }}"
                        min="0" placeholder="Masukkan masa kerja" readonly style="background-color: #eceef1;">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Biaya</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="gaji" readonly
                        style="background-color: #eceef1;" value="{{ old('gaji', $dosen->gaji ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Rekening</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="no_rekening"
                        value="{{ old('no_rekening', $dosen->no_rekening ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Bank</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="bank" value="{{ old('bank', $dosen->bank ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Nama Rekening</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="nama_rekening"
                        value="{{ old('nama_rekening', $dosen->nama_rekening ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Nama Supplier</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="nama_penerima"
                        value="{{ old('nama_penerima', $dosen->nama_penerima ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">NPWP</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="npwp" value="{{ old('npwp', $dosen->npwp ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Pemegang Wilayah</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" name="pemegang_wilayah" readonly style="background-color: #eceef1;"
                        value="{{ old('pemegang_wilayah', $dosen->pemegang_wilayah ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label">Eligible Span</label>
                <div class="col-sm-10">
                    <select class="form-select" name="eligible_span" style="pointer-events: none; background-color: #eceef1;" readonly>
                        <option value="YA" {{ old('eligible_span', $dosen->eligible_span ?? '') == 'YA' ? 'selected' : '' }}>YA</option>
                        <option value="TIDAK" {{ old('eligible_span', $dosen->eligible_span ?? '') == 'TIDAK' ? 'selected' : '' }}>TIDAK</option>
                    </select>
                </div>
            </div>

            <div class="row justify-content-end mt-3">
                <div class="col-sm-10">
                    <div class="d-flex justify-content-center">
                        <button type="submit" class="btn btn-success mx-2">Simpan</button>
                        <a href="{{ route('admin.rekap-usulan-non-el') }}" class="btn btn-secondary mx-2">Batal</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
