@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')
<div class="md2-page-header">
    <div class="page-titles">
        <h3>Usulan Tukin Susulan</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Usulan</a></li>
                <li class="breadcrumb-item active">Tukin Susulan</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
  <div class="col-12">
    <!-- Filter Form -->
    <div class="card md2-card mb-4">
      <div class="card-body px-4 pb-4 pt-4">
        @php
          use Carbon\Carbon;
          $bulanSekarang = Carbon::now()->month;
          $tahunSekarang = Carbon::now()->year;
          $selectedTahun = session('tahun') ? (int) session('tahun') : $tahunSekarang;
          $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
          ];
          // Susulan: jika selected tahun < tahun sekarang, tampilkan 1..12; jika sama, tampilkan 1..(bulan sebelumnya)
          // Jika sekarang Januari (bulan 1), maka tidak ada bulan yang ditampilkan untuk tahun berjalan.
          if ($selectedTahun < $tahunSekarang) {
            $maxBulan = 12;
          } else {
            $maxBulan = max(0, $bulanSekarang - 1);
          }
        @endphp

        <form id="bulanForm" method="GET" action="{{ route('pts.usulan-tukin-susulan') }}">
          <div class="row">
            <div class="col-md-4">
              <label for="bulan" class="form-label fw-semibold">Periode</label>
              <select class="form-select" id="bulan" name="bulan">
                <option value="" disabled {{ empty($bulan) ? 'selected' : '' }}>Pilih Bulan</option>
                @for ($i = 1; $i <= $maxBulan; $i++)
                  <option value="{{ $i }}" {{ (int)($bulan ?? 0) === $i ? 'selected' : '' }}>
                    {{ $namaBulan[$i] }} {{ $selectedTahun }}
                  </option>
                @endfor
              </select>
            </div>
          </div>
        </form>

        <script>
          document.getElementById('bulan').addEventListener('change', function () {
            document.getElementById('bulanForm').submit();
          });
        </script>
      </div>
    </div>

    <!-- TABEL -->
    <div class="card md2-card mb-4">
      <div class="card-body px-4 pb-4 pt-4">
        @if(session('info'))
          <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @php
          $listPns = $dosenListPNS ?? collect();
          $countPns = $listPns->count();
        @endphp

        <span class="fw-semibold">Jumlah Dosen PNS: <span id="jumlahDosen">{{ $countPns }}</span></span>

        <h6 class="mt-3 mb-2 fw-bold" style="color: #64748b; font-size: 0.85rem; text-transform: uppercase;">Daftar Nama Dosen PNS ({{ $countPns }})</h6>
        <div class="table-responsive text-nowrap border rounded mb-4">
          <table class="table table-hover md2-table m-0" id="dosenTablePns">
            <thead style="background-color: #dbdee0;">
              <tr>
                <th>No</th>
                <th>NUPTK</th>
                <th>NIDN</th>
                <th>Nama Dosen</th>
                <th>Jabatan</th>
                <th>Kelas Jabatan</th>
                <th>Nilai Tukin Kelas Jabatan</th>
                <th>Sertifikat</th>
                <th>KD</th>
                <th>KP</th>
                <th>PP</th>
                <th>Status</th>
                <th>Ket. Status</th>
              </tr>
            </thead>
            <tbody>
              @php $no = 1; @endphp
              @forelse ($listPns as $d)
                @php
                  $kelas = '-'; $nilai = '-';
                  $jab = $d->jabatan;
                  if (in_array($jab, ['Guru Besar', 'Guru Besar 1050'])) { $kelas = '15'; $nilai = 'Rp. 19.280.000'; }
                  elseif ($jab === 'Lektor Kepala') { $kelas = '13'; $nilai = 'Rp. 10.936.000'; }
                  elseif ($jab === 'Lektor') { $kelas = '11'; $nilai = 'Rp. 8.757.600'; }
                  elseif ($jab === 'Asisten Ahli') { $kelas = '9'; $nilai = 'Rp. 5.079.200'; }
                  elseif ($jab === 'Tanpa Jabatan') { $kelas = '8'; $nilai = 'Rp. 4.595.150'; }
                  elseif ($jab === 'CPNS') { $kelas = '7'; $nilai = 'Rp. 3.915.950'; }
                @endphp
                <tr>
                  <td>{{ $no++ }}</td>
                  <td class="text-center">{{ $d->nuptk ?? '-' }}</td>
                  <td class="text-center"><strong>{{ $d->nidn }}</strong></td>
                  <td>{{ $d->nama }}</td>
                  <td class="text-center">{{ $d->jabatan ?? '-' }}</td>
                  <td class="text-center">{{ $kelas }}</td>
                  <td class="text-center">{{ $nilai }}</td>
                  <td>{{ $d->sertifikat_dosen ?? '-' }}</td>
                  <td class="text-center">{{ $d->kd ?? '-' }}</td>
                  <td class="text-center">{{ $d->kp ?? '-' }}</td>
                  <td class="text-center">{{ $d->pp ?? '-' }}</td>
                  <td class="text-center">
                    @if(($d->aktif ?? 0) == 1)
                      <span class="badge bg-label-success border border-success">Aktif</span>
                    @else
                      <span class="badge bg-label-danger">Tidak Aktif</span>
                    @endif
                  </td>
                  <td class="text-center"><strong>{{ $d->keterangan ?? '-' }}</strong></td>
                </tr>
              @empty
                <tr>
                  <td colspan="13" class="text-center">Tidak ada data dosen.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>


        <!-- TOMBOL DI BAWAH TABEL -->
        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 mt-4">
          <!-- Tombol Modal -->
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDataSetting">
            <span class="tf-icons bx bx-box"></span> Masukkan Data Setting
          </button>

          <!-- Upload + Usulkan -->
          <div class="d-flex align-items-center flex-wrap gap-2">
            <label for="fileInput" class="btn btn-secondary mb-0 d-flex align-items-center" id="uploadBtn">
              <span class="tf-icons bx bx-cloud-upload me-2"></span>
              <span id="fileName">Upload</span>
            </label>
            <input type="file" id="fileInput" name="file" form="formUsulkanTukinSusulan" accept="application/pdf" class="d-none" onchange="updateFileName(this)">

            <form id="formUsulkanTukinSusulan" method="POST" action="{{ route('pts.usulkan-tukin-susulan') }}" class="d-inline" enctype="multipart/form-data">
              @csrf
              <input type="hidden" name="bulan" id="bulanHidden" value="{{ $bulan }}" />
              <input type="hidden" name="nidn" id="hidNIDN" />
              <input type="hidden" name="nuptk" id="hidNUPTK" />
              <input type="hidden" name="nama" id="hidNama" />
              <input type="hidden" name="jabatan" id="hidJabatan" />
              <input type="hidden" name="kota" id="hidKota" />
              <input type="hidden" name="nomor_surat" id="hidNomorSurat" />
              <button type="button" id="usulkanButton" class="btn btn-info" onclick="submitUsulanSusulan()">
                <span class="tf-icons bx bx-send"></span> Usulkan
              </button>
            </form>
          </div>
        </div>

        <!-- CATATAN -->
        <div class="alert alert-info small mt-3">
          <ul class="mb-0 ps-3">
            <li><strong>Penamaan File: Tukin Susulan_[Bulan Tahun]_[Nama PTS]</strong></li>
            <li><strong>Ukuran file tidak lebih dari 2MB</strong></li>
            <li><strong>Isi data setting pimpinan sebelum print</strong></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Modal Setting Pimpinan -->
    <div class="modal fade" id="modalDataSetting" tabindex="-1" aria-labelledby="exampleModalLabel1" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel1">Setting Pimpinan Penandatangan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div class="row g-2">
                <div class="col mb-3">
                  <label for="modalNIDN" class="form-label">NIDN</label>
                  <input type="text" id="modalNIDN" class="form-control" />
                </div>
                <div class="col mb-3">
                  <label for="modalNUPTK" class="form-label">NUPTK</label>
                  <input type="text" id="modalNUPTK" class="form-control" />
                </div>
              </div>

              <div class="row">
                <div class="col mb-3">
                  <label for="modalNama" class="form-label">Nama</label>
                  <input type="text" id="modalNama" class="form-control" />
                </div>
              </div>

            <div class="row g-2">
              <div class="col mb-3">
                <label for="modalJabatan" class="form-label">Jabatan</label>
                <input type="text" id="modalJabatan" class="form-control" />
              </div>
              <div class="col mb-3">
                <label for="modalKota" class="form-label">Kota</label>
                <input type="text" id="modalKota" class="form-control" />
              </div>
            </div>

            <div class="row">
              <div class="col mb-3">
                <label for="modalNomorSurat" class="form-label">Nomor Surat</label>
                <input type="text" id="modalNomorSurat" class="form-control" />
              </div>
            </div>

            <div class="row">
              <div class="col mb-3">
                <label for="modalAlamatPTS" class="form-label">Alamat PTS</label>
                <input type="text" id="modalAlamatPTS" class="form-control" />
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              Kembali
            </button>
            <button id="printButtonModal" class="btn btn-primary mx-2" onclick="openPrintPage()">
              <span class="tf-icons bx bx-file"></span>&nbsp; Print
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  @if(session('success'))
  Swal.fire({
    position: "center",
    icon: "success",
    title: "Berhasil",
    text: "{{ session('success') }}",
    showConfirmButton: true,
    timer: 1500
  });
  @endif

  // hide Usulkan button until a file is chosen
  try {
    const btnUsulkan = document.getElementById('usulkanButton');
    const fileInputEl = document.getElementById('fileInput');
    if (fileInputEl && fileInputEl.files && fileInputEl.files.length > 0) {
      btnUsulkan.style.display = '';
    } else {
      btnUsulkan.style.display = 'none';
    }
    fileInputEl?.addEventListener('change', function() {
      if (this.files && this.files.length > 0) btnUsulkan.style.display = '';
      else btnUsulkan.style.display = 'none';
    });
  } catch (e) {}

  function updateFileName(input) {
    const fileName = input.files.length > 0 ? input.files[0].name : 'Upload';
    document.getElementById('fileName').textContent = fileName;
  }

  function openPrintPage() {
    const bulan = document.getElementById('bulan').value;
    const data = {
      nidn: document.getElementById('modalNIDN').value,
      nama: document.getElementById('modalNama').value,
      jabatan: document.getElementById('modalJabatan').value,
      kota: document.getElementById('modalKota').value,
      nomor: document.getElementById('modalNomorSurat').value,
      alamat: document.getElementById('modalAlamatPTS').value,
      bulan
    };

    localStorage.setItem('dataTukinSusulan', JSON.stringify(data));
    const url = "{{ route('pts.print-tukin-susulan') }}" + "?bulan=" + bulan;
    window.open(url, '_blank');
  }

  function submitUsulanSusulan() {
    const jumlah = Number((document.getElementById('jumlahDosen')?.textContent || '0').toString().trim());
    if (!jumlah || jumlah === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Tidak Bisa Mengusulkan',
        text: 'Jumlah Dosen: 0 — tidak bisa mengusulkan.',
        showConfirmButton: true
      });
      return;
    }
    const nidnRaw = document.getElementById('modalNIDN').value;
    const namaRaw = document.getElementById('modalNama').value;
    const jabatanRaw = document.getElementById('modalJabatan').value;
    const kotaRaw = document.getElementById('modalKota').value;
    const nomorSuratRaw = document.getElementById('modalNomorSurat').value;
    const bulan = document.getElementById('bulan').value;
    const fileEl = document.getElementById('fileInput');

    if (!bulan || !fileEl || fileEl.files.length === 0) {
      alert('Mohon pilih bulan dan lampirkan file PDF sebelum mengusulkan.');
      return;
    }

    Swal.fire({
      title: "Apakah anda yakin?",
      text: "Setelah mengusulkan, Anda tidak bisa membatalkan.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Ya, usulkan",
      cancelButtonText: 'Batal',
    }).then((result) => {
      if (!result.isConfirmed) return;

      Swal.fire({
        title: 'Mohon Tunggu...',
        html: `
                      <div class="d-flex justify-content-center align-items-center flex-column">
                          <div class="spinner-border spinner-border-lg text-success" role="status">
                              <span class="visually-hidden">Loading...</span>
                          </div>
                          <div class="mt-2">Sedang menyimpan data</div>
                      </div>
                  `,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        backdrop: true
      });

      const nidn = nidnRaw && nidnRaw.trim() !== '' ? nidnRaw : '-';
      const nama = namaRaw && namaRaw.trim() !== '' ? namaRaw : `{{ Auth::guard('pts')->user()->nama_pimpinan ?? '-' }}`;
      const jabatan = jabatanRaw && jabatanRaw.trim() !== '' ? jabatanRaw : `{{ Auth::guard('pts')->user()->jabatan_pimpinan ?? '-' }}`;
      const kota = kotaRaw && kotaRaw.trim() !== '' ? kotaRaw : `{{ Auth::guard('pts')->user()->kota_pt ?? Auth::guard('pts')->user()->kota ?? '-' }}`;
      const nomorSurat = nomorSuratRaw && nomorSuratRaw.trim() !== '' ? nomorSuratRaw : '-';

      document.getElementById('hidNIDN').value = nidn;
      document.getElementById('hidNUPTK').value = (document.getElementById('modalNUPTK')?.value || '').toString().trim() !== '' ? document.getElementById('modalNUPTK').value : '-';
      document.getElementById('hidNama').value = nama;
      document.getElementById('hidJabatan').value = jabatan;
      document.getElementById('hidKota').value = kota;
      document.getElementById('hidNomorSurat').value = nomorSurat;
      document.getElementById('bulanHidden').value = bulan;
      document.getElementById('formUsulkanTukinSusulan').submit();
    });
  }

  @if(session('internal_error'))
  Swal.fire({
    icon: 'error',
    title: 'Internal Error',
    text: "{{ session('internal_error') }}",
    showConfirmButton: true
  });
  @endif
</script>
@endsection
