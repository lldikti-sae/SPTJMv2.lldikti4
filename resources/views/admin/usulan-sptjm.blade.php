@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
    $currentYear = session('tahun') ?? date('Y');
    $countUsulan = DB::table('q_sptjm')->where('tahun', $currentYear)->where('status', 'Usulan')->count();
    $countValidasi = DB::table('q_sptjm')->where('tahun', $currentYear)->where('status', 'Validasi')->count();
    $countProses = DB::table('q_sptjm')->where('tahun', $currentYear)->where('status', 'Proses')->count();
    $countSelesai = DB::table('q_sptjm')->where('tahun', $currentYear)->where('status', 'Selesai')->count();
    $countTolak = DB::table('q_sptjm')->where('tahun', $currentYear)->where('status', 'Tolak')->count();
@endphp

<style>
    .card-sptjm {
        border: 1.5px solid #dbeafe !important;
        box-shadow: 0 10px 30px rgba(26, 86, 219, 0.15) !important;
        border-radius: 12px !important;
        background: #ffffff !important;
    }
    .status-card { transition: all 0.25s ease; cursor: pointer; }
    .status-card:hover {
        transform: translateY(-2px);
        border-color: #1a56db !important;
        box-shadow: 0 10px 25px rgba(26, 86, 219, 0.1) !important;
    }
</style>

{{-- Page Header (uses global md-page-header) --}}
<div class="md-page-header">
    <div class="page-titles">
        <h1>Usulan SPTJM</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Usulan</a></li>
                <li class="breadcrumb-item active">Usulan SPTJM</li>
            </ol>
        </nav>
    </div>
</div>

<div class="content-wrapper" style="padding: 0;">

    <!-- Filters Card -->
    <div class="card card-sptjm" style="margin-bottom: 12px;">
        <div class="card-body" style="padding: 14px 20px;">
            <form id="filterForm" method="POST">
                @csrf
                <div class="row align-items-end g-2">
                    <!-- Pilih Tipe SPTJM -->
                    <div class="col-lg-4 col-md-5">
                        <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em; margin-bottom: 2px;" for="pilihsptjm">Pilih Tipe SPTJM</label>
                        <select id="pilihsptjm" class="form-select form-select-sm" name="pilihsptjm" style="border-color: #cbd5e1;">
                            <option value="SPTJM Berjalan">SPTJM Berjalan</option>
                            <option value="SPTJM Susulan">SPTJM Susulan</option>
                            <option value="TUKIN Berjalan">TUKIN Berjalan</option>
                            <option value="TUKIN Susulan">TUKIN Susulan</option>
                        </select>
                    </div>

                    <!-- Pilih Bulan -->
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em; margin-bottom: 2px;" for="selectTypeOptBulan">Bulan</label>
                        <select name="bulan" id="selectTypeOptBulan" class="form-select form-select-sm" style="border-color: #cbd5e1;">
                            <option value="All">All</option>
                            <option value="Januari">Januari</option>
                            <option value="Februari">Februari</option>
                            <option value="Maret">Maret</option>
                            <option value="April">April</option>
                            <option value="Mei">Mei</option>
                            <option value="Juni">Juni</option>
                            <option value="Juli">Juli</option>
                            <option value="Agustus">Agustus</option>
                            <option value="September">September</option>
                            <option value="Oktober">Oktober</option>
                            <option value="November">November</option>
                            <option value="Desember">Desember</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="col-lg-5 col-md-3 d-flex justify-content-md-end">
                        <div class="w-100" style="max-width: 300px;">
                            <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em; margin-bottom: 2px;">Search</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" style="border-color: #cbd5e1; background: #f8fafc;"><i class="bx bx-search"></i></span>
                                <input type="search" class="form-control" id="searchInput" placeholder="Search..." style="border-color: #cbd5e1;">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cards Stats & Status Buttons -->
   <div class="row g-2 mb-3">
        <!-- Card Usulan -->
        <div class="col">
            <div class="card status-card status-btn p-2" data-status="Usulan" style="border-radius: 10px; border: 1.5px solid #dbeafe; box-shadow: 0 2px 8px rgba(26, 86, 219, 0.05); background: white;">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;" border-radius: 8px; background-color: #eef2f6;">
                        <i class="bx bx-folder" style="font-size: 1.1rem; color: #475569;"></i>
                    </div>
                    <div>
                        <span class="d-block text-uppercase fw-bold text-muted" style="font-size: 0.6rem; letter-spacing: 0.04em; line-height: 1.2;">Usulan</span>
                        <span class="fw-bold" style="color: #0f2b5c; font-size: 1rem; line-height: 1.2;">{{ $countUsulan }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Validasi -->
        <div class="col">
            <div class="card status-card status-btn p-2" data-status="Validasi" style="border-radius: 10px; border: 1.5px solid #dbeafe; box-shadow: 0 2px 8px rgba(26, 86, 219, 0.05); background: white;">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;" border-radius: 8px; background-color: #fff8eb;">
                        <i class="bx bx-hourglass" style="font-size: 1.1rem; color: #f59e0b;"></i>
                    </div>
                    <div>
                        <span class="d-block text-uppercase fw-bold text-muted" style="font-size: 0.6rem; letter-spacing: 0.04em; line-height: 1.2;">Validasi</span>
                        <span class="fw-bold" style="color: #0f2b5c; font-size: 1rem; line-height: 1.2;">{{ $countValidasi }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Proses -->
       <div class="col">
            <div class="card status-card status-btn p-2" data-status="Proses" style="border-radius: 10px; border: 1.5px solid #dbeafe; box-shadow: 0 2px 8px rgba(26, 86, 219, 0.05); background: white;">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;" border-radius: 8px; background-color: #eff6ff;">
                        <i class="bx bx-sync" style="font-size: 1.1rem; color: #3b82f6;"></i>
                    </div>
                    <div>
                        <span class="d-block text-uppercase fw-bold text-muted" style="font-size: 0.6rem; letter-spacing: 0.04em; line-height: 1.2;">Proses</span>
                        <span class="fw-bold" style="color: #0f2b5c; font-size: 1rem; line-height: 1.2;">{{ $countProses }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Selesai -->
        <div class="col">
            <div class="card status-card status-btn p-2" data-status="Selesai" style="border-radius: 10px; border: 1.5px solid #dbeafe; box-shadow: 0 2px 8px rgba(26, 86, 219, 0.05); background: white;">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;" border-radius: 8px; background-color: #ecfdf5;">
                        <i class="bx bx-check-circle" style="font-size: 1.1rem; color: #10b981;"></i>
                    </div>
                    <div>
                        <span class="d-block text-uppercase fw-bold text-muted" style="font-size: 0.6rem; letter-spacing: 0.04em; line-height: 1.2;">Selesai</span>
                        <span class="fw-bold" style="color: #0f2b5c; font-size: 1rem; line-height: 1.2;">{{ $countSelesai }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Tolak -->
       <div class="col">
            <div class="card status-card status-btn p-2" data-status="Tolak" style="border-radius: 10px; border: 1.5px solid #dbeafe; box-shadow: 0 2px 8px rgba(26, 86, 219, 0.05); background: white;">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;" border-radius: 8px; background-color: #fef2f2;">
                        <i class="bx bx-x-circle" style="font-size: 1.1rem; color: #ef4444;"></i>
                    </div>
                    <div>
                        <span class="d-block text-uppercase fw-bold text-muted" style="font-size: 0.6rem; letter-spacing: 0.04em; line-height: 1.2;">Ditolak</span>
                        <span class="fw-bold" style="color: #0f2b5c; font-size: 1rem; line-height: 1.2;">{{ $countTolak }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card card-sptjm">
        <div class="card-body" style="padding: 14px 20px 16px;">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover" id="dataTable" style="width:100%; margin-bottom: 0 !important;">
                    <thead>
                        <tr>
                            <th>ID Usulan</th>
                            <th>Tahun</th>
                            <th>Kode PT</th>
                            <th>Nama PT</th>
                            <th>Bulan</th>
                            <th>Nama Penandatangan</th>
                            <th>Jabatan</th>
                            <th>Wilayah</th>
                            <th>File</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pilihSptjm = document.getElementById('pilihsptjm');
    const bulanSelect = document.getElementById('selectTypeOptBulan');
    const statusButtons = document.querySelectorAll('.status-btn');

    // Init DataTable (client-side) with pagination
    const dt = $('#dataTable').DataTable({
        processing: true,
        serverSide: false,
        paging: true,
        lengthChange: true,
        searching: true,
        responsive: true,
        order: [[0, 'desc']],
        columns: [
            { data: 'id_usulan' },
            { data: 'tahun' },
            { data: 'kode_pts' },
            { data: 'nama_pts' },
            { data: 'bulan' },
            { data: 'nama' },
            { data: 'jabatan' },
            { data: 'wilayah' },
            { data: 'file', render: function(d, type, row){
                if(!d) return '-';
                var idStr = (row.id_usulan || '').toString().trim();
                var up = idStr.toUpperCase();
                var prefix = '';
                if(up.startsWith('BT')) prefix = 'BT';
                else if(up.startsWith('ST')) prefix = 'ST';
                else if(up.startsWith('B')) prefix = 'B';
                else if(up.startsWith('S')) prefix = 'S';

                var folder = '';
                if(prefix === 'B') folder = 'uploadFile_SPTJM_B';
                else if(prefix === 'S') folder = 'uploadFile_SPTJM_S';
                else if(prefix === 'BT') folder = 'uploadFile_TUKIN_B';
                else if(prefix === 'ST') folder = 'uploadFile_TUKIN_S';

                var filePath = d.toString();
                // normalize: remove leading storage/ or /storage/
                filePath = filePath.replace(/^\/storage\//i, '').replace(/^storage\//i, '').replace(/^\//, '');
                // remove unwanted internal folder 'sptjm_susulan/' if present
                filePath = filePath.replace(/^sptjm_susulan\//i, '');

                var lower = filePath.toLowerCase();
                var folderLower = (folder || '').toLowerCase();
                var href = '';
                if(folder && lower.startsWith(folderLower + '/')) {
                    href = '/storage/' + filePath; // already contains folder
                } else if(folder) {
                    href = '/storage/' + folder + '/' + filePath; // prepend folder
                } else {
                    href = '/storage/' + filePath; // no folder determined
                }

                return `<a href="${href}" target="_blank"><i class="bx bx-file"></i> Lihat Dokumen</a>`;
            }},
            { data: 'status', defaultContent: 'N/A' }
        ],
        data: []
    });

    statusButtons.forEach(button => {
        button.addEventListener('click', function() {
            const status = this.getAttribute('data-status');
            const tipeSptjm = pilihSptjm.value;
            const bulan = bulanSelect.value;

            // Reset all status cards styling
            statusButtons.forEach(btn => {
                btn.style.setProperty('border-color', '#dbeafe', 'important');
                btn.style.setProperty('background-color', '#ffffff', 'important');
                btn.style.setProperty('box-shadow', '0 4px 12px rgba(26, 86, 219, 0.05)', 'important');
            });

            // Highlight selected card
            this.style.setProperty('border-color', '#1a56db', 'important');
            this.style.setProperty('background-color', '#f0f5ff', 'important');
            this.style.setProperty('box-shadow', '0 10px 25px rgba(26, 86, 219, 0.15)', 'important');

            Swal.fire({
                title: 'Mohon tunggu...',
                html: `
                      <div class="d-flex justify-content-center align-items-center flex-column">
                          <div class="spinner-border spinner-border-lg text-primary" role="status">
                              <span class="visually-hidden">Loading...</span>
                          </div>
                          <div class="mt-2">Sedang mencari data</div>
                      </div>
                  `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                backdrop: true
            });

            fetch("{{ route('admin.usulan-sptjm.data') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        pilihsptjm: tipeSptjm,
                        bulan: bulan,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    // Populate DataTable with the returned data
                    dt.clear();
                    if (data.success && Array.isArray(data.data)) {
                        dt.rows.add(data.data);
                    }
                    dt.draw();
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat mengambil data.',
                    });
                });
        });
    });

    // Fitur Pencarian
    document.getElementById("searchInput").addEventListener("keyup", function() {
        $('#dataTable').DataTable().search(this.value).draw();
    });

    // User clicks a status card to load data
});
</script>
@endsection
