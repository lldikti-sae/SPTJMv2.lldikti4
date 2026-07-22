@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online - SKPP')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

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
        <button class="btn btn-sm btn-primary" id="btnBuatSkpp" data-bs-toggle="modal" data-bs-target="#modalSkpp" data-bs-backdrop="static" data-backdrop="static" data-bs-keyboard="false" data-keyboard="false">
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
                        @elseif($skpp->status === 'menunggu_konfirmasi')
                            <span class="badge bg-label-info">Menunggu Konfirmasi</span>
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
                            <a href="{{ route('admin.skpp.cetak', $skpp->id) }}" class="sptjm-icon-btn sptjm-btn-print" target="_blank" title="Cetak Surat">
                                <i class="bx bx-printer"></i>
                            </a>
                            @if(!empty($skpp->lampiran))
                                <a href="{{ asset('storage/Dokumen_Histori_Dosen2/' . $skpp->lampiran) }}" class="sptjm-icon-btn {{ $skpp->status === 'setuju' ? 'sptjm-btn-reset' : 'sptjm-btn-view' }}" target="_blank" title="Lihat PDF SKPP">
                                    <i class="bx bx-file"></i>
                                </a>
                            @endif
                            @if($skpp->status !== 'setuju')
                            <button type="button" class="sptjm-icon-btn sptjm-btn-edit" onclick="editSkpp({{ $skpp->id }})" title="Edit Surat">
                                <i class="bx bx-edit"></i>
                            </button>
                            
                            @if($skpp->status === 'open' || $skpp->status === 'tolak')
                            <button type="button" class="sptjm-icon-btn sptjm-btn-reset" onclick="uploadPdf({{ $skpp->id }})" title="Upload PDF">
                                <i class="bx bx-upload"></i>
                            </button>
                            @endif

                            <button type="button" class="sptjm-icon-btn sptjm-btn-delete" onclick="hapusSkpp({{ $skpp->id }})" title="Hapus Surat">
                                <i class="bx bx-trash"></i>
                            </button>
                            @endif
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

/* Sembunyikan panah (spin button) pada input number */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>

{{-- Modal Buat SKPP --}}
<div class="modal fade" id="modalSkpp" tabindex="-1" aria-labelledby="modalSkppLabel" aria-hidden="true" data-bs-backdrop="static" data-backdrop="static" data-bs-keyboard="false" data-keyboard="false">
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
                        <div id="skppSearchNotFound" style="display: none;">
                            <div class="alert alert-warning mb-2">
                                <i class="bx bx-error-circle me-1"></i> Dosen tidak ditemukan. Periksa kembali NIDN/NUPTK yang dimasukkan.
                            </div>
                            <div class="text-center">
                                <button type="button" class="btn btn-warning" id="btnManualSuratKeterangan">
                                    <i class="bx bx-pencil me-1"></i> Buat Surat Keterangan Manual
                                </button>
                            </div>
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
                        <div class="col-md-4">
                            <label class="form-label">Jabatan - Status</label>
                            <input type="text" class="form-control" id="skppDosenJabatanStatus" readonly
                                style="background-color: #eceef1;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kode PT - Perguruan Tinggi</label>
                            <input type="text" class="form-control" id="skppDosenKodePt" readonly
                                style="background-color: #eceef1;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama PIC</label>
                            <input type="text" class="form-control" id="skppDosenPic" readonly
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
                    <div id="skppButtonArea" class="mt-4" style="display: none;">
                        <hr>
                        <div id="skppExistingAlert" class="alert alert-danger mb-3" style="display: none;"></div>
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
                    <h6 class="fw-bold mb-3" id="skppStep3Title"><i class="bx bx-edit me-1"></i> Preview & Lengkapi Data SKPP</h6>
                    <form id="formSkppPreview">
                        <input type="hidden" id="prev_jenis_surat" name="prev_jenis_surat">
                        <div id="skppManualDosenSection" class="border rounded p-3 mb-4" style="background-color: #f8f9fa;">
                            <h6 class="fw-bold mb-3">Data Dosen & Tahun</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">NIDN <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="prev_nidn" name="nidn" placeholder="Ketik NIDN..." required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">NUPTK <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="prev_nuptk" name="nuptk" placeholder="Ketik NUPTK..." required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nama Dosen <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="prev_nama" name="nama" placeholder="Nama Lengkap beserta gelar..." required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jabatan / Status</label>
                                    <input type="text" class="form-control" id="prev_jabatan_status" name="jabatan_status" placeholder="Misal: Lektor - Aktif">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Perguruan Tinggi (PTS)</label>
                                    <input type="text" class="form-control" id="prev_pts" name="pts" placeholder="Ketik nama PTS...">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tahun Pengajuan <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="prev_tahun" name="tahun" value="{{ date('Y') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Nomor Surat SKPP / Keterangan <small class="text-muted">(opsional)</small></label>
                                <input type="text" class="form-control" id="prev_nomor_skpp" name="nomor_skpp" placeholder="Misal: 3137/LL4/PR/2026">
                            </div>
                        </div>
                        {{-- Fields hanya untuk Surat SKPP --}}
                        <div id="skppOnlyFields">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Pangkat</label>
                                    <input type="text" class="form-control" id="prev_pangkat" name="pangkat" placeholder="Misal: Penata Muda">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Golongan</label>
                                    <input type="text" class="form-control" id="prev_golongan" name="golongan" placeholder="Misal: III/b">
                                </div>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Berdasarkan Surat dari (Nama Univ/PTS yang dituju)</label>
                                <input type="text" class="form-control" id="prev_nama_surat_pts" name="nama_surat_pts" placeholder="Ketik nama instansi...">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nomor Surat dari PTS</label>
                                <input type="text" class="form-control" id="prev_nomor_surat_pts" name="nomor_surat_pts" placeholder="Misal: 1627/SPm/01/2026">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Surat dari PTS</label>
                                <input type="text" class="form-control bg-white" id="prev_tanggal_surat_pts" name="tanggal_surat_pts" placeholder="Pilih Tanggal...">
                            </div>
                        </div>
                        {{-- Fields hanya untuk Surat SKPP --}}
                        <div id="skppOnlyFields2">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nomor Surat Lolos Butuh</label>
                                    <input type="text" class="form-control" id="prev_nomor_surat_lolos_butuh" name="nomor_surat_lolos_butuh" placeholder="1627/SPm/01/2026">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Surat Lolos Butuh</label>
                                    <input type="text" class="form-control bg-white" id="prev_tanggal_surat_lolos_butuh" name="tanggal_surat_lolos_butuh" placeholder="Pilih Tanggal...">
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tunjangan Profesi Kotor (Rp)</label>
                                    <input type="number" class="form-control" id="prev_tpd_kotor" name="tpd_kotor">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">PPh. 21 TPD (Rp)</label>
                                    <input type="number" class="form-control" id="prev_tpd_pajak" name="tpd_pajak">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tunjangan Profesi Bersih (Rp)</label>
                                    <input type="number" class="form-control" id="prev_tpd_bersih" name="tpd_bersih">
                                </div>
                            </div>
                            {{-- TKGB Fields (Tunjangan Kehormatan) - Hanya untuk Guru Besar --}}
                            <div id="tkgbFields" style="display: none;">
                                <div class="alert alert-info py-2 mb-2">
                                    <i class="bx bx-info-circle me-1"></i> Dosen ini terdeteksi sebagai <strong>Guru Besar</strong>, memiliki Tunjangan Kehormatan.
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Tunjangan Kehormatan Kotor (Rp)</label>
                                        <input type="number" class="form-control" id="prev_tkgb_kotor" name="tkgb_kotor">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">PPh. 21 TKGB (Rp)</label>
                                        <input type="number" class="form-control" id="prev_tkgb_pajak" name="tkgb_pajak">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tunjangan Kehormatan Bersih (Rp)</label>
                                        <input type="number" class="form-control" id="prev_tkgb_bersih" name="tkgb_bersih">
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Terhitung s.d Bulan</label>
                                    <input type="text" class="form-control" id="prev_terhitung_bulan" name="terhitung_bulan" placeholder="Misal: Mei">
                                </div>
                            </div>
                            <hr>
                            <h6 class="fw-bold mb-3">Tembusan (Disampaikan kepada)</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tujuan (Wilayah)</label>
                                    <select class="form-select" id="prev_wilayah_lldikti" name="wilayah_lldikti">
                                        <option value="">-- Pilih Wilayah --</option>
                                        <option value="I">Wilayah I</option>
                                        <option value="II">Wilayah II</option>
                                        <option value="III">Wilayah III</option>
                                        <option value="V">Wilayah V</option>
                                        <option value="VI">Wilayah VI</option>
                                        <option value="VII">Wilayah VII</option>
                                        <option value="VIII">Wilayah VIII</option>
                                        <option value="IX">Wilayah IX</option>
                                        <option value="X">Wilayah X</option>
                                        <option value="XI">Wilayah XI</option>
                                        <option value="XII">Wilayah XII</option>
                                        <option value="XIII">Wilayah XIII</option>
                                        <option value="XIV">Wilayah XIV</option>
                                        <option value="XV">Wilayah XV</option>
                                        <option value="XVI">Wilayah XVI</option>
                                        <option value="XVII">Wilayah XVII</option>
                                        <option value="Lainnya">Lainnya (Universitas/Instansi)</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="kotaLldiktiContainer">
                                    <label class="form-label">Kota Tujuan</label>
                                    <select class="form-select" id="prev_kota_lldikti" name="kota_lldikti">
                                        <option value="">-- Pilih Kota --</option>
                                        <option value="Medan">Medan</option>
                                        <option value="Palembang">Palembang</option>
                                        <option value="Jakarta">Jakarta</option>
                                        <option value="Bandung">Bandung</option>
                                        <option value="Yogyakarta">Yogyakarta</option>
                                        <option value="Semarang">Semarang</option>
                                        <option value="Surabaya">Surabaya</option>
                                        <option value="Denpasar">Denpasar</option>
                                        <option value="Makassar">Makassar</option>
                                        <option value="Padang">Padang</option>
                                        <option value="Banjarmasin">Banjarmasin</option>
                                        <option value="Ambon">Ambon</option>
                                        <option value="Banda Aceh">Banda Aceh</option>
                                        <option value="Manokwari">Manokwari</option>
                                        <option value="Kupang">Kupang</option>
                                        <option value="Gorontalo">Gorontalo</option>
                                        <option value="Pekanbaru">Pekanbaru</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="wilayahCustomContainer" style="display: none;">
                                    <label class="form-label">Tujuan Surat (Nama Instansi/Universitas)</label>
                                    <input type="text" class="form-control" id="prev_wilayah_lldikti_custom" name="wilayah_lldikti_custom" placeholder="Misal: Universitas Padjadjaran">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h6 class="fw-bold mb-3">Tanggal Surat & Penandatangan</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Tanggal Cetak Surat <small class="text-muted">(opsional, default hari ini)</small></label>
                                <input type="text" class="form-control bg-white" id="prev_tanggal_cetak" name="tanggal_cetak" placeholder="Pilih Tanggal Surat...">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Pilih Penandatangan (Otomatis dari Master Pejabat)</label>
                                <select class="form-select" id="select_penandatangan">
                                    <option value="">-- Ketik Manual --</option>
                                    @foreach($m_pejabat as $p)
                                        <option value="{{ json_encode(['nama' => $p->nama, 'jabatan' => rtrim($p->jabatan, ', '), 'nip' => $p->nip]) }}" {{ stripos($p->nama, 'lukman') !== false ? 'selected' : '' }}>
                                            {{ $p->nama }} - {{ rtrim($p->jabatan, ', ') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jabatan Penandatangan</label>
                                <input type="text" class="form-control" id="prev_ttd_jabatan" name="ttd_jabatan" value="{{ rtrim($pejabat->jabatan1 ?? 'Kuasa Pengguna Anggaran', ', ') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nama Penandatangan</label>
                                <input type="text" class="form-control" id="prev_ttd_nama" name="ttd_nama" value="{{ $pejabat->pejabat1 ?? 'Dr. Lukman, S.T., M.Hum.' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">NIP Penandatangan</label>
                                <input type="text" class="form-control" id="prev_ttd_nip" name="ttd_nip" value="{{ $pejabat->nip_pejabat1 ?? '197805112003121002' }}">
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
    // State
    let currentDosen = null;
    let currentTahun = null;
    let currentBulanKosong = [];
    let currentDosenExistingSkpp = false;
    let currentDosenExistingMessage = '';
    let isManualMode = false;
    let editModeId = null;
    let currentJenisSurat = '';
    let currentIsGuruBesar = false;

document.addEventListener('DOMContentLoaded', function() {
    // Nonaktifkan scroll wheel pada input number
    document.addEventListener('wheel', function(event) {
        if (document.activeElement.type === 'number') {
            event.preventDefault();
        }
    }, { passive: false });

    // Inisialisasi Flatpickr
    flatpickr("#prev_tanggal_surat_pts", {
        dateFormat: "j F Y",
        locale: "id",
        allowInput: true
    });
    
    flatpickr("#prev_tanggal_surat_lolos_butuh", {
        dateFormat: "j F Y",
        locale: "id",
        allowInput: true
    });

    flatpickr("#prev_tanggal_cetak", {
        dateFormat: "j F Y",
        locale: "id",
        allowInput: true
    });

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
        currentDosenExistingSkpp = false;
        currentDosenExistingMessage = '';
        isManualMode = false;
        editModeId = null;
        document.getElementById('btnSimpanSkppFinal').innerHTML = '<i class="bx bx-save me-1"></i> Simpan & Buat Surat';
        currentIsGuruBesar = false;
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
                if (json.existing_skpp) {
                    Swal.fire('Perhatian', json.existing_message, 'warning');
                    return;
                }
                document.getElementById('skppSearchNotFound').style.display = 'block';
                return;
            }

            currentDosen = json.data;
            currentDosenExistingSkpp = json.existing_skpp || false;
            currentDosenExistingMessage = json.existing_message || '';
            const d = json.data;

            // Populate dosen data
            const nidnNuptk = [];
            if (d.nidn) nidnNuptk.push(d.nidn);
            if (d.nuptk) nidnNuptk.push(d.nuptk);
            document.getElementById('skppDosenNidnNuptk').value = nidnNuptk.join(' / ') || '-';
            document.getElementById('skppDosenNama').value = d.nama || '-';
            document.getElementById('skppDosenJabatanStatus').value = d.jabatan_status || '-';
            document.getElementById('skppDosenKodePt').value = (d.kode_pt || '-') + ' - ' + (d.pts || '-');
            document.getElementById('skppDosenPic').value = d.pic || '-';

            document.getElementById('skppSearchResult').style.display = 'none';
            document.getElementById('skppStep2').style.display = 'block';

            // Load available years
            loadTahunDosen(currentDosen.nidn || '', currentDosen.nuptk || '');
        })
        .catch(err => {
            document.getElementById('skppSearchLoading').style.display = 'none';
            document.getElementById('skppSearchNotFound').style.display = 'block';
        });
    });

    // Buat Surat Keterangan Manual (bila dosen tidak ditemukan)
    document.getElementById('btnManualSuratKeterangan').addEventListener('click', function() {
        isManualMode = true;
        currentJenisSurat = 'Surat Keterangan';
        document.getElementById('prev_jenis_surat').value = currentJenisSurat;
        currentDosen = null;
        currentTahun = new Date().getFullYear();
        currentBulanKosong = [];
        
        document.getElementById('skppSearchResult').style.display = 'none';
        document.getElementById('skppStep3').style.display = 'block';
        document.getElementById('skppStep3Title').innerHTML = '<i class="bx bx-edit me-1"></i> Buat Surat Keterangan Manual';
        
        document.getElementById('skppOnlyFields').style.display = 'none';
        document.getElementById('skppOnlyFields2').style.display = 'none';
        
        document.getElementById('prev_nidn').readOnly = false;
        document.getElementById('prev_nuptk').readOnly = false;
        document.getElementById('prev_nama').readOnly = false;
        document.getElementById('prev_jabatan_status').readOnly = false;
        document.getElementById('prev_pts').readOnly = false;
        document.getElementById('prev_tahun').readOnly = false;
        
        const searchInput = document.getElementById('skppSearchInput').value.trim();
        document.getElementById('prev_nidn').value = '';
        document.getElementById('prev_nuptk').value = '';
        if (searchInput.length === 16) {
            document.getElementById('prev_nuptk').value = searchInput;
        } else {
            document.getElementById('prev_nidn').value = searchInput;
        }
        
        document.getElementById('prev_tahun').value = currentTahun;
        document.getElementById('prev_nama').value = '';
        document.getElementById('prev_jabatan_status').value = '';
        document.getElementById('prev_pts').value = '';
        document.getElementById('prev_nomor_skpp').value = '';
        document.getElementById('prev_tanggal_cetak').value = '';
        
        // Reset ttd fields based on dropdown
        let selectTtd = document.getElementById('select_penandatangan');
        if (selectTtd.value) {
            let data = JSON.parse(selectTtd.value);
            document.getElementById('prev_ttd_jabatan').value = data.jabatan;
            document.getElementById('prev_ttd_nama').value = data.nama;
            document.getElementById('prev_ttd_nip').value = data.nip;
            
            document.getElementById('prev_ttd_jabatan').readOnly = true;
            document.getElementById('prev_ttd_nama').readOnly = true;
            document.getElementById('prev_ttd_nip').readOnly = true;
            
            document.getElementById('prev_ttd_jabatan').style.backgroundColor = '#eceef1';
            document.getElementById('prev_ttd_nama').style.backgroundColor = '#eceef1';
            document.getElementById('prev_ttd_nip').style.backgroundColor = '#eceef1';
        } else {
            document.getElementById('prev_ttd_jabatan').value = '';
            document.getElementById('prev_ttd_nama').value = '';
            document.getElementById('prev_ttd_nip').value = '';
            
            document.getElementById('prev_ttd_jabatan').readOnly = false;
            document.getElementById('prev_ttd_nama').readOnly = false;
            document.getElementById('prev_ttd_nip').readOnly = false;
            
            document.getElementById('prev_ttd_jabatan').style.backgroundColor = '';
            document.getElementById('prev_ttd_nama').style.backgroundColor = '';
            document.getElementById('prev_ttd_nip').style.backgroundColor = '';
        }
    });

    // Handle dropdown change
    document.getElementById('select_penandatangan').addEventListener('change', function() {
        const ttdJabatan = document.getElementById('prev_ttd_jabatan');
        const ttdNama = document.getElementById('prev_ttd_nama');
        const ttdNip = document.getElementById('prev_ttd_nip');
        
        if (this.value) {
            let data = JSON.parse(this.value);
            ttdJabatan.value = data.jabatan;
            ttdNama.value = data.nama;
            ttdNip.value = data.nip;
            
            ttdJabatan.readOnly = true;
            ttdNama.readOnly = true;
            ttdNip.readOnly = true;
            
            ttdJabatan.style.backgroundColor = '#eceef1';
            ttdNama.style.backgroundColor = '#eceef1';
            ttdNip.style.backgroundColor = '#eceef1';
        } else {
            ttdJabatan.value = '';
            ttdNama.value = '';
            ttdNip.value = '';

            ttdJabatan.readOnly = false;
            ttdNama.readOnly = false;
            ttdNip.readOnly = false;
            
            ttdJabatan.style.backgroundColor = '';
            ttdNama.style.backgroundColor = '';
            ttdNip.style.backgroundColor = '';
        }
    });

    // Initialize dropdown on page load
    let selectTtdInit = document.getElementById('select_penandatangan');
    if(selectTtdInit.value) {
        selectTtdInit.dispatchEvent(new Event('change'));
    }

    // Allow Enter key on search
    document.getElementById('skppSearchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('btnSkppSearch').click();
        }
    });

    // Load Tahun Dosen
    function loadTahunDosen(nidn, nuptk) {
        fetch("{{ route('admin.skpp.get-tahun') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ nidn: nidn, nuptk: nuptk }),
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
            body: JSON.stringify({ 
                nidn: currentDosen.nidn || '', 
                nuptk: currentDosen.nuptk || '',
                tahun: currentTahun 
            }),
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

            if (currentDosenExistingSkpp) {
                const alertEl = document.getElementById('skppExistingAlert');
                alertEl.style.display = 'block';
                alertEl.innerHTML = '<i class="bx bx-error me-1"></i> ' + currentDosenExistingMessage;
                document.getElementById('btnBuatSuratKeterangan').disabled = true;
                document.getElementById('btnBuatSuratSkpp').disabled = true;
            } else {
                document.getElementById('skppExistingAlert').style.display = 'none';
                document.getElementById('btnBuatSuratKeterangan').disabled = false;
                document.getElementById('btnBuatSuratSkpp').disabled = false;
            }
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

        isManualMode = false;

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
            document.getElementById('prev_nidn').readOnly = true;
            document.getElementById('prev_nuptk').readOnly = true;
            document.getElementById('prev_nama').readOnly = true;
            document.getElementById('prev_jabatan_status').readOnly = true;
            document.getElementById('prev_pts').readOnly = true;
            document.getElementById('prev_tahun').readOnly = true;

            document.getElementById('prev_nidn').value = currentDosen.nidn || '';
            document.getElementById('prev_nuptk').value = currentDosen.nuptk || '';
            document.getElementById('prev_nama').value = currentDosen.nama || '';
            document.getElementById('prev_jabatan_status').value = currentDosen.jabatan_status || '';
            document.getElementById('prev_pts').value = currentDosen.pts || '';
            document.getElementById('prev_tahun').value = currentTahun || '';

            document.getElementById('prev_nomor_skpp').value = data.nomor_skpp || '';
            document.getElementById('prev_nama_surat_pts').value = '';
            document.getElementById('prev_pangkat').value = data.pangkat || '';
            document.getElementById('prev_golongan').value = data.golongan || '';
            document.getElementById('prev_tpd_kotor').value = data.tpd_kotor || 0;
            document.getElementById('prev_tpd_pajak').value = data.tpd_pajak || 0;
            document.getElementById('prev_tpd_bersih').value = data.tpd_bersih || 0;
            document.getElementById('prev_terhitung_bulan').value = data.bulan_terakhir_nama || '';
            document.getElementById('prev_wilayah_lldikti').value = '';
            document.getElementById('prev_kota_lldikti').value = '';

            // TKGB fields (Tunjangan Kehormatan)
            currentIsGuruBesar = data.is_guru_besar || false;
            document.getElementById('prev_tkgb_kotor').value = data.tkgb_kotor || 0;
            document.getElementById('prev_tkgb_pajak').value = data.tkgb_pajak || 0;
            document.getElementById('prev_tkgb_bersih').value = data.tkgb_bersih || 0;
            document.getElementById('tkgbFields').style.display = currentIsGuruBesar ? 'block' : 'none';

            document.getElementById('prev_jenis_surat').value = jenisSurat;

            // Show/hide fields based on jenis surat
            const isKeterangan = (jenisSurat === 'Surat Keterangan');
            document.getElementById('skppOnlyFields').style.display = isKeterangan ? 'none' : 'block';
            document.getElementById('skppOnlyFields2').style.display = isKeterangan ? 'none' : 'block';
            document.getElementById('skppStep3Title').innerHTML = '<i class="bx bx-edit me-1"></i> Preview & Lengkapi Data ' + (isKeterangan ? 'Surat Keterangan' : 'SKPP');

            // Reset wilayah containers
            document.getElementById('kotaLldiktiContainer').style.display = 'block';
            document.getElementById('wilayahCustomContainer').style.display = 'none';

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
        const jenisSurat = document.getElementById('prev_jenis_surat').value;
        const bulanStr = currentBulanKosong.map(b => b.kode + ' (' + b.bulan + ' ' + b.tahun + '): ' + b.status).join(', ');

        const nama = document.getElementById('prev_nama').value.trim();
        const tahun = document.getElementById('prev_tahun').value;

        const nidnInput = document.getElementById('prev_nidn').value.trim();
        const nuptkInput = document.getElementById('prev_nuptk').value.trim();

        if (!nidnInput || !nuptkInput || !nama || !tahun) {
            Swal.fire('Perhatian', 'NIDN, NUPTK, Nama Dosen, dan Tahun wajib diisi.', 'warning');
            return;
        }

        const payload = {
            nidn: document.getElementById('prev_nidn').value.trim(),
            nuptk: document.getElementById('prev_nuptk').value.trim(),
            nama: nama,
            jabatan_status: document.getElementById('prev_jabatan_status').value.trim(),
            kode_pt: currentDosen ? (currentDosen.kode_pt || '') : '',
            pts: document.getElementById('prev_pts').value.trim(),
            nama_surat_pts: document.getElementById('prev_nama_surat_pts').value,
            tahun: tahun,
            bulan_belum_usulan: isManualMode ? '' : bulanStr,
            jenis_surat: jenisSurat,
            nomor_skpp: document.getElementById('prev_nomor_skpp').value,
            nomor_surat_pts: document.getElementById('prev_nomor_surat_pts').value,
            tanggal_surat_pts: document.getElementById('prev_tanggal_surat_pts').value,
            nomor_surat_lolos_butuh: document.getElementById('prev_nomor_surat_lolos_butuh').value,
            tanggal_surat_lolos_butuh: document.getElementById('prev_tanggal_surat_lolos_butuh').value,
            tpd_kotor: document.getElementById('prev_tpd_kotor').value,
            tpd_pajak: document.getElementById('prev_tpd_pajak').value,
            tpd_bersih: document.getElementById('prev_tpd_bersih').value,
            tkgb_kotor: document.getElementById('prev_tkgb_kotor').value,
            tkgb_pajak: document.getElementById('prev_tkgb_pajak').value,
            tkgb_bersih: document.getElementById('prev_tkgb_bersih').value,
            is_guru_besar: currentIsGuruBesar,
            terhitung_bulan: document.getElementById('prev_terhitung_bulan').value,
            pangkat: document.getElementById('prev_pangkat').value,
            golongan: document.getElementById('prev_golongan').value,
            wilayah_lldikti: document.getElementById('prev_wilayah_lldikti').value,
            wilayah_lldikti_custom: document.getElementById('prev_wilayah_lldikti_custom').value,
            kota_lldikti: document.getElementById('prev_kota_lldikti').value,
            ttd_jabatan: document.getElementById('prev_ttd_jabatan').value,
            ttd_nama: document.getElementById('prev_ttd_nama').value,
            ttd_nip: document.getElementById('prev_ttd_nip').value,
            tanggal_cetak: document.getElementById('prev_tanggal_cetak').value,
        };

        // Required fields berbeda untuk Surat Keterangan vs Surat SKPP
        let requiredFields;
        if (jenisSurat === 'Surat Keterangan') {
            requiredFields = [
                'nama_surat_pts', 'nomor_surat_pts', 'tanggal_surat_pts',
                'ttd_jabatan', 'ttd_nama', 'ttd_nip'
            ];
        } else {
            requiredFields = [
                'nama_surat_pts', 'nomor_surat_pts', 'tanggal_surat_pts',
                'nomor_surat_lolos_butuh', 'tanggal_surat_lolos_butuh',
                'tpd_kotor', 'tpd_pajak', 'tpd_bersih', 'terhitung_bulan',
                'pangkat', 'golongan',
                'ttd_jabatan', 'ttd_nama', 'ttd_nip'
            ];
        }



        // Validasi wilayah: jika Lainnya, custom field wajib diisi
        if (jenisSurat !== 'Surat Keterangan') {
            const wilayahVal = document.getElementById('prev_wilayah_lldikti').value;
            if (!wilayahVal) {
                Swal.fire('Perhatian', 'Harap pilih wilayah/tujuan surat terlebih dahulu.', 'warning');
                return;
            }
            if (wilayahVal === 'Lainnya') {
                const customVal = document.getElementById('prev_wilayah_lldikti_custom').value.trim();
                if (!customVal) {
                    Swal.fire('Perhatian', 'Harap isi nama instansi/universitas tujuan surat.', 'warning');
                    return;
                }
            } else {
                if (!document.getElementById('prev_kota_lldikti').value) {
                    Swal.fire('Perhatian', 'Harap pilih kota LLDIKTI tujuan.', 'warning');
                    return;
                }
            }
        }

        let isValid = true;
        for (let field of requiredFields) {
            let value = document.getElementById('prev_' + field).value;
            if (!value || value.toString().trim() === '') {
                isValid = false;
                break;
            }
        }

        if (!isValid) {
            Swal.fire('Perhatian', 'Harap lengkapi semua isian terlebih dahulu sebelum menyimpan.', 'warning');
            return;
        }

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
            buttonsStyling: false,
            allowOutsideClick: false
        }).then(result => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Mohon Tunggu...',
                html: '<div class="spinner-border text-primary" role="status"></div><div class="mt-2">Menyimpan SKPP...</div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            let fetchUrl = "{{ route('admin.skpp.store') }}";
            let fetchMethod = "POST";

            if (editModeId) {
                fetchUrl = `/admin/skpp/${editModeId}/update`;
                fetchMethod = "PUT";
            }

            fetch(fetchUrl, {
                method: fetchMethod,
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
                        window.location.href = window.location.href;
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

function editSkpp(id) {
    Swal.fire({
        title: 'Memuat Data...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/admin/skpp/${id}/edit`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        Swal.close();
        if (!data.success) {
            Swal.fire('Error', data.message || 'Gagal memuat data', 'error');
            return;
        }

        const skpp = data.skpp;
        const detail = data.detail;

        editModeId = skpp.id;
        currentJenisSurat = skpp.jenis_pengajuan;
        if (!currentJenisSurat) {
            currentJenisSurat = (skpp.judul && skpp.judul.toLowerCase().includes('keterangan')) ? 'Surat Keterangan' : 'Surat SKPP';
        }
        document.getElementById('prev_jenis_surat').value = currentJenisSurat;
        isManualMode = false; // We can just treat it as editable if we want, but unlocking inputs is better

        // Populate currentDosen so that when saving, kode_pt is available
        currentDosen = { kode_pt: skpp.kode_pts || detail.kode_pt || '' };

        // Cek jika dosen manual (tidak ada NIDN/NUPTK di transaksi) 
        // Sebenarnya karena kita edit, kita bisa buka saja data dosennya agar bisa diperbaiki
        document.getElementById('prev_nidn').readOnly = false;
        document.getElementById('prev_nuptk').readOnly = false;
        document.getElementById('prev_nama').readOnly = false;
        document.getElementById('prev_jabatan_status').readOnly = false;
        document.getElementById('prev_pts').readOnly = false;
        document.getElementById('prev_tahun').readOnly = false;

        document.getElementById('prev_nidn').value = detail.nidn || skpp.nidn || '';
        document.getElementById('prev_nuptk').value = detail.nuptk || skpp.nuptk || '';
        document.getElementById('prev_nama').value = detail.nama || '';
        document.getElementById('prev_jabatan_status').value = detail.jabatan_status || '';
        document.getElementById('prev_pts').value = detail.pts || '';
        document.getElementById('prev_tahun').value = detail.tahun || '';

        document.getElementById('prev_nomor_skpp').value = detail.nomor_skpp || '';
        document.getElementById('prev_nama_surat_pts').value = detail.nama_surat_pts || '';
        document.getElementById('prev_nomor_surat_pts').value = detail.nomor_surat_pts || '';
        
        function parseIndoDate(dateStr) {
            if (!dateStr) return null;
            const months = {
                "Januari": "January", "Februari": "February", "Maret": "March", "April": "April",
                "Mei": "May", "Juni": "June", "Juli": "July", "Agustus": "August",
                "September": "September", "Oktober": "October", "November": "November", "Desember": "December"
            };
            let engStr = dateStr;
            for (let id in months) {
                if (engStr.includes(id)) {
                    engStr = engStr.replace(id, months[id]);
                    break;
                }
            }
            let d = new Date(engStr);
            return isNaN(d) ? null : d;
        }

        const valTglPts = detail.tanggal_surat_pts || '';
        const parsedTglPts = parseIndoDate(valTglPts);
        const elTglPts = document.getElementById('prev_tanggal_surat_pts');
        if (elTglPts._flatpickr) elTglPts._flatpickr.destroy();
        elTglPts.value = valTglPts;
        flatpickr(elTglPts, { dateFormat: "j F Y", locale: "id", allowInput: true, defaultDate: parsedTglPts });

        // Fallback untuk data lama yang tidak menyimpan pangkat & golongan secara terpisah
        let pg_pangkat = detail.pangkat || '';
        let pg_golongan = detail.golongan || '';
        if (!pg_pangkat && detail.pangkat_golongan) {
            let split_pg = detail.pangkat_golongan.split(',');
            pg_pangkat = split_pg[0] ? split_pg[0].trim() : '';
            if (split_pg.length > 1) {
                pg_golongan = split_pg[1] ? split_pg[1].trim() : '';
            }
        }
        document.getElementById('prev_pangkat').value = pg_pangkat;
        document.getElementById('prev_golongan').value = pg_golongan;
        document.getElementById('prev_tpd_kotor').value = detail.tpd_kotor || 0;
        document.getElementById('prev_tpd_pajak').value = detail.tpd_pajak || 0;
        document.getElementById('prev_tpd_bersih').value = detail.tpd_bersih || 0;
        
        document.getElementById('prev_tkgb_kotor').value = detail.tkgb_kotor || 0;
        document.getElementById('prev_tkgb_pajak').value = detail.tkgb_pajak || 0;
        document.getElementById('prev_tkgb_bersih').value = detail.tkgb_bersih || 0;

        document.getElementById('prev_terhitung_bulan').value = detail.terhitung_bulan || '';
        document.getElementById('prev_wilayah_lldikti').value = detail.wilayah_lldikti || '';
        document.getElementById('prev_wilayah_lldikti_custom').value = detail.wilayah_lldikti_custom || '';
        document.getElementById('prev_kota_lldikti').value = detail.kota_lldikti || '';
        document.getElementById('prev_nomor_surat_lolos_butuh').value = detail.nomor_surat_lolos_butuh || '';
        const valTglLolos = detail.tanggal_surat_lolos_butuh || '';
        const parsedTglLolos = parseIndoDate(valTglLolos);
        const elTglLolos = document.getElementById('prev_tanggal_surat_lolos_butuh');
        if (elTglLolos._flatpickr) elTglLolos._flatpickr.destroy();
        elTglLolos.value = valTglLolos;
        flatpickr(elTglLolos, { dateFormat: "j F Y", locale: "id", allowInput: true, defaultDate: parsedTglLolos });

        const valTglCetak = detail.tanggal_cetak || '';
        const parsedTglCetak = parseIndoDate(valTglCetak);
        const elTglCetak = document.getElementById('prev_tanggal_cetak');
        if (elTglCetak._flatpickr) elTglCetak._flatpickr.destroy();
        elTglCetak.value = valTglCetak;
        flatpickr(elTglCetak, { dateFormat: "j F Y", locale: "id", allowInput: true, defaultDate: parsedTglCetak });

        // Find matching option in select_penandatangan or set to Ketik Manual
        let matched = false;
        const selectPenandatangan = document.getElementById('select_penandatangan');
        for (let i = 0; i < selectPenandatangan.options.length; i++) {
            let opt = selectPenandatangan.options[i];
            if (opt.value) {
                try {
                    let optData = JSON.parse(opt.value);
                    if (optData.nama === (detail.ttd_nama || '')) {
                        selectPenandatangan.value = opt.value;
                        matched = true;
                        break;
                    }
                } catch(e) {}
            }
        }
        if (!matched) {
            selectPenandatangan.value = "";
        }
        
        // Trigger change to update readOnly states, but then overwrite values just in case they were custom edited
        selectPenandatangan.dispatchEvent(new Event('change'));
        
        document.getElementById('prev_ttd_jabatan').value = detail.ttd_jabatan || '';
        document.getElementById('prev_ttd_nama').value = detail.ttd_nama || '';
        document.getElementById('prev_ttd_nip').value = detail.ttd_nip || '';

        currentIsGuruBesar = detail.is_guru_besar === true || detail.is_guru_besar === 'true';
        const tkgbFields = document.getElementById('tkgbFields');
        if (tkgbFields) {
            tkgbFields.style.display = currentIsGuruBesar ? 'block' : 'none';
        }

        const wilayahVal = document.getElementById('prev_wilayah_lldikti').value;
        const customContainer = document.getElementById('wilayahCustomContainer');
        const kotaContainer = document.getElementById('kotaLldiktiContainer');
        if (customContainer && kotaContainer) {
            if (wilayahVal === 'Lainnya') {
                customContainer.style.display = 'block';
                kotaContainer.style.display = 'none';
            } else {
                customContainer.style.display = 'none';
                kotaContainer.style.display = 'block';
            }
        }

        const isKeterangan = (currentJenisSurat === 'Surat Keterangan');
        document.getElementById('skppOnlyFields').style.display = isKeterangan ? 'none' : 'block';
        document.getElementById('skppOnlyFields2').style.display = isKeterangan ? 'none' : 'block';
        document.getElementById('skppStep3Title').innerHTML = '<i class="bx bx-edit me-1"></i> Edit Data ' + (isKeterangan ? 'Surat Keterangan' : 'SKPP');

        document.getElementById('btnSimpanSkppFinal').innerHTML = '<i class="bx bx-save me-1"></i> Update & Buat Surat';

        document.getElementById('skppStep2').style.display = 'none';
        document.getElementById('skppSearchResult').style.display = 'none';
        document.getElementById('skppSearchLoading').style.display = 'none';
        document.getElementById('skppSearchNotFound').style.display = 'none';
        document.getElementById('skppDetailBulan').style.display = 'none';
        document.getElementById('skppButtonArea').style.display = 'none';
        document.getElementById('skppStep3').style.display = 'block';

        const modal = new bootstrap.Modal(document.getElementById('modalSkpp'));
        modal.show();
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Gagal memuat data', 'error');
    });
}

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
        buttonsStyling: false,
        allowOutsideClick: false
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
                        window.location.href = window.location.href;
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

function uploadPdf(id) {
    Swal.fire({
        title: 'Upload PDF SKPP',
        text: 'Upload file PDF SKPP yang sudah ditandatangani. Status pengajuan ini akan berubah menjadi "Menunggu Konfirmasi".',
        input: 'file',
        inputAttributes: {
            'accept': 'application/pdf',
            'aria-label': 'Pilih File PDF'
        },
        showCancelButton: true,
        confirmButtonText: 'Upload',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'btn btn-success me-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const file = result.value;
            const formData = new FormData();
            formData.append('pdf_file', file);
            formData.append('skpp_id', id);

            Swal.fire({
                title: 'Mengunggah...',
                html: '<div class="spinner-border text-primary" role="status"></div><div class="mt-2">Mohon tunggu...</div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            fetch("{{ route('admin.skpp.upload-pdf') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(async r => {
                if (!r.ok) throw new Error(await r.text());
                return r.json();
            })
            .then(json => {
                if (json.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: json.message || 'PDF berhasil diupload.',
                        icon: 'success',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    }).then(() => {
                        window.location.href = window.location.href;
                    });
                } else {
                    Swal.fire('Gagal', json.message || 'Terjadi kesalahan.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Gagal mengunggah file.', 'error');
            });
        }
    });
}

function konfirmasiSkpp(id) {
    Swal.fire({
        title: 'Konfirmasi Penonaktifan?',
        text: "Dengan melakukan konfirmasi, dosen ini akan dinonaktifkan secara otomatis dan tercatat dalam histori.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Konfirmasi!',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'btn btn-primary me-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mohon Tunggu...',
                html: '<div class="spinner-border text-primary" role="status"></div><div class="mt-2">Memproses konfirmasi...</div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            fetch(`/admin/skpp/${id}/konfirmasi`, {
                method: 'POST',
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
                        title: 'Berhasil!',
                        text: json.message || 'Dosen berhasil dinonaktifkan.',
                        icon: 'success',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    }).then(() => {
                        window.location.href = window.location.href;
                    });
                } else {
                    Swal.fire('Gagal', json.message || 'Terjadi kesalahan.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Gagal melakukan konfirmasi.', 'error');
            });
        }
    });
}

function tolakSkpp(id) {
    Swal.fire({
        title: 'Tolak Pengajuan SKPP?',
        text: 'Silakan masukkan alasan penolakan:',
        input: 'textarea',
        inputPlaceholder: 'Alasan penolakan...',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Tolak!',
        cancelButtonText: 'Batal',
        customClass: {
            confirmButton: 'btn btn-danger me-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        allowOutsideClick: false,
        preConfirm: (alasan) => {
            if (!alasan) {
                Swal.showValidationMessage('Alasan penolakan wajib diisi!');
            }
            return alasan;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Mohon Tunggu...',
                html: '<div class="spinner-border text-primary" role="status"></div><div class="mt-2">Memproses penolakan...</div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });

            fetch(`/admin/skpp/${id}/tolak`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ alasan: result.value })
            })
            .then(async r => {
                if (!r.ok) throw new Error(await r.text());
                return r.json();
            })
            .then(json => {
                if (json.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: json.message || 'Pengajuan ditolak.',
                        icon: 'success',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    }).then(() => {
                        window.location.href = window.location.href;
                    });
                } else {
                    Swal.fire('Gagal', json.message || 'Terjadi kesalahan.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Gagal melakukan penolakan.', 'error');
            });
        }
    });
}

// Auto update kota berdasarkan wilayah
const kotaMap = {
    'I': 'Medan', 'II': 'Palembang', 'III': 'Jakarta', 'IV': 'Bandung',
    'V': 'Yogyakarta', 'VI': 'Semarang', 'VII': 'Surabaya', 'VIII': 'Denpasar',
    'IX': 'Makassar', 'X': 'Padang', 'XI': 'Banjarmasin', 'XII': 'Ambon',
    'XIII': 'Banda Aceh', 'XIV': 'Manokwari', 'XV': 'Kupang', 'XVI': 'Gorontalo',
    'XVII': 'Pekanbaru'
};

document.getElementById('prev_wilayah_lldikti').addEventListener('change', function() {
    const wil = this.value;
    if (wil === 'Lainnya') {
        // Tampilkan input custom, sembunyikan kota dropdown
        document.getElementById('kotaLldiktiContainer').style.display = 'none';
        document.getElementById('wilayahCustomContainer').style.display = 'block';
        document.getElementById('prev_kota_lldikti').value = '';
        document.getElementById('prev_wilayah_lldikti_custom').value = '';
        document.getElementById('prev_wilayah_lldikti_custom').focus();
    } else {
        // Tampilkan kota dropdown, sembunyikan custom
        document.getElementById('kotaLldiktiContainer').style.display = 'block';
        document.getElementById('wilayahCustomContainer').style.display = 'none';
        document.getElementById('prev_wilayah_lldikti_custom').value = '';
        if (kotaMap[wil]) {
            document.getElementById('prev_kota_lldikti').value = kotaMap[wil];
        }
    }
});
</script>
@endsection
