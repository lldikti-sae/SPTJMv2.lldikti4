@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')
<style>
    .card-usulan {
        border: 1.5px solid #dbeafe !important;
        box-shadow: 0 10px 30px rgba(26, 86, 219, 0.15) !important;
        border-radius: 12px !important;
        background: #ffffff !important;
    }
</style>

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card card-usulan mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-4" style="color: #0f2b5c !important; font-size: 1.15rem; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="bx bx-edit-alt"></i> Pengaturan Usulan SPTJM
                    </h5>
                    <form id="pengaturanUsulanForm" action="{{ route('pengaturan-usulan.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="jenis_usulan">Jenis Usulan</label>
                                <select id="jenis_usulan" class="form-select" name="jenis_usulan" required style="border-color: #cbd5e1;">
                                    <option value="" selected disabled>-- PILIH --</option>
                                    <option value="SPTJM">SPTJM</option>
                                    <option value="TUKIN">TUKIN</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="tahun">Tahun</label>
                                <select id="tahun" class="form-select" name="tahun" required style="border-color: #cbd5e1;">
                                    <option value="" disabled>-- PILIH TAHUN --</option>
                                    @php
                                        $yearsToDisplay = (isset($listTahun) && count($listTahun) > 0) ? $listTahun : range(2023, (int)date('Y'));
                                    @endphp
                                    @foreach($yearsToDisplay as $y)
                                        <option value="{{ $y }}" {{ (date('Y') == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="bulan">Bulan</label>
                                <select id="bulan" class="form-select" name="bulan" required style="border-color: #cbd5e1;">
                                    @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                                    'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $bulan)
                                    <option value="{{ $bulan }}">
                                        [{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}] {{ $bulan }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="pencairan_ke">Pencairan ke</label>
                                <select id="pencairan_ke" class="form-select" name="pencairan_ke" required style="border-color: #cbd5e1;">
                                    <option value="">Pilih Opsi</option>
                                </select>
                            </div>
                        </div>

                        <div class="row align-items-end g-3 mt-1">
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="tanggal_mulai">Tanggal Mulai</label>
                                <input class="form-control" type="date" name="tanggal_mulai" required style="border-color: #cbd5e1;">
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;" for="tanggal_selesai">Tanggal Selesai</label>
                                <input class="form-control" type="date" name="tanggal_selesai" required style="border-color: #cbd5e1;">
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Status</label>
                                <div class="d-flex align-items-center gap-4 mt-2">
                                    <div class="form-check d-flex align-items-center">
                                        <input name="status" class="form-check-input" type="radio" value="Aktifkan"
                                            id="status_aktif" checked style="width: 1.1rem; height: 1.1rem; margin-top: 0; margin-right: 6px;">
                                        <label class="form-check-label fw-bold text-dark" for="status_aktif" style="font-size: 0.85rem;">Aktifkan</label>
                                    </div>
                                    <div class="form-check d-flex align-items-center">
                                        <input name="status" class="form-check-input" type="radio" value="Nonaktifkan"
                                            id="status_nonaktif" style="width: 1.1rem; height: 1.1rem; margin-top: 0; margin-right: 6px;">
                                        <label class="form-check-label fw-bold text-dark" for="status_nonaktif" style="font-size: 0.85rem;">Nonaktifkan</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-6 d-flex justify-content-end">
                                <button type="submit" class="btn btn-success px-4 py-2 fw-bold" style="background-color: #28c76f; border-color: #28c76f; font-size: 0.875rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center; color: #ffffff;">
                                    <i class="bx bx-save" style="font-size: 1.15rem;"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr class="my-4">

                    <!-- TABEL & MODAL -->
                    <div class="table-responsive text-nowrap">
                        <table id="pengaturanUsulanTable" class="table table-hover md2-table" style="margin-bottom: 0 !important;">
                            <thead>
                                <tr>
                                    <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Jenis Usulan</th>
                                    <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Tahun</th>
                                    <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Bulan</th>
                                    <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Pencairan ke</th>
                                    <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Tanggal Mulai</th>
                                    <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important;">Tanggal Selesai</th>
                                    <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; text-align: center;">Status</th>
                                    <th style="background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem !important; letter-spacing: 0.05em !important; font-weight: 700 !important; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @foreach ($pengaturanUsulan as $data)
                                <tr>
                                    <td>
                                        @php
                                            $jenisUsulan = strtolower($data->jenis_usulan ?? '');
                                            $badgeStyle = '';
                                            if (str_contains($jenisUsulan, 'sptjm')) {
                                                $badgeStyle = 'background-color: #e0e7ff !important; color: #3730a3 !important; font-weight: 700; font-size: 0.72rem; padding: 6px 12px; border-radius: 20px; text-transform: uppercase; display: inline-block;';
                                            } elseif (str_contains($jenisUsulan, 'tukin')) {
                                                $badgeStyle = 'background-color: #fef3c7 !important; color: #d97706 !important; font-weight: 700; font-size: 0.72rem; padding: 6px 12px; border-radius: 20px; text-transform: uppercase; display: inline-block;';
                                            } else {
                                                $badgeStyle = 'background-color: #f1f5f9 !important; color: #475569 !important; font-weight: 700; font-size: 0.72rem; padding: 6px 12px; border-radius: 20px; text-transform: uppercase; display: inline-block;';
                                            }
                                        @endphp
                                        <span class="badge" style="{{ $badgeStyle }}">{{ $data->jenis_usulan ?? '-' }}</span>
                                    </td>
                                    <td><span class="fw-semibold text-dark">{{ $data->tahun }}</span></td>
                                    <td><span class="fw-semibold text-dark">{{ $data->bulan }}</span></td>
                                    <td><span class="fw-semibold text-dark">{{ $data->pencairan_ke }}</span></td>
                                    <td><span class="text-dark">{{ $data->tanggal_mulai }}</span></td>
                                    <td><span class="text-dark">{{ $data->tanggal_selesai }}</span></td>
                                    <td style="text-align: center;">
                                        @if(strtolower($data->status) === 'aktifkan')
                                            <span class="badge" style="background-color: #d2f8e1 !important; color: #1f7a42 !important; font-weight: 700; font-size: 0.72rem; padding: 6px 12px; border-radius: 20px; text-transform: uppercase;">Aktifkan</span>
                                        @else
                                            <span class="badge" style="background-color: #fbe0e0 !important; color: #b92c2c !important; font-weight: 700; font-size: 0.72rem; padding: 6px 12px; border-radius: 20px; text-transform: uppercase;">Nonaktifkan</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        @php
                                            $isChecked = false;
                                            if (is_array($configAktif) && isset($configAktif[$data->jenis_usulan])) {
                                                $entry = $configAktif[$data->jenis_usulan];
                                                $isChecked = ((int) ($entry['pencairan_ke'] ?? 0) === (int) $data->pencairan_ke)
                                                  && ((string) ($entry['tahun'] ?? '') === (string) $data->tahun);
                                            }
                                        @endphp
                                        <button type="button" class="sptjm-icon-btn sptjm-btn-edit edit-usulan"
                                            data-id="{{ $data->id }}" data-tahun="{{ $data->tahun }} "
                                            data-bulan="{{ $data->bulan }}"
                                            data-pencairan_ke="{{ $data->pencairan_ke }}"
                                            data-tanggal_mulai="{{ $data->tanggal_mulai }}"
                                            data-tanggal_selesai="{{ $data->tanggal_selesai }}"
                                            data-status="{{ $data->status }}" data-bs-toggle="modal"
                                            data-bs-target="#modalUsulanForm" title="Edit">
                                            <i class="bx bx-pencil"></i>
                                        </button>

                                        @if ($isChecked)
                                            <button type="button" class="toggle-ceklist sptjm-icon-btn sptjm-btn-delete" title="Nonaktifkan"
                                                data-action="noncek" data-id="{{ $data->id }}" data-jenis="{{ $data->jenis_usulan }}">
                                                <i class="bx bx-block"></i>
                                            </button>
                                        @else
                                            <button type="button" class="toggle-ceklist sptjm-icon-btn sptjm-btn-reset" title="Aktifkan Kembali"
                                                data-action="cek" data-id="{{ $data->id }}" data-jenis="{{ $data->jenis_usulan }}">
                                                <i class="bx bx-check"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        if (typeof $ !== 'undefined' && $.fn.DataTable) {
                            $('#pengaturanUsulanTable').DataTable({
                                paging: true,
                                searching: true,
                                ordering: true,
                                order: [[1, 'desc']], // default order by Tahun desc
                                columnDefs: [
                                    { orderable: false, searchable: false, targets: 7 } // disable sorting/search on Aksi column
                                ],
                                lengthChange: true,
                                language: {
                                    paginate: { first: 'Awal', last: 'Akhir', next: 'â†’', previous: 'â†' },
                                    zeroRecords: 'Data tidak ditemukan',
                                    infoEmpty: 'Tidak ada data tersedia',
                                    searchPlaceholder: 'Cari data...',
                                    search: 'Cari:'
                                }
                            });
                        }
                    });
                    </script>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalUsulanForm" tabindex="-1" aria-labelledby="modalUsulanFormLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form id="usulanForm" method="POST">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalUsulanTitle">Edit Pengaturan Usulan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <input type="hidden" name="_method" id="formMethod" value="PUT">
                                    <input type="hidden" id="usulanId" name="id">
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="modal_tahun" class="form-label">Tahun</label>
                                            <input type="text" class="form-control" id="modal_tahun" name="tahun"
                                                readonly style="background-color: #eceef1;">
                                        </div>
                                        <div class="mb-3">
                                            <label for="modal_bulan" class="form-label">Bulan</label>
                                            <input type="text" class="form-control" id="modal_bulan" name="bulan"
                                                readonly style="background-color: #eceef1;">
                                        </div>
                                        <div class="mb-3">
                                            <label for="modal_pencairan_ke" class="form-label">Pencairan ke</label>
                                            <input type="text" class="form-control" id="modal_pencairan_ke"
                                                name="pencairan_ke" readonly style="background-color: #eceef1;">
                                        </div>
                                        <div class="mb-3">
                                            <label for="modal_tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                            <input type="date" class="form-control" id="modal_tanggal_mulai"
                                                name="tanggal_mulai" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="modal_tanggal_selesai" class="form-label">Tanggal
                                                Selesai</label>
                                            <input type="date" class="form-control" id="modal_tanggal_selesai"
                                                name="tanggal_selesai" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="modal_status" class="form-label">Status</label>
                                            <select class="form-select" id="modal_status" name="status" required>
                                                <option value="Aktifkan">Aktifkan</option>
                                                <option value="Nonaktifkan">Nonaktifkan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <div id="modalSaveWarning" class="activation-warning d-none w-100 mb-2" role="alert"></div>
                                        <button type="submit" class="btn btn-success" style="background-color: #28c76f; border-color: #28c76f; color: #ffffff;">Simpan</button>
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const usedSptjmPencairan = @json($existingSptjm);
const usedTukinPencairan = @json($existingTukin);
const maxSptjmPencairan = @json($maxSptjm);

function updatePencairanDropdown() {
    const jenis = document.getElementById('jenis_usulan').value;
    const year = document.getElementById('tahun').value;
    const dropdown = document.getElementById('pencairan_ke');

    dropdown.innerHTML = ''; // reset opsi

    let options = [];

    if (jenis === 'SPTJM') {
        const usedThisYear = (usedSptjmPencairan && usedSptjmPencairan[year]) ? usedSptjmPencairan[year] : [];
        const all = Array.from({
            length: 20
        }, (_, i) => i + 1);
        options = all.filter(num => !usedThisYear.includes(num));
    } else if (jenis === 'TUKIN') {
        const usedThisYear = (usedTukinPencairan && usedTukinPencairan[year]) ? usedTukinPencairan[year] : [];
        const maxAllowed = maxSptjmPencairan > 0 ? maxSptjmPencairan + 1 : 1;
        const all = Array.from({
            length: maxAllowed
        }, (_, i) => i + 1);
        options = all.filter(num => !usedThisYear.includes(num));
    }

    if (options.length) {
        options.forEach(num => {
            const opt = document.createElement('option');
            opt.value = num;
            opt.textContent = num;
            dropdown.appendChild(opt);
        });
    } else {
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'Tidak ada opsi tersedia';
        dropdown.appendChild(opt);
    }
}

document.getElementById('jenis_usulan').addEventListener('change', updatePencairanDropdown);
document.getElementById('tahun').addEventListener('change', updatePencairanDropdown);

// initialize on load
updatePencairanDropdown();
</script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert untuk Notifikasi Sukses
    @if(session('add-success'))
    Swal.fire({
        title: 'Berhasil!',
        text: '{{ session('
        add - success ') }}',
        icon: 'success',
        customClass: {
            confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false
    });
    @endif

    @if(session('edit-success'))
    Swal.fire({
        title: 'Berhasil!',
        text: '{{ session('
        edit - success ') }}',
        icon: 'success',
        customClass: {
            confirmButton: 'btn btn-primary'
        },
        buttonsStyling: false
    });
    @endif

    document.body.addEventListener('click', function(event) {
        if (event.target.closest('.edit-usulan')) {
            let button = event.target.closest('.edit-usulan');
            document.getElementById('modalUsulanTitle').innerText = 'Edit Pengaturan Usulan';
            document.getElementById('usulanId').value = button.dataset.id;
            document.getElementById('modal_tahun').value = button.dataset.tahun;
            document.getElementById('modal_bulan').value = button.dataset.bulan;
            document.getElementById('modal_pencairan_ke').value = button.dataset.pencairan_ke;
            document.getElementById('modal_tanggal_mulai').value = button.dataset.tanggal_mulai;
            document.getElementById('modal_tanggal_selesai').value = button.dataset.tanggal_selesai;
            document.getElementById('modal_status').value = button.dataset.status;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('usulanForm').setAttribute('action',
                `/admin/pengaturan-usulan/${button.dataset.id}`);
        }
    });
});
</script>
<script>
// Prevent activating if tanggal_selesai is in the past â€” show modal warning via SweetAlert
function isDateBeforeToday(dateStr) {
    if (!dateStr) return false;
    const d = new Date(dateStr);
    const today = new Date();
    // zero time for comparison
    today.setHours(0,0,0,0);
    d.setHours(0,0,0,0);
    return d < today;
}

// Handler for forms: main create form and modal edit form
function attachActivateValidation(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    form.addEventListener('submit', function(e) {
        // find status value: support both radio inputs and select elements
        const statusRadio = form.querySelector('input[name="status"]:checked');
        const statusSelect = form.querySelector('select[name="status"]');
        const tanggalSelesaiInput = form.querySelector('input[name="tanggal_selesai"]');
        const statusVal = statusRadio ? statusRadio.value : (statusSelect ? statusSelect.value : null);
        const tanggalVal = tanggalSelesaiInput ? tanggalSelesaiInput.value : null;

        if (statusVal === 'Aktifkan' && isDateBeforeToday(tanggalVal)) {
            e.preventDefault();
            // Jika form berada dalam modal, tutup modal terlebih dahulu
            const modalEl = form.closest('.modal');
            if (modalEl) {
                const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                try { modalInstance.hide(); } catch (err) { /* ignore */ }
                // beri jeda kecil agar modal tertutup sebelum menampilkan SweetAlert
                setTimeout(() => {
                    Swal.fire({
                        title: 'Tidak bisa diaktifkan',
                        html: 'Tanggal selesai telah lewat sehingga pengaturan tidak dapat diaktifkan. Ubah tanggal selesai atau pilih status "Nonaktifkan".',
                        icon: 'warning',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    });
                }, 250);
            } else {
                Swal.fire({
                    title: 'Tidak bisa diaktifkan',
                    html: 'Tanggal selesai telah lewat sehingga pengaturan tidak dapat diaktifkan. Ubah tanggal selesai atau pilih status "Nonaktifkan".',
                    icon: 'warning',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            }
            return false;
        }
        // allow submit
    });
}

// Attach to both forms
attachActivateValidation('#pengaturanUsulanForm');
attachActivateValidation('#usulanForm');
</script>
<!-- Tampilkan modal dan label peringatan jika server mengembalikan error aktivasi -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('activate-error'))
        const msg = "{{ session('activate-error') }}";
        // tampilkan SweetAlert sebagai modal peringatan gagal simpan
        Swal.fire({
            title: 'Gagal menyimpan',
            text: msg,
            icon: 'error',
            customClass: { confirmButton: 'btn btn-primary' },
            buttonsStyling: false
        });

        // tunjukkan di modal edit jika ada
        const modalSaveWarning = document.getElementById('modalSaveWarning');
        if (modalSaveWarning) {
            modalSaveWarning.textContent = msg;
            modalSaveWarning.classList.remove('d-none');
        }
    @endif
});
</script>
<script>
// Handle ceklis/non-ceklist via AJAX to avoid page reload
document.addEventListener('click', async function(e) {
    const btn = e.target.closest('.toggle-ceklist');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    const action = btn.dataset.action; // 'cek' or 'noncek'
    const id = btn.dataset.id;
    const jenis = btn.dataset.jenis;
    btn.disabled = true;
    try {
        let res;
        if (action === 'cek') {
            res = await fetch(`/admin/pengaturan-usulan/${id}/ceklist`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
        } else {
            res = await fetch('/admin/pengaturan-usulan/non-ceklist', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ jenis: jenis })
            });
        }

        if (res.ok) {
            // Ensure only one checked per jenis: when checking, mark clicked as active (red block) and others as non-active (green check)
            if (action === 'cek') {
                const related = document.querySelectorAll(`.toggle-ceklist[data-jenis="${jenis}"]`);
                related.forEach(el => {
                    if (el === btn) {
                        // this becomes ACTIVE (red block)
                        el.classList.remove('sptjm-btn-reset');
                        el.classList.add('sptjm-btn-delete');
                        el.dataset.action = 'noncek';
                        el.title = 'Nonaktifkan';
                        el.innerHTML = '<i class="bx bx-block"></i>';
                    } else {
                        // others become INACTIVE (green check)
                        el.classList.remove('sptjm-btn-delete');
                        el.classList.add('sptjm-btn-reset');
                        el.dataset.action = 'cek';
                        el.title = 'Aktifkan Kembali';
                        el.innerHTML = '<i class="bx bx-check"></i>';
                    }
                });
            } else {
                // clicking noncek => set this button back to INACTIVE (green check)
                btn.classList.remove('sptjm-btn-delete');
                btn.classList.add('sptjm-btn-reset');
                btn.dataset.action = 'cek';
                btn.title = 'Aktifkan Kembali';
                btn.innerHTML = '<i class="bx bx-check"></i>';
            }
        } else {
            console.error('Request failed', res.status);
            const errText = await res.text().catch(() => '');
            console.error(errText);
        }
    } catch (err) {
        console.error(err);
    } finally {
        btn.disabled = false;
    }
});
</script>
<style>
/* Styling khusus untuk label peringatan aktivasi (menyerupai screenshot) */
.activation-warning {
    background-color: #fdecea; /* very light red/pink */
    color: #c0392b; /* darker red text */
    border-radius: 10px;
    padding: 18px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    font-size: 14px;
    line-height: 1.5;
}

/* Letakkan box peringatan ke kanan dan batasi lebar agar tidak menumpuk */
/* main card warning removed; no styles needed */

/* Modal warning full width inside modal footer */
#modalSaveWarning {
    background-color: #fdecea;
    color: #c0392b;
}
</style>
@endsection