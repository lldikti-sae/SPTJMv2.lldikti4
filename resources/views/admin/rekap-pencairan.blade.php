@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="row">
  <div class="col-12">
    <!-- Filter Form -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-0">Proses Rekapitulasi Pencairan</h5>
        <hr>
        <form method="GET" action="{{ route('rekap-pencairan') }}">
          <div class="row g-2 align-items-end mb-3">
            <!-- Tipe SPTJM -->
            <div class="col-md-3">
              <label for="tipe_sptjm" class="form-label fw-semibold">Pilih Tipe SPTJM</label>
              <select class="form-select h-100" id="tipe_sptjm" name="tipe_sptjm">
                <option value="SPTJM" {{ request('tipe_sptjm', 'SPTJM') == 'SPTJM' ? 'selected' : '' }}>SPTJM</option>
                <option value="TUKIN" {{ request('tipe_sptjm') == 'TUKIN' ? 'selected' : '' }}>TUKIN</option>
              </select>
            </div>

            <!-- Dropdown Pencairan ke -->
            <div class="col-md-3">
              <label for="pencairan_ke" class="form-label fw-semibold">Pencairan ke-</label>
              <select class="form-select h-100" id="pencairan_ke" name="pencairan_ke"
                onchange="this.form.submit()">
                <option value="Semua" {{ $pencairanKe == 'Semua' ? 'selected' : '' }}>Semua</option>
                @for ($i = 1; $i <= 20; $i++)
                  <option value="{{ $i }}" {{ $pencairanKe == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
              </select>
            </div>

            <!-- Tombol Proses -->
            <div class="col-md-auto">
              <button type="submit" name="status" value="Proses"
                class="btn btn-outline-warning w-100 align-self-end {{ $status == 'Proses' ? 'active' : '' }}">
                Proses
              </button>
            </div>

            <!-- Tombol Selesai -->
            <div class="col-md-auto">
              <button type="submit" name="status" value="Selesai"
                class="btn btn-outline-success w-100 align-self-end {{ $status == 'Selesai' ? 'active' : '' }}">
                Selesai
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <hr>

    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Tabel Rekapitulasi</h6>
        <div class="input-group w-100" style="max-width: 300px;">
          <span class="input-group-text"><i class="tf-icons bx bx-search"></i></span>
          <input type="text" class="form-control" placeholder="Search..." id="searchInput" name="search"
            value="">
        </div>
      </div>

      <div class="card-body">
        <div class="table-responsive text-nowrap">
          <table class="table table-bordered table-hover" id="rekapTable">
            <thead>
              <tr>
                <th rowspan="2" style="vertical-align: middle;">No</th>
                <th rowspan="2" style="vertical-align: middle;">Tahun</th>
                <th rowspan="2" style="vertical-align: middle;">Pencairan ke-</th>
                <th rowspan="2" style="vertical-align: middle;">Status Pegawai</th>
                <th rowspan="2" style="vertical-align: middle;">Jenis</th>
                <th rowspan="2" style="vertical-align: middle;">Bank</th>
                <th colspan="3" style="text-align: center;">Nominal</th>
                <th rowspan="2" style="vertical-align: middle; text-align: center;">Aksi</th>
              </tr>
              <tr>
                <th>Jumlah Kotor</th>
                <th>Jumlah Pajak</th>
                <th>Jumlah Bersih</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              @if (isset($data) && count($data))
              @foreach ($data as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->tahun }}</td>
                <td>{{ $item->pencairan_ke }}</td>
                <td>{{ $item->status_pegawai }}</td>
                <td>{{ $item->jenis }}</td>
                <td>{{ $item->bank }}</td>
                <td class="text-end">{{ number_format($item->jumlah_kotor, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($item->jumlah_pajak, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($item->jumlah_bersih, 0, ',', '.') }}
                </td>
                <td>
                  @if (request()->query('status') === "Proses")
                  <div class="show">
                    <form action="{{ route('rekap-pencairan.destroy', $item->no) }}" method="POST"
                      class="delete-form d-inline">
                      @csrf
                      @method('DELETE')

                      {{-- Tombol File PDF --}}
                      <a href="{{ route('admin.print-pencairan', ['id' => $item->no]) }}"
                        target="_blank"
                        class="btn btn-sm btn-secondary me-1 d-inline-flex align-items-center justify-content-center text-center"
                        title="Lihat File PDF" style="width: 36px; height: 36px;">
                        <i class="bx bx-file"></i>
                      </a>

                      {{-- Tombol Export XLS --}}
                      <a href="{{ route('admin.export-pencairan', $item->no) }}"
                        class="btn btn-sm btn-success me-1 d-inline-flex align-items-center justify-content-center text-center"
                        title="Unduh XLS" style="width: 36px; height: 36px;">
                        <i class="bx bx-download"></i>
                      </a>

                      {{-- Tombol Hapus --}}
                      <button type="button"
                        class="btn btn-sm btn-danger me-1 d-inline-flex align-items-center justify-content-center text-center delete-rekap"
                        title="Hapus Data" style="width: 36px; height: 36px;">
                        <i class="bx bx-trash"></i>
                      </button>

                      {{-- Tombol Modal SP2D --}}
                      <button type="button"
                        class="btn btn-sm btn-dark d-inline-flex align-items-center justify-content-center text-center"
                        data-bs-toggle="modal" data-bs-target="#sp2dModal{{ $item->no }}"
                        title="Input SP2D" style="width: 36px; height: 36px;">
                        <i class="bx bx-edit-alt"></i>
                      </button>
                    </form>
                  </div>
                  @endif
                </td>
              </tr>

              <!-- Modal untuk setiap data -->
              <div class="modal fade" id="sp2dModal{{ $item->no }}" tabindex="-1"
                aria-labelledby="sp2dModalLabel{{ $item->no }}" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="sp2dModalLabel{{ $item->no }}">
                        Mohon Diisi</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                    </div>
                    <form action="{{ route('sp2d.simpan') }}" method="POST">
                      @csrf
                      <input type="hidden" name="no" value="{{ $item->no }}">
                      <div class="modal-body">
                        <div class="mb-3">
                          <label for="no_sp2d_{{ $item->no }}" class="form-label">No
                            SP2D</label>
                          <input type="text" class="form-control" id="no_sp2d_{{ $item->no }}"
                            name="no_sp2d" required>
                        </div>
                        <div class="mb-3">
                          <label for="tanggal_sp2d_{{ $item->no }}" class="form-label">Tanggal
                            SP2D</label>
                          <input type="date" class="form-control"
                            id="tanggal_sp2d_{{ $item->no }}" name="tanggal_sp2d" required>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary"
                          data-bs-dismiss="modal">Batal</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              @endforeach
              @else
              <tr>
                <td colspan="10" class="text-center">Tidak ada data ditampilkan</td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
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
  document.querySelectorAll('.status-btn').forEach(function(button) {
    button.addEventListener('click', function() {
      const status = this.getAttribute('data-status');
      const pencairanKe = document.getElementById('pencairan_ke').value;
      const url = `?status=${status}&pencairan_ke=${pencairanKe}`;
      window.location.href = url;
    });
  });

  // delete action: use SptjmAlert.question modal then submit
  document.querySelectorAll('.delete-rekap').forEach(button => {
    button.addEventListener('click', async function () {
      const form = this.closest('.delete-form');
      try {
        const res = await SptjmAlert.question('Apakah Anda Yakin?', 'Data yang dihapus tidak bisa dikembalikan!', {
          confirmButtonText: 'Ya, Hapus!',
          cancelButtonText: 'Batal',
        });
        if (res && res.isConfirmed) {
          // submit the form (contains CSRF + method=DELETE)
          form.submit();
        }
      } catch (e) {
        // fallback to native confirm
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
          form.submit();
        }
      }
    });
  });
</script>

@endsection