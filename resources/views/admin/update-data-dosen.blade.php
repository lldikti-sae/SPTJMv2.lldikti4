@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content-wrapper">
  <div class="row">
    <div class="col-12">
      @php
        $identifier = !empty($data_dosen->NIDN)
          ? $data_dosen->NIDN
          : (!empty($data_dosen->NUPTK) ? $data_dosen->NUPTK : '');
      @endphp
      <form action="{{ url('admin/update-data-dosen/' . $identifier) }}" method="POST"
        enctype="multipart/form-data" id="formUpdateDasdos">
        @csrf
        @method('PUT')

        <div class="card mb-4">
          <div class="card-header">Informasi Perubahan</div>
          <div class="card-body">
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">No Dokumen</label>
              <div class="col-sm-4">
                <input type="text" class="form-control" name="no_dokumen_ubah" id="basic-default-name" required>
              </div>
              <label class="col-sm-2 col-form-label" for="basic-default-company">Status</label>
              <div class="col-sm-4">
                <select class="form-select" aria-label="Default select example" name="Aktif" readonly>
                  <option value="1" {{ $data_dosen->Aktif == 1 ? 'selected' : '' }}>Aktif</option>
                  <option value="0" {{ $data_dosen->Aktif == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="formFile">Tanggal Dokumen</label>
              <div class="col-sm-4">
                <input class="form-control" type="date" name="tgl_dokumen_ubah" id="html5-date-input" required>
              </div>
              <label class="col-sm-2 col-form-label" for="basic-default-name">Alasan Perubahan</label>
              <div class="col-sm-4">
                <select class="form-select" id="exampleFormControlSelect1" name="alasan_perubahan" aria-label="Default select example" required>
                  <option value="" selected>-- Pilih Alasan --</option>
                  <option value="Meninggal">Meninggal</option>
                  <option value="Mutasi Keluar LLDIKTI 4">Mutasi Keluar LLDIKTI 4</option>
                  <option value="Cuti">Cuti</option>
                  <option value="Pensiun">Pensiun</option>
                  <option value="Tugas Belajar">Tugas Belajar</option>
                  <option value="Pengaktifan Kembali">Pengaktifan Kembali</option>
                  <option value="Dihentikan Pembayaran">Dihentikan Pembayaran</option>
                  <option value="Mutasi/Resign">Mutasi/Resign</option>
                </select>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Dokumen</label>
              <div class="col-sm-4">
                <input class="form-control" type="file" name="dokumen" accept=".pdf, .doc, .docx" required>
              </div>
              <label class="col-sm-2 col-form-label" for="basic-default-name">Terhitung Mulai Tanggal</label>
              <div class="col-sm-4">
                <input class="form-control" type="date" name="tanggal_update_terakhir" id="html5-date-input" required>
              </div>
            </div>

            <div class="row mb-3 ">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Keterangan</label>
              <div class="col-sm-4">
                <input type="text" class="form-control" name="keterangan" id="basic-default-name" required>
              </div>
            <hr class="my-4">
            <h5 class="fw-bold mb-4" style="color: #0f2b5c;">Data Dosen</h5>
            <input type="hidden" name="pk" value="">
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">NIDN</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name" name="nidn"
                  value="{{ $data_dosen->NIDN ?? 'Data tidak tersedia' }}" readonly
                  style="background-color: #eceef1;">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-nuptk">NUPTK</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-nuptk" name="nuptk"
                  value="{{ $data_dosen->NUPTK ?? '' }}"
                  placeholder="Kosong jika tidak tersedia" readonly style="background-color: #eceef1;">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">NIK</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name" name="nik"
                  value="{{ $data_dosen->NIK ?? 'Data tidak tersedia' }}" readonly
                  style="background-color: #eceef1;">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Nama</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name" name="nama"
                  value="{{ $data_dosen->Nama ?? 'Data tidak tersedia' }}">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Tempat Lahir</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name" name="ttl"
                  value="{{ $data_dosen->TTL ?? 'Data tidak tersedia' }}" readonly
                  style="background-color: #eceef1;">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Tanggal Lahir</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name-TTL" name="tanggal_lahir"
                  value="{{ $data_dosen->Tanggal_Lahir ?? 'Data tidak tersedia' }}" readonly
                  style="background-color: #eceef1;">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Usia</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name-Usia" name="usia"
                  value="{{ $data_dosen->Usia ?? 'Data tidak tersedia' }}" readonly
                  style="background-color: #eceef1;">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Kode PTS</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name" name="kode_pt"
                  value="{{ $data_dosen->Kode_PT ?? 'Data tidak tersedia' }}">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">PTS</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name" name="pts"
                  value="{{ $data_dosen->PTS ?? 'Data tidak tersedia' }}">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Pemegang
                Wilayah</label>
              <div class="col-sm-10">
                @php
                  $currentPemegang = isset($data_dosen->Pemegang_Wilayah) ? trim(strtolower($data_dosen->Pemegang_Wilayah)) : '';
                  $hasMatch = false;
                @endphp
                <select name="pemegang_wilayah" class="form-control">
                  @foreach(($pics ?? []) as $pic)
                    @php
                      $picVal = trim(strtolower($pic));
                      if ($picVal === $currentPemegang) { $hasMatch = true; }
                    @endphp
                    <option value="{{ $pic }}" {{ ($currentPemegang !== '' && $picVal === $currentPemegang) ? 'selected' : '' }}>{{ $pic }}</option>
                  @endforeach
                  @if(!$hasMatch)
                    <option value="" selected>-- Pilih Pemegang Wilayah (PIC) --</option>
                  @endif
                </select>
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Jenis</label>
              <div class="col-sm-10">
                <select class="form-select" aria-label="Default select example" name="jenis" disabled>
                  <option value="NON PNS" {{ $data_dosen->Jenis == 'NON PNS' ? 'selected' : '' }}>
                    NON PNS</option>
                  <option value="PNS" {{ $data_dosen->Jenis == 'PNS' ? 'selected' : '' }}>PNS
                  </option>
                </select>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="tmt_jad_pertama">TMT JAD Pertama</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="tmt_jad_pertama" name="tmt_jad_pertama"
                  value="{{ $data_dosen->TMT_JAD_Pertama ?? '' }}" readonly style="background-color: #eceef1;">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="tmt_jad_akhir">TMT JAD Akhir</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="tmt_jad_akhir" name="tmt_jad_akhir"
                  value="{{ $data_dosen->TMT_JAD_Akhir ?? '' }}" readonly style="background-color: #eceef1;">
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Jabatan</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name" name="jabatan"
                  value="{{ $data_dosen->jabatan }}" readonly style="background-color: #eceef1;">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Golongan</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="golongan" name="gol"
                  value="{{ $data_dosen->gol }}" readonly style="background-color: #eceef1;">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Masa Kerja</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="masa_kerja" name="tahun"
                  value="{{ $data_dosen->masa_kerja }}" readonly style="background-color: #eceef1;">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Biaya</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="biaya_per_bulan" name="gaji" .=""
                  value="{{ $data_dosen->gaji }}" readonly style="background-color: #eceef1;">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Rekening</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name" name="no_rekening"
                  value="{{ $data_dosen->No_Rekening }}">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Bank</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name" name="bank"
                  value="{{ $data_dosen->Bank }}">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Rekening</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name" name="nama_rekening"
                  value="{{ $data_dosen->Nama_Rekening }}">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Nama Supplier</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name" name="nama_penerima"
                  value="{{ $data_dosen->Nama_Penerima }}">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">NPWP</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="basic-default-name" name="npwp"
                  value="{{ $data_dosen->NPWP }}">
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-company">Eligible Span</label>
              <div class="col-sm-10">
                <select class="form-select" aria-label="Default select example" name="eligible_span"
                  readonly="">
                  <option value="YA" {{ $data_dosen->Eligible_span == 'YA' ? 'selected' : '' }}>
                    YA</option>
                  <option value="TIDAK" {{ $data_dosen->Eligible_span == 'TIDAK' ? 'selected' : '' }}>
                    TIDAK
                  </option>
                </select>
              </div>
            </div>
            <div class="row justify-content-end mt-3">
              <div class="col-sm-10">
                <div class="d-flex justify-content-center">
                  <button type="submit" id="btnSubmitUpdateDataDosen" class="btn btn-success mx-2">Simpan</button>
                  @php
                    $identifier = !empty($data_dosen->NIDN)
                      ? $data_dosen->NIDN
                      : (!empty($data_dosen->NUPTK) ? $data_dosen->NUPTK : '');
                    $backUrl = !empty($identifier) ? route('data-dosen.show', ['nidn' => $identifier]) : route('admin.data-dosen');
                  @endphp
                  <a href="{{ $backUrl }}" class="btn btn-secondary mx-2">Batal</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
    Swal.fire({
      title: 'Berhasil!',
      text: @json(session('success')),
      icon: 'success',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
    @endif

    @if($errors->any())
    const validationErrors = @json($errors->all());
    Swal.fire({
      title: 'Validasi gagal',
      icon: 'error',
      html: validationErrors.join('<br>'),
      customClass: { confirmButton: 'btn btn-danger' },
      buttonsStyling: false
    });
    @endif

    const loadingAlert = () => {
      return Swal.fire({
        title: 'Mohon tunggu...',
        html: `
                        <div class="d-flex justify-content-center align-items-center flex-column">
                            <div class='spinner-border spinner-border-lg text-success' role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Mohon tunggu <br>Sedang mengupdate data!</div>
                        </div>
                    `,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        backdrop: true
      });
    }

    const formUpdateDasdos = document.getElementById('formUpdateDasdos');
    const btnSubmit = document.getElementById('btnSubmitUpdateDataDosen');

    if (btnSubmit && formUpdateDasdos) {
      btnSubmit.addEventListener('click', () => {
        // If the form is valid (HTML5 required fields satisfied), show loading immediately.
        if (formUpdateDasdos.checkValidity()) {
          loadingAlert();
        } else {
          // Trigger native validation UI
          formUpdateDasdos.reportValidity();
        }
      });
    }

    if (formUpdateDasdos) {
      formUpdateDasdos.addEventListener('submit', () => {
        // Safety: still show loading on submit
        loadingAlert();
      });
    }
  });
</script>
@endsection