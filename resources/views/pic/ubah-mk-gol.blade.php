@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content-wrapper">
  <div class="row">
    <div class="col-12">
      @php
        $identifier = $data_dosen->NIDN ?? $data_dosen->NUPTK ?? '';
      @endphp
      <form action="{{ url('pic/ubah-mk-gol/' . $identifier) }}" method="POST"
        enctype="multipart/form-data" id="ubahMkForm">
        @csrf
        @method('PUT')
        <div class="card mb-4">
          <div class="card-header">Informasi Perubahan</div>
          <div class="card-body">
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">No Dokumen</label>
              <div class="col-sm-4">
                <input type="text" class="form-control" name="no_dokumen_ubah" id="basic-default-name"
                  required="">
              </div>
              <label class="col-sm-2 col-form-label" for="basic-default-company">Status</label>
              <div class="col-sm-4">
                <select class="form-select" aria-label="Default select example" name="Aktif" readonly=""
                  required="">
                  <option value="1" {{ $data_dosen->Aktif == 1 ? 'selected' : '' }}>Aktif
                  </option>
                  <option value="0" {{ $data_dosen->Aktif == 0 ? 'selected' : '' }}>Tidak Aktif
                  </option>
                </select>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="formFile">Tanggal Dokumen</label>
              <div class="col-sm-4">
                <input class="form-control" type="date" name="tgl_dokumen_ubah" id="html5-date-input"
                  required="">
              </div>
              <label class="col-sm-2 col-form-label" for="basic-default-name">Alasan Perubahan</label>
              <div class="col-sm-4">
                <select class="form-select" id="exampleFormControlSelect1" name="alasan_perubahan"
                  aria-label="Default select example" required="">
                  <option selected="">-- Pilih Alasan --</option>
                  @if(!empty($isDraftAddFlow) && $isDraftAddFlow)
                    <option value="Penambahan Data Baru">Penambahan Data Baru</option>
                  @else
                    <option value="Perubahan Golongan dan Masa Kerja">Perubahan Golongan dan Masa Kerja</option>
                  @endif
                </select>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Dokumen</label>
              <div class="col-sm-4">
                <input class="form-control" type="file" name="dokumen" accept=".pdf, .doc, .docx"
                  required="">
              </div>
              <label class="col-sm-2 col-form-label" for="basic-default-name">Terhitung Mulai
                Tanggal</label>
              <div class="col-sm-4">
                <input class="form-control" type="date" name="tanggal_update_terakhir"
                  id="html5-date-input" required="">
              </div>
            </div>

            <div class="row mb-3 ">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Keterangan</label>
              <div class="col-sm-4">
                <input type="text" class="form-control" name="keterangan" id="basic-default-name"
                  required="">
              </div>
            </div>
          </div>
        </div>

        <hr class="my-3">

        <div class="card mb-4">
          <div class="card-header">Data Dosen</div>
          <div class="card-body">
            <input type="hidden" name="pk" value="">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">NIDN</label>
                <input type="text" class="form-control" name="nidn"
                  value="{{ $data_dosen->NIDN ?? 'Data tidak tersedia' }}" readonly
                  style="background-color: #eceef1;">
              </div>
              <div class="col-md-6">
                <label class="form-label">NUPTK</label>
                <input type="text" class="form-control" id="basic-default-nuptk" name="nuptk"
                  value="{{ $data_dosen->NUPTK ?? session('draft_add_dosen.' . ($data_dosen->NIDN ?? '') . '.nuptk') ?? 'Data tidak tersedia' }}" readonly
                  style="background-color: #eceef1;">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" name="nama"
                  value="{{ $data_dosen->Nama ?? 'Data tidak tersedia' }}" readonly
                  style="background-color: #eceef1;">
              </div>
              <div class="col-md-6">
                <label class="form-label">NIK</label>
                <input type="text" class="form-control" name="nik"
                  value="{{ $data_dosen->NIK ?? 'Data tidak tersedia' }}" readonly
                  style="background-color: #eceef1;">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">TTL</label>
                <input type="text" class="form-control" id="ttl_display"
                  value="{{ ($data_dosen->Tanggal_Lahir ?? 'Data tidak tersedia') }} - {{ ($data_dosen->TTL ?? 'Data tidak tersedia') }}"
                  readonly style="background-color: #eceef1;">
                <input type="hidden" name="tanggal_lahir" value="{{ $data_dosen->Tanggal_Lahir ?? '' }}">
                <input type="hidden" name="ttl" value="{{ $data_dosen->TTL ?? '' }}">
              </div>
              <div class="col-md-6">
                <label class="form-label">Usia</label>
                <input type="text" class="form-control" id="basic-default-name-Usia" name="usia"
                  value="{{ $data_dosen->Usia ?? 'Data tidak tersedia' }}" readonly
                  style="background-color: #eceef1;">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Kode PTS / PTS</label>
                <input type="text" class="form-control" id="kode_pts_display"
                  value="{{ ($data_dosen->Kode_PT ?? 'Data tidak tersedia') }} - {{ ($data_dosen->PTS ?? 'Data tidak tersedia') }}"
                  readonly style="background-color: #eceef1;">
                <input type="hidden" name="kode_pt" value="{{ $data_dosen->Kode_PT ?? '' }}">
                <input type="hidden" name="pts" value="{{ $data_dosen->PTS ?? '' }}">
              </div>
              <div class="col-md-6">
                <label class="form-label">Jenis</label>
                <input type="text" class="form-control" name="jenis" value="{{ $data_dosen->Jenis ?? '' }}" readonly style="background-color: #eceef1;">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">TMT JAD Pertama</label>
                @php
                  $__formatForInput = function($v) {
                    if (empty($v)) return '';
                    try {
                      if (strpos($v, '/') !== false) {
                        $d = \Carbon\Carbon::createFromFormat('d/m/Y', $v);
                      } else {
                        $d = \Carbon\Carbon::parse($v);
                      }
                      return $d->format('Y-m-d');
                    } catch (\Exception $e) {
                      return '';
                    }
                  };
                @endphp
                <input type="date" class="form-control" id="tmt_jad_pertama" name="tmt_jad_pertama"
                  value="{{ old('tmt_jad_pertama', call_user_func($__formatForInput, $data_dosen->TMT_JAD_Pertama ?? null)) }}">
              </div>
              <div class="col-md-6">
                <label class="form-label">TMT JAD Akhir</label>
                <input type="date" class="form-control" id="tmt_jad_akhir" name="tmt_jad_akhir"
                  value="{{ old('tmt_jad_akhir', call_user_func($__formatForInput, $data_dosen->TMT_JAD_Akhir ?? null)) }}">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">TMT Inpassing Akhir</label>
                <input type="date" class="form-control" id="tmt_inpassing_akhir" name="tmt_inpassing_akhir"
                  value="{{ old('tmt_inpassing_akhir', call_user_func($__formatForInput, $data_dosen->TMT_Inpassing_Akhir ?? null)) }}">
              </div>
              <div class="col-md-6">
                <label class="form-label">Jabatan</label>
                <select class="form-select" aria-label="Default select example" name="jabatan">
                  <option value="Asisten Ahli" {{ trim($data_dosen->jabatan) == 'Asisten Ahli' ? 'selected' : '' }}>Asisten Ahli</option>
                  <option value="Guru Besar" {{ trim($data_dosen->jabatan) == 'Guru Besar' ? 'selected' : '' }}>Guru Besar</option>
                  <option value="Guru Besar 1050" {{ trim($data_dosen->jabatan) == 'Guru Besar 1050' ? 'selected' : '' }}>Guru Besar 1050</option>
                  <option value="Lektor" {{ trim($data_dosen->jabatan) == 'Lektor' ? 'selected' : '' }}>Lektor</option>
                  <option value="Lektor Kepala" {{ trim($data_dosen->jabatan) == 'Lektor Kepala' ? 'selected' : '' }}>Lektor Kepala</option>
                  <option value="Tenaga Pengajar" {{ trim($data_dosen->jabatan) == 'Tenaga Pengajar' ? 'selected' : '' }}>Tenaga Pengajar</option>
                </select>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Golongan</label>
                <select class="form-select" name="gol" id="golongan" required>
                  <option value="III/a" {{ isset($data_dosen) && $data_dosen->gol == 'III/a' ? 'selected' : '' }}>III/a</option>
                  <option value="III/b" {{ isset($data_dosen) && $data_dosen->gol == 'III/b' ? 'selected' : '' }}>III/b</option>
                  <option value="III/c" {{ isset($data_dosen) && $data_dosen->gol == 'III/c' ? 'selected' : '' }}>III/c</option>
                  <option value="III/d" {{ isset($data_dosen) && $data_dosen->gol == 'III/d' ? 'selected' : '' }}>III/d</option>
                  <option value="IV/a" {{ isset($data_dosen) && $data_dosen->gol == 'IV/a' ? 'selected' : '' }}>IV/a</option>
                  <option value="IV/b" {{ isset($data_dosen) && $data_dosen->gol == 'IV/b' ? 'selected' : '' }}>IV/b</option>
                  <option value="IV/c" {{ isset($data_dosen) && $data_dosen->gol == 'IV/c' ? 'selected' : '' }}>IV/c</option>
                  <option value="IV/d" {{ isset($data_dosen) && $data_dosen->gol == 'IV/d' ? 'selected' : '' }}>IV/d</option>
                  <option value="IV/e" {{ isset($data_dosen) && $data_dosen->gol == 'IV/e' ? 'selected' : '' }}>IV/e</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Masa Kerja (tahun)</label>
                <input type="number" class="form-control" id="masa_kerja" name="tahun" min="0" placeholder="Masukkan masa kerja" value="{{ isset($data_dosen) ? $data_dosen->masa_kerja : '' }}">
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Pemegang Wilayah</label>
                <input type="text" class="form-control" name="pemegang_wilayah" value="{{ $data_dosen->Pemegang_Wilayah }}" readonly style="background-color: #eceef1;">
              </div>
              <div class="col-md-6">
                <label class="form-label">ELigible Span</label>
                <input type="text" class="form-control" name="eligible_span" value="{{ $data_dosen->Eligible_span ?? '' }}" readonly style="background-color: #eceef1;">
              </div>
            </div>
            <!-- <div class="row mb-3"> -->
              {{--
              <div class="col-md-6">
                <label class="form-label">Rekening</label>
                <input type="text" class="form-control" name="no_rekening" value="{{ $data_dosen->No_Rekening }}" readonly style="background-color: #eceef1;">
              </div>
              <div class="col-md-6">
                <label class="form-label">Bank</label>
                <input type="text" class="form-control" name="bank" value="{{ $data_dosen->Bank }}" readonly style="background-color: #eceef1;">
              </div>
              --}}
            <!-- </div> -->

            <!-- <div class="row mb-3"> -->
              {{--
              <div class="col-md-6">
                <label class="form-label">Nama Rekening</label>
                <input type="text" class="form-control" name="nama_rekening" value="{{ $data_dosen->Nama_Rekening }}" readonly style="background-color: #eceef1;">
              </div>
              <div class="col-md-6">
                <label class="form-label">Nama Supplier</label>
                <input type="text" class="form-control" name="nama_penerima" value="{{ $data_dosen->Nama_Penerima }}" readonly style="background-color: #eceef1;">
              </div>
              --}}
            <!-- </div> -->
            <div class="row mt-3">
              <div class="col-12 d-flex justify-content-center">
                <button type="submit" class="btn btn-success mx-2">Simpan</button>
                @php
                  $identifier = $data_dosen->NIDN ?? $data_dosen->NUPTK ?? '';
                  $backUrl = !empty($identifier) ? route('dosen.showData', ['nidn' => $identifier]) : route('pic.lihat-data-dosen');
                @endphp
                <button type="button" id="btnCancel" class="btn btn-secondary mx-2">Batal</button>
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
    const golonganInput = document.getElementById('golongan');
    const masaKerjaInput = document.getElementById('masa_kerja');
    const gajiInput = document.getElementById('gaji');

    function fetchGaji() {
      const golongan = golonganInput.value;
      const masaKerja = masaKerjaInput.value;

      if (golongan && masaKerja) {
        fetch("{{ route('pic.ubah-mk-gol.get-biaya') }}", {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
              golongan: golongan,
              masa_kerja: masaKerja
            })
          })
          .then(response => response.json())
          .then(data => {
            gajiInput.value = data.gaji ?? "Data tidak ditemukan";
          })
          .catch(error => {
            console.error('Error:', error);
            gajiInput.value = "Error mengambil data";
          });
      }
    }
    golonganInput.addEventListener('change', fetchGaji);
    masaKerjaInput.addEventListener('input', fetchGaji);
    
    const jabatanSelect = document.querySelector('select[name="jabatan"]');
    if (jabatanSelect) {
      jabatanSelect.addEventListener('change', function() {
        const val = this.value.trim();
        let newGol = null;
        if (val === 'Guru Besar 1050') newGol = 'IV/e';
        else if (val === 'Guru Besar') newGol = 'IV/d';
        
        if (newGol) {
          if (golonganInput) golonganInput.value = newGol;
          fetchGaji();
        }
      });
    }

    fetchGaji();

    //alert
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

    const ubahMkForm = document.getElementById('ubahMkForm')
    ubahMkForm.addEventListener('submit', function() {
      loadingAlert();
    });

    // Cancel button: try history.back() when same-origin referrer exists,
    // otherwise navigate to server-provided fallback route.
    const btnCancel = document.getElementById('btnCancel');
    const fallbackBackUrl = @json($backUrl);
    if (btnCancel) {
      btnCancel.addEventListener('click', function(evt) {
        evt.preventDefault();
        try {
          const ref = document.referrer || '';
          const sameOrigin = ref ? (new URL(ref)).origin === window.location.origin : false;
          if (sameOrigin && ref !== '') {
            history.back();
          } else {
            window.location.href = fallbackBackUrl;
          }
        } catch (err) {
          window.location.href = fallbackBackUrl;
        }
      });
    }

    @if(session('success'))
    Swal.fire({
      title: 'Berhasil!',
      text: "{{ session('success') }}",
      icon: 'success',
      customClass: {
        confirmButton: 'btn btn-primary'
      },

      buttonsStyling: false
    });
    @endif
  });
</script>

@endsection