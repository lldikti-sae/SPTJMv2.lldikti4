@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@php
  // fallback jika master status perubahan belum tersedia
  $fallbackStatusPerubahan = [
    'Meninggal',
    'Mutasi Keluar LLDIKTI 4',
    'Cuti',
    'Pensiun',
    'Tugas Belajar',
    'Pengaktifan Kembali',
    'Dihentikan Pembayaran',
    'Mutasi/Resign',
  ];
  $statusList = (isset($statusPerubahan) && $statusPerubahan->count()) ? $statusPerubahan->all() : $fallbackStatusPerubahan;

  // ambil kode usulan terakhir (bulan 12..1) sebagai default awal (digunakan hanya untuk keterangan)
  $defaultKodeUsulan = null;
  for ($i = 12; $i >= 1; $i--) {
    $v = $data_dosen->{'KodeUsulan'.$i} ?? null;
    if (!empty($v)) {
      $defaultKodeUsulan = $v;
      break;
    }
  }
  $defaultKeterangan = $data_dosen->Keterangan ?? '';
@endphp
<div class="content-wrapper">
  <div class="row">
    <div class="col-12">
      @php $identifier = $data_dosen->NIDN ?? $data_dosen->NUPTK ?? ''; @endphp
      <form action="{{ url('admin/ubah-data-dosen/' . $identifier) }}" method="POST"
        enctype="multipart/form-data" id="form-ubah-data-dosen">
        @csrf
        @method('PUT')
        <input type="hidden" name="mode" value="{{ $mode ?? 'edit' }}">
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
                <select class="form-select" aria-label="Default select example" name="Aktif" id="status_aktif">
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
                <input class="form-control" type="date" name="tgl_dokumen_ubah" id="tgl_dokumen_ubah"
                  required="">
              </div>
              <label class="col-sm-2 col-form-label" for="basic-default-name">Alasan Perubahan</label>
              <div class="col-sm-4">
                <select class="form-select" id="alasan_perubahan" name="alasan_perubahan"
                  aria-label="Default select example" required="">
                  <option value="">-- Pilih Alasan --</option>
                  @foreach($statusList as $status)
                    <option value="{{ $status }}">{{ $status }}</option>
                  @endforeach
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
                  id="tmt" required="">
              </div>
            </div>

            <div class="row mb-3 ">
              <label class="col-sm-2 col-form-label" for="basic-default-name">Keterangan</label>
              <div class="col-sm-4">
                <input type="text" class="form-control" id="keterangan" name="keterangan" required value="{{ $defaultKeterangan }}">
              </div>
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
                  value="{{ $data_dosen->NUPTK ?? '' }}" readonly
                  style="background-color: #eceef1;" placeholder="Kosong jika tidak tersedia">
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
                  value="{{ $data_dosen->Nama ?? 'Data tidak tersedia' }}" readonly
                  style="background-color: #eceef1;">
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
              <label class="col-sm-2 col-form-label" for="basic-default-name">Jenis</label>
              <div class="col-sm-10">
                <select class="form-select" aria-label="Default select example" name="jenis">
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
                <!-- <input type="text" class="form-control" id="basic-default-name" name="jabatan"
                                    value="{{ $data_dosen->Jabatan12 }}"> -->
                <select name="jabatan" id="jabatan" class="form-control" required>
                  <option value="">Pilih Jabatan</option>
                  @foreach ($jabatans as $jabatan)
                  <option value="{{ $jabatan }}" @selected(trim($data_dosen->jabatan) ==
                    trim($jabatan))>
                    {{ $jabatan }}
                  </option>
                  @endforeach
                </select>

              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="golongan">Golongan</label>
              <div class="col-sm-10">
                <select class="form-select" name="gol" id="golongan" required>
                  <option value="">Pilih Golongan</option>
                  <option value="III/a"
                    {{ isset($data_dosen) && $data_dosen->gol == 'III/a' ? 'selected' : '' }}>
                    III/a</option>
                  <option value="III/b"
                    {{ isset($data_dosen) && $data_dosen->gol == 'III/b' ? 'selected' : '' }}>
                    III/b</option>
                  <option value="III/c"
                    {{ isset($data_dosen) && $data_dosen->gol == 'III/c' ? 'selected' : '' }}>
                    III/c</option>
                  <option value="III/d"
                    {{ isset($data_dosen) && $data_dosen->gol == 'III/d' ? 'selected' : '' }}>
                    III/d</option>
                  <option value="IV/a"
                    {{ isset($data_dosen) && $data_dosen->gol == 'IV/a' ? 'selected' : '' }}>
                    IV/a</option>
                  <option value="IV/b"
                    {{ isset($data_dosen) && $data_dosen->gol == 'IV/b' ? 'selected' : '' }}>
                    IV/b</option>
                  <option value="IV/c"
                    {{ isset($data_dosen) && $data_dosen->gol == 'IV/c' ? 'selected' : '' }}>
                    IV/c</option>
                  <option value="IV/d"
                    {{ isset($data_dosen) && $data_dosen->gol == 'IV/d' ? 'selected' : '' }}>
                    IV/d</option>
                  <option value="IV/e"
                    {{ isset($data_dosen) && $data_dosen->gol == 'IV/e' ? 'selected' : '' }}>
                    IV/e</option>
                </select>
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="masa_kerja">Masa Kerja (tahun)</label>
              <div class="col-sm-10">
                <input type="number" class="form-control" id="masa_kerja" name="tahun" min="0"
                  placeholder="Masukkan masa kerja"
                  value="{{ isset($data_dosen) ? $data_dosen->masa_kerja : '' }}">
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="gaji">Biaya</label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="gaji" name="gaji"
                  value="{{ isset($data_dosen) ? $data_dosen->Gaji12 : '' }}" readonly
                  style="background-color: #eceef1;">
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
                <!-- <input type="text" class="form-control" id="basic-default-name" name="bank"
                  value="{{ $data_dosen->Bank }}"> -->
                <select name="bank" id="" class="form-control">
                  @foreach ($banks as $bank)
                  <option value="{{$bank}}" @if ($data_dosen->Bank == $bank)
                    selected
                    @endif>{{ $bank }}</option>
                  @endforeach
                </select>
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
              <label class="col-sm-2 col-form-label" for="basic-default-name">Pemegang
                Wilayah</label>
              <div class="col-sm-10">
                <select name="pemegang_wilayah" class="form-control">
                  @if(empty($data_dosen->Pemegang_Wilayah))
                  <option value="" selected>-- Pilih Pemegang Wilayah (PIC) --</option>
                  @endif
                  @foreach(($pics ?? []) as $pic)
                  <option value="{{ $pic }}" {{ (isset($data_dosen->Pemegang_Wilayah) && $data_dosen->Pemegang_Wilayah == $pic) ? 'selected' : '' }}>{{ $pic }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="basic-default-company">Eligible
                Span</label>
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
        </div>
      </form>
    </div>
  </div>
</div>

<script>
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
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // KodeUsulan per-bulan dari s_transaksi_2 (untuk menyesuaikan dropdown dengan TMT)
    const kodeUsulanBulanan = {
      1: @json($data_dosen->KodeUsulan1 ?? null),
      2: @json($data_dosen->KodeUsulan2 ?? null),
      3: @json($data_dosen->KodeUsulan3 ?? null),
      4: @json($data_dosen->KodeUsulan4 ?? null),
      5: @json($data_dosen->KodeUsulan5 ?? null),
      6: @json($data_dosen->KodeUsulan6 ?? null),
      7: @json($data_dosen->KodeUsulan7 ?? null),
      8: @json($data_dosen->KodeUsulan8 ?? null),
      9: @json($data_dosen->KodeUsulan9 ?? null),
      10: @json($data_dosen->KodeUsulan10 ?? null),
      11: @json($data_dosen->KodeUsulan11 ?? null),
      12: @json($data_dosen->KodeUsulan12 ?? null),
    };

    const tmtInput = document.getElementById('tmt');
    const statusAktifSelect = document.getElementById('status_aktif');
    const alasanSelect = document.getElementById('alasan_perubahan');
    const keteranganInput = document.getElementById('keterangan');

    function setSelectValue(el, value) {
      const strVal = (value === null || value === undefined) ? '' : String(value);
      if (!el) return;
      const tag = el.tagName ? el.tagName.toUpperCase() : '';
      if (tag === 'SELECT') {
        const exists = Array.from(el.options).some(opt => opt.value === strVal);
        if (exists) {
          el.value = strVal;
          return;
        }
        if (strVal !== '') {
          const opt = document.createElement('option');
          opt.value = strVal;
          opt.textContent = strVal;
          opt.selected = true;
          el.insertBefore(opt, el.options[1] || null);
        } else {
          el.value = '';
        }
      } else {
        // treat as input/textarea
        el.value = strVal;
      }
    }

    function syncDropdownByTmt() {
      if (!tmtInput || !tmtInput.value) return;
      const dt = new Date(tmtInput.value);
      if (isNaN(dt.getTime())) return;
      const month = dt.getMonth() + 1; // 1..12
      // Jika status saat ini Aktif, arahkan alasan ke "Pengaktifan Kembali"
      if (statusAktifSelect && String(statusAktifSelect.value) === '1') {
        // Only auto-fill if user hasn't chosen a reason yet
        if (alasanSelect && (!alasanSelect.value || String(alasanSelect.value).trim() === '')) {
          setSelectValue(alasanSelect, 'Pengaktifan Kembali');
        }
        return;
      }

      // Do not auto-fill `keterangan` field. Only set Alasan when appropriate.
      // (KodeUsulan per-bulan is left unused for automatic population.)
    }

    if (tmtInput) {
      tmtInput.addEventListener('change', syncDropdownByTmt);
    }

    // Jika status berubah:
    // - tidak aktif -> aktif: KodeUsulan akan di-NULL-kan mulai TMT (backend), jadi kosongkan dropdown
    // - aktif -> tidak aktif: biarkan user pilih alasan/keterangan (atau sync dari TMT jika sudah dipilih)
    if (statusAktifSelect) {
      statusAktifSelect.addEventListener('change', function() {
        const val = String(statusAktifSelect.value);
        if (val === '1') {
          // re-aktif: only auto-select "Pengaktifan Kembali" if user hasn't selected another reason
          if (alasanSelect && (!alasanSelect.value || String(alasanSelect.value).trim() === '')) {
            setSelectValue(alasanSelect, 'Pengaktifan Kembali');
          }
        } else if (val === '0') {
          syncDropdownByTmt();
        }
      });
    }

    const golonganInput = document.getElementById('golongan');
    const masaKerjaInput = document.getElementById('masa_kerja');
    const gajiInput = document.getElementById('gaji');

    function fetchGaji() {
      const golongan = golonganInput.value;
      const masaKerja = masaKerjaInput.value;

      if (golongan && masaKerja) {
        fetch("{{ route('admin.ubah-data-dosen.get-biaya') }}", {
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

    fetchGaji();
    //alert reuseable
    const alert = (text, title, icon, warnaBtn) => {
      return Swal.fire({
        title,
        text,
        icon,
        confirmButtonText: 'OK',
        customClass: {
          confirmButton: warnaBtn
        },
        buttonsStyling: false
      })
    }
    //feching data
    const form = document.getElementById("form-ubah-data-dosen");

    form.addEventListener("submit", function(e) {
      e.preventDefault(); // cegah submit default

      // tampilkan loading
      Swal.fire({
        title: 'Mohon Tunggu...',
        html: `
                <div class="d-flex justify-content-center align-items-center flex-column">
                    <div class="spinner-border spinner-border-lg text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Mohon tunggu, sedang mengupdate data...</div>
                </div>
            `,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        backdrop: true
      });

      // ambil data form
      const formData = new FormData(form);

      fetch(form.action, {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
          },
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          Swal.close(); // tutup loading

          if (data.success) {
            alert(data.message ?? "Data berhasil disimpan", 'Berhasil!', 'success',
              'btn btn-primary').then(() => {
              // redirect kalau perlu
              // window.location.reload =
              //   `{{ url('admin/ubah-data-dosen') }}/${data.nidn}`;
              location.reload();
            });
          } else {
            alert(data.message ?? 'Terjadi kesalahan saat ubah data!', 'Gagal!',
              'error', 'btn btn-danger')
          }
        })
        .catch(error => {
          Swal.close();
          alert('INTERNAL SERVEL ERROR!', 'Error!', 'error', 'btn btn-danger')
          console.error("Error:", error);
        });
    });

  });
</script>

@endsection