@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Validasi Usulan SPTJM</h5>
  <hr>

  <form id="filterForm" method="POST">
    @csrf
    <div class="row align-items-center" style="padding: 20px;">
      <!-- Pilih Tipe SPTJM -->
      <div class="col-lg-3 col-md-4 mb-2 mb-md-0">
        <label class="form-label" for="pilihsptjm">Pilih Tipe SPTJM</label>
        <select id="pilihsptjm" class="form-select" name="pilihsptjm">
          <option value="B%">SPTJM Berjalan</option>
          <option value="S%">SPTJM Susulan</option>
          <option value="BT%">TUKIN Berjalan</option>
          <option value="ST%">TUKIN Susulan</option>
        </select>
      </div>

      <!-- Pilih Bulan -->
      <div class="col-lg-2 col-md-3 mb-2 mb-md-0">
        <label class="form-label" for="selectTypeOptBulan">Bulan</label>
        <select name="bulan" id="selectTypeOptBulan" class="form-select">
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

    <!-- Button Filter Status -->
    <div class="demo-inline-spacing mb-4"
      style="display: flex; gap: 11px; justify-content: start; margin-left: 20px;">
      <button type="button" class="btn btn-outline-dark status-btn" data-status="Usulan">Usulan</button>
      <button type="button" class="btn btn-outline-primary status-btn" data-status="Validasi">Validasi</button>
      <button type="button" class="btn btn-outline-warning status-btn" data-status="Proses">Proses</button>
      <button type="button" class="btn btn-outline-success status-btn" data-status="Selesai">Selesai</button>
      <button type="button" class="btn btn-outline-danger status-btn" data-status="Tolak">Tolak</button>
    </div>
  </form>

  <hr>

  <div class="table-responsive text-nowrap mt-4">
    <table class="table table-sm table-bordered" id="dataTable">
      <thead style="background-color: #dbdee0; text-align: center">
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
      <tbody>
        <tr>
          <td colspan="11" class="text-center">Data tidak ada</td>
        </tr>
      </tbody>
    </table>
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

    // helper to toggle outline <-> solid button classes based on selection
    function updateStatusButtonsUI(status) {
      const map = {
        'Usulan': ['btn-outline-dark', 'btn-dark'],
        'Validasi': ['btn-outline-primary', 'btn-primary'],
        'Proses': ['btn-outline-warning', 'btn-warning'],
        'Selesai': ['btn-outline-success', 'btn-success'],
        'Tolak': ['btn-outline-danger', 'btn-danger']
      };
      $('.status-btn').each(function() {
        const s = $(this).data('status');
        const outline = map[s] ? map[s][0] : 'btn-outline-secondary';
        const solid = map[s] ? map[s][1] : 'btn-secondary';
        $(this).removeClass(outline).removeClass(solid);
        if (s === status) $(this).addClass(solid); else $(this).addClass(outline);
      });
    }

    // Initialize DataTable with AJAX source
    const table = $('#dataTable').DataTable({
      processing: true,
      serverSide: false,
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
            return data ? `<a href="${storageBaseUrl}/${data}" target="_self"><i class="bx bx-file"></i> Lihat Dokumen</a>` : '-';
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
              return `<button class="btn btn-sm btn-primary me-1" onclick="handleSetuju(${no}, '${row.id_usulan}')">Setujui</button>` +
                `<button class="btn btn-sm btn-danger" onclick="handleTolak(${no}, '${row.id_usulan}','${bulanAngka}')">Tolak</button>`;
            }
            if (currentStatus === 'Validasi') {
              const no = row.no ?? '';
              return `<button class="btn btn-sm btn-primary" onclick="handleValidasi(${no}, '${row.id_usulan}')">Validasi</button>`;
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

    // Custom search input
    $('#searchInput').on('keyup', function() { table.search(this.value).draw(); });
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