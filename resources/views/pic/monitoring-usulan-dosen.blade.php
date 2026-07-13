@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')
<div class="md2-page-header">
    <div class="page-titles">
        <h3>Monitoring Usulan Dosen</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Data Dosen</a></li>
                <li class="breadcrumb-item active">Monitoring Usulan Dosen</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md2-card mb-4">
    <div class="card-body px-4 pb-4 pt-0">

        {{-- Filter Section --}}
        <div class="pt-3 pb-3 mb-3 border-bottom">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('pic.monitoring-usulan-dosen') }}">
                <div class="col-md-3 col-sm-12">
                    <label for="searchInput" class="form-label fw-bold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">NIDN / NUPTK / Nama</label>
                    <input type="text" class="form-control" id="searchInput" name="search"
                        placeholder="Masukan NIDN..." value="{{ request('search') }}" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; height: 38px;">
                </div>

                <div class="col-md-2 col-sm-5">
                    <label for="awalPeriode" class="form-label fw-bold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">Periode Awal</label>
                    <select id="awalPeriode" name="awalPeriode" class="form-select" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
                        @foreach ($bulanIndonesia as $key => $bulan)
                        <option value="{{ $key }}" {{ request('awalPeriode', 1) == $key ? 'selected' : '' }}>
                            {{ $bulan }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto text-center px-1" style="height: 38px; display: flex; align-items: center; justify-content: center;">
                    <span class="text-muted fw-semibold">s.d</span>
                </div>

                <div class="col-md-2 col-sm-5">
                    <label for="akhirPeriode" class="form-label fw-bold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.05em; color: #64748b;">Periode Akhir</label>
                    <select id="akhirPeriode" name="akhirPeriode" class="form-select" style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.88rem; color: #374151; height: 38px;">
                        @foreach ($bulanIndonesia as $key => $bulan)
                        <option value="{{ $key }}" {{ request('akhirPeriode', now()->month) == $key ? 'selected' : '' }}>
                            {{ $bulan }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 col-sm-12 text-md-end align-self-end">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('pic.monitoring-usulan-dosen.export', request()->query()) }}" target="_blank" class="btn btn-success d-flex align-items-center gap-1" style="border-radius: 8px; font-weight: 600; font-size: 0.88rem; height: 38px; padding: 0 20px;">
                            <i class="bx bx-download"></i> Export XLS
                        </a>
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-1" style="border-radius: 8px; font-weight: 600; font-size: 0.88rem; height: 38px; padding: 0 20px; background-color: #0b3d91; border-color: #0b3d91;">
                            <i class="bx bx-search-alt"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover md2-table text-center" id="monitoringTable" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>NIDN</th>
                        <th>NUPTK</th>
                        <th>Nama</th>
                        <th>Jenis</th>
                        <th>Kode PT</th>
                        <th class="text-center" style="text-align: center !important;">PTS</th>
                        <th>Bulan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dosenList as $i => $data)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td class="text-center">{{ $data->NIDN }}</td>
                        <td class="text-center">{{ $data->NUPTK ?? '-' }}</td>
                        <td class="text-start" style="white-space: normal; max-width: 220px;">{{ $data->Nama }}</td>
                        <td class="text-center">{{ $data->Jenis }}</td>
                        <td class="text-center">{{ $data->Kode_PT }}</td>
                        <td style="white-space: normal; max-width: 220px; text-align: center !important;">{{ $data->PTS }}</td>
                        <td class="text-center">
                            <button type="button"
                                class="badge bg-label-warning border-0 py-2 px-3 fw-bold cursor-pointer"
                                style="border-radius: 20px; font-size: 0.78rem; transition: transform 0.2s;"
                                onclick="showDetailModal('{{ addslashes($data->Nama) }}', '{{ addslashes($data->kode_belum_usulan) }}')">
                                <i class="bx bx-calendar-x me-1" style="font-size: 0.85rem;"></i>
                                {{ $data->bulan_belum_usulan }} Bulan
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Tidak ada dosen aktif tanpa usulan.</td>
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
