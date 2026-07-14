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
    .status-card { transition: all 0.25s ease; cursor: pointer; margin-bottom: 0 !important; }
    .status-card:hover {
        transform: translateY(-2px);
        border-color: #1a56db !important;
        box-shadow: 0 10px 25px rgba(26, 86, 219, 0.1) !important;
    }
</style>

{{-- Page Header --}}
<div class="md2-page-header">
    <div class="page-titles">
        <h3>Usulan SPTJM</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Usulan</a></li>
                <li class="breadcrumb-item active">Usulan SPTJM</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md2-card mb-4">
    <div class="card-body px-4 pb-4 pt-0">

        <!-- Filters Section -->
        <div class="pt-3 pb-3 mb-3 border-bottom">
            <form id="filterForm" method="POST">
                @csrf
                <div class="row align-items-end g-3">
                    <!-- Pilih Tipe SPTJM -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;" for="pilihsptjm">Pilih Tipe SPTJM</label>
                        <select id="pilihsptjm" class="form-select form-select-sm" name="pilihsptjm" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
                            <option value="SPTJM Berjalan">SPTJM Berjalan</option>
                            <option value="SPTJM Susulan">SPTJM Susulan</option>
                            <option value="TUKIN Berjalan">TUKIN Berjalan</option>
                            <option value="TUKIN Susulan">TUKIN Susulan</option>
                        </select>
                    </div>

                    <!-- Pilih Bulan -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;" for="selectTypeOptBulan">Bulan</label>
                        <select name="bulan" id="selectTypeOptBulan" class="form-select form-select-sm" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
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
                </div>
            </form>
        </div>

        <!-- Cards Stats / Status Buttons -->
        <div class="row g-2 mb-4">
            <!-- Card Usulan -->
            <div class="col">
                <div class="card status-card status-btn p-2" data-status="Usulan" style="border-radius: 10px; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.02); background: white; cursor: pointer;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; border-radius: 8px; background-color: #f1f5f9;">
                            <i class="bx bx-folder" style="font-size: 1.1rem; color: #475569;"></i>
                        </div>
                        <div>
                            <span class="status-title d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em; line-height: 1.2; font-weight: 800; color: #0f172a;">Usulan</span>
                            <span class="status-count fw-bold text-dark" style="font-size: 1.35rem; line-height: 1.2; font-weight: 900 !important;">{{ $countUsulan }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card Validasi -->
            <div class="col">
                <div class="card status-card status-btn p-2" data-status="Validasi" style="border-radius: 10px; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.02); background: white; cursor: pointer;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; border-radius: 8px; background-color: #fff8eb;">
                            <i class="bx bx-hourglass" style="font-size: 1.1rem; color: #d97706;"></i>
                        </div>
                        <div>
                            <span class="status-title d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em; line-height: 1.2; font-weight: 800; color: #0f172a;">Validasi</span>
                            <span class="status-count fw-bold text-dark" style="font-size: 1.35rem; line-height: 1.2; font-weight: 900 !important;">{{ $countValidasi }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card Proses -->
            <div class="col">
                <div class="card status-card status-btn p-2" data-status="Proses" style="border-radius: 10px; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.02); background: white; cursor: pointer;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; border-radius: 8px; background-color: #eff6ff;">
                            <i class="bx bx-sync" style="font-size: 1.1rem; color: #2563eb;"></i>
                        </div>
                        <div>
                            <span class="status-title d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em; line-height: 1.2; font-weight: 800; color: #0f172a;">Proses</span>
                            <span class="status-count fw-bold text-dark" style="font-size: 1.35rem; line-height: 1.2; font-weight: 900 !important;">{{ $countProses }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card Selesai -->
            <div class="col">
                <div class="card status-card status-btn p-2" data-status="Selesai" style="border-radius: 10px; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.02); background: white; cursor: pointer;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; border-radius: 8px; background-color: #f0fdf4;">
                            <i class="bx bx-check-circle" style="font-size: 1.1rem; color: #16a34a;"></i>
                        </div>
                        <div>
                            <span class="status-title d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em; line-height: 1.2; font-weight: 800; color: #0f172a;">Selesai</span>
                            <span class="status-count fw-bold text-dark" style="font-size: 1.35rem; line-height: 1.2; font-weight: 900 !important;">{{ $countSelesai }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card Tolak -->
            <div class="col">
                <div class="card status-card status-btn p-2" data-status="Tolak" style="border-radius: 10px; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.02); background: white; cursor: pointer;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; border-radius: 8px; background-color: #fef2f2;">
                            <i class="bx bx-x-circle" style="font-size: 1.1rem; color: #dc2626;"></i>
                        </div>
                        <div>
                            <span class="status-title d-block text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.05em; line-height: 1.2; font-weight: 800; color: #0f172a;">Ditolak</span>
                            <span class="status-count fw-bold text-dark" style="font-size: 1.35rem; line-height: 1.2; font-weight: 900 !important;">{{ $countTolak }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="mb-4">
            <table class="table table-hover md2-table text-center" id="dataTable" style="width:100%;">
                <thead>
                    <tr>
                        <th>ID Usulan</th>
                        <th>Tahun</th>
                        <th>Kode PT</th>
                        <th class="text-start">Nama PT</th>
                        <th>Bulan</th>
                        <th class="text-start">Nama Penandatangan</th>
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
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"table-responsive text-nowrap"t><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        order: [[0, 'desc']],
        columns: [
            { data: 'id_usulan' },
            { data: 'tahun' },
            { data: 'kode_pts' },
            { data: 'nama_pts', className: 'text-start' },
            { data: 'bulan' },
            { data: 'nama', className: 'text-start' },
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
            loadData(status);
        });
    });

    // Color configurations for the status cards when active
    const activeConfigs = {
        'Usulan': { border: '#475569', bg: '#f1f5f9', shadow: 'rgba(71, 85, 105, 0.25)', text: '#475569' },
        'Validasi': { border: '#d97706', bg: '#fef3c7', shadow: 'rgba(217, 119, 6, 0.3)', text: '#b45309' },
        'Proses': { border: '#2563eb', bg: '#dbeafe', shadow: 'rgba(37, 99, 235, 0.3)', text: '#1d4ed8' },
        'Selesai': { border: '#16a34a', bg: '#d1fae5', shadow: 'rgba(22, 163, 74, 0.3)', text: '#065f46' },
        'Tolak': { border: '#dc2626', bg: '#fee2e2', shadow: 'rgba(220, 38, 38, 0.3)', text: '#991b1b' }
    };

    let activeStatus = 'Usulan';

    function loadData(status) {
        activeStatus = status;
        const tipeSptjm = pilihSptjm.value;
        const bulan = bulanSelect.value;

        // Reset all status cards styling
        statusButtons.forEach(btn => {
            btn.style.setProperty('border-color', '#cbd5e1', 'important');
            btn.style.setProperty('background-color', '#ffffff', 'important');
            btn.style.setProperty('box-shadow', '0 2px 8px rgba(0,0,0,0.02)', 'important');
            const title = btn.querySelector('.status-title');
            if (title) {
                title.classList.remove('text-muted');
                title.style.setProperty('color', '#0f172a', 'important');
            }
        });


        // Highlight selected card dynamically
        const activeCard = document.querySelector(`.status-btn[data-status="${status}"]`);
        if (activeCard) {
            const cfg = activeConfigs[status] || activeConfigs['Usulan'];
            activeCard.style.setProperty('border-color', cfg.border, 'important');
            activeCard.style.setProperty('background-color', cfg.bg, 'important');
            activeCard.style.setProperty('box-shadow', `0 10px 25px ${cfg.shadow}`, 'important');
            const title = activeCard.querySelector('.status-title');
            if (title) {
                title.classList.remove('text-muted');
                title.style.setProperty('color', cfg.text, 'important');
            }
        }

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

                // Update status card counts in real time
                if (data.success && data.counts) {
                    for (const [key, val] of Object.entries(data.counts)) {
                        const countSpan = document.querySelector(`.status-btn[data-status="${key}"] .status-count`);
                        if (countSpan) {
                            countSpan.textContent = val;
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan saat mengambil data.',
                });
            });
    }

    // Auto-reload data when dropdown filter changes
    pilihSptjm.addEventListener('change', function() {
        loadData(activeStatus);
    });

    bulanSelect.addEventListener('change', function() {
        loadData(activeStatus);
    });

    // Auto-load the initial "Usulan" data on mount
    loadData('Usulan');
});
</script>
@endsection
