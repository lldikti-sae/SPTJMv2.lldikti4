@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
    $currentYear = session('tahun') ?? date('Y');
    $emailPIC = auth()->user()->email;
    $countUsulan = DB::table('q_sptjm')->where('tahun', $currentYear)->where('status', 'Usulan')->where('wilayah', $emailPIC)->count();
    $countValidasi = DB::table('q_sptjm')->where('tahun', $currentYear)->where('status', 'Validasi')->where('wilayah', $emailPIC)->count();
    $countProses = DB::table('q_sptjm')->where('tahun', $currentYear)->where('status', 'Proses')->where('wilayah', $emailPIC)->count();
    $countSelesai = DB::table('q_sptjm')->where('tahun', $currentYear)->where('status', 'Selesai')->where('wilayah', $emailPIC)->count();
    $countTolak = DB::table('q_sptjm')->where('tahun', $currentYear)->where('status', 'Tolak')->where('wilayah', $emailPIC)->count();
@endphp

<style>
    .status-card { transition: all 0.25s ease; cursor: pointer; margin-bottom: 0 !important; }
    .status-card:hover {
        transform: translateY(-2px);
        border-color: #1a56db !important;
        box-shadow: 0 10px 25px rgba(26, 86, 219, 0.1) !important;
    }
</style>

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h3>Validasi Usulan</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Validasi</a></li>
                <li class="breadcrumb-item active">Validasi Usulan SPTJM</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md-card mb-4">
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
                            <option value="B%">SPTJM Berjalan</option>
                            <option value="S%">SPTJM Susulan</option>
                            <option value="BT%">TUKIN Berjalan</option>
                            <option value="ST%">TUKIN Susulan</option>
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
        <div class="table-responsive text-nowrap mt-4">
            <table class="table table-hover" id="dataTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th>ID Usulan</th>
                        <th>Tahun</th>
                        <th>Tanggal Usulan</th>
                        <th>Bulan</th>
                        <th>Kode PT</th>
                        <th>Nama PT</th>
                        <th>Nama Penandatangan</th>
                        <th>Jabatan</th>
                        <th>File</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
  @if(session('success'))
  Swal.fire({
    title: "{{ session('success') }}",
    icon: "success",
    draggable: false
  });
  @endif
  @if(session('error'))
  Swal.fire({
    title: "{{ session('error') }}",
    icon: "error",
    draggable: false
  });
  @endif

  $(document).ready(function() {
    const storageBaseUrl = "{{ asset('storage') }}";
    let currentStatus = 'Usulan';

    // expose status so global handlers (outside ready scope) can access it
    window.picValidasiUsulanCurrentStatus = currentStatus;

    // restore saved filters (persist between navigation)
    try {
      const savedPilih = localStorage.getItem('pic.validasi-usulan.pilihsptjm');
      const savedBulan = localStorage.getItem('pic.validasi-usulan.bulan');
      const savedStatus = localStorage.getItem('pic.validasi-usulan.status');
      if (savedPilih) {
        $('#pilihsptjm').val(savedPilih);
      }
      if (savedBulan) {
        $('#selectTypeOptBulan').val(savedBulan);
      }
      if (savedStatus) {
        currentStatus = savedStatus;
      }
    } catch (e) {
      console.warn('Could not read saved filters', e);
    }

    // Color configurations for the status cards when active
    const activeConfigs = {
        'Usulan': { border: '#475569', bg: '#f1f5f9', shadow: 'rgba(71, 85, 105, 0.25)', text: '#475569' },
        'Validasi': { border: '#d97706', bg: '#fef3c7', shadow: 'rgba(217, 119, 6, 0.3)', text: '#b45309' },
        'Proses': { border: '#2563eb', bg: '#dbeafe', shadow: 'rgba(37, 99, 235, 0.3)', text: '#1d4ed8' },
        'Selesai': { border: '#16a34a', bg: '#d1fae5', shadow: 'rgba(22, 163, 74, 0.3)', text: '#065f46' },
        'Tolak': { border: '#dc2626', bg: '#fee2e2', shadow: 'rgba(220, 38, 38, 0.3)', text: '#991b1b' }
    };

    // helper to toggle outline <-> solid button classes based on selection
    function updateStatusButtonsUI(status) {
      // Reset all status cards styling
      $('.status-btn').each(function() {
          this.style.setProperty('border-color', '#cbd5e1', 'important');
          this.style.setProperty('background-color', '#ffffff', 'important');
          this.style.setProperty('box-shadow', '0 2px 8px rgba(0,0,0,0.02)', 'important');
          const title = this.querySelector('.status-title');
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
    }

    // Initialize DataTable with AJAX source
    const table = $('#dataTable').DataTable({
      processing: true,
      serverSide: false,
      dom: "<'md-toolbar'<'entries-wrap'l><'search-wrap'f>><'table-responsive text-nowrap't><'row dt-bottom-row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
      ajax: {
        url: "{{ route('pic.validasi-usulan.data') }}",
        type: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        data: function(d) {
          d.pilihsptjm = $('#pilihsptjm').val();
          d.bulan = $('#selectTypeOptBulan').val();
          d.status = currentStatus;
        },
        dataSrc: function(json) {
          if (!json.success) return [];
          // Update status card counts in real time
          if (json.counts) {
            for (const [key, val] of Object.entries(json.counts)) {
              const countSpan = document.querySelector(`.status-btn[data-status="${key}"] .status-count`);
              if (countSpan) {
                countSpan.textContent = val;
              }
            }
          }
          return json.data;
        }
      },
      columns: [
        { data: 'id_usulan' },
        { data: 'tahun' },
        { data: 'tanggal_usulan' },
        { data: 'bulan' },
        { data: 'kode_pts' },
        { data: 'nama_pts' },
        { data: 'nama' },
        { data: 'jabatan' },
        { data: 'file', render: function(data) {
            return data ? `<a href="${storageBaseUrl}/${data}" class="sptjm-icon-btn sptjm-btn-view" target="_blank" title="Lihat Dokumen"><i class="bx bx-file"></i></a>` : '-';
          }
        },
        { data: 'status' },
        { data: null, orderable: false, searchable: false, render: function(data, type, row) {
            if (currentStatus === 'Proses' || currentStatus === 'Selesai') return '';
            if (currentStatus === 'Tolak') return `<span>${row.alasan_penolakan || '-'}</span>`;
            if (currentStatus === 'Usulan') {
              // derive bulanAngka from id_usulan if possible
              const match = (row.id_usulan || '').match(/^[A-Z]+\s(\d{2})/);
              const bulanAngka = match ? match[1] : '';
              const no = row.no ?? '';
              return `<button type="button" class="sptjm-icon-btn sptjm-btn-reset me-1" onclick="handleSetuju(${no}, '${row.id_usulan}')" title="Setujui"><i class="bx bx-check"></i></button>` +
                `<button type="button" class="sptjm-icon-btn sptjm-btn-delete" onclick="handleTolak(${no}, '${row.id_usulan}','${bulanAngka}')" title="Tolak"><i class="bx bx-x"></i></button>`;
            }
            if (currentStatus === 'Validasi') {
              const no = row.no ?? '';
              return `<button type="button" class="sptjm-icon-btn sptjm-btn-edit" onclick="handleValidasi(${no}, '${row.id_usulan}')" title="Validasi"><i class="bx bx-check-double"></i></button>`;
            }
            return '';
          }
        }
      ],
      language: {
        lengthMenu: "Show _MENU_ entries",
        zeroRecords: "Tidak ada data yang cocok",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
        paginate: { previous: "Sebelumnya", next: "Berikutnya" }
      }
    });

    // expose table globally so other functions can reload without full page refresh
    window.validasiTable = table;

    // Status buttons
    $('.status-btn').on('click', function() {
      currentStatus = $(this).data('status');
      window.picValidasiUsulanCurrentStatus = currentStatus;
      try { localStorage.setItem('pic.validasi-usulan.status', currentStatus); } catch (e) {}
      updateStatusButtonsUI(currentStatus);
      table.ajax.reload();
    });

    // Filters change reload and persist selection
    $('#pilihsptjm, #selectTypeOptBulan').on('change', function() {
      try {
        localStorage.setItem('pic.validasi-usulan.pilihsptjm', $('#pilihsptjm').val());
        localStorage.setItem('pic.validasi-usulan.bulan', $('#selectTypeOptBulan').val());
      } catch (e) {}
      table.ajax.reload();
    });

    // apply initial status button UI based on restored value
    updateStatusButtonsUI(currentStatus);
  });

  // Fungsi untuk Setujui
  function handleSetuju(no, idUsulan) {
    Swal.fire({
      title: 'Konfirmasi',
      text: 'Apakah Anda yakin ingin menyetujui usulan ini?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Setujui',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        fetch(`/pic/validasi-usulan/${no}/setujui`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id_usulan: idUsulan })
          })
          .then(async (response) => {
            const text = await response.text();
            try {
              return JSON.parse(text);
            } catch (e) {
              throw new Error(`HTTP ${response.status}: ${text.slice(0, 500)}`);
            }
          })
          .then(data => {
            if (data.success) {
              Swal.fire('Berhasil!', 'Usulan telah disetujui.', 'success').then(() => {
                if (window.validasiTable) window.validasiTable.ajax.reload(null, false);
              });
            } else {
              Swal.fire('Gagal!', data.message || 'Terjadi kesalahan saat menyetujui.',
                'error');
            }
          })
          .catch(error => {
            console.error(error);
            Swal.fire('Error!', error?.message || 'Server tidak merespons.', 'error');
          });
      }
    });
  }

  // Fungsi untuk Tolak
  function handleTolak(no, idUsulan, bulanAngka) {
    Swal.fire({
      title: 'Masukkan Alasan Penolakan',
      input: 'textarea',
      inputPlaceholder: 'Tulis alasan penolakan di sini...',
      showCancelButton: true,
      confirmButtonText: 'Tolak',
      cancelButtonText: 'Batal',
      inputValidator: (value) => {
        if (!value) return 'Alasan penolakan wajib diisi!';
      }
    }).then((result) => {
      if (result.isConfirmed) {
        const alasan = result.value;

        fetch(`/pic/validasi-usulan/${no}/tolak`, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              alasan,
              bulanAngka,
              id_usulan: idUsulan
            })
          })
          .then(async (res) => {
            const text = await res.text();
            try {
              return JSON.parse(text);
            } catch (e) {
              throw new Error(`HTTP ${res.status}: ${text.slice(0, 500)}`);
            }
          })
          .then(data => {
            if (data.success) {
              Swal.fire('Berhasil!', 'Usulan berhasil ditolak.', 'success').then(() => {
                if (window.validasiTable) window.validasiTable.ajax.reload(null, false);
              });
            } else {
              Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error');
            }
          })
          .catch((error) => {
            Swal.fire('Error!', error?.message || 'Server tidak merespons.', 'error');
          });
      }
    });
  }

  // Fungsi untuk Validasi dengan konfirmasi SweetAlert
  // Navigasi sekarang dilakukan di TAB SAMA (tidak membuka tab baru)
  function handleValidasi(id, idUsulan) {
    Swal.fire({
      title: 'Konfirmasi Validasi',
      text: 'Apakah Anda yakin ingin memvalidasi usulan ini?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Validasi',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        // Sebelum masuk halaman validasi, cek dulu apakah
        // konfigurasi jenis usulan & pencairan_ke sudah di-set oleh admin
        const no = id;

        fetch(`/pic/validasi-usulan/${no}/cek-kode-cair`)
          .then(async (res) => {
            const text = await res.text();
            try {
              return JSON.parse(text);
            } catch (e) {
              throw new Error(`HTTP ${res.status}: ${text.slice(0, 500)}`);
            }
          })
          .then(data => {
            if (data.allowed) {
              // persist current Pilih Tipe SPTJM, Bulan and Status so list retains filters after return
              try {
                localStorage.setItem('pic.validasi-usulan.pilihsptjm', $('#pilihsptjm').val());
                localStorage.setItem('pic.validasi-usulan.bulan', $('#selectTypeOptBulan').val());
                localStorage.setItem('pic.validasi-usulan.status', window.picValidasiUsulanCurrentStatus || 'Usulan');
              } catch (e) {}

              const safeIdUsulan = (idUsulan || '').replace(/\s+/g, '-');
              window.location.href = `/pic/validasi-usulan/${no}/validasi-data-dosen?usulan=${encodeURIComponent(safeIdUsulan)}`;
            } else {
              Swal.fire({
                title: 'Tidak Bisa Validasi',
                text: data.message || 'Tidak bisa validasi. Silakan meminta izin kepada admin untuk membukanya.',
                icon: 'warning',
                confirmButtonText: 'OK'
              });
            }
          })
          .catch((error) => {
            Swal.fire({
              title: 'Error',
              text: error?.message || 'Gagal mengecek konfigurasi kode cair.',
              icon: 'error',
              confirmButtonText: 'OK'
            });
          });
      }
    });
  }
</script>

@endsection