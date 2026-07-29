@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
<style>
    .alert-custom-success {
        background-color: #e6fcf5 !important;
        color: #0ca678 !important;
        border: 1px solid #c3fae8 !important;
        border-radius: 8px !important;
        padding: 12px 20px !important;
        font-weight: 600 !important;
    }
</style>

{{-- Page Header --}}
<div class="md2-page-header">
    <div class="page-titles">
        <h3>Rekapitulasi Usulan Eligible</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Rekapitulasi</a></li>
                <li class="breadcrumb-item active">Usulan Eligible</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md2-card mb-4">
    <div class="card-body px-4 pb-4 pt-0">

        <!-- Filter Section -->
        <div class="pt-3 pb-3 mb-3 border-bottom">
            <form action="{{ route('admin.rekap-usulan-eligible') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <!-- Tipe SPTJM -->
                    <div class="col">
                        <label for="tipe_sptjm" class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">Tipe SPTJM</label>
                        <select class="form-select" id="tipe_sptjm" name="tipe_sptjm" onchange="this.form.submit()" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
                            <option value="SPTJM" {{ request('tipe_sptjm', 'SPTJM') == 'SPTJM' ? 'selected' : '' }}>SPTJM</option>
                            <option value="TUKIN" {{ request('tipe_sptjm') == 'TUKIN' ? 'selected' : '' }}>TUKIN</option>
                        </select>
                    </div>

                    <!-- Pencairan -->
                    <div class="col">
                        <label for="pencairan_ke" class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">Pencairan ke-</label>
                        <select class="form-select" id="pencairan_ke" name="pencairan_ke" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
                            <option value="Semua" {{ request('pencairan_ke') == 'Semua' ? 'selected' : '' }}>Semua</option>
                            @for ($i = 1; $i <= 20; $i++)
                                <option value="{{ $i }}" {{ request('pencairan_ke') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Bank -->
                    <div class="col">
                        <label for="bank" class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">Pilih Bank</label>
                        <select class="form-select" id="bank" name="bank" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
                            <option value="Semua" {{ request('bank') == 'Semua' ? 'selected' : '' }}>Semua</option>
                            @foreach (['BRI', 'MANDIRI', 'BNI', 'BTN', 'BSI'] as $bank)
                                <option value="{{ $bank }}" {{ request('bank') == $bank ? 'selected' : '' }}>{{ $bank }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Pegawai -->
                    <div class="col">
                        <label for="status_pegawai" class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">Status Pegawai</label>
                        <select class="form-select" id="status_pegawai" name="status_pegawai" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
                            @foreach (['Semua', 'NON PNS', 'PNS'] as $status)
                                <option value="{{ $status }}" {{ request('status_pegawai') == $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Eligible -->
                    <div class="col">
                        <label for="Eligible_span" class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">Eligible Span</label>
                        <select class="form-select" id="Eligible_span" name="Eligible_span" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
                            <option value="YA" {{ request('Eligible_span') == 'YA' ? 'selected' : '' }}>YA</option>
                        </select>
                    </div>

                    <!-- Tunjangan -->
                    <div class="col">
                        <label for="tunjangan" class="form-label fw-bold text-dark text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">Tunjangan</label>
                        <select class="form-select" id="tunjangan" name="tunjangan" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
                            @foreach (['Semua', 'tpd1' => 'TPD', 'tkgb1' => 'TKGB'] as $value => $label)
                                <option value="{{ is_int($value) ? $label : $value }}" {{ request('tunjangan') == (is_int($value) ? $label : $value) ? 'selected' : '' }}>
                                    {{ is_int($value) ? $label : $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Button "Lihat" on the right end -->
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-1" style="background-color: #0b3d91; border-color: #0b3d91; border-radius: 8px; font-weight: 600; font-size: 0.88rem; height: 38px; padding: 0 24px;">
                            <i class="bx bx-search-alt"></i> Lihat
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if ($hasFilter != false)
            <div class="alert alert-custom-success mb-3" id="alertFilter">
                <i class="bx bx-check-circle me-2"></i> {{ $success }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif

        <!-- Data Table -->
        @if ($hasFilter)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold text-dark">Tabel Rekapitulasi</h6>
            </div>

            <div class="mb-4">
                <table class="table table-hover md2-table text-center" id="rekapTable" style="width:100%">
                    <thead>
                        <tr>
                            <th>NIDN</th>
                            <th>NUPTK</th>
                            <th>No Peserta</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Golongan</th>
                            <th>Masa Kerja</th>
                            <th>Status Pegawai</th>
                            <th>Bank</th>
                            <th>Eligible</th>
                            <th>Status</th>
                            <th>Tahun</th>
                            @foreach ($bulanMap as $bln)
                                <th>{{ $bln }}</th>
                            @endforeach
                            <th>Jumlah Kotor TPD</th>
                            <th>Jumlah Kotor TKGB</th>
                            <th>PPH TPD</th>
                            <th>PPH TKGB</th>
                            <th>Bersih TPD</th>
                            <th>Bersih TKGB</th>
                            <th>No Rekening</th>
                            <th>NPWP</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            @if (!empty($rekap))
                <div class="border-top border-secondary my-4"></div>
                <h5 class="fw-bold text-dark mb-3">Rekapitulasi Usulan untuk Proses</h5>
                <div class="table-responsive text-nowrap w-100 mb-4">
                    <table class="table table-hover md2-table text-center" id="tableRekap" style="width:100%">
                        <thead>
                            <tr>
                                <th>Grup</th>
                                <th>Jumlah Dosen</th>
                                <th>Bank</th>
                                <th>Status Pegawai</th>
                                <th>Tunjangan</th>
                                <th>Total Kotor</th>
                                <th>Total Pajak</th>
                                <th>Total Bersih</th>
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
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-warning px-5 py-2 fw-bold" id="btnProses" style="background-color: #d97706; border-color: #d97706; color: white; border-radius: 8px;">Proses</button>
                    </div>
                </form>
            @endif
        @else
            <div class="alert alert-danger mt-3 text-bold px-4 py-3" style="border-radius: 8px;">
                <i class="bx bx-error-circle me-2"></i> Silakan pilih terlebih dahulu untuk menampilkan data rekapitulasi.
            </div>
        @endif

    </div>
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
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"table-responsive text-nowrap"t><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
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
          next: "â†’",
          previous: "â†",
        },
        zeroRecords: "Data tidak ditemukan",
        infoEmpty: "Menampilkan 0 entri",
        info: "Menampilkan _START_–_END_ dari _TOTAL_ entri",
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