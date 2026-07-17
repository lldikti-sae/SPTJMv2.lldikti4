@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h3>Validasi Usulan</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Validasi</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pic.validasi-usulan') }}">Validasi Usulan SPTJM</a></li>
                <li class="breadcrumb-item active">Detail Dosen Usulan</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md-card">
    <div class="md-card-inner">
        {{-- Informasi Usulan --}}
        <div class="mb-2">
            <div class="row mb-1">
                <div class="col-md-2 fw-bold">Bulan</div>
                <div class="col-md-10">: {{ $pengajuan->bulan ?? '-' }}</div>
            </div>
            <div class="row mb-1">
                <div class="col-md-2 fw-bold">Kode PT</div>
                <div class="col-md-10">: {{ $pengajuan->kode_pts ?? '-' }}</div>
            </div>
            <div class="row mb-1">
                <div class="col-md-2 fw-bold">Nama PT</div>
                <div class="col-md-10">: {{ $pengajuan->nama_pts ?? '-' }}</div>
            </div>
        </div>

        {{-- Tabel Dosen (dipisah berdasarkan Jenis: PNS vs NON PNS) --}}
        <h6 class="mb-2">Daftar Nama Dosen (PNS) :</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-hover table-bordered">
                <thead style="background-color: #dbdee0;">
                    <tr>
                        <th>No</th>
                        <th>NIDN/NUPTK</th>
                        <th>Nama Dosen</th>
                        <th>BKD</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($dosenPns ?? collect()) as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->nidn }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->kesimpulan_bkd ?? '-' }}</td>
                        <td>
                            @if ($item->aktif == '1')
                            <span class="badge bg-label-primary">Aktif</span>
                            @else
                            <span class="badge bg-label-danger">Tidak Aktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data dosen PNS</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h6 class="mb-2">Daftar Nama Dosen (NON PNS) :</h6>
        <div class="table-responsive mb-4">
            <table class="table table-sm table-hover table-bordered">
                <thead style="background-color: #dbdee0;">
                    <tr>
                        <th>No</th>
                        <th>NIDN/NUPTK</th>
                        <th>Nama Dosen</th>
                        <th>BKD</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($dosenNonPns ?? collect()) as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->nidn }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->kesimpulan_bkd ?? '-' }}</td>
                        <td>
                            @if ($item->aktif == '1')
                            <span class="badge bg-label-primary">Aktif</span>
                            @else
                            <span class="badge bg-label-danger">Tidak Aktif</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data dosen NON PNS</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tombol Aksi --}}
        <div class="d-flex justify-content-center gap-2 mt-4">
            <form id="prosesForm" action="{{ url('/pic/validasi-usulan/' . $pengajuan->id_usulan . '/proses') }}"
                method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="no" value="{{ $pengajuan->no }}">
                <button type="submit" class="btn btn-warning rounded-pill px-4" id="prosesBtn">Proses</button>
            </form>
            <button type="button" class="btn btn-danger rounded-pill px-4"
                onclick="handleTolak('{{ $pengajuan->no }}', '{{ $pengajuan->id_usulan }}', '{{ $pengajuan->bulan }}')">Tolak</button>
            <a href="{{ route('pic.validasi-usulan') }}" class="btn btn-secondary rounded-pill px-4">Kembali</a>
        </div>
    </div>
</div>

<script>
const prosesForm = document.getElementById('prosesForm');
const prosesBtn = document.getElementById('prosesBtn');

// Tambahkan konfirmasi SweetAlert untuk tombol Proses
prosesForm.addEventListener('submit', function(e) {
    e.preventDefault(); // cegah submit dulu

    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data akan diproses dan tidak dapat diubah kembali.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, proses sekarang!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // persist list filters before leaving so list restores state upon return
            try {
                const savedPilih = localStorage.getItem('pic.validasi-usulan.pilihsptjm');
                const savedBulan = localStorage.getItem('pic.validasi-usulan.bulan');
                if (savedPilih) localStorage.setItem('pic.validasi-usulan.pilihsptjm', savedPilih);
                if (savedBulan) localStorage.setItem('pic.validasi-usulan.bulan', savedBulan);
            } catch (e) {}

            // Submit form setelah konfirmasi
            prosesForm.submit();
        }
    });
});

// Fungsi Tolak: samakan perilaku dengan halaman daftar validasi-usulan
function handleTolak(no, idUsulan, bulanNama) {
    Swal.fire({
        title: 'Masukkan Alasan Penolakan',
        input: 'textarea',
        inputPlaceholder: 'Tulis alasan penolakan di sini...',
        showCancelButton: true,
        confirmButtonText: 'Tolak',
        cancelButtonText: 'Batal',
        inputValidator: (value) => {
            if (!value) return 'Alasan penolakan wajib diisi!';
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const alasan = result.value;

            // Opsional: jika ingin, bisa mapping nama bulan ke angka; backend saat ini tidak memakainya
            const mapBulan = {
                'Januari': '01', 'Februari': '02', 'Maret': '03', 'April': '04', 'Mei': '05', 'Juni': '06',
                'Juli': '07', 'Agustus': '08', 'September': '09', 'Oktober': '10', 'November': '11', 'Desember': '12'
            };
            const bulanAngka = mapBulan[bulanNama] || null;

            fetch(`/pic/validasi-usulan/${no}/tolak`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        alasan,
                        bulanAngka,
                        id_usulan: idUsulan
                    })
                })
                .then(async (res) => {
                    const text = await res.text();
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error(`HTTP ${res.status}: ${text.slice(0, 500)}`);
                    }
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil!', 'Usulan berhasil ditolak.', 'success').then(() => {
                            try {
                                // ensure filters are persisted
                                const savedPilih = localStorage.getItem('pic.validasi-usulan.pilihsptjm');
                                const savedBulan = localStorage.getItem('pic.validasi-usulan.bulan');
                                if (savedPilih) localStorage.setItem('pic.validasi-usulan.pilihsptjm', savedPilih);
                                if (savedBulan) localStorage.setItem('pic.validasi-usulan.bulan', savedBulan);
                            } catch (e) {}
                            // console.log(data);
                            window.location.href = '/pic/validasi-usulan';
                        });
                    } else {
                        Swal.fire('Gagal!', data.message || 'Terjadi kesalahan.', 'error');
                    }
                })
                .catch((error) => {
                    Swal.fire('Error!', error?.message || 'Server tidak merespons.', 'error');
                });
        }
    });
}
</script>
@endsection
