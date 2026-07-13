@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('vendor-style')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endsection

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

@section('page-style')
<style>
/* â”€â”€ Select2 Custom Theme for SPTJM â”€â”€ */
.select2-container--bootstrap-5 .select2-selection {
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 8px !important;
    font-size: 0.9rem !important;
    color: #374151 !important;
    min-height: 40px !important;
    padding: 5px 10px !important;
    font-family: 'Public Sans', sans-serif !important;
    background-color: #fff !important;
    box-shadow: none !important;
}
.select2-container--bootstrap-5.select2-container--focus .select2-selection {
    border-color: #1a56db !important;
    box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.1) !important;
}
.select2-container--bootstrap-5 .select2-search__field {
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 6px !important;
    font-size: 0.85rem !important;
    font-family: 'Public Sans', sans-serif !important;
    padding: 5px 10px !important;
    outline: none !important;
    color: #374151 !important;
}
.select2-container--bootstrap-5 .select2-search__field:focus {
    border-color: #1a56db !important;
    box-shadow: 0 0 0 2px rgba(26,86,219,0.12) !important;
}
.select2-container--bootstrap-5 .select2-dropdown {
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 8px !important;
    box-shadow: 0 8px 24px rgba(11, 61, 145, 0.12) !important;
    font-family: 'Public Sans', sans-serif !important;
    font-size: 0.88rem !important;
    overflow: hidden;
}
.select2-container--bootstrap-5 .select2-results__option {
    padding: 7px 14px !important;
    color: #374151 !important;
}
.select2-container--bootstrap-5 .select2-results__option--highlighted {
    background-color: #eff6ff !important;
    color: #0b3d91 !important;
    font-weight: 500 !important;
}
.select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
    background-color: #0b3d91 !important;
    color: #fff !important;
}
.select2-container--bootstrap-5 .select2-selection__rendered {
    color: #374151 !important;
    font-size: 0.9rem !important;
    line-height: 1.6 !important;
    padding: 0 !important;
}
.select2-container--bootstrap-5 .select2-selection__placeholder {
    color: #94a3b8 !important;
}
.select2-container {
    width: 100% !important;
}
</style>
@endsection

@section('content')

<style>
    .card-laporan {
        border: 1.5px solid #dbeafe !important;
        box-shadow: 0 10px 30px rgba(26, 86, 219, 0.15) !important;
        border-radius: 12px !important;
        background: #ffffff !important;
    }
    #myTable th, #myTable td {
        padding: 5px 8px !important;
        font-size: 0.8rem !important;
        line-height: 1.2 !important;
    }
</style>

<div class="content-wrapper">

    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: 10px;">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Card Utama -->
    <div class="card card-laporan mb-4">
        <div class="card-body px-4 pb-4 pt-0">
            
            <!-- Filter Section -->
            <div class="pt-3 pb-3 mb-3 border-bottom">
                <form method="GET" action="{{ url('admin/laporan-keuangan') }}" id="filterForm">
                    <div class="row g-3 align-items-end">
                        <!-- Pilih Perguruan Tinggi -->
                        <div class="col-md-4">
                            <label for="basic-default-kodept" class="form-label fw-bold text-uppercase mb-1"
                                style="font-size: 0.68rem; letter-spacing: 0.06em; color: #64748b;">Pilih Perguruan Tinggi</label>
                            <select class="form-select" id="basic-default-kodept" name="kode_pt" data-select2-kodept>
                                <option value="">- Pilih Kode PT (opsional) -</option>
                                <option value="Semua" {{ $kode_pt === 'Semua' ? 'selected' : '' }}>Semua</option>
                                @foreach ($ptsList as $pt)
                                <option value="{{ $pt->kode_pts }}" {{ $kode_pt === (string) $pt->kode_pts ? 'selected' : '' }}>
                                    {{ $pt->kode_pts }} - {{ $pt->nama_pts }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Cari NIDN / NUPTK -->
                        <div class="col-md-4">
                            <label for="basic-default-nidn" class="form-label fw-bold text-uppercase mb-1"
                                style="font-size: 0.68rem; letter-spacing: 0.06em; color: #64748b;">Cari NIDN / NUPTK</label>
                            <input type="text" class="form-control" id="basic-default-nidn" name="nidn"
                                placeholder="Masukkan NIDN/NUPTK..." value="{{ request('nidn') }}"
                                style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem;">
                        </div>

                        <!-- Tombol Tampilkan -->
                        <div class="col-md-auto d-flex gap-2">
                            <button type="submit" class="btn fw-semibold d-flex align-items-center gap-2" name="submit"
                                style="background-color: #0f2b5c; color: #ffffff; border-color: #0f2b5c; border-radius: 8px; padding: 9px 20px; font-size: 0.875rem; white-space: nowrap;">
                                <i class="bx bx-search" style="font-size: 1rem;"></i> Tampilkan Data
                            </button>

                            @if (request()->filled('kode_pt') || request()->filled('nidn'))
                            <a href="{{ route('laporan-keuangan-admin.export', ['kode_pt' => request('kode_pt'), 'nidn' => request('nidn')]) }}"
                                class="btn fw-semibold d-flex align-items-center gap-2"
                                style="background-color: #10b981; color: #ffffff; border-color: #10b981; border-radius: 8px; padding: 9px 20px; font-size: 0.875rem; white-space: nowrap;">
                                <i class="bx bx-download" style="font-size: 1rem;"></i> Export XLS
                            </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="mb-4">


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

                    @for ($i = 1; $i <= 12; $i++) <th>Gaji</th>
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
                table {
                  border-collapse: collapse;
                }


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
      processing: true,
      serverSide: true,
      order: [],
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"table-responsive text-nowrap"t><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
      ajax: {
        url: "{{ url('admin/laporan-keuangan') }}",
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
                th,
                td {
                  border: 1px solid rgb(193, 195, 197);
                  /* Garis tepi */
                  padding: 8px;
                  /* Ruang dalam sel */
                  text-align: center;
                  /* Rata tengah */
                }

                thead th {
                  background-color: white;
                  /* Ubah warna latar belakang header menjadi putih */
                }
              </style>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    if (typeof $.fn.select2 === 'undefined') return;

    $('#basic-default-kodept').select2({
        theme: 'bootstrap-5',
        placeholder: 'Cari Kode PT/Nama Perguruan Tinggi',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() { return 'Tidak ditemukan'; },
            searching: function() { return 'Mencari...'; }
        },
        matcher: function(params, data) {
            if (!params.term || params.term.trim() === '') return data;
            var term = params.term.toLowerCase();
            var text = (data.text || '').toLowerCase();
            if (text.indexOf(term) >= 0) return data;
            return null;
        }
    });
});
</script>
@endpush
@endsection