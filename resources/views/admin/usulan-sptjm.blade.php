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
    .status-card:hover {
        transform: translateY(-2px);
        border-color: #1a56db !important;
        box-shadow: 0 10px 25px rgba(26, 86, 219, 0.1) !important;
    }
</style>

<div class="content-wrapper">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1" style="color: #0f2b5c !important; font-size: 1.5rem;">Usulan SPTJM</h4>
            <p class="text-muted mb-0" style="font-size: 0.875rem;">Kelola dan pantau seluruh pengajuan SPTJM (Surat Pernyataan Tanggung Jawab Mutlak).</p>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card card-sptjm mb-4">
        <div class="card-body p-4">
            <form id="filterForm" method="POST">
                @csrf
                <div class="row align-items-end g-3">
                    <!-- Pilih Tipe SPTJM -->
                    <div class="col-lg-4 col-md-5">
                        <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="pilihsptjm">Pilih Tipe SPTJM</label>
                        <select id="pilihsptjm" class="form-select" name="pilihsptjm" style="border-color: #cbd5e1;">
                            <option value="SPTJM Berjalan">SPTJM Berjalan</option>
                            <option value="SPTJM Susulan">SPTJM Susulan</option>
                            <option value="TUKIN Berjalan">TUKIN Berjalan</option>
                            <option value="TUKIN Susulan">TUKIN Susulan</option>
                        </select>
                    </div>

                    <!-- Pilih Bulan -->
                    <div class="col-lg-3 col-md-4">
                        <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="selectTypeOptBulan">Bulan</label>
                        <select name="bulan" id="selectTypeOptBulan" class="form-select" style="border-color: #cbd5e1;">
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
                        <div class="w-100" style="max-width: 320px;">
                            <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Search</label>
                            <div class="input-group">
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
    <div class="row row-cols-1 row-cols-md-5 g-3 mb-4">
        <!-- Card Usulan -->
        <div class="col">
            <div class="card status-card status-btn cursor-pointer p-3" data-status="Usulan" style="border-radius: 12px; border: 1.5px solid #dbeafe; box-shadow: 0 4px 12px rgba(26, 86, 219, 0.05); background: white; transition: all 0.25s ease;">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 10px; background-color: #eef2f6;">
                        <i class="bx bx-folder" style="font-size: 1.35rem; color: #475569;"></i>
                    </div>
                    <div>
                        <span class="d-block mb-1 text-uppercase fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 0.05em;">Usulan</span>
                        <h4 class="card-title mb-0 fw-bold" style="color: #0f2b5c;">{{ $countUsulan }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Validasi -->
        <div class="col">
            <div class="card status-card status-btn cursor-pointer p-3" data-status="Validasi" style="border-radius: 12px; border: 1.5px solid #dbeafe; box-shadow: 0 4px 12px rgba(26, 86, 219, 0.05); background: white; transition: all 0.25s ease;">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 10px; background-color: #fff8eb;">
                        <i class="bx bx-hourglass" style="font-size: 1.35rem; color: #f59e0b;"></i>
                    </div>
                    <div>
                        <span class="d-block mb-1 text-uppercase fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 0.05em;">Validasi</span>
                        <h4 class="card-title mb-0 fw-bold" style="color: #0f2b5c;">{{ $countValidasi }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Proses -->
        <div class="col">
            <div class="card status-card status-btn cursor-pointer p-3" data-status="Proses" style="border-radius: 12px; border: 1.5px solid #dbeafe; box-shadow: 0 4px 12px rgba(26, 86, 219, 0.05); background: white; transition: all 0.25s ease;">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 10px; background-color: #eff6ff;">
                        <i class="bx bx-sync" style="font-size: 1.35rem; color: #3b82f6;"></i>
                    </div>
                    <div>
                        <span class="d-block mb-1 text-uppercase fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 0.05em;">Proses</span>
                        <h4 class="card-title mb-0 fw-bold" style="color: #0f2b5c;">{{ $countProses }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Selesai -->
        <div class="col">
            <div class="card status-card status-btn cursor-pointer p-3" data-status="Selesai" style="border-radius: 12px; border: 1.5px solid #dbeafe; box-shadow: 0 4px 12px rgba(26, 86, 219, 0.05); background: white; transition: all 0.25s ease;">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 10px; background-color: #ecfdf5;">
                        <i class="bx bx-check-circle" style="font-size: 1.35rem; color: #10b981;"></i>
                    </div>
                    <div>
                        <span class="d-block mb-1 text-uppercase fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 0.05em;">Selesai</span>
                        <h4 class="card-title mb-0 fw-bold" style="color: #0f2b5c;">{{ $countSelesai }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Tolak -->
        <div class="col">
            <div class="card status-card status-btn cursor-pointer p-3" data-status="Tolak" style="border-radius: 12px; border: 1.5px solid #dbeafe; box-shadow: 0 4px 12px rgba(26, 86, 219, 0.05); background: white; transition: all 0.25s ease;">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 10px; background-color: #fef2f2;">
                        <i class="bx bx-x-circle" style="font-size: 1.35rem; color: #ef4444;"></i>
                    </div>
                    <div>
                        <span class="d-block mb-1 text-uppercase fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 0.05em;">Ditolak</span>
                        <h4 class="card-title mb-0 fw-bold" style="color: #0f2b5c;">{{ $countTolak }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card card-sptjm mb-4">
        <div class="card-body p-4">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover md2-table" id="dataTable" style="width:100%; margin-bottom: 0 !important;">
                    <thead>
                        <tr>
                            <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">ID Usulan</th>
                            <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Tahun</th>
                            <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Kode PT</th>
                            <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Nama PT</th>
                            <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Bulan</th>
                            <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Nama Penandatangan</th>
                            <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Jabatan</th>
                            <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Wilayah</th>
                            <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">File</th>
                            <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Keterangan</th>
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
        pageLength: 10,
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
