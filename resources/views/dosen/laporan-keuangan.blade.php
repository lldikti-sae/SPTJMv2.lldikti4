@extends('layouts/contentNavbarLayoutDosen')

@section('title', 'SPTJM Online')

@section('content')

<style>
.dt-inner-wrapper {
    min-width: 100%;
    width: max-content;
}
</style>

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Laporan Keuangan</h5>
  <hr>

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="table-responsive text-nowrap">
    <div class="col-12">
      <div>
        <div class="card-body">
          <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('dosen.laporan-keuangan.export') }}" class="btn btn-success">
              <span class="tf-icons bx bx-download"></span>&nbsp; Export XLS
            </a>
          </div>

            <table id="myTable" class="table table-bordered table-hover" style="width:100%; border-collapse: collapse;">
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

          <style>
            table { border-collapse: collapse; }
            th, td {
              border: 1px solid rgb(193, 195, 197);
              padding: 8px;
              text-align: center;
            }
            thead th { background-color: white; }
          </style>

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
                  var gaji = Number(row['gaji' + monthIndex] || 0);
                  return renderMoney(gaji);
                };
              }

              function renderFieldNumber() {
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
                columns.push({ data: 'tpd' + i, render: renderFieldNumber() });
                columns.push({ data: 'tkgb' + i, render: renderFieldNumber() });
              }

              columns.push({ data: 'jumlah_gaji', orderable: false, searchable: false, render: renderFieldNumber() });
              columns.push({ data: 'jumlah_tpd', orderable: false, searchable: false, render: renderFieldNumber() });
              columns.push({ data: 'jumlah_tkgb', orderable: false, searchable: false, render: renderFieldNumber() });
              columns.push({ data: 'selisih_tpd', orderable: false, searchable: false, render: renderFieldNumber() });
              columns.push({ data: 'selisih_tkgb', orderable: false, searchable: false, render: renderFieldNumber() });
              columns.push({ data: 'total_gaji', orderable: false, searchable: false, render: renderFieldNumber() });
              columns.push({ data: 'total_tpd', orderable: false, searchable: false, render: renderFieldNumber() });
              columns.push({ data: 'total_tkgb', orderable: false, searchable: false, render: renderFieldNumber() });

              $('#myTable').DataTable({
                dom: '<"table-responsive pb-3"<"dt-inner-wrapper"<"d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 px-3"lf><"text-nowrap"rt><"d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 px-3"ip>>>',
                processing: true,
                serverSide: true,
                order: [],
                ajax: {
                  url: "{{ url('dosen/laporan-keuangan') }}",
                  type: 'POST',
                  headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
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
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
