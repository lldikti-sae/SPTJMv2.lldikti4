@extends('layouts/contentNavbarLayout')

@section('title', 'Detail Rekap - SPTJM Online')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Detail Rekap: {{ $rekap->periode }}</h5>
                        <div class="small text-muted mt-1">
                            Bank: <b>{{ $rekap->bank }}</b> | Tipe: <b>{{ $rekap->tipe }}</b> | Jenis: <b>{{ $rekap->jenis ?: 'Semua' }}</b> | Total Pegawai (aktif di rekap): <b>{{ $rekap->pegawai }}</b> | Total Nominal: <b>Rp {{ number_format((float)$rekap->total_nominal, 0, ',', '.') }}</b>
                        </div>
                    </div>
                    <a href="{{ route('admin.kekurangan-bayar') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
                <div class="card-body bg-white">
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="bx bx-info-circle me-2"></i>
                        <div>
                            Jika ada dosen yang tidak ingin diikutkan dalam pembayaran SP2D massal untuk rekap ini, silakan klik tombol <b>Keluarkan</b>. Dosen yang dikeluarkan tidak akan diproses SP2D-nya.
                        </div>
                    </div>

                    <form action="{{ route('admin.kekurangan-bayar.detail-rekap', $rekap->id) }}" method="GET" class="mb-3 d-flex gap-2">
                        <input type="text" name="search" class="form-control" placeholder="Cari NIDN / Nama..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">Cari</button>
                        <a href="{{ route('admin.kekurangan-bayar.detail-rekap', $rekap->id) }}" class="btn btn-outline-secondary">Reset</a>
                    </form>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-bordered table-striped table-sm" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>NIDN</th>
                                    <th>NUPTK</th>
                                    <th>Nama</th>
                                    <th class="text-center">Jenis</th>
                                    <th class="text-center">Bank</th>
                                    <th class="text-end">Nominal</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dosenRows as $idx => $dosen)
                                    <tr>
                                        <td class="text-center">{{ $dosenRows->firstItem() + $idx }}</td>
                                        <td>{{ !empty($dosen->NIDN) ? $dosen->NIDN : '-' }}</td>
                                        <td>{{ !empty($dosen->NUPTK) ? $dosen->NUPTK : '-' }}</td>
                                        <td>{{ $dosen->Nama }}</td>
                                        <td class="text-center">{{ $dosen->Jenis }}</td>
                                        <td class="text-center">{{ $dosen->Bank }}</td>
                                        <td class="text-end fw-semibold {{ $isKurang ? 'text-danger' : 'text-success' }}">
                                            Rp {{ number_format(abs($dosen->kesimpulan), 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            @if(!empty($rekap->sp2d))
                                                <span class="badge bg-secondary" style="font-size:10px;">Sudah SP2D</span>
                                            @else
                                                <form action="{{ route('admin.kekurangan-bayar.exclude-rekap', $rekap->id) }}" method="POST" class="d-inline form-exclude" data-nama="{{ $dosen->Nama }}">
                                                    @csrf
                                                    <input type="hidden" name="nidn" value="{{ $dosen->NIDN }}">
                                                    <input type="hidden" name="nominal" value="{{ abs($dosen->kesimpulan) }}">
                                                    <button type="submit" class="sptjm-icon-btn sptjm-btn-delete" title="Keluarkan">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Tidak ada data dosen yang sesuai kriteria pencarian atau semua sudah dikeluarkan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-2 w-100">
                        {{ $dosenRows->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.form-exclude');
    
    // Prefer SptjmAlert (SweetAlert wrapper), then legacy alertApi, else null
    const alertApi = (typeof window !== 'undefined') ? (window.SptjmAlert || window.alertApi || null) : null;
    
    forms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const nama = this.getAttribute('data-nama');
            const html = `Apakah Anda yakin ingin mengeluarkan <b>${nama}</b> dari rekap ini? <br><small class="text-muted">Dosen yang dikeluarkan tidak akan terbayar pada saat proses SP2D rekap ini berjalan.</small>`;
            
            if (alertApi) {
                const r = await alertApi.question('Konfirmasi Pengeluaran', html, { confirmButtonText: 'Ya, Keluarkan', confirmButtonColor: '#d33' });
                if (r && r.isConfirmed) this.submit();
            } else {
                if(confirm('Keluarkan dosen ini dari rekap?')) {
                    this.submit();
                }
            }
        });
    });
});
</script>
@endpush
