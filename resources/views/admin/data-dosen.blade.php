@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('page-style')
<style>
/* â”€â”€ Status Filter Buttons â€” unik untuk halaman ini â”€â”€ */
.md-status-filters {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
}
.md-status-btn {
    background-color: #fff;
    border: 1px solid #e2e8f0;
    color: #4a5568;
    font-weight: 500;
    font-size: 0.82rem;
    padding: 5px 18px;
    border-radius: 20px;
    transition: all 0.2s;
    cursor: pointer;
}
.md-status-btn.active {
    background-color: #0b3d91;
    border-color: #0b3d91;
    color: #fff;
}
.md-status-btn:hover:not(.active) {
    background-color: #f8fafc;
    border-color: #cbd5e1;
}



/* NIDN link style */
.text-link-nidn {
    color: #0b3d91;
    font-weight: 500;
    text-decoration: none;
}
.text-link-nidn:hover { text-decoration: underline; }

/* â”€â”€ Aksi Circular Buttons â”€â”€ */
.btn-aksi-circle {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    color: #fff !important;
    margin-right: 3px;
    font-size: 0.85rem;
    transition: transform 0.2s, opacity 0.2s;
    text-decoration: none;
    cursor: pointer;
}
.btn-aksi-circle:hover { transform: scale(1.1); opacity: 0.9; }
.btn-aksi-view  { background: #1e3a8a; }
.btn-aksi-check { background: #166534; }
.btn-aksi-block { background: #dc2626; }
.btn-aksi-edit  { background: #eab308; }
</style>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h1>Data Dosen</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Dosen</a></li>
                <li class="breadcrumb-item active">Lihat Data Dosen</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn-sinkron-md" id="btnSinkronisasi" data-bs-toggle="modal" data-bs-target="#modalSinkronisasi">
            <i class="bx bx-transfer-alt"></i> Sinkronisasi
        </button>
        <button type="button" class="btn-tambah-md" id="addDosen" data-bs-toggle="modal" data-bs-target="#modalDosenForm">
            <i class="bx bx-plus"></i> Tambah Data
        </button>
    </div>
</div>

{{-- Status Filters --}}
<div class="md-status-filters">
    <button type="button" class="md-status-btn active" data-status="Semua">Semua</button>
    <button type="button" class="md-status-btn" data-status="Aktif">Aktif</button>
    <button type="button" class="md-status-btn" data-status="Tidak Aktif">Tidak Aktif</button>
</div>

{{-- Main Card --}}
<div class="md-card">
    <div class="md-card-inner">
        {{-- Toolbar --}}
        <!-- DataTables will inject length menu and search filter here via DOM option -->

        {{-- Table --}}
        <div class="md-table-wrap">
            <table class="table table-hover" id="dosenTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>NIDN</th>
                        <th>NUPTK</th>
                        <th>NAMA DOSEN</th>
                        <th>KODE PTS</th>
                        <th>NAMA PTS</th>
                        <th>ELIGIBLE SPAN</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- Modal Sinkronisasi Dosen antar PTS -->
<div class="modal fade" id="modalSinkronisasi" tabindex="-1" aria-labelledby="modalSinkronisasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSinkronisasiLabel">Sinkronisasi PTS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSinkronisasi" method="POST" action="{{ route('admin.data-dosen.sync.proses') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <small class="text-muted">Sinkronisasi untuk perpindahan dosen dari PTS asal ke PTS tujuan.</small>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Kode PTS Asal</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="kode_pts_asal_input" name="kode_pts_asal" placeholder="Masukkan kode PTS asal">
                                <button type="button" class="btn btn-outline-primary" id="btnCariDosenSinkron">Cari</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama PTS Asal</label>
                            <input type="text" class="form-control" id="nama_pts_asal_display" readonly style="background-color: #eceef1;">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Kode PTS Tujuan</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="kode_pts_tujuan_input" name="kode_pts_tujuan" placeholder="Masukkan kode PTS tujuan">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama PTS Tujuan</label>
                            <input type="text" class="form-control" id="nama_pts_tujuan_display" readonly style="background-color: #eceef1;">
                        </div>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-sm table-hover" id="tableDosenSinkron">
                                            <thead style="background-color: #dbdee0;">
                                                <tr>
                                                    <th style="width: 30px;"><input type="checkbox" id="checkAllSinkron" class="form-check-input"></th>
                                                    <th>NIDN</th>
                                                    <th>NUPTK</th>
                                                    <th>Nama Dosen</th>
                                                    <th>Kode PTS</th>
                                                    <th>Nama PTS</th>
                                                    <th>Status</th>
                                                    <th>Eligible Span</th>
                                                </tr>
                                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="8" class="text-center">Silakan masukkan Kode PTS asal lalu klik Cari.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success" id="btnProsesSinkronisasi">Proses</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="modalDosenForm" tabindex="-1" aria-labelledby="modalDosenFormLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDosenTitle">Tambah Dosen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="dosenForm" method="POST" action="{{ route('admin.data-dosen.store') }}"
                enctype="multipart/form-data">
                @csrf
                                @if ($errors->any())
                                    <div class="alert alert-danger m-3 mb-0" role="alert">
                                        <div class="fw-bold mb-1">Gagal menyimpan:</div>
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                <!-- Single-step: semua field ditampilkan dalam 1 halaman -->
                <div class="modal-body" id="step1">
                    <div class="mb-3">
                        <label class="form-label">NIDN</label>
                                                <input type="text" class="form-control" name="nidn" value="{{ old('nidn') }}" placeholder="Masukkan NIDN">
                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">NUPTK</label>
                                        <input type="text" class="form-control" name="nuptk" value="{{ old('nuptk') }}" placeholder="Masukkan NUPTK">
                                    </div>

                    <div class="mb-3">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control" name="nik" value="{{ old('nik') }}" placeholder="Masukkan NIK" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" class="form-control" name="nama" value="{{ old('nama') }}" placeholder="Masukkan Nama" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control" name="ttl" value="{{ old('ttl') }}" placeholder="Masukkan Tempat Lahir" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input class="form-control" type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required
                            onchange="hitungUsia()" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Usia</label>
                        <input type="text" class="form-control" id="usia" name="usia" value="{{ old('usia') }}" readonly
                            style="background-color: #eceef1;">
                    </div>
                    <!-- <div class="mb-3">
                        <label class="form-label">Kode Perguruan Tinggi</label>
                        <input type="text" class="form-control" id="kode_pts_input" name="kode_pts"
                            placeholder="Masukkan Kode PT" oninput="getNamaPTS()" required>
                    </div> -->
                    <div class="mb-3">
                        <label class="form-label">Kode Perguruan Tinggi</label>
                        <select name="kode_pts" id="kode_pts_input" class="form-select" required>
                            <option value="">-- Pilih Kode PT --</option>
                            @foreach ($kodePT as $kpt)
                            <option value="{{ $kpt }}" {{ old('kode_pts') == $kpt ? 'selected' : '' }}>{{ $kpt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Perguruan Tinggi</label>
                        <input type="text" class="form-control" id="nama_pts_display" name="nama_pts_display" readonly
                            style="background-color: #eceef1;">
                        <input type="hidden" id="pts_hidden" name="pts" value="{{ old('pts', '') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis</label>
                        <select class="form-select" name="jenis" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="PNS" {{ old('jenis') == 'PNS' ? 'selected' : '' }}>PNS</option>
                            <option value="NON PNS" {{ old('jenis') == 'NON PNS' ? 'selected' : '' }}>Non PNS</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sertifikat Dosen</label>
                        <input type="text" class="form-control" name="sertifikat_dosen" value="{{ old('sertifikat_dosen') }}" required
                            placeholder="Masukkan Sertifikat Dosen">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tahun Lulus</label>
                        <input type="text" class="form-control" name="tahun_lulus" value="{{ old('tahun_lulus') }}" required
                            placeholder="Masukkan Tahun Lulus">
                    </div>

                    <!-- <div class="mb-3">
                        <label class="form-label">SK Inpassing</label>
                        <input type="text" class="form-control" name="sk_inpassing" required
                            placeholder="Masukkan SK Inpassing">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pangkat</label>
                        <input type="text" class="form-control" name="pangkat" required placeholder="Masukkan Pangkat">
                    </div> -->
                    <div class="mb-3">
                        <label class="form-label">Status Aktif</label>
                        <select class="form-select" name="aktif" required>
                            <option value="1" {{ old('aktif', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('aktif') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="btnNext">Berikutnya</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dosenForm = document.getElementById("dosenForm");

    dosenForm.addEventListener('submit', function() {
        // Tutup modal
        const modalDosen = document.getElementById('modalDosenForm');
        const modalInstance = bootstrap.Modal.getInstance(modalDosen);
        if (modalInstance) modalInstance.hide();
        Swal.fire({
            title: 'Mohon Tunggu...',
            html: `
          <div class="d-flex justify-content-center align-items-center flex-column">
            <div class="spinner-border spinner-border-lg text-success" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Sedang menyimpan data...</div>
          </div>
        `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            backdrop: true
        });
        setTimeout(() => {
            Swal.close()
        }, 1500);
    });
    // SweetAlert untuk Notifikasi Sukses
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
    @if(session('error'))
    Swal.fire({
        title: 'Gagal!',
        text: "{{ session('error') }}",
        icon: 'error',
        customClass: {
            confirmButton: 'btn btn-danger'
        },
        buttonsStyling: false
    });
    });
    @endif

    @if(session('requires_scheduling'))
    Swal.fire({
        title: 'Perhatian!',
        text: "{{ session('warning') ?? 'Dosen ini tidak bisa dipindah PTS sekarang karena dalam proses usulan pencairan aktif.' }}",
        icon: 'warning',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Tutup'
    });
    @endif

    @if ($errors->any())
            // Re-open Tambah Dosen modal and show validation errors
            try {
                const modalEl = document.getElementById('modalDosenForm');
                if (modalEl && window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                }
            } catch (e) {}

            try {
                Swal.fire({
                    title: 'Gagal!',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    allowOutsideClick: false,
                });
            } catch (e) {}
        @endif

    // Single-step: gunakan validasi native browser saat submit

    // Fungsi untuk menghitung usia
    function hitungUsia() {
        const tanggalLahir = document.getElementById('tanggal_lahir').value;
        const usiaInput = document.getElementById('usia');

        if (tanggalLahir) {
            // Compute age as simple year difference (e.g., 2026 - 2004)
            // Accepts input type=date (YYYY-MM-DD) or other parseable formats.
            try {
                const lahir = new Date(tanggalLahir);
                const usia = new Date().getFullYear() - lahir.getFullYear();
                usiaInput.value = usia;
            } catch (e) {
                usiaInput.value = '';
            }
        } else {
            usiaInput.value = '';
        }
    }

    // Event Listener untuk perubahan tanggal lahir
    document.getElementById('tanggal_lahir').addEventListener('change', hitungUsia);

    // Fungsi untuk mendapatkan nama PT berdasarkan kode PT (form tambah dosen)
    function getNamaPTS() {
        const kode = document.getElementById('kode_pts_input').value.trim();
        const namaPtsDisplay = document.getElementById('nama_pts_display');
        if (kode.length === 0) {
            namaPtsDisplay.value = '';
            return;
        }

        fetch(`/admin/data-dosen/${kode}`)
            .then(response => {
                if (!response.ok) throw new Error('Data tidak ditemukan');
                return response.json();
            })
            .then(data => {
                if (data.nama_pts) {
                    namaPtsDisplay.value = data.nama_pts;
                } else {
                    namaPtsDisplay.value = 'Kode PT tidak ditemukan';
                }
            })
            .catch(() => {
                namaPtsDisplay.value = 'Kode PT tidak ditemukan';
            });
    }

    // Event Listener untuk perubahan kode PT (form tambah dosen)
    document.getElementById('kode_pts_input').addEventListener('change', getNamaPTS);

    // Ensure usia and pts hidden mirror are set before submit
    document.getElementById('dosenForm').addEventListener('submit', function (e) {
        try {
            // recompute usia
            hitungUsia();
            // mirror pts name into hidden input
            const nama = document.getElementById('nama_pts_display').value || '';
            const hidden = document.getElementById('pts_hidden');
            if (hidden) hidden.value = nama;
        } catch (ex) {}
    });

    // Auto-copy NIK => NUPTK (only if NUPTK is empty)
    try {
        const form = document.getElementById('dosenForm');
        const nikInput = form ? form.querySelector('input[name="nik"]') : null;
        const nuptkInput = form ? form.querySelector('input[name="nuptk"]') : null;
        if (nikInput && nuptkInput) {
            const sync = () => {
                const nikVal = (nikInput.value || '').trim();
                const nuptkVal = (nuptkInput.value || '').trim();
                if (nikVal !== '' && nuptkVal === '') {
                    nuptkInput.value = nikVal;
                }
            };
            nikInput.addEventListener('change', sync);
            nikInput.addEventListener('blur', sync);
        }
    } catch (e) {}

    // Fungsi mendapatkan nama PT untuk PTS Asal pada modal sinkronisasi
    function getNamaPTSAsal() {
        const kode = document.getElementById('kode_pts_asal_input').value.trim();
        const namaPtsDisplay = document.getElementById('nama_pts_asal_display');
        if (kode.length === 0) {
            namaPtsDisplay.value = '';
            return;
        }

        fetch(`/admin/data-dosen/${kode}`)
            .then(response => {
                if (!response.ok) throw new Error('Data tidak ditemukan');
                return response.json();
            })
            .then(data => {
                if (data.nama_pts) {
                    namaPtsDisplay.value = data.nama_pts;
                } else {
                    namaPtsDisplay.value = 'Kode PT tidak ditemukan';
                }
            })
            .catch(() => {
                namaPtsDisplay.value = 'Kode PT tidak ditemukan';
            });
    }

    // Fungsi mendapatkan nama PT untuk PTS Tujuan pada modal sinkronisasi
    function getNamaPTSTujuan() {
        const kode = document.getElementById('kode_pts_tujuan_input').value.trim();
        const namaPtsDisplay = document.getElementById('nama_pts_tujuan_display');
        if (kode.length === 0) {
            namaPtsDisplay.value = '';
            return;
        }

        fetch(`/admin/data-dosen/${kode}`)
            .then(response => {
                if (!response.ok) throw new Error('Data tidak ditemukan');
                return response.json();
            })
            .then(data => {
                if (data.nama_pts) {
                    namaPtsDisplay.value = data.nama_pts;
                } else {
                    namaPtsDisplay.value = 'Kode PT tidak ditemukan';
                }
            })
            .catch(() => {
                namaPtsDisplay.value = 'Kode PT tidak ditemukan';
            });
    }

    // Event listener untuk perubahan kode PT pada modal sinkronisasi
    document.getElementById('kode_pts_asal_input').addEventListener('change', getNamaPTSAsal);
    document.getElementById('kode_pts_tujuan_input').addEventListener('change', getNamaPTSTujuan);

    // Inisialisasi DataTable Yajra (server-side)
    const table = $('#dosenTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: false,
        paging: true,
        deferRender: true,
        dom: "<'md-toolbar'<'entries-wrap'l><'search-wrap'f>><'table-responsive text-nowrap't><'row dt-bottom-row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        ajax: {
            url: '{{ route("admin.data-dosen") }}'
        },
        columns: [{
                data: 'nidn',
                name: 'nidn',
                searchable: true,
                render: function(data, type, row) {
                    if(data && data !== '-') {
                        return '<a href="#" class="text-link-nidn">' + data + '</a>';
                    }
                    return data;
                }
            },
            {
                data: 'nuptk',
                name: 'nuptk'
            },
            {
                data: 'nama',
                name: 'nama'
            },
            {
                data: 'kode_pt',
                name: 'kode_pt'
            },
            {
                data: 'pts',
                name: 'pts'
            },
            {
                data: 'eligible_span',
                name: 'eligible_span',
                render: function(data, type, row) {
                    const val = (data || '').toString().toUpperCase();
                    if (val === 'YA' || val === 'Y' || val === '1') {
                        return '<span class="badge-ya">YA</span>';
                    } else if (val === 'TIDAK' || val === 'TDK' || val === 'N' || val === '0') {
                        return '<span class="badge-tidak">TIDAK</span>';
                    }
                    return data;
                }
            },
            {
                data: 'aksi',
                name: 'aksi',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return data;
                }
            }
        ],
        order: [
            [4, 'desc'],
            [2, 'asc']
        ],
        responsive: true,
        pagingType: 'simple_numbers',
        language: {
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "â†’",
                previous: "â†",
            },
            zeroRecords: "Data tidak ditemukan",
            infoEmpty: "Tidak ada data tersedia",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ entri",
            search: "Filter Data:",
            searchPlaceholder: "Cari data...",
            lengthMenu: "Show _MENU_ entries"
        },
    });

    // â”€â”€ Status Filter Buttons (Semua / Aktif / Tidak Aktif) â”€â”€
    // Filters by 'eligible_span' column (index 5) using regex exact match.
    // eligible_span has NO editColumn on server, so Yajra applies
    // SQL-level filtering directly â€” no controller changes needed.
    $('.md-status-btn').on('click', function() {
        $('.md-status-btn').removeClass('active');
        $(this).addClass('active');

        const status = $(this).data('status');
        const eligibleColIdx = 5; // index of 'eligible_span' column

        if (status === 'Aktif') {
            // Standard search for 'YA' (will use LIKE '%YA%')
            table.column(eligibleColIdx).search('YA').draw();
        } else if (status === 'Tidak Aktif') {
            // Standard search for 'TIDAK' (will use LIKE '%TIDAK%')
            table.column(eligibleColIdx).search('TIDAK').draw();
        } else {
            // Semua â€” clear column filter
            table.column(eligibleColIdx).search('').draw();
        }
    });

    // --- Logika Sinkronisasi Dosen antar PTS ---
    const btnCariDosenSinkron = document.getElementById('btnCariDosenSinkron');
    const tableDosenSinkronBody = document.querySelector('#tableDosenSinkron tbody');
    const checkAllSinkron = document.getElementById('checkAllSinkron');
    const formSinkronisasi = document.getElementById('formSinkronisasi');
    const searchDosenSinkronInput = document.getElementById('searchDosenSinkron');
    let tableSinkron = null;
    // Menyimpan pilihan NIDN secara global (semua halaman DataTable)
    let selectedNidnGlobal = new Set();

    if (btnCariDosenSinkron) {
        btnCariDosenSinkron.addEventListener('click', function() {
            const kodeAsal = document.getElementById('kode_pts_asal_input').value.trim();
            if (!kodeAsal) {
                Swal.fire('Perhatian', 'Silakan isi Kode PTS asal terlebih dahulu.', 'warning');
                return;
            }

            // --- Integrated reset before searching ---
            // Clear displayed PTS name (it will be re-fetched)
            const namaPtsDisplay = document.getElementById('nama_pts_asal_display');
            if (namaPtsDisplay) namaPtsDisplay.value = '';

            // show loading message in tbody
            tableDosenSinkronBody.innerHTML = '<tr><td colspan="7" class="text-center">Memuat data dosen...</td></tr>';

            // Safely destroy existing DataTable instance if present
            if (window.jQuery && $.fn && $.fn.DataTable && $.fn.DataTable.isDataTable('#tableDosenSinkron')) {
                try {
                    $('#tableDosenSinkron').off('draw.dt');
                    $('#tableDosenSinkron').DataTable().clear().destroy();
                } catch (e) {
                    // ignore
                }
                tableSinkron = null;
            }

            // Reset pilihan global ketika ambil data baru berdasarkan PTS asal
            selectedNidnGlobal = new Set();
            if (checkAllSinkron) {
                checkAllSinkron.checked = false;
                checkAllSinkron.indeterminate = false;
            }

            // Fetch new name and data
            getNamaPTSAsal();

            fetch("{{ route('admin.data-dosen.sync.search') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        kode_pts: kodeAsal
                    }),
                })
                .then(response => {
                    if (!response.ok) throw new Error('Gagal mengambil data');
                    return response.json();
                })
                .then(json => {
                    const data = json.data || [];
                    if (!data.length) {
                        tableDosenSinkronBody.innerHTML = '<tr><td colspan="8" class="text-center">Tidak ada data dosen untuk kode PTS tersebut.</td></tr>';
                        if (tableSinkron) {
                            tableSinkron.clear().draw();
                        }
                        return;
                    }

                    let rows = '';
                    data.forEach(row => {
                        const status = row.aktif == 1 || row.aktif === '1' || ['YA', 'Ya', 'ya', 'Y'].includes(row.aktif) ? 'Aktif' : 'Tidak Aktif';
                        const nuptkDisplay = row.nuptk && String(row.nuptk).trim() !== '' ? row.nuptk : '-';
                        const nidnDisplay = row.nidn && String(row.nidn).trim() !== '' ? row.nidn : '-';
                        // Use NIDN as the identifier when present; otherwise fall back to NUPTK
                        const identifier = (row.nidn && String(row.nidn).trim() !== '') ? row.nidn : (row.nuptk ? String(row.nuptk).trim() : '');
                        rows += `
                            <tr>
                                <td><input type="checkbox" class="form-check-input chk-dosen" value="${identifier}"></td>
                                <td>${nidnDisplay}</td>
                                <td class="text-center">${nuptkDisplay}</td>
                                <td>${row.nama}</td>
                                <td>${row.kode_pt}</td>
                                <td>${row.pts}</td>
                                <td>${status}</td>
                                <td>${row.eligible_span ?? ''}</td>
                            </tr>
                        `;
                    });
                    tableDosenSinkronBody.innerHTML = rows;
                    if (checkAllSinkron) {
                        checkAllSinkron.checked = false;
                        checkAllSinkron.indeterminate = false;
                    }

                            // Inisialisasi atau re-inisialisasi DataTable untuk tabel sinkronisasi
                            // Pastikan instance lama dihancurkan dengan benar sebelum membuat yang baru
                            if (window.jQuery && $.fn && $.fn.DataTable && $.fn.DataTable.isDataTable('#tableDosenSinkron')) {
                                try {
                                    $('#tableDosenSinkron').off('draw.dt');
                                    $('#tableDosenSinkron').DataTable().clear().destroy();
                                } catch (e) {
                                    // ignore errors during destroy
                                }
                                tableSinkron = null;
                            }

                                tableSinkron = $('#tableDosenSinkron').DataTable({
                        paging: true,
                        searching: true,
                            info: true,
                        lengthChange: true,
                        order: [[1, 'asc']], // urut berdasarkan NIDN
                        columnDefs: [
                            { orderable: false, searchable: false, targets: 0 }, // disable sorting/search on checkbox column
                        ],
                        language: {
                            paginate: {
                                first: "Awal",
                                last: "Akhir",
                                next: "â†’",
                                previous: "â†",
                            },
                            zeroRecords: "Tidak ada data dosen",
                            infoEmpty: "Tidak ada data tersedia",
                               info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                            search: "Filter:",
                        },
                    });

                    // Saat halaman DataTable berganti, sinkronkan checkbox dengan selectedNidnGlobal
                    // Gunakan namespaced event untuk memudahkan unbind saat destroy
                    $('#tableDosenSinkron').off('draw.dt').on('draw.dt', function() {
                        $('#tableDosenSinkron .chk-dosen').each(function() {
                            const nidn = String(this.value);
                            this.checked = selectedNidnGlobal.has(nidn);
                        });

                        // Update status checkbox "select all" (checked / indeterminate)
                        if (checkAllSinkron) {
                            const totalRows = tableSinkron.rows().nodes().length;
                            let selectedCount = 0;
                            tableSinkron.rows().nodes().each(function(row) {
                                const cb = row.querySelector('.chk-dosen');
                                if (cb && selectedNidnGlobal.has(String(cb.value))) {
                                    selectedCount++;
                                }
                            });

                            checkAllSinkron.checked = totalRows > 0 && selectedCount === totalRows;
                            checkAllSinkron.indeterminate = selectedCount > 0 && selectedCount < totalRows;
                        }
                    });
                })
                .catch(() => {
                    tableDosenSinkronBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Terjadi kesalahan saat mengambil data dosen.</td></tr>';
                });
        });
    }

    

    if (checkAllSinkron) {
        checkAllSinkron.addEventListener('change', function() {
            if (!tableSinkron) {
                return;
            }

            // Ambil semua baris (seluruh pagination) dari DataTable
            const allRows = tableSinkron.rows().nodes();
            selectedNidnGlobal = new Set();

            allRows.each(function(row) {
                const cb = row.querySelector('.chk-dosen');
                if (!cb) return;
                cb.checked = checkAllSinkron.checked;
                const nidn = String(cb.value);
                if (checkAllSinkron.checked) {
                    selectedNidnGlobal.add(nidn);
                }
            });

            // Jika tidak dicentang, pastikan state global kosong dan indeterminate direset
            if (!checkAllSinkron.checked) {
                checkAllSinkron.indeterminate = false;
            }
        });
    }

    // Handler untuk checkbox per baris (menggunakan event delegation)
    $('#tableDosenSinkron tbody').on('change', '.chk-dosen', function() {
        const nidn = String(this.value);
        if (this.checked) {
            selectedNidnGlobal.add(nidn);
        } else {
            selectedNidnGlobal.delete(nidn);
        }

        // Update status select-all (checked / indeterminate)
        if (tableSinkron && checkAllSinkron) {
            const totalRows = tableSinkron.rows().nodes().length;
            let selectedCount = 0;
            tableSinkron.rows().nodes().each(function(row) {
                const cb = row.querySelector('.chk-dosen');
                if (cb && selectedNidnGlobal.has(String(cb.value))) {
                    selectedCount++;
                }
            });

            checkAllSinkron.checked = totalRows > 0 && selectedCount === totalRows;
            checkAllSinkron.indeterminate = selectedCount > 0 && selectedCount < totalRows;
        }
    });

    // Pencarian custom untuk tabel sinkronisasi menggunakan input di atas tabel
    if (searchDosenSinkronInput) {
        searchDosenSinkronInput.addEventListener('keyup', function() {
            if (tableSinkron) {
                tableSinkron.search(this.value).draw();
            }
        });
    }

    if (formSinkronisasi) {
        formSinkronisasi.addEventListener('submit', function(e) {
            e.preventDefault();

            const kodeAsal = document.getElementById('kode_pts_asal_input').value.trim();
            const kodeTujuan = document.getElementById('kode_pts_tujuan_input').value.trim();
            const selected = Array.from(selectedNidnGlobal);

            if (!kodeAsal || !kodeTujuan) {
                Swal.fire('Perhatian', 'Kode PTS asal dan tujuan wajib diisi.', 'warning');
                return;
            }

            if (kodeAsal === kodeTujuan) {
                Swal.fire('Perhatian', 'Kode PTS asal dan tujuan tidak boleh sama.', 'warning');
                return;
            }

            if (!selected.length) {
                Swal.fire('Perhatian', 'Silakan pilih minimal satu dosen yang akan dipindahkan.', 'warning');
                return;
            }

            // Tutup modal sinkronisasi terlebih dahulu, baru tampilkan konfirmasi
            const modalEl = document.getElementById('modalSinkronisasi');
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if (modalInstance) {
                modalInstance.hide();
            }

            setTimeout(() => {
                Swal.fire({
                    title: 'Konfirmasi',
                    text: `Yakin ingin memindahkan ${selected.length} dosen ke PTS tujuan?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Proses',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-primary me-2',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (!result.isConfirmed) return;

                    Swal.fire({
                        title: 'Memproses...',
                        html: '<div class="spinner-border text-success" role="status"></div><div class="mt-2">Sedang memindahkan data dosen...</div>',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    });

                    fetch("{{ route('admin.data-dosen.sync.proses') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                kode_pts_asal: kodeAsal,
                                kode_pts_tujuan: kodeTujuan,
                                nidn: selected,
                            }),
                        })
                        .then(response => response.json())
                        .then(json => {
                            Swal.close();
                            if (json.status === 'success') {
                                Swal.fire('Berhasil', json.message || 'Data dosen berhasil dipindahkan.', 'success');

                                table.ajax.reload();
                            } else {
                                Swal.fire('Gagal', json.message || 'Gagal memproses sinkronisasi.', 'error');
                            }
                        })
                        .catch(() => {
                            Swal.close();
                            Swal.fire('Gagal', 'Terjadi kesalahan pada server saat memproses sinkronisasi.', 'error');
                        });
                });
            }, 300);
        });
    }

});
</script>
@endsection

