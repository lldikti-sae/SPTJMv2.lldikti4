@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h3>Laporan Keuangan</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Monitoring</a></li>
                <li class="breadcrumb-item active">Laporan Keuangan</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md-card">
  <div class="md-card-inner">

  @if (session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <form method="GET" action="{{ url('pic/laporan-keuangan') }}" id="filterForm">
    <div class="row mb-3 align-items-end">
      <div class="d-flex flex-wrap gap-2">
        <!-- Kode PT -->
        <div>
          <select class="form-control" id="basic-default-kodept" name="kode_pt" style="border-radius: 8px; min-width: 200px;">
            <option value="">-- Kode PT (opsional) --</option>
            <option value="Semua" {{ (($kode_pt ?? request('kode_pt')) === 'Semua') ? 'selected' : '' }}>Semua</option>
            @foreach(($ptsList ?? []) as $pt)
              <option value="{{ $pt->kode_pts }}" {{ (($kode_pt ?? request('kode_pt')) === $pt->kode_pts) ? 'selected' : '' }}>
                {{ $pt->kode_pts }} - {{ $pt->nama_pts }}
              </option>
            @endforeach
          </select>
        </div>
        
        <!-- NIDN or NUPTK -->
        <div>
          <input type="text" class="form-control" id="basic-default-nidn" name="nidn"
            placeholder="Masukkan NIDN/NUPTK" value="{{ request('nidn') }}" style="border-radius: 8px;">
        </div>

        <!-- Button Cari -->
        <div>
          <button type="submit" class="btn btn-primary rounded-pill px-4" name="submit">
            <span class="tf-icons bx bx-search"></span>&nbsp; Cari
          </button>
        </div>

        @if (($kode_pt ?? request('kode_pt')) || request('nidn'))
        <a href="{{ route('laporan-keuangan-pic.export', ['kode_pt' => $kode_pt ?? request('kode_pt'), 'nidn' => request('nidn'), 'tahun' => session('tahun')]) }}"
          class="btn btn-success rounded-pill px-4">
          <span class="tf-icons bx bx-download"></span>&nbsp; Export XLS
        </a>
        @endif

      </div>
    </div>

            <!-- Tabel hasil pencarian -->
    <div class="table-responsive text-nowrap mt-3">
              <!-- Table Display -->
              <table id="myTable" class="table table-bordered table-hover"
                style="width:100%; border-collapse: collapse;">
                <thead>
                  <tr>
                    <th colspan="10">Identitas Dosen</th>
                    @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                    'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $month)
                    <th colspan="4">{{ $month }}</th>
                    @endforeach
                    <th colspan="3">Jumlah</th>
                    <th colspan="2">Selisih Bayar</th>
                    <th colspan="3">Total</th>
                  </tr>
                  <tr>
                    <th>NIDN</th>
                    <th>NUPTK</th>
                    <th>Nama</th>
                    <th>Jenis</th>
                    <th>Jabatan</th>
                    <th>Status</th>
                    <th>Eligible Span</th>
                    <th>Nama Bank</th>
                    <th>Kode PT</th>
                    <th>Nama Perguruan Tinggi</th>

                    @for ($i = 1; $i <= 12; $i++)
                      <th>Gaji</th>
                      <th>KC</th>
                      <th>TPD</th>
                      <th>TKGB</th>
                    @endfor

                    <th>Gaji</th>
                    <th>TPD</th>
                    <th>TKGB</th>

                    <th>TPD</th>
                    <th>TKGB</th>

                    <th>Gaji</th>
                    <th>TPD</th>
                    <th>TKGB</th>
                  </tr>
                </thead>

                <tbody></tbody>
                <tfoot>
                  <tr>
                    <td colspan="10"><strong>Jumlah</strong></td>

                    @for ($i = 1; $i <= 12; $i++)
                      <td><strong class="ft-gaji" data-i="{{ $i }}">0</strong></td>
                      <td>-</td>
                      <td><strong class="ft-tpd" data-i="{{ $i }}">0</strong></td>
                      <td><strong class="ft-tkgb" data-i="{{ $i }}">0</strong></td>
                    @endfor

                    <td><strong id="ft-grand-gaji">0</strong></td>
                    <td><strong id="ft-grand-tpd">0</strong></td>
                    <td><strong id="ft-grand-tkgb">0</strong></td>
                    <td><strong id="ft-grand-selisih-tpd">0</strong></td>
                    <td><strong id="ft-grand-selisih-tkgb">0</strong></td>
                    <td><strong id="ft-total-gaji">0</strong></td>
                    <td><strong id="ft-total-tpd">0</strong></td>
                    <td><strong id="ft-total-tkgb">0</strong></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </form>
    </div>
  </div>

@push('scripts')
<script>
  (function () {
    if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
      return;
    }

    function formatNumber(value) {
      var n = Number(value || 0);
      try {
        return new Intl.NumberFormat('id-ID').format(n);
      } catch (e) {
        return String(n);
      }
    }

    function renderMoney(data) {
      return formatNumber(data);
    }

    function renderMonthGaji(monthIndex) {
      return function (data, type, row) {
        var tpd = Number(row['tpd' + monthIndex] || 0);
        var tkgb = Number(row['tkgb' + monthIndex] || 0);
        return renderMoney(tpd + tkgb);
      };
    }

    function renderFieldNumber(fieldName) {
      return function (data) {
        return renderMoney(data);
      };
    }

    var columns = [
      { data: 'nidn' },
      { data: 'nuptk', defaultContent: '-' },
      { data: 'nama' },
      { data: 'jenis' },
      { data: 'jabatan', defaultContent: '-' },
      { data: 'aktif' },
      { data: 'eligible_span' },
      { data: 'bank' },
      { data: 'kode_pt' },
      { data: 'pts' }
    ];

    for (var i = 1; i <= 12; i++) {
      columns.push({ data: null, orderable: false, searchable: false, render: renderMonthGaji(i) });
      columns.push({ data: 'kodeusulan' + i, defaultContent: '-' });
      columns.push({ data: 'tpd' + i, render: renderFieldNumber('tpd' + i) });
      columns.push({ data: 'tkgb' + i, render: renderFieldNumber('tkgb' + i) });
    }

    columns.push({ data: 'jumlah_gaji', orderable: false, searchable: false, render: renderFieldNumber('jumlah_gaji') });
    columns.push({ data: 'jumlah_tpd', orderable: false, searchable: false, render: renderFieldNumber('jumlah_tpd') });
    columns.push({ data: 'jumlah_tkgb', orderable: false, searchable: false, render: renderFieldNumber('jumlah_tkgb') });
    columns.push({ data: 'selisih_tpd', orderable: false, searchable: false, render: renderFieldNumber('selisih_tpd') });
    columns.push({ data: 'selisih_tkgb', orderable: false, searchable: false, render: renderFieldNumber('selisih_tkgb') });
    columns.push({ data: 'total_gaji', orderable: false, searchable: false, render: renderFieldNumber('total_gaji') });
    columns.push({ data: 'total_tpd', orderable: false, searchable: false, render: renderFieldNumber('total_tpd') });
    columns.push({ data: 'total_tkgb', orderable: false, searchable: false, render: renderFieldNumber('total_tkgb') });

    $('#myTable').DataTable({
      processing: true,
      serverSide: true,
      order: [],
      ajax: {
        url: "{{ url('pic/laporan-keuangan') }}",
        type: 'POST',
        headers: {
          'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        data: function (d) {
          d.kode_pt = $('#basic-default-kodept').val();
          d.nidn = $('#basic-default-nidn').val();
        }
      },
      columns: columns,
      drawCallback: function (settings) {
        var json = settings.json || {};
        var totals = json.totals || {};
        var gajiPerMonth = totals.gajiPerMonth || [];
        var tpdPerMonth = totals.tpdPerMonth || [];
        var tkgbPerMonth = totals.tkgbPerMonth || [];

        for (var m = 1; m <= 12; m++) {
          $('.ft-gaji[data-i="' + m + '"]').text(formatNumber(gajiPerMonth[m - 1] || 0));
          $('.ft-tpd[data-i="' + m + '"]').text(formatNumber(tpdPerMonth[m - 1] || 0));
          $('.ft-tkgb[data-i="' + m + '"]').text(formatNumber(tkgbPerMonth[m - 1] || 0));
        }

        $('#ft-grand-gaji').text(formatNumber(totals.grandGaji || 0));
        $('#ft-grand-tpd').text(formatNumber(totals.grandTpd || 0));
        $('#ft-grand-tkgb').text(formatNumber(totals.grandTkgb || 0));
        $('#ft-grand-selisih-tpd').text(formatNumber(totals.grandSelisihTpd || 0));
        $('#ft-grand-selisih-tkgb').text(formatNumber(totals.grandSelisihTkgb || 0));
        $('#ft-total-gaji').text(formatNumber(totals.grandGaji || 0));
        $('#ft-total-tpd').text(formatNumber(totals.grandTpd || 0));
        $('#ft-total-tkgb').text(formatNumber(totals.grandTkgb || 0));
      }
    });
  })();
</script>
@endpush

@endsection