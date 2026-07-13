@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content-wrapper">
  <div class="row">
    <div class="col-12">
      @if (session('error'))
        <div class="alert alert-danger" role="alert">
          {{ session('error') }}
        </div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger" role="alert">
          <div class="fw-bold mb-1">Gagal menyimpan:</div>
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @php
        $identifier = $data_dosen->NIDN ?? $data_dosen->NUPTK ?? '';
      @endphp
      <form action="{{ url('admin/ubah-mk-gol/' . $identifier) }}" method="POST"
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
                <input type="text" class="form-control" value="{{ ($data_dosen->Aktif ?? 0) == 1 ? 'Aktif' : 'Tidak Aktif' }}" readonly style="background-color: #eceef1;">
                <input type="hidden" name="Aktif" value="{{ $data_dosen->Aktif ?? 0 }}">
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
                  <option value="" disabled {{ old('alasan_perubahan') ? '' : 'selected' }}>-- Pilih Alasan --</option>
                  @if(!empty($isDraftAddFlow) && $isDraftAddFlow)
                    <option value="Penambahan Data Baru" {{ old('alasan_perubahan') === 'Penambahan Data Baru' ? 'selected' : (!old('alasan_perubahan') ? 'selected' : '') }}>Penambahan Data Baru</option>
                  @else
                    <option value="Perubahan Golongan dan Masa Kerja" {{ old('alasan_perubahan') === 'Perubahan Golongan dan Masa Kerja' ? 'selected' : (!old('alasan_perubahan') ? 'selected' : '') }}>Perubahan Golongan dan Masa Kerja</option>
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

              @php
                $rawInpassing = $data_dosen->Inpassing ?? '';
                $lowerInpassing = strtolower(trim((string) $rawInpassing));
                if (in_array($lowerInpassing, ['1','ya','yes','true','berdasarkan inpassing','berdasarkan'])) {
                  $inpassingDefault = 'Berdasarkan Inpassing';
                } elseif (in_array($lowerInpassing, ['0','tidak','no','false','Sesuai TMT Awal dan Akhir','tanpa'])) {
                  $inpassingDefault = 'Sesuai TMT Awal dan Akhir';
                } elseif ($lowerInpassing === 'berdasarkan inpassing') {
                  $inpassingDefault = 'Berdasarkan Inpassing';
                } elseif ($lowerInpassing === 'Sesuai TMT Awal dan Akhir') {
                  $inpassingDefault = 'Sesuai TMT Awal dan Akhir';
                } else {
                  // fallback: use raw DB value if it matches exactly one of the options, else keep as empty
                  $inpassingDefault = $rawInpassing;
                }
              @endphp

              @php
                $inpassingSelected = old('inpassing', $inpassingDefault ?? '');
              @endphp

              <div class="col-md-6">
                <label class="form-label">Inpassing</label>
                <select class="form-select" id="inpassing_select" name="inpassing" aria-label="Default select example">
                  <option value="Berdasarkan Inpassing" {{ $inpassingSelected == 'Berdasarkan Inpassing' ? 'selected' : '' }}>Berdasarkan Inpassing</option>
                  <option value="Sesuai TMT Awal dan Akhir" {{ $inpassingSelected == 'Sesuai TMT Awal dan Akhir' ? 'selected' : '' }}>Sesuai TMT Awal dan Akhir</option>
                </select>
              </div>
            </div>

            <div class="row mb-3">
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
              <div class="col-md-6">
                <label class="form-label">Golongan</label>
                @php
                  $currentGol = isset($data_dosen) ? ($data_dosen->gol ?? '') : '';
                  $showReadonlyGol = ($inpassingDefault ?? '') == 'Sesuai TMT Awal dan Akhir';
                @endphp

                <div id="golongan_container">
                  <select class="form-select" id="golongan_select" {{ $showReadonlyGol ? '' : 'name=gol' }} {{ $showReadonlyGol ? 'style=display:none' : '' }} aria-label="Default select example">
                    <option value="III/a" {{ $currentGol == 'III/a' ? 'selected' : '' }}>III/a</option>
                    <option value="III/b" {{ $currentGol == 'III/b' ? 'selected' : '' }}>III/b</option>
                    <option value="III/c" {{ $currentGol == 'III/c' ? 'selected' : '' }}>III/c</option>
                    <option value="III/d" {{ $currentGol == 'III/d' ? 'selected' : '' }}>III/d</option>
                    <option value="IV/a" {{ $currentGol == 'IV/a' ? 'selected' : '' }}>IV/a</option>
                    <option value="IV/b" {{ $currentGol == 'IV/b' ? 'selected' : '' }}>IV/b</option>
                    <option value="IV/c" {{ $currentGol == 'IV/c' ? 'selected' : '' }}>IV/c</option>
                    <option value="IV/d" {{ $currentGol == 'IV/d' ? 'selected' : '' }}>IV/d</option>
                    <option value="IV/e" {{ $currentGol == 'IV/e' ? 'selected' : '' }}>IV/e</option>
                  </select>

                  <input type="text" class="form-control" id="golongan_input" value="{{ $currentGol }}" readonly style="background-color: #eceef1; {{ $showReadonlyGol ? '' : 'display:none' }}" {{ $showReadonlyGol ? 'name=gol' : '' }}>
                </div>
              </div>
              <!-- <div class="col-md-6">
                <label class="form-label">Masa Kerja (tahun)</label>
                <input type="number" class="form-control" id="masa_kerja" name="tahun" min="0"
                  placeholder="Masukkan masa kerja" value="{{ isset($data_dosen) ? $data_dosen->masa_kerja : '' }}">
              </div> -->
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Pemegang Wilayah</label>
                <input type="text" class="form-control" name="pemegang_wilayah"
                  value="{{ $data_dosen->Pemegang_Wilayah }}" readonly style="background-color: #eceef1;">
              </div>
              <div class="col-md-6">
                <label class="form-label">ELigible Span</label>
                <input type="text" class="form-control" name="eligible_span" value="{{ $data_dosen->Eligible_span ?? '' }}" readonly style="background-color: #eceef1;">
              </div>
              {{--
              <div class="col-md-6">
                <label class="form-label">Biaya</label>
                <input type="number" class="form-control" id="gaji" name="gaji" step="1000"
                  value="{{ isset($data_dosen) ? $data_dosen->Gaji12 : '' }}" readonly style="background-color: #eceef1;">
              </div>
              --}}
            </div>

            <!-- <div class="row mb-3"> -->
              {{--
              <div class="col-md-6">
                <label class="form-label">Rekening</label>
                <input type="text" class="form-control" name="no_rekening"
                  value="{{ $data_dosen->No_Rekening }}" readonly style="background-color: #eceef1;">
              </div>
              <div class="col-md-6">
                <label class="form-label">Bank</label>
                <input type="text" class="form-control" name="bank"
                  value="{{ $data_dosen->Bank }}" readonly style="background-color: #eceef1;">
              </div>
              --}}
            <!-- </div> -->

            <!-- <div class="row mb-3"> -->
              {{--
              <div class="col-md-6">
                <label class="form-label">Nama Rekening</label>
                <input type="text" class="form-control" name="nama_rekening"
                  value="{{ $data_dosen->Nama_Rekening }}" readonly style="background-color: #eceef1;">
              </div>
              <div class="col-md-6">
                <label class="form-label">Nama Supplier</label>
                <input type="text" class="form-control" name="nama_penerima"
                  value="{{ $data_dosen->Nama_Penerima }}" readonly style="background-color: #eceef1;">
              </div>
              --}}
            <!-- </div> -->

            <!-- <div class="row mb-3"> -->
              {{--
              <div class="col-md-6">
                <label class="form-label">NPWP</label>
                <input type="text" class="form-control" name="npwp"
                  value="{{ $data_dosen->NPWP }}" readonly style="background-color: #eceef1;">
              </div>
              --}}
            <!-- </div> -->
            <div class="row mt-3">
              <div class="col-12 d-flex justify-content-center">
                <button type="submit" class="btn btn-success mx-2">Simpan</button>
                @php
                  $identifier = $data_dosen->NIDN ?? $data_dosen->NUPTK ?? '';
                  $backUrl = !empty($identifier) ? route('data-dosen.show', ['nidn' => $identifier]) : route('admin.data-dosen');
                @endphp
                <a href="{{ $backUrl }}" class="btn btn-secondary mx-2">Batal</a>
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
    const golonganSelect = document.getElementById('golongan_select');
    const golonganInput = document.getElementById('golongan_input');
    const masaKerjaInput = document.getElementById('masa_kerja');
    const gajiInput = document.getElementById('gaji');
    const inpassingSelect = document.getElementById('inpassing_select');

    function getGolonganValue() {
      if (golonganSelect && golonganSelect.style.display === 'none') {
        return golonganInput ? golonganInput.value : '';
      }
      return golonganSelect ? golonganSelect.value : (golonganInput ? golonganInput.value : '');
    }

    function setGolonganMode(isReadonly) {
      if (isReadonly) {
        if (golonganSelect) {
          golonganSelect.style.display = 'none';
          golonganSelect.removeAttribute('name');
        }
        if (golonganInput) {
          golonganInput.style.display = '';
          golonganInput.setAttribute('name', 'gol');
        }
      } else {
        if (golonganSelect) {
          golonganSelect.style.display = '';
          golonganSelect.setAttribute('name', 'gol');
        }
        if (golonganInput) {
          golonganInput.style.display = 'none';
          golonganInput.removeAttribute('name');
        }
      }
    }

    function fetchGaji() {
      const golongan = getGolonganValue();
      const masaKerja = masaKerjaInput ? masaKerjaInput.value : '';

      if (golongan && masaKerja) {
        fetch("{{ route('admin.ubah-mk-gol.get-biaya') }}", {
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
            if (gajiInput) gajiInput.value = data.gaji ?? "Data tidak ditemukan";
          })
          .catch(error => {
            console.error('Error:', error);
            if (gajiInput) gajiInput.value = "Error mengambil data";
          });
      }
    }

    // init mode based on inpassing value
    if (inpassingSelect) {
      const initialReadonly = inpassingSelect.value === 'Sesuai TMT Awal dan Akhir';
      setGolonganMode(initialReadonly);
      inpassingSelect.addEventListener('change', function() {
        const isReadonly = this.value === 'Sesuai TMT Awal dan Akhir';
        setGolonganMode(isReadonly);
        fetchGaji();
      });
    }

    if (golonganSelect) golonganSelect.addEventListener('change', fetchGaji);
    if (masaKerjaInput) masaKerjaInput.addEventListener('input', fetchGaji);
    // initial fetch
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

    @if(session('success'))
    Swal.fire({
      title: 'Berhasil!',
      text: "{{ session('success') }}",
      icon: 'success',
      showConfirmButton: false,
      timer: 1200,
      timerProgressBar: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
    }).then(() => {
      @if(session('next_url'))
        window.location.href = "{{ session('next_url') }}";
      @endif
    });
    @endif
  });
</script>

@endsection