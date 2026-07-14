@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .card-pencairan {
        border: 1.5px solid #dbeafe !important;
        box-shadow: 0 10px 30px rgba(26, 86, 219, 0.15) !important;
        border-radius: 12px !important;
        background: #ffffff !important;
    }
    .md2-table th, .md2-table td {
        padding: 6px 12px !important;
        font-size: 0.82rem !important;
        line-height: 1.2 !important;
    }
</style>

<div class="content-wrapper">

    <!-- Card Utama -->
    <div class="card card-pencairan mb-4">
        <div class="card-body px-4 pb-4 pt-0">
            
            <!-- Filter Section -->
            <div class="pt-3 pb-3 mb-3 border-bottom">
                <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color: #0f2b5c; font-size: 0.95rem;">
                    <i class="bx bx-filter" style="color: #d97706; font-size: 1.15rem;"></i>
                    Proses Rekapitulasi Pencairan
                </h6>
                <form method="GET" action="{{ route('rekap-pencairan') }}" id="filterPencairanForm">
                    <div class="d-flex flex-wrap align-items-end gap-3">
                        <!-- Tipe SPTJM -->
                        <div style="min-width: 200px;">
                            <label for="tipe_sptjm" class="form-label fw-bold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.06em; color: #64748b;">Tipe Pencairan</label>
                            <select class="form-select" id="tipe_sptjm" name="tipe_sptjm"
                                style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; color: #374151;">
                                <option value="SPTJM" {{ request('tipe_sptjm', 'SPTJM') == 'SPTJM' ? 'selected' : '' }}>SPTJM</option>
                                <option value="TUKIN" {{ request('tipe_sptjm') == 'TUKIN' ? 'selected' : '' }}>TUKIN</option>
                            </select>
                        </div>

                        <!-- Dropdown Pencairan ke- -->
                        <div style="min-width: 200px;">
                            <label for="pencairan_ke" class="form-label fw-bold text-uppercase mb-1" style="font-size: 0.68rem; letter-spacing: 0.06em; color: #64748b;">Pencairan ke-</label>
                            <select class="form-select" id="pencairan_ke" name="pencairan_ke"
                                style="border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; color: #374151;">
                                <option value="Semua" {{ $pencairanKe == 'Semua' ? 'selected' : '' }}>Semua Tahap</option>
                                @for ($i = 1; $i <= 20; $i++)
                                    <option value="{{ $i }}" {{ $pencairanKe == $i ? 'selected' : '' }}>Tahap {{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Tombol Proses & Selesai berdampingan (segmented button style) -->
                        <div class="d-flex" style="border: 1.5px solid #e2e8f0; border-radius: 8px; overflow: hidden; align-self: flex-end;">
                            <button type="submit" name="status" value="Proses"
                                style="border: none; border-right: 1.5px solid #e2e8f0; padding: 9px 24px; font-weight: 700; font-size: 0.875rem; cursor: pointer; transition: all 0.2s;
                                    {{ $status == 'Proses'
                                        ? 'background-color: #f59e0b; color: #ffffff;'
                                        : 'background-color: #ffffff; color: #64748b;' }}">
                                Proses
                            </button>
                            <button type="submit" name="status" value="Selesai"
                                style="border: none; padding: 9px 24px; font-weight: 700; font-size: 0.875rem; cursor: pointer; transition: all 0.2s;
                                    {{ $status == 'Selesai'
                                        ? 'background-color: #10b981; color: #ffffff;'
                                        : 'background-color: #ffffff; color: #64748b;' }}">
                                Selesai
                            </button>
                        </div>

                        <!-- Sinkronkan Data di kanan -->
                        <div class="ms-auto" style="align-self: flex-end;">
                            <a href="{{ route('rekap-pencairan') }}"
                                class="btn d-flex align-items-center gap-2 fw-semibold"
                                style="background-color: #0f2b5c; color: #ffffff; border-color: #0f2b5c; border-radius: 8px; padding: 9px 20px; font-size: 0.875rem; white-space: nowrap; text-decoration: none;">
                                <i class="bx bx-refresh" style="font-size: 1.05rem;"></i> Sinkronkan Data
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table Header Toolbar -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2" style="color: #0f2b5c;">
                    <i class="bx bx-table" style="color: #1a56db; font-size: 1.15rem;"></i> Tabel Rekapitulasi
                </h6>
                <div class="d-flex align-items-center"
                    style="border: 1.5px solid #e2e8f0; border-radius: 50px; background: #f8fafc; padding: 5px 14px; min-width: 240px;">
                    <i class="bx bx-search me-2" style="color: #94a3b8; font-size: 0.95rem;"></i>
                    <input type="text" id="searchInput" placeholder="Cari data rekapitulasi..."
                        style="border: none; background: transparent; outline: none; font-size: 0.84rem; color: #374151; width: 100%;">
                </div>
            </div>

            <div class="table-responsive text-nowrap">
                <table class="table table-hover md2-table text-center" id="rekapTable" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th rowspan="2" style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; vertical-align: middle; border-right: 1px solid #e2e8f0;">NO</th>
                            <th rowspan="2" style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; vertical-align: middle; border-right: 1px solid #e2e8f0;">TAHUN</th>
                            <th rowspan="2" style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; vertical-align: middle; border-right: 1px solid #e2e8f0;">PENCAIRAN</th>
                            <th rowspan="2" style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; vertical-align: middle; border-right: 1px solid #e2e8f0; white-space: nowrap !important;">STATUS PEGAWAI</th>
                            <th rowspan="2" style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; vertical-align: middle; border-right: 1px solid #e2e8f0;">JENIS</th>
                            <th rowspan="2" style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; vertical-align: middle; border-right: 1px solid #e2e8f0;">BANK</th>
                            <th colspan="3" style="background-color: #eff6ff !important; color: #1a56db !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; text-align: center !important; border-bottom: 2px solid #bfdbfe; border-right: 1px solid #e2e8f0;">NOMINAL (IDR)</th>
                            <th rowspan="2" style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; vertical-align: middle; text-align: center;">AKSI</th>
                        </tr>
                        <tr>
                            <th style="background-color: #f0f9ff !important; color: #475569 !important; font-size: 0.72rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; border-right: 1px solid #e2e8f0; text-align: center !important;">JUMLAH KOTOR</th>
                            <th style="background-color: #f0f9ff !important; color: #ef4444 !important; font-size: 0.72rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; border-right: 1px solid #e2e8f0; text-align: center !important;">PAJAK</th>
                            <th style="background-color: #f0f9ff !important; color: #10b981 !important; font-size: 0.72rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; border-right: 1px solid #e2e8f0; text-align: center !important;">JUMLAH BERSIH</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @if (isset($data) && count($data))
                        @foreach ($data as $item)
                        @php
                            // Badge warna bank
                            $bankColors = [
                                'BRI'     => ['bg' => '#1a56db', 'text' => '#fff'],
                                'MANDIRI' => ['bg' => '#f59e0b', 'text' => '#fff'],
                                'BNI'     => ['bg' => '#ef4444', 'text' => '#fff'],
                                'BTN'     => ['bg' => '#f97316', 'text' => '#fff'],
                                'BSI'     => ['bg' => '#10b981', 'text' => '#fff'],
                            ];
                            $bankUp = strtoupper(trim($item->bank ?? ''));
                            $bankBg  = $bankColors[$bankUp]['bg']  ?? '#64748b';
                            $bankTxt = $bankColors[$bankUp]['text'] ?? '#fff';

                            // Badge status pegawai
                            $isPns = strtoupper(trim($item->status_pegawai ?? '')) === 'PNS';
                            $spBg   = $isPns ? '#dbeafe' : '#fbe0e0';
                            $spText = $isPns ? '#1a56db' : '#b92c2c';
                        @endphp
                        <tr>
                            <td class="fw-semibold" style="color: #374151;">{{ $loop->iteration }}</td>
                            <td style="color: #374151;">{{ $item->tahun }}</td>
                            <td>
                                <span style="display:inline-flex; align-items:center; justify-content:center; background:#dbeafe; color:#1a56db; border-radius:50%; width:28px; height:28px; font-size:0.8rem; font-weight:700;">{{ $item->pencairan_ke }}</span>
                            </td>
                            <td>
                                <span class="badge" style="display:inline-flex; align-items:center; justify-content:center; text-align:center; background:{{ $spBg }} !important; color:{{ $spText }} !important; border-radius:20px !important; padding:3px 12px !important; font-size:0.72rem !important; font-weight:700 !important; line-height:1.2 !important; min-width: 90px; white-space: nowrap !important;">{{ strtoupper($item->status_pegawai) }}</span>
                            </td>
                            <td style="color:#374151; font-weight:600;">{{ $item->jenis }}</td>
                            <td>
                                <span class="badge-bank" style="display:inline-block; background:{{ $bankBg }}; color:{{ $bankTxt }} !important; border-radius:6px; padding:3px 8px; font-size:0.72rem; font-weight:700;">{{ strtoupper($item->bank) }}</span>
                            </td>
                            <td class="text-center fw-semibold" style="color:#0f2b5c;">{{ number_format($item->jumlah_kotor, 0, ',', '.') }}</td>
                            <td class="text-center fw-semibold" style="color:#ef4444;">{{ number_format($item->jumlah_pajak, 0, ',', '.') }}</td>
                            <td class="text-center fw-bold" style="color:#10b981;">{{ number_format($item->jumlah_bersih, 0, ',', '.') }}</td>
                            <td>
                                @if (request()->query('status') === "Proses")
                                <div class="show">
                                    <form action="{{ route('rekap-pencairan.destroy', $item->no) }}" method="POST" class="delete-form d-inline">
                                        @csrf
                                        @method('DELETE')

                                        {{-- Tombol File PDF --}}
                                        <a href="{{ route('admin.print-pencairan', ['id' => $item->no]) }}" target="_blank" class="sptjm-icon-btn sptjm-btn-print me-1" title="Lihat File PDF">
                                            <i class="bx bx-file"></i>
                                        </a>

                                        {{-- Tombol Export XLS --}}
                                        <a href="{{ route('admin.export-pencairan', $item->no) }}" class="sptjm-icon-btn sptjm-btn-reset me-1" title="Unduh XLS">
                                            <i class="bx bx-download"></i>
                                        </a>

                                        {{-- Tombol Hapus --}}
                                        <button type="button" class="sptjm-icon-btn sptjm-btn-delete me-1 delete-rekap" title="Hapus Data">
                                            <i class="bx bx-trash"></i>
                                        </button>

                                        {{-- Tombol Modal SP2D --}}
                                        <button type="button" class="sptjm-icon-btn sptjm-btn-edit" data-bs-toggle="modal" data-bs-target="#sp2dModal{{ $item->no }}" title="Input SP2D">
                                            <i class="bx bx-edit-alt"></i>
                                        </button>
                                    </form>
                                </div>
                                @endif

                                <!-- Modal untuk setiap data -->
                                <div class="modal fade text-start" id="sp2dModal{{ $item->no }}" tabindex="-1" aria-labelledby="sp2dModalLabel{{ $item->no }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
                                    <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; padding: 20px;">
                                        <h5 class="modal-title fw-bold text-dark" id="sp2dModalLabel{{ $item->no }}" style="color: #0f2b5c !important;">Mohon Diisi</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('sp2d.simpan') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="no" value="{{ $item->no }}">
                                        <div class="modal-body" style="padding: 24px;">
                                            <div class="mb-3">
                                                <label for="no_sp2d_{{ $item->no }}" class="form-label fw-bold text-muted text-uppercase" style="font-size: 0.72rem;">No SP2D</label>
                                                <input type="text" class="form-control" id="no_sp2d_{{ $item->no }}" name="no_sp2d" required style="border-color: #cbd5e1; border-radius: 8px;">
                                            </div>
                                            <div class="mb-3">
                                                <label for="tanggal_sp2d_{{ $item->no }}" class="form-label fw-bold text-muted text-uppercase" style="font-size: 0.72rem;">Tanggal SP2D</label>
                                                <input type="date" class="form-control" id="tanggal_sp2d_{{ $item->no }}" name="tanggal_sp2d" required style="border-color: #cbd5e1; border-radius: 8px;">
                                            </div>
                                        </div>
                                        <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 20px;">
                                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Batal</button>
                                            <button type="submit" class="btn btn-primary px-4" style="background-color: #0f2b5c; border-color: #0f2b5c; border-radius: 8px;">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                            </td>
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">Tidak ada data ditampilkan</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- alert -->
@if (session('success') || session('error'))
<script>
  Swal.fire({
    icon: "{{ session('success') ? 'success' : 'error' }}",
    title: "{{ session('success') ? 'Berhasil!' : 'Gagal!' }}",
    text: "{{ session('success') ?? session('error') }}"
  });
</script>
@endif

<script>
  // Loading saat submit filter
  const filterForm = document.getElementById("filterPencairanForm");
  if(filterForm) {
      filterForm.addEventListener("submit", function() {
          Swal.fire({
              title: 'Mohon Tunggu...',
              html: `
                <div class="d-flex justify-content-center align-items-center flex-column">
                  <div class="spinner-border spinner-border-lg" style="color: #0f2b5c;" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <div class="mt-3 fw-semibold text-muted" style="font-size: 0.9rem;">Sedang memproses dan memfilter data rekapan...</div>
                </div>
              `,
              showConfirmButton: false,
              allowOutsideClick: false,
              allowEscapeKey: false,
              backdrop: true,
              scrollbarPadding: false
          });
      });
  }

  // Fitur Pencarian
  document.getElementById("searchInput").addEventListener("keyup", function() {
      const value = this.value.toLowerCase();
      const rows = document.querySelectorAll("#rekapTable tbody tr");
      rows.forEach(row => {
          const text = row.textContent.toLowerCase();
          row.style.display = text.includes(value) ? "" : "none";
      });
  });

  // delete action: use Swal.fire directly
  document.querySelectorAll('.delete-rekap').forEach(button => {
    button.addEventListener('click', function () {
      const form = this.closest('.delete-form');
      Swal.fire({
        title: 'Apakah Anda Yakin?',
        text: 'Data yang dihapus tidak bisa dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
</script>

@endsection