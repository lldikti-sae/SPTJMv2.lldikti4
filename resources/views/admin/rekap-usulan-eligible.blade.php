@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')
<div class="row">
  <div class="col-12">

    <!-- Filter Form -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Rekapitulasi Berjalan Eligible</h5>
        <hr>
        @if ($hasFilter != false)
        <div class="alert alert-success" id="alertFilter">{{ $success }}</div>
        @endif

        @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

      </div>
      <div class="card-body">
        <form action="{{ route('admin.rekap-usulan-eligible') }}" method="GET">
          <div class="row g-3">

            <!-- Tipe SPTJM -->
            <div class="col-md-2">
              <label for="tipe_sptjm" class="form-label fw-semibold">Pilih Tipe SPTJM</label>
              <select class="form-select" id="tipe_sptjm" name="tipe_sptjm" onchange="this.form.submit()">
                <option value="SPTJM" {{ request('tipe_sptjm', 'SPTJM') == 'SPTJM' ? 'selected' : '' }}>SPTJM</option>
                <option value="TUKIN" {{ request('tipe_sptjm') == 'TUKIN' ? 'selected' : '' }}>TUKIN</option>
              </select>
            </div>

            <!-- Pencairan -->
            <div class="col-md-2">
              <label for="pencairan_ke" class="form-label fw-semibold">Pencairan ke-</label>
              <select class="form-select" id="pencairan_ke" name="pencairan_ke">
                <option value="Semua" {{ request('pencairan_ke') == 'Semua' ? 'selected' : '' }}>Semua
                </option>
                @for ($i = 1; $i <= 20; $i++)
                  <option value="{{ $i }}" {{ request('pencairan_ke') == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
              </select>
            </div>

            <!-- Bank -->
            <div class="col-md-2">
              <label for="bank" class="form-label fw-semibold">Pilih Bank</label>
              <select class="form-select" id="bank" name="bank">
                <option value="Semua" {{ request('bank') == 'Semua' ? 'selected' : '' }}>Semua</option>
                @foreach (['BRI', 'MANDIRI', 'BNI', 'BTN', 'BSI'] as $bank)
                <option value="{{ $bank }}" {{ request('bank') == $bank ? 'selected' : '' }}>{{ $bank }}
                </option>
                @endforeach
              </select>
            </div>

            <!-- Status Pegawai -->
            <div class="col-md-2">
              <label for="status_pegawai" class="form-label fw-semibold">Status Pegawai</label>
              <select class="form-select" id="status_pegawai" name="status_pegawai">
                @foreach (['Semua', 'NON PNS', 'PNS'] as $status)
                <option value="{{ $status }}"
                  {{ request('status_pegawai') == $status ? 'selected' : '' }}>
                  {{ $status }}
                </option>
                @endforeach
              </select>
            </div>

            <!-- Eligible -->
            <div class="col-md-2">
              <label for="Eligible_span" class="form-label fw-semibold">Eligible Span</label>
              <select class="form-select" id="Eligible_span" name="Eligible_span">
                <option value="YA" {{ request('Eligible_span') == 'YA' ? 'selected' : '' }}>YA
                </option>
              </select>
            </div>

            <!-- Tunjangan -->
            <div class="col-md-2">
              <label for="tunjangan" class="form-label fw-semibold">Tunjangan</label>
              <select class="form-select" id="tunjangan" name="tunjangan">
                @foreach (['Semua', 'tpd1' => 'TPD', 'tkgb1' => 'TKGB'] as $value => $label)
                <option value="{{ is_int($value) ? $label : $value }}"
                  {{ request('tunjangan') == (is_int($value) ? $label : $value) ? 'selected' : '' }}>
                  {{ is_int($value) ? $label : $label }}
                </option>
                @endforeach
              </select>
            </div>

            <!-- Submit -->
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">Lihat</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <hr>

    <!-- Data Table -->
    @if ($hasFilter)
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Tabel Rekapitulasi</h6>
      </div>

      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-sm table-bordered text-center table-hover" id="rekapTable">
            <thead style="background-color: #dbdee0;">
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
            <tbody>
              {{-- Data dimuat via DataTables AJAX --}}
            </tbody>
          </table>
        </div>
        <br>
        
        @if (!empty($rekap))
        <div class="border-top border-secondary my-3"></div>
        <h5>Rekapitulasi Usulan untuk Proses</h5>
        <div class="d-flex align-items-center justify-content-center"></div>
        <div class="table-responsive text-nowrap w-100">
          <table class="table table-sm table-bordered text-center table-hover" id="tableRekap">
            <thead style="background-color: #dbdee0;">
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
          <div class="text-center mt-4">
            <button type="button" class="btn btn-warning" id="btnProses">Proses</button>
          </div>
        </form>
        @endif
      </div>
    </div>
  </div>
  @else
  <div class="alert alert-danger mt-3 text-bold">
    Silakan pilih terlebih dahulu untuk menampilkan data rekapitulasi.
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
      scrollX: true,
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