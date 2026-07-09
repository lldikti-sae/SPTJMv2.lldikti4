@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')
<div class="card">
  <h5 class="card-header">Data Dosen Belum Ada Usulan</h5>

  {{-- Form Filter --}}
  <div class="container mb-4">
    <hr />
    <form class="row g-3 align-items-end" method="GET" action="{{ route('pic.monitoring-usulan-dosen') }}">
      <div class="col-md-2">
        <label for="searchInput" class="form-label fw-semibold">NIDN</label>
        <div class="input-group">
          <input type="text" class="form-control" id="searchInput" name="search"
            placeholder="Masukan NIDN...">
        </div>
      </div>

      <div class="col-md-2">
        <label for="awalPeriode" class="form-label fw-semibold">Periode Awal</label>
        <select id="awalPeriode" name="awalPeriode" class="form-select">
          @foreach ($bulanIndonesia as $key => $bulan)
          <option value="{{ $key }}" {{ request('awalPeriode', 1) == $key ? 'selected' : '' }}>
            {{ $bulan }}
          </option>
          @endforeach
        </select>
      </div>

      <div class="col-auto">
        <label class="fw-semibold text-center d-block">s.d</label>
      </div>

      <div class="col-md-2">
        <label for="akhirPeriode" class="form-label fw-semibold">Periode Akhir</label>
        <select id="akhirPeriode" name="akhirPeriode" class="form-select">
          @foreach ($bulanIndonesia as $key => $bulan)
          <option value="{{ $key }}"
            {{ request('akhirPeriode', now()->month) == $key ? 'selected' : '' }}>
            {{ $bulan }}
          </option>
          @endforeach
        </select>
      </div>

      <div class="col-auto align-self-end">
        <button type="submit" class="btn btn-primary me-2">Tampilkan</button>
        <a href="{{ route('pic.monitoring-usulan-dosen.export', request()->query()) }}" target="_blank" class="btn btn-success">Export Xls</a>
      </div>
    </form>
  </div>

  <div class="card-body">
    {{-- Tabel Data --}}
    <div class="table-responsive">
      <table class="table table-bordered align-middle text-nowrap w-100 table-hover" id="monitoringTable">
        <thead class="text-center" style="background-color: #dbdee0;">
          <tr>
            <th>No</th>
            <th>NIDN</th>
            <th>NUPTK</th>
            <th>Nama</th>
            <th>Jenis</th>
            <th>Kode PT</th>
            <th>PTS</th>
            <th>Bulan</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($dosenList as $i => $data)
          <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td class="text-center">{{ $data->NIDN }}</td>
            <td class="text-center">{{ $data->NUPTK ?? '' }}</td>
            <td class="text-wrap">{{ $data->Nama }}</td>
            <td class="text-center">{{ $data->Jenis }}</td>
            <td class="text-center">{{ $data->Kode_PT }}</td>
            <td class="text-wrap">{{ $data->PTS }}</td>
            <td class="text-center">
              <a href="javascript:void(0);" class="text-decoration-underline text-primary"
                onclick="showDetailModal('{{ $data->Nama }}', '{{ $data->kode_belum_usulan }}')">
                {{ $data->bulan_belum_usulan }} Bulan
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="text-center text-muted">Tidak ada dosen aktif tanpa usulan.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Modal Detail Bulan --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Bulan Belum Diusulkan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <p><strong>Nama :</strong> <span id="modalNamaDosen"></span></p>
        <div id="modalListBulan" style="font-family: monospace;"></div>
      </div>
    </div>
  </div>
</div>



{{-- JavaScript --}}
<script>
  $(document).ready(function() {
    var table = $('#monitoringTable').DataTable({
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
      columnDefs: [
        { orderable: false, targets: [6] }
      ],
      language: {
        lengthMenu: "Show _MENU_ entries",
        zeroRecords: "Tidak ada data yang cocok",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
        paginate: { previous: "Sebelumnya", next: "Berikutnya" }
      }
    });

    // Hubungkan input pencarian kustom ke DataTables
    $('#searchInput').on('keyup', function() {
      table.search(this.value).draw();
    });

    // Jika ingin, fokus pencarian bawaan DataTables bisa disembunyikan via CSS
  });

  // Tampilkan Detail Modal (tetap menggunakan fungsi vanilla untuk bootstrap 5)
  function showDetailModal(nama, kodeBelum) {
    document.getElementById('modalNamaDosen').textContent = nama;
    const kodeList = kodeBelum.split(',').map(k => k.trim()).filter(k => k !== '');
    const formatted = kodeList.map(bulan => {
      const padded = bulan.padEnd(10, ' ');
      return `${padded}: Belum Diusulkan`;
    }).join('\n');
    document.getElementById('modalListBulan').innerHTML = `<pre>${formatted}</pre>`;
    const modal = new bootstrap.Modal(document.getElementById('modalDetail'));
    modal.show();
  }
</script>
@endsection
