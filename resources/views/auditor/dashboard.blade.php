@extends('layouts/contentNavbarLayoutAuditor')

@section('title', 'SPTJM Online')

@section('content')
  <div class="row g-4">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex flex-wrap justify-content-between align-items-start">
            <div>
              <h5 class="mb-1">Dashboard Auditor</h5>
              <div class="text-muted">Ringkasan audit data dosen dan laporan keuangan (Tahun {{ $tahun }})</div>
            </div>
            <div class="d-flex gap-2 mt-2 mt-md-0">
              <a href="{{ route('auditor.data-dosen') }}" class="btn btn-primary btn-sm">
                <i class="bx bx-id-card bx-xs me-1"></i> Audit Data Dosen
              </a>
              <a href="{{ route('auditor.laporan-keuangan') }}" class="btn btn-success btn-sm">
                <i class="bx bx-money bx-xs me-1"></i> Audit Keuangan
              </a>
            </div>
          </div>

          <hr>

          <div class="row g-3">
            <div class="col-12 col-md-3">
              <div class="border rounded p-3 h-100">
                <div class="text-muted">Total Dosen</div>
                <div class="fs-4 fw-semibold">{{ number_format((int) $totalDosen, 0, ',', '.') }}</div>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="border rounded p-3 h-100">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="text-muted">Dosen Aktif</div>
                    <div class="fs-4 fw-semibold">{{ number_format((int) $dosenAktif, 0, ',', '.') }}</div>
                    <div class="small text-muted">Tidak aktif: {{ number_format((int) $dosenTidakAktif, 0, ',', '.') }}</div>
                  </div>
                  <div>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#dosenAktifDetails" aria-expanded="false" aria-controls="dosenAktifDetails">
                      <i class="bx bx-chevron-down"></i>
                    </button>
                  </div>
                </div>

                <div class="collapse mt-2" id="dosenAktifDetails">
                  <ul class="list-unstyled mb-0 small">
                    <li>PNS Aktif: <strong>{{ number_format((int) ($jumlahPnsAktif ?? 0), 0, ',', '.') }}</strong></li>
                    <li>PNS Tidak Aktif: <strong>{{ number_format((int) ($jumlahPnsTidakAktif ?? 0), 0, ',', '.') }}</strong></li>
                    <li>Non-PNS Aktif: <strong>{{ number_format((int) ($jumlahNonPnsAktif ?? 0), 0, ',', '.') }}</strong></li>
                    <li>Non-PNS Tidak Aktif: <strong>{{ number_format((int) ($jumlahNonPnsTidakAktif ?? 0), 0, ',', '.') }}</strong></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="border rounded p-3 h-100">
                <div class="text-muted">PTS Aktif</div>
                <div class="fs-4 fw-semibold">{{ number_format((int) $ptsAktif, 0, ',', '.') }}</div>
                <div class="small text-muted">PTS Tidak Aktif: {{ number_format((int) $ptsTidakAktif, 0, ',', '.') }}</div>
              </div>
            </div>
            <div class="col-12 col-md-3">
              <div class="border rounded p-3 h-100">
                <div class="text-muted">Eligible Span</div>
                <div class="fs-4 fw-semibold">{{ number_format((int) $dosenEligible, 0, ',', '.') }}</div>
                <div class="small text-muted">Tidak Eligible Span: {{ number_format((int) $dosenTidakEligible, 0, ',', '.') }}</div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="border rounded p-3 h-100">
                <div class="text-muted">Total TPD</div>
                <div class="fs-4 fw-semibold">{{ number_format((float) ($finance->total_tpd ?? 0), 0, ',', '.') }}</div>
                <div class="small text-muted">Akumulasi 12 bulan</div>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="border rounded p-3 h-100">
                <div class="text-muted">Total TKGB</div>
                <div class="fs-4 fw-semibold">{{ number_format((float) ($finance->total_tkgb ?? 0), 0, ',', '.') }}</div>
                <div class="small text-muted">Akumulasi 12 bulan</div>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="border rounded p-3 h-100">
                <div class="text-muted">Total (TPD + TKGB)</div>
                <div class="fs-4 fw-semibold">{{ number_format((float) ($finance->total_bayar ?? 0), 0, ',', '.') }}</div>
                <div class="small text-muted">Ringkasan pembayaran</div>
              </div>
            </div>

            <!-- <div class="col-12">
              <div class="alert alert-warning mb-0">
                <div class="small">
                  Data dosen tanpa NIDN dan tanpa NUPTK: <span class="fw-semibold">{{ number_format((int) $dosenMissingIdentifier, 0, ',', '.') }}</span>.
                  Gunakan menu audit untuk menelusuri detailnya.
                </div>
              </div>
            </div> -->

          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h6 class="mb-0">Top 5 PTS berdasarkan jumlah dosen</h6>
          <div class="text-muted small">Tahun {{ $tahun }}</div>
        </div>
        <div class="card-body pt-0">
          <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead style="background-color: #dbdee0;">
              <tr>
                <th>Kode PTS</th>
                <th>Nama PTS</th>
                <th class="text-end">Total Dosen</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($topPtsByDosen as $row)
                <tr>
                  <td>{{ $row->kode_pt ?? '-' }}</td>
                  <td>{{ $row->pts ?? '-' }}</td>
                  <td class="text-end">{{ number_format((int) ($row->total ?? 0), 0, ',', '.') }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-muted">Tidak ada data.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h6 class="mb-0">Data Dosen Pensiun Berjalan</h6>
          <div class="text-muted small">Tahun {{ $tahun }}</div>
        </div>
        <div class="card-body pt-0">
          <div>
            <table class="table table-sm table-hover" id="dosenPensiunTable">
              <thead style="background-color: #dbdee0;">
                <tr>
                  <th>NIDN</th>
                  <th>NUPTK</th>
                  <th>Nama Dosen</th>
                  <th>Nama PTS</th>
                  <th>TMT Pensiun</th>
                  <th>Usia</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        if (typeof $ === 'undefined' || typeof $.fn.DataTable === 'undefined') {
          return;
        }

        $('#dosenPensiunTable').DataTable({
          processing: true,
          serverSide: true,
          paging: true,
          lengthChange: true,
          dom: '<"row mx-0 mt-3 mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"table-responsive text-nowrap"t><"row mx-0 mt-2 mb-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
          ajax: {
            url: '{{ route('auditor.dashboard.dosen-pensiun.data') }}'
          },
          columns: [
            { data: 'nidn', name: 'nidn' },
            { data: 'nuptk', name: 'nuptk' },
            { data: 'nama', name: 'nama' },
            { data: 'pts', name: 'pts' },
            { data: 'tmt_pensiun', name: 'tmt_pensiun' },
            { data: 'usia', name: 'usia' },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
          ],
          order: [[3, 'asc']],
          pagingType: 'simple_numbers',
          language: {
            paginate: {
              first: 'Awal',
              last: 'Akhir',
              next: 'â†’',
              previous: 'â†'
            },
            zeroRecords: 'Data tidak ditemukan',
            infoEmpty: 'Tidak ada data tersedia',
            searchPlaceholder: 'Cari data...',
            search: 'Cari Data:'
          }
        });
      });
    </script>
  @endpush
@endsection
