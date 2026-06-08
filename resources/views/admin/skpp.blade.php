@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online - SKPP')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card" style="width: 100%; padding: 10px;">
    <h5 class="card-header text-start p-2">SKPP</h5>
    <hr>
    <div class="d-flex justify-content-between align-items-center mb-3 px-3">
        {{-- Search --}}
        <form class="d-flex align-items-center" method="GET" action="{{ route('admin.skpp') }}">
            <div class="input-group" style="width: 320px;">
                <input type="text" class="form-control form-control-sm" name="search"
                    placeholder="Cari NIDN/Nama/PTS..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bx bx-search"></i></button>
            </div>
        </form>
        {{-- Buat SKPP Button --}}
        <button class="btn btn-sm btn-primary" id="btnBuatSkpp" data-bs-toggle="modal" data-bs-target="#modalSkpp">
            <i class="bx bx-plus bx-sm me-1"></i> Buat SKPP
        </button>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-sm table-hover" id="skppTable">
            <thead style="background-color: #dbdee0;">
                <tr>
                    <th>No</th>
                    <th>NIDN</th>
                    <th>NUPTK</th>
                    <th>Nama Dosen</th>
                    <th>Kode PTS</th>
                    <th>Nama PTS</th>
                    <th>Tahun</th>
                    <th>Jenis Surat</th>
                    <th>Status</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($skppList as $idx => $skpp)
                @php 
                    $detail = json_decode($skpp->pesan, true) ?? []; 
                @endphp
                <tr>
                    <td class="text-center">{{ $skppList->firstItem() + $idx }}</td>
                    <td>{{ $skpp->nidn ?: '-' }}</td>
                    <td>{{ $skpp->nuptk ?: '-' }}</td>
                    <td>{{ $detail['nama'] ?? '-' }}</td>
                    <td class="text-center">{{ $skpp->kode_pts ?: '-' }}</td>
                    <td>{{ $detail['pts'] ?? '-' }}</td>
                    <td class="text-center">{{ $detail['tahun'] ?? '-' }}</td>
                    <td>{{ $skpp->jenis_pengajuan }}</td>
                    <td class="text-center">
                        @if($skpp->status === 'open')
                            <span class="badge bg-label-warning">Proses</span>
                        @elseif($skpp->status === 'setuju')
                            <span class="badge bg-label-success">Selesai</span>
                        @elseif($skpp->status === 'tolak')
                            <span class="badge bg-label-danger">Ditolak</span>
                        @else
                            <span class="badge bg-label-secondary">{{ ucfirst($skpp->status) }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($skpp->created_at)->format('d-m-Y H:i') }}</td>
                    <td class="text-center">
                        <div class="d-flex justify-content-center gap-1">
                            <a href="{{ route('admin.skpp.cetak', $skpp->id) }}" class="btn btn-sm btn-outline-info" target="_blank" title="Cetak Surat">
                                <i class="bx bx-printer"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusSkpp({{ $skpp->id }})" title="Hapus Surat">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center text-muted">Belum ada data SKPP.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- Pagination --}}
    <div class="d-flex justify-content-end mt-3 me-3">
        {{ $skppList->links('pagination::simple-bootstrap-5') }}
    </div>
</div>

<style>
/* Fix z-index issue where SweetAlert goes behind Bootstrap modal */
.swal2-container {
    z-index: 99999 !important;
}
</style>

{{-- Modal Buat SKPP --}}
<div class="modal fade" id="modalSkpp" tabindex="-1" aria-labelledby="modalSkppLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSkppLabel">Buat SKPP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Step 1: Search NIDN/NUPTK --}}
                <div id="skppStep1">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cari Dosen</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="skppSearchInput"
                                placeholder="Masukkan NIDN / NUPTK dosen...">
                            <button type="button" class="btn btn-primary" id="btnSkppSearch">
                                <i class="bx bx-search me-1"></i> Cari
                            </button>
                        </div>
                        <small class="text-muted">Masukkan NIDN atau NUPTK dosen untuk mencari data.</small>
                    </div>
                    <div id="skppSearchResult" style="display: none;">
                        <div id="skppSearchLoading" class="text-center py-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Mencari data dosen...</div>
                        </div>
                        <div id="skppSearchNotFound" class="alert alert-warning" style="display: none;">
                            <i class="bx bx-error-circle me-1"></i> Dosen tidak ditemukan. Periksa kembali NIDN/NUPTK yang dimasukkan.
                        </div>
                    </div>
                </div>

                {{-- Step 2: Data Dosen (shown after search) --}}
                <div id="skppStep2" style="display: none;">
                    <hr>
                    <h6 class="fw-bold mb-3"><i class="bx bx-user me-1"></i> Data Dosen</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">NIDN / NUPTK</label>
                            <input type="text" class="form-control" id="skppDosenNidnNuptk" readonly
                                style="background-color: #eceef1;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Dosen</label>
                            <input type="text" class="form-control" id="skppDosenNama" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jabatan - Status</label>
                            <input type="text" class="form-control" id="skppDosenJabatanStatus" readonly
                                style="background-color: #eceef1;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode PT - Perguruan Tinggi</label>
                            <input type="text" class="form-control" id="skppDosenKodePt" readonly
                                style="background-color: #eceef1;">
                        </div>
                    </div>

                    {{-- Pilih Tahun --}}
                    <hr>
                    <h6 class="fw-bold mb-3"><i class="bx bx-calendar me-1"></i> Pilih Tahun</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <select class="form-select" id="skppTahunSelect">
                                <option value="">-- Pilih Tahun --</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-outline-primary" id="btnSkppLihatTahun">
                                <i class="bx bx-show me-1"></i> Tampilkan
                            </button>
                        </div>
                    </div>

                    {{-- Detail Bulan Belum Diusulkan --}}
                    <div id="skppDetailBulan" style="display: none;">
                        <hr>
                        <h6 class="fw-bold mb-3"><i class="bx bx-list-ul me-1"></i> Detail Bulan Belum Diusulkan</h6>
                        <div id="skppDetailBulanLoading" class="text-center py-3" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span class="ms-2">Memuat data...</span>
                        </div>
                        <div id="skppDetailBulanEmpty" class="alert alert-success" style="display: none;">
                            <i class="bx bx-check-circle me-1"></i> Semua bulan sudah diusulkan pada tahun ini.
                        </div>
                        <div id="skppDetailBulanList" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kode Usulan</th>
                                            <th>Bulan & Tahun</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="skppDetailBulanTbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Buttons: Buat Surat --}}
                    <div id="skppButtonArea" style="display: none;">
                        <hr>
                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" class="btn btn-success" id="btnBuatSuratKeterangan">
                                <i class="bx bx-file me-1"></i> Buat Surat Keterangan
                            </button>
                            <button type="button" class="btn btn-primary" id="btnBuatSuratSkpp">
                                <i class="bx bx-file-blank me-1"></i> Buat Surat SKPP
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Preview & Edit SKPP --}}
                <div id="skppStep3" style="display: none;">
                    <hr>
                    <h6 class="fw-bold mb-3"><i class="bx bx-edit me-1"></i> Preview & Lengkapi Data SKPP</h6>
                    <form id="formSkppPreview">
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Nomor Surat SKPP / Keterangan</label>
                                <input type="text" class="form-control" id="prev_nomor_skpp" name="nomor_skpp" placeholder="Misal: 3137/LL4/PR/2026">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nomor Surat dari PTS</label>
                                <input type="text" class="form-control" id="prev_nomor_surat_pts" name="nomor_surat_pts" placeholder="Misal: 1627/SPm/01/2026">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Surat dari PTS</label>
                                <input type="text" class="form-control" id="prev_tanggal_surat_pts" name="tanggal_surat_pts" placeholder="Misal: 18 Mei 2026">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nomor Surat Lolos Butuh</label>
                                <input type="text" class="form-control" id="prev_nomor_surat_lolos_butuh" name="nomor_surat_lolos_butuh" placeholder="Opsional">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Surat Lolos Butuh</label>
                                <input type="text" class="form-control" id="prev_tanggal_surat_lolos_butuh" name="tanggal_surat_lolos_butuh" placeholder="Opsional">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tunjangan Kotor (Rp)</label>
                                <input type="number" class="form-control" id="prev_tpd_kotor" name="tpd_kotor">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pajak Penghasilan (Rp)</label>
                                <input type="number" class="form-control" id="prev_tpd_pajak" name="tpd_pajak">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tunjangan Bersih (Rp)</label>
                                <input type="number" class="form-control" id="prev_tpd_bersih" name="tpd_bersih">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Terhitung s.d Bulan</label>
                                <input type="text" class="form-control" id="prev_terhitung_bulan" name="terhitung_bulan" placeholder="Misal: Mei">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-secondary" id="btnKembaliStep2">Kembali</button>
                            <button type="button" class="btn btn-primary" id="btnSimpanSkppFinal"><i class="bx bx-save me-1"></i> Simpan & Buat Surat</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert notifications
    @if(session('success'))
    Swal.fire({
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        icon: 'success',
        customClass: { confirmButton: 'btn btn-primary' },
        buttonsStyling: false
    });
    @endif
    @if(session('error'))
    Swal.fire({
        title: 'Gagal!',
        text: "{{ session('error') }}",
        icon: 'error',
        customClass: { confirmButton: 'btn btn-danger' },
        buttonsStyling: false
    });
    @endif

    // State
    let currentDosen = null;
    let currentTahun = null;
    let currentBulanKosong = [];

    // Reset modal on close
    const modalEl = document.getElementById('modalSkpp');
    modalEl.addEventListener('hidden.bs.modal', function() {
        resetModal();
    });

    function resetModal() {
        document.getElementById('skppSearchInput').value = '';
        document.getElementById('skppStep2').style.display = 'none';
        document.getElementById('skppSearchResult').style.display = 'none';
        document.getElementById('skppSearchLoading').style.display = 'none';
        document.getElementById('skppSearchNotFound').style.display = 'none';
        document.getElementById('skppDetailBulan').style.display = 'none';
        document.getElementById('skppButtonArea').style.display = 'none';
        document.getElementById('skppStep3').style.display = 'none';
        document.getElementById('skppTahunSelect').innerHTML = '<option value="">-- Pilih Tahun --</option>';
        currentDosen = null;
        currentTahun = null;
        currentBulanKosong = [];
        currentJenisSurat = '';
    }

    // Search Dosen
    document.getElementById('btnSkppSearch').addEventListener('click', function() {
        const identifier = document.getElementById('skppSearchInput').value.trim();
        if (!identifier) {
            Swal.fire('Perhatian', 'Silakan masukkan NIDN atau NUPTK.', 'warning');
            return;
        }

        // Reset previous results
        document.getElementById('skppStep2').style.display = 'none';
        document.getElementById('skppSearchResult').style.display = 'block';
        document.getElementById('skppSearchLoading').style.display = 'block';
        document.getElementById('skppSearchNotFound').style.display = 'none';
        document.getElementById('skppDetailBulan').style.display = 'none';
        document.getElementById('skppButtonArea').style.display = 'none';

        fetch("{{ route('admin.skpp.search-dosen') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ identifier: identifier }),
        })
        .then(r => r.json())
        .then(json => {
            document.getElementById('skppSearchLoading').style.display = 'none';
            if (!json.found) {
                document.getElementById('skppSearchNotFound').style.display = 'block';
                return;
            }

            currentDosen = json.data;
            const d = json.data;

            // Populate dosen data
            const nidnNuptk = [];
            if (d.nidn) nidnNuptk.push(d.nidn);
            if (d.nuptk) nidnNuptk.push(d.nuptk);
            document.getElementById('skppDosenNidnNuptk').value = nidnNuptk.join(' / ') || '-';
            document.getElementById('skppDosenNama').value = d.nama || '-';
            document.getElementById('skppDosenJabatanStatus').value = d.jabatan_status || '-';
            document.getElementById('skppDosenKodePt').value = (d.kode_pt || '-') + ' - ' + (d.pts || '-');

            document.getElementById('skppSearchResult').style.display = 'none';
            document.getElementById('skppStep2').style.display = 'block';

            // Load available years
            loadTahunDosen(identifier);
        })
        .catch(err => {
            document.getElementById('skppSearchLoading').style.display = 'none';
            document.getElementById('skppSearchNotFound').style.display = 'block';
        });
    });

    // Allow Enter key on search
    document.getElementById('skppSearchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('btnSkppSearch').click();
        }
    });

    // Load Tahun Dosen
    function loadTahunDosen(identifier) {
        fetch("{{ route('admin.skpp.get-tahun') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ identifier: identifier }),
        })
        .then(r => r.json())
        .then(json => {
            const select = document.getElementById('skppTahunSelect');
            select.innerHTML = '<option value="">-- Pilih Tahun --</option>';
            if (json.tahun && json.tahun.length > 0) {
                json.tahun.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t;
                    opt.textContent = t;
                    select.appendChild(opt);
                });
            }
        })
        .catch(err => {
            console.error('Gagal memuat tahun:', err);
        });
    }

    // Lihat Tahun -> Get Detail Bulan
    document.getElementById('btnSkppLihatTahun').addEventListener('click', function() {
        const tahun = document.getElementById('skppTahunSelect').value;
        if (!tahun) {
            Swal.fire('Perhatian', 'Silakan pilih tahun terlebih dahulu.', 'warning');
            return;
        }

        currentTahun = tahun;
        const identifier = currentDosen.nidn || currentDosen.nuptk;

        document.getElementById('skppDetailBulan').style.display = 'block';
        document.getElementById('skppDetailBulanLoading').style.display = 'block';
        document.getElementById('skppDetailBulanEmpty').style.display = 'none';
        document.getElementById('skppDetailBulanList').style.display = 'none';
        document.getElementById('skppButtonArea').style.display = 'none';

        fetch("{{ route('admin.skpp.get-detail-bulan') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ identifier: identifier }),
        })
        .then(r => r.json())
        .then(json => {
            document.getElementById('skppDetailBulanLoading').style.display = 'none';

            if (!json.found) {
                document.getElementById('skppDetailBulanEmpty').style.display = 'block';
                document.getElementById('skppDetailBulanEmpty').innerHTML = '<i class="bx bx-error-circle me-1"></i> ' + (json.message || 'Data tidak ditemukan.');
                document.getElementById('skppDetailBulanEmpty').className = 'alert alert-warning';
                return;
            }

            currentBulanKosong = json.bulan_kosong || [];

            if (currentBulanKosong.length === 0) {
                document.getElementById('skppDetailBulanEmpty').style.display = 'block';
                document.getElementById('skppDetailBulanEmpty').innerHTML = '<i class="bx bx-check-circle me-1"></i> Semua bulan sudah diusulkan sampai saat ini.';
                document.getElementById('skppDetailBulanEmpty').className = 'alert alert-success';
            } else {
                const tbody = document.getElementById('skppDetailBulanTbody');
                tbody.innerHTML = '';
                currentBulanKosong.forEach(item => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><code>${item.kode}</code></td>
                        <td>${item.bulan} ${item.tahun}</td>
                        <td><span class="badge bg-label-danger">${item.status}</span></td>
                    `;
                    tbody.appendChild(tr);
                });
                document.getElementById('skppDetailBulanList').style.display = 'block';
            }

            // Show buttons
            document.getElementById('skppButtonArea').style.display = 'block';
        })
        .catch(err => {
            document.getElementById('skppDetailBulanLoading').style.display = 'none';
            Swal.fire('Error', 'Gagal memuat data bulan.', 'error');
        });
    });

    // Buat Surat Keterangan
    document.getElementById('btnBuatSuratKeterangan').addEventListener('click', function() {
        previewSkpp('Surat Keterangan');
    });

    // Buat Surat SKPP
    document.getElementById('btnBuatSuratSkpp').addEventListener('click', function() {
        previewSkpp('Surat SKPP');
    });

    document.getElementById('btnKembaliStep2').addEventListener('click', function() {
        document.getElementById('skppStep3').style.display = 'none';
        document.getElementById('skppStep2').style.display = 'block';
    });

    function previewSkpp(jenisSurat) {
        if (!currentDosen || !currentTahun) {
            Swal.fire('Perhatian', 'Data dosen dan tahun harus dipilih.', 'warning');
            return;
        }

        currentJenisSurat = jenisSurat;

        Swal.fire({
            title: 'Memuat Data...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch("{{ route('admin.skpp.get-preview-data') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                nidn: currentDosen.nidn || '',
                nuptk: currentDosen.nuptk || '',
                tahun: currentTahun
            })
        })
        .then(r => r.json())
        .then(data => {
            Swal.close();
            // Populate form
            document.getElementById('prev_nomor_skpp').value = data.nomor_skpp || '';
            document.getElementById('prev_tpd_kotor').value = data.tpd_kotor || 0;
            document.getElementById('prev_tpd_pajak').value = data.tpd_pajak || 0;
            document.getElementById('prev_tpd_bersih').value = data.tpd_bersih || 0;
            document.getElementById('prev_terhitung_bulan').value = data.bulan_terakhir_nama || '';

            // Hide step 2, show step 3
            document.getElementById('skppStep2').style.display = 'none';
            document.getElementById('skppStep3').style.display = 'block';
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Gagal memuat data preview', 'error');
        });
    }

    // Save Data
    document.getElementById('btnSimpanSkppFinal').addEventListener('click', function() {
        const jenisSurat = currentJenisSurat;
        const bulanStr = currentBulanKosong.map(b => b.kode + ' (' + b.bulan + ' ' + b.tahun + '): ' + b.status).join(', ');

        const payload = {
            nidn: currentDosen.nidn || '',
            nuptk: currentDosen.nuptk || '',
            nama: currentDosen.nama,
            jabatan_status: currentDosen.jabatan_status,
            kode_pt: currentDosen.kode_pt,
            pts: currentDosen.pts,
            tahun: currentTahun,
            bulan_belum_usulan: bulanStr,
            jenis_surat: jenisSurat,
            nomor_skpp: document.getElementById('prev_nomor_skpp').value,
            nomor_surat_pts: document.getElementById('prev_nomor_surat_pts').value,
            tanggal_surat_pts: document.getElementById('prev_tanggal_surat_pts').value,
            nomor_surat_lolos_butuh: document.getElementById('prev_nomor_surat_lolos_butuh').value,
            tanggal_surat_lolos_butuh: document.getElementById('prev_tanggal_surat_lolos_butuh').value,
            tpd_kotor: document.getElementById('prev_tpd_kotor').value,
            tpd_pajak: document.getElementById('prev_tpd_pajak').value,
            tpd_bersih: document.getElementById('prev_tpd_bersih').value,
            terhitung_bulan: document.getElementById('prev_terhitung_bulan').value,
        };

        Swal.fire({
            title: 'Konfirmasi',
            html: `Simpan dan buat <strong>${jenisSurat}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-primary me-2',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Mohon Tunggu...',
                html: '<div class="spinner-border text-primary" role="status"></div><div class="mt-2">Menyimpan SKPP...</div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            fetch("{{ route('admin.skpp.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(payload),
            })
            .then(async r => {
                if (!r.ok) {
                    const text = await r.text();
                    throw new Error(text.substring(0, 100));
                }
                return r.json();
            })
            .then(json => {
                if (json.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: json.message || 'SKPP berhasil dibuat.',
                        icon: 'success',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    }).then(() => {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal', json.message || 'Terjadi kesalahan.', 'error');
                }
            })
            .catch(err => {
                console.error("Fetch Error:", err);
                Swal.fire('Error', 'Terjadi kesalahan saat menyimpan SKPP: ' + err.message, 'error');
            });
        });
    });
});

function hapusSkpp(id) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data surat SKPP ini akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mohon Tunggu...',
                html: '<div class="spinner-border text-primary" role="status"></div><div class="mt-2">Menghapus SKPP...</div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            fetch(`/admin/skpp/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(async r => {
                if (!r.ok) throw new Error(await r.text());
                return r.json();
            })
            .then(json => {
                if (json.success) {
                    Swal.fire({
                        title: 'Terhapus!',
                        text: json.message || 'Surat berhasil dihapus.',
                        icon: 'success',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal', json.message || 'Terjadi kesalahan.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Gagal menghapus data', 'error');
            });
        }
    });
}
</script>
@endsection
