@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-header pb-2">
                <!-- Tambahkan padding-bottom kecil -->
                <h5 class="mb-1">Usulan Susulan</h5>
                <hr> <!-- ubah mb-0 jadi mb-1 agar ada jarak kecil -->
            </div>
            <div class="card-body ">
                <!-- Tambahkan padding-top kecil -->
                @php
                use Carbon\Carbon;

                $bulanSekarang = now()->month;
                $tahunSekarang = now()->year;
                $selectedTahun = session('tahun') ?? $tahunSekarang;

                // Daftar nama bulan
                $namaBulan = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
                ];

                // Susulan: tampil hanya sampai bulan sebelumnya jika periode adalah tahun berjalan
                // Jika periode (session year) < tahun sekarang, tampilkan seluruh 12 bulan
                if ($selectedTahun < $tahunSekarang) {
                    $batasBulan = 12;
                } else {
                    // Untuk tahun berjalan: tampilkan hanya bulan-bulan sebelum bulan sekarang.
                    // Jika sekarang Januari (bulan 1), maka tidak ada bulan yang ditampilkan.
                    $batasBulan = max(0, $bulanSekarang - 1);
                }
                @endphp

                <form id="bulanForm" method="GET"
                    action="{{ route('pts.usulan-sptjm-susulan') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="bulan" class="form-label fw-semibold">Periode</label>
                            <select class="form-select" id="bulan" name="bulan">
                                <option value="" disabled {{ empty($bulan) ? 'selected' : '' }}>Pilih Bulan
                                </option>
                                @for ($i = 1; $i <= $batasBulan; $i++)
                                    <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                        {{ $namaBulan[$i] }} {{ $selectedTahun }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    </form>

                    <script>
                    document.getElementById('bulan').addEventListener('change', function() {
                        document.getElementById('bulanForm').submit();
                    });
                    </script>
            </div>
        </div>

        <hr>

        <!-- TABEL -->
        <div class="card mb-4">
            <h5 class="card-header">Tabel Usulan SPTJM Susulan</h5>
            <div class="card-body">
                @if (session('info'))
                <div class="alert alert-warning" role="alert">{{ session('info') }}</div>
                @endif
                @php
                                    $listPns = $dosenListPNS ?? collect();
                                    $listNonPns = $dosenListNonPNS ?? collect();
                                    $countPns = $listPns->count();
                                    $countNonPns = $listNonPns->count();
                  $countTotal = $countPns + $countNonPns;
                @endphp

                <span class="fw-semibold">Jumlah Dosen: <span id="jumlahDosen">{{ $countTotal }}</span></span>

                <h6 class="mt-3 mb-2">Daftar Nama Dosen PNS ({{ $countPns }})</h6>
                <div class="table-responsive text-nowrap">
                    <table class="table table-sm table-hover mt-1" id="dosenTablePns">
                        <thead style="background-color: #dbdee0;">
                            <tr>
                                <th>No</th>
                                <th>NIDN</th>
                                <th>NUPTK</th>
                                <th>Nama Dosen</th>
                                <th>Golongan</th>
                                <th>Masa Kerja</th>
                                <th>Jabatan</th>
                                <th>BKD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @forelse ($listPns as $dosen)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $dosen->nidn }}</td>
                                <td class="text-center">{{ $dosen->nuptk ?? '-' }}</td>
                                <td>{{ $dosen->nama }}</td>
                                <td>{{ $dosen->gol ?? ($dosen->{'gol'.($bulan ?? 12)} ?? '-') }}</td>
                                <td>{{ $dosen->tahun ?? ($dosen->{'tahun'.($bulan ?? 12)} ?? '-') }}</td>
                                <td>{{ $dosen->jabatan ?? ($dosen->{'jabatan'.($bulan ?? 12)} ?? '-') }}</td>
                                <td>{{ $dosen->kesimpulan_bkd ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data dosen.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h6 class="mt-4 mb-2">Daftar Nama Dosen NON PNS ({{ $countNonPns }})</h6>
                <div class="table-responsive text-nowrap">
                    <table class="table table-sm table-hover mt-1" id="dosenTableNonPns">
                        <thead style="background-color: #dbdee0;">
                            <tr>
                                <th>No</th>
                                <th>NIDN</th>
                                <th>NUPTK</th>
                                <th>Nama Dosen</th>
                                <th>Golongan</th>
                                <th>Masa Kerja</th>
                                <th>Jabatan</th>
                                <th>BKD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @forelse ($listNonPns as $dosen)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $dosen->nidn }}</td>
                                <td class="text-center">{{ $dosen->nuptk ?? '-' }}</td>
                                <td>{{ $dosen->nama }}</td>
                                <td>{{ $dosen->gol ?? ($dosen->{'gol'.($bulan ?? 12)} ?? '-') }}</td>
                                <td>{{ $dosen->tahun ?? ($dosen->{'tahun'.($bulan ?? 12)} ?? '-') }}</td>
                                <td>{{ $dosen->jabatan ?? ($dosen->{'jabatan'.($bulan ?? 12)} ?? '-') }}</td>
                                <td>{{ $dosen->kesimpulan_bkd ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data dosen.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- TOMBOL DI BAWAH TABEL -->
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-2 mt-4">

                    <!-- Tombol Modal -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#modalDataSetting">
                        <span class="tf-icons bx bx-box"></span> Masukkan Data Setting
                    </button>

                    <!-- Form Upload + Usulkan -->
                    <form id="formUsulkan" method="POST" action="{{ route('pts.upload-sptjm-susulan') }}"
                        enctype="multipart/form-data" class="d-flex align-items-center flex-wrap gap-2">
                        @csrf

                        <!-- Hidden input -->
                        <input type="hidden" name="kode_pts" id="uploadKodePTS">
                        <input type="hidden" name="nama_pts" id="uploadNamaPTS">
                        <input type="hidden" name="bulan" id="uploadBulan">
                        <input type="hidden" name="tahun" id="uploadTahun">
                        <input type="hidden" name="nidn" id="uploadNIDN">
                        <input type="hidden" name="nuptk" id="uploadNUPTK">
                        <input type="hidden" name="nama" id="uploadNama">
                        <input type="hidden" name="jabatan" id="uploadJabatan">
                        <input type="hidden" name="kota" id="uploadKota">
                        <input type="hidden" name="nomor_surat" id="uploadNomorSurat">
                        <input type="hidden" name="alamat_pts" id="uploadAlamatPTS">

                        <!-- Upload File Button -->
                        <label for="fileInput" class="btn btn-secondary mb-0 d-flex align-items-center" id="uploadBtn">
                            <span class="tf-icons bx bx-cloud-upload me-2"></span>
                            <span id="fileName">Upload</span>
                        </label>
                        <input type="file" name="file" id="fileInput" class="d-none" onchange="updateFileName(this)"
                            required>

                        <!-- Usulkan Button -->
                        <button type="button" id="usulkanButton" class="btn btn-info">
                            <span class="tf-icons bx bx-send"></span> Usulkan
                        </button>
                    </form>


                </div>

                <!-- CATATAN -->
                <div class="alert alert-info small mt-3">
                    <ul class="mb-0 ps-3">
                        <li>Penamaan File SPTJM: <strong>SPTJM Berjalan_[Bulan Tahun]_[Nama PTS]</strong></li>
                        <li>Ukuran file tidak lebih dari 2MB</li>
                        <li>Isi data setting pimpinan sebelum upload file SPTJM</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Modal Setting Pimpinan -->
        <div class="modal fade" id="modalDataSetting" tabindex="-1" aria-labelledby="exampleModalLabel1"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Setting Pimpinan Penandatangan SPTJM</h5>
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
                        <button id="printButton" class="btn btn-primary mx-2" onclick="openPrintPage()">
                            <span class="tf-icons bx bx-file"></span>&nbsp; Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script Pencarian -->
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
@if(session('internal_error'))
Swal.fire({
    icon: 'error',
    title: 'Internal Error',
    text: 'Terjadi kesalahan internal saat memproses usulan. Silakan coba lagi atau hubungi admin.',
    showConfirmButton: true
});
@endif
const btnUsulkan = document.getElementById("usulkanButton")
// hide Usulkan button until a file is chosen
try {
    const fileInputEl = document.getElementById('fileInput');
    if (fileInputEl && fileInputEl.files && fileInputEl.files.length > 0) {
        btnUsulkan.style.display = '';
    } else {
        btnUsulkan.style.display = 'none';
    }
    // toggle when file selected
    fileInputEl?.addEventListener('change', function() {
        if (this.files && this.files.length > 0) btnUsulkan.style.display = '';
        else btnUsulkan.style.display = 'none';
    });
} catch (e) {}

btnUsulkan.addEventListener('click', () => {
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
        if (result.isConfirmed) {
            fungsiUsulkan()
        }
    });
})
document.getElementById("searchInput")?.addEventListener("keyup", function() {
    var filter = this.value.toLowerCase();
    var rows = document.querySelectorAll("#rekapTable tbody tr");
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});

function updateFileName(input) {
    const fileName = input.files.length > 0 ? input.files[0].name : 'Upload';
    document.getElementById('fileName').textContent = fileName;
}

function openPrintPage() {
    const data = {
        nidn: document.getElementById('modalNIDN').value,
        nuptk: document.getElementById('modalNUPTK') ? document.getElementById('modalNUPTK').value : '',
        nama: document.getElementById('modalNama').value,
        jabatan: document.getElementById('modalJabatan').value,
        kota: document.getElementById('modalKota').value,
        nomor: document.getElementById('modalNomorSurat').value,
        alamat: document.getElementById('modalAlamatPTS').value,
    };

    localStorage.setItem('dataSPTJM', JSON.stringify(data));

    // Ambil bulan dari select
    const bulan = document.getElementById('bulan')?.value ?? '';

    // Redirect ke print-susulan dengan parameter bulan
    const url = "{{ route('pts.print-sptjm-susulan', ['bulan' => '__BULAN__']) }}".replace('__BULAN__', bulan);

    window.open(url, '_blank');
}

function fungsiUsulkan() {
    // Ambil data dari modal setting
    const nidnRaw = document.getElementById('modalNIDN').value;
    const nuptkRaw = document.getElementById('modalNUPTK') ? document.getElementById('modalNUPTK').value : '';
    const namaRaw = document.getElementById('modalNama').value;
    const jabatanRaw = document.getElementById('modalJabatan').value;
    const kotaRaw = document.getElementById('modalKota').value;
    const nomorSuratRaw = document.getElementById('modalNomorSurat').value;
    const alamatPTSRaw = document.getElementById('modalAlamatPTS').value;
    const file = document.getElementById('fileInput').value

    // Ambil data dari dropdown filter
    const bulan = document.getElementById('bulan').value;
    const tahun = new Date().getFullYear();

    // Ambil data user PTS
    const kodePTS = "{{ Auth::guard('pts')->user()->kode_pts }}";
    const namaPTS = "{{ Auth::guard('pts')->user()->nama_pts }}";

    // Validasi sederhana sebelum submit: hanya butuh bulan dan file
    if (!bulan || !file) {
        alert("Mohon pilih bulan dan pilih file sebelum mengusulkan.");
        return;
    }

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

    // Gunakan default jika field modal kosong
    const nidn = nidnRaw && nidnRaw.trim() !== '' ? nidnRaw : '-';
    const nama = namaRaw && namaRaw.trim() !== '' ? namaRaw : `{{ Auth::guard('pts')->user()->nama_pimpinan ?? '-' }}`;
    const jabatan = jabatanRaw && jabatanRaw.trim() !== '' ? jabatanRaw : `{{ Auth::guard('pts')->user()->jabatan_pimpinan ?? '-' }}`;
    const kota = kotaRaw && kotaRaw.trim() !== '' ? kotaRaw : `{{ Auth::guard('pts')->user()->kota_pt ?? Auth::guard('pts')->user()->kota ?? '-' }}`;
    const nomorSurat = nomorSuratRaw && nomorSuratRaw.trim() !== '' ? nomorSuratRaw : '-';
    const alamatPTS = alamatPTSRaw && alamatPTSRaw.trim() !== '' ? alamatPTSRaw : `{{ Auth::guard('pts')->user()->alamat_pt ?? Auth::guard('pts')->user()->alamat ?? '' }}`;
    const nuptk = nuptkRaw && nuptkRaw.trim() !== '' ? nuptkRaw : '-';

    // Set hidden inputs
    document.getElementById('uploadKodePTS').value = kodePTS;
    document.getElementById('uploadNamaPTS').value = namaPTS;
    document.getElementById('uploadBulan').value = bulan;
    document.getElementById('uploadTahun').value = tahun;
    document.getElementById('uploadNIDN').value = nidn;
    document.getElementById('uploadNUPTK').value = nuptk;
    document.getElementById('uploadNama').value = nama;
    document.getElementById('uploadJabatan').value = jabatan;
    document.getElementById('uploadKota').value = kota;
    document.getElementById('uploadNomorSurat').value = nomorSurat;
    document.getElementById('uploadAlamatPTS').value = alamatPTS;

    // Submit form
    document.getElementById('formUsulkan').submit();
}
</script>
@endsection
