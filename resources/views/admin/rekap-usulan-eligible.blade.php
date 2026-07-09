@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
@section('content')
<style>
    .card-eligible {
        border: 1.5px solid #dbeafe !important;
        box-shadow: 0 10px 30px rgba(26, 86, 219, 0.15) !important;
        border-radius: 12px !important;
        background: #ffffff !important;
    }
    .alert-custom-success {
        background-color: #e6fcf5 !important;
        color: #0ca678 !important;
        border: 1px solid #c3fae8 !important;
        border-radius: 8px !important;
        padding: 12px 20px !important;
        font-weight: 600 !important;
    }
</style>

<div class="content-wrapper">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb" style="font-size: 0.85rem; padding: 0; background: transparent;">
            <li class="breadcrumb-item"><a href="#" style="color: #64748b;">Proses Pembayaran</a></li>
            <li class="breadcrumb-item"><a href="#" style="color: #64748b;">Rekapitulasi Usulan</a></li>
            <li class="breadcrumb-item active fw-bold" aria-current="page" style="color: #1a56db;">Eligible</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1" style="color: #0f2b5c !important; font-size: 1.5rem;">Rekapitulasi Berjalan Eligible</h4>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card card-eligible mb-4">
        <div class="card-body p-4">
            <h6 class="fw-bold text-dark mb-3" style="color: #0f2b5c !important; display: inline-flex; align-items: center; gap: 8px;">
                <i class="bx bx-filter-alt" style="color: #d97706;"></i> Parameter Filter Data
            </h6>
            <form action="{{ route('admin.rekap-usulan-eligible') }}" method="GET">
                <div class="row g-3">
                    <!-- Tipe SPTJM -->
                    <div class="col">
                        <label for="tipe_sptjm" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Tipe SPTJM</label>
                        <select class="form-select" id="tipe_sptjm" name="tipe_sptjm" onchange="this.form.submit()" style="border-color: #cbd5e1;">
                            <option value="SPTJM" {{ request('tipe_sptjm', 'SPTJM') == 'SPTJM' ? 'selected' : '' }}>SPTJM</option>
                            <option value="TUKIN" {{ request('tipe_sptjm') == 'TUKIN' ? 'selected' : '' }}>TUKIN</option>
                        </select>
                    </div>

                    <!-- Pencairan -->
                    <div class="col">
                        <label for="pencairan_ke" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Pencairan ke-</label>
                        <select class="form-select" id="pencairan_ke" name="pencairan_ke" style="border-color: #cbd5e1;">
                            <option value="Semua" {{ request('pencairan_ke') == 'Semua' ? 'selected' : '' }}>Semua</option>
                            @for ($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}" {{ request('pencairan_ke') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Bank -->
                    <div class="col">
                        <label for="bank" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Pilih Bank</label>
                        <select class="form-select" id="bank" name="bank" style="border-color: #cbd5e1;">
                            <option value="Semua" {{ request('bank') == 'Semua' ? 'selected' : '' }}>Semua</option>
                            @foreach (['BRI', 'MANDIRI', 'BNI', 'BTN', 'BSI'] as $bank)
                                <option value="{{ $bank }}" {{ request('bank') == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Pegawai -->
                    <div class="col">
                        <label for="status_pegawai" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Status Pegawai</label>
                        <select class="form-select" id="status_pegawai" name="status_pegawai" style="border-color: #cbd5e1;">
                            @foreach (['Semua', 'NON PNS', 'PNS'] as $status)
                                <option value="{{ $status }}" {{ request('status_pegawai') == $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Eligible -->
                    <div class="col">
                        <label for="Eligible_span" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Eligible Span</label>
                        <select class="form-select" id="Eligible_span" name="Eligible_span" style="border-color: #cbd5e1;">
                            <option value="YA" {{ request('Eligible_span') == 'YA' ? 'selected' : '' }}>YA</option>
                        </select>
                    </div>

                    <!-- Tunjangan -->
                    <div class="col">
                        <label for="tunjangan" class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.05em;">Tunjangan</label>
                        <select class="form-select" id="tunjangan" name="tunjangan" style="border-color: #cbd5e1;">
                            @foreach (['Semua', 'tpd1' => 'TPD', 'tkgb1' => 'TKGB'] as $value => $label)
                                <option value="{{ is_int($value) ? $label : $value }}" {{ request('tunjangan') == (is_int($value) ? $label : $value) ? 'selected' : '' }}>
                                    {{ is_int($value) ? $label : $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" style="background-color: #0f2b5c; border-color: #0f2b5c; border-radius: 6px; font-size: 0.875rem;">Lihat</button>
                </div>
            </form>
        </div>
    </div>

    @if ($hasFilter != false)
        <div class="alert alert-custom-success mb-4" id="alertFilter">
            <i class="bx bx-check-circle me-2"></i> {{ $success }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <!-- Data Table -->
    @if ($hasFilter)
    <div class="card card-eligible mb-4">
      <div class="card-header d-flex justify-content-between align-items-center p-4">
        <h6 class="mb-0 fw-bold text-dark" style="color: #0f2b5c !important;">Tabel Rekapitulasi</h6>
      </div>

      <div class="card-body px-4 pb-4 pt-0">
        <div class="table-responsive text-nowrap">
          <table class="table table-hover md2-table text-center" id="rekapTable" style="width:100%">
            <thead>
              <tr>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">NIDN</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">NUPTK</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">No Peserta</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Nama</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Jabatan</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Golongan</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Masa Kerja</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Status Pegawai</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Bank</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Eligible</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Status</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Tahun</th>
                @foreach ($bulanMap as $bln)
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">{{ $bln }}</th>
                @endforeach
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Jumlah Kotor TPD</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Jumlah Kotor TKGB</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">PPH TPD</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">PPH TKGB</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Bersih TPD</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Bersih TKGB</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">No Rekening</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">NPWP</th>
              </tr>
            </thead>
            <tbody>
              {{-- Data dimuat via DataTables AJAX --}}
            </tbody>
          </table>
        </div>
        <br>
        
        @if (!empty($rekap))
        <div class="border-top border-secondary my-4"></div>
        <h5 class="fw-bold text-dark mb-3" style="color: #0f2b5c !important;">Rekapitulasi Usulan untuk Proses</h5>
        <div class="table-responsive text-nowrap w-100 mb-4">
          <table class="table table-hover md2-table text-center" id="tableRekap" style="width:100%">
            <thead>
              <tr>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Grup</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Jumlah Dosen</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Bank</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Status Pegawai</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Tunjangan</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Total Kotor</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Total Pajak</th>
                <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Total Bersih</th>
              </tr>
            </thead>
            <tbody>
              @php $no = 1; @endphp
              @foreach ($rekap as $groups)
              @foreach ($groups as $group)
              <tr>
                <td>{{ $no++ }}</td>
                <td>{{ $group['jmlh_dosen'] }}</td>
                <td>{{ $group['bank'] }}</td>
                <td>{{ $group['status_pegawai'] }}</td>
                <td>{{ $group['tunjangan'] }}</td>
                <td>{{ number_format($group['total_kotor_semua'], 0, ',', '.') }}</td>
                <td>{{ number_format($group['total_pajak_semua'], 0, ',', '.') }}</td>
                <td>{{ number_format($group['total_bersih_semua'], 0, ',', '.') }}</td>
                <td class="d-none">{{ implode(', ', $group['nidns']) }}</td>
              </tr>
              @endforeach
              @endforeach
            </tbody>
          </table>
        </div>
        <form id="formProses" method="POST" action="{{ route('admin.rekap-usulan-eligible.proses') }}">
          @csrf
          <input type="hidden" name="rekap_json" id="rekap_json">
          <input type="hidden" name="tipe_sptjm" value="{{ request('tipe_sptjm', 'SPTJM') }}">
          <input type="hidden" name="pencairan_ke" value="{{ request('pencairan_ke') }}">
          <input type="hidden" name="eligible_span" value="{{ request('Eligible_span')}}">
          <div class="text-center">
            <button type="button" class="btn btn-warning px-5 py-2 fw-bold" id="btnProses" style="background-color: #d97706; border-color: #d97706; color: white;">Proses</button>
          </div>
        </form>
        @endif
      </div>
    </div>
  </div>
  @else
  <div class="alert alert-danger mt-3 text-bold px-4 py-3" style="border-radius: 8px;">
    <i class="bx bx-error-circle me-2"></i> Silakan pilih terlebih dahulu untuk menampilkan data rekapitulasi.
  </div>
  @endif
</div>
</div>
<script>
  @if(session('success'))
  Swal.fire({
    title: "Sukses",
    text: "{{ session('success') }}",
    icon: "success",
    draggable: false
  });
  @endif

  const btnProses = document.getElementById('btnProses')
  btnProses.addEventListener('click', (e) => {
    Swal.fire({
      title: "Apakah Anda Yakin?",
      text: "Anda akan memproses data dan tidak bisa diubah lagi!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Ya, Proses!",
      cancelButtonText: "Batal!"
    }).then((result) => {
      if (result.isConfirmed) {
        submitProses()
      }
    });
  })

  const submitProses = () => {
    let table = document.querySelector('#tableRekap tbody');
    let rows = table.querySelectorAll('tr');
    let data = [];

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

    rows.forEach(row => {
      let cells = row.querySelectorAll('td');
      data.push({
        grup: cells[0].innerText.trim(),
        jumlah_dosen: cells[1].innerText.trim(),
        bank: cells[2].innerText.trim(),
        status_pegawai: cells[3].innerText.trim(),
        tunjangan: cells[4].innerText.trim(),
        total_kotor: cells[5].innerText.trim().replace(/\./g, ''),
        total_pajak: cells[6].innerText.trim().replace(/\./g, ''),
        total_bersih: cells[7].innerText.trim().replace(/\./g, ''),
        nidns: cells[8].innerText.trim().split(',').map(n => n.trim())
      });
    });

    // Masukkan data JSON ke input hidden
    document.getElementById('rekap_json').value = JSON.stringify(data);

    // submit form langsung
    document.getElementById('formProses').submit();
  }

  $(document).ready(function() {
    const ajaxUrl = "{{ route('admin.rekap-usulan-eligible.data') }}";
    const tipeSptjm = "{{ request('tipe_sptjm', 'SPTJM') }}";
    const pencairanKe = "{{ request('pencairan_ke', 'Semua') }}";
    const bank = "{{ request('bank', 'Semua') }}";
    const statusPegawai = "{{ request('status_pegawai', 'Semua') }}";
    const eligibleSpan = "{{ request('Eligible_span', 'YA') }}";
    const tunjangan = "{{ request('tunjangan', 'Semua') }}";

    $('#rekapTable').DataTable({
      processing: true,
      serverSide: true,
      paging: true,
      pageLength: 100,
      lengthMenu: [[50, 100, 200, 500], [50, 100, 200, 500]],
      ajax: {
        url: ajaxUrl,
        data: {
          tipe_sptjm: tipeSptjm,
          pencairan_ke: pencairanKe,
          bank: bank,
          status_pegawai: statusPegawai,
          Eligible_span: eligibleSpan,
          tunjangan: tunjangan,
        }
      },
      language: {
        paginate: {
          first: "Awal",
          last: "Akhir",
          next: "→",
          previous: "←",
        },
        zeroRecords: "Data tidak ditemukan",
        infoEmpty: "Tidak ada data tersedia",
        searchPlaceholder: "Cari data...",
        search: "Cari Data:"
      },
    });

    setTimeout(() => {
      $('#alertFilter').slideUp(1000);
    }, 1000);
  });
</script>

@endsection