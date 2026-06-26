@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="row">
    <div class="col-12">
        @if(auth()->user()->role !== 'pic')
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-header text-start p-1 mb-3">Cut Off Data Sisternas</h5>
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered">
                        <thead style="background-color: #dbdee0;">
                            <tr>
                                <th>Pelaporan</th>
                                <th>Untuk Pembayaran</th>
                                <th>Upload</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            <tr>
                                <form action="{{ route('admin.cutoff-sisternas.upload') }}" method="POST"
                                    enctype="multipart/form-data" class="uploadForm">
                                    @csrf
                                    <td>
                                        <input name="table" value="o_sister_genap_tl" type="hidden">
                                        <strong>Genap Tahun Lalu [Maret - Agustus]</strong>
                                    </td>
                                    <td>Januari - Februari Berjalan</td>
                                    <td>
                                        <input class="form-control" type="file" name="dokumen" required>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <button type="button" class="btn btn-sm btn-warning clear-data-btn"
                                                data-table="o_sister_genap_tl">Clear
                                                Data</button>
                                            <button type="submit" class="btn btn-sm btn-success mx-2">Simpan</button>
                                        </div>
                                    </td>
                                </form>
                            </tr>

                            <tr>
                                <form action="{{ route('admin.cutoff-sisternas.upload') }}" method="POST"
                                    enctype="multipart/form-data" class="uploadForm">
                                    @csrf
                                    <td>
                                        <input name="table" value="p_sister_ganjil_tl" type="hidden">
                                        <strong>Ganjil Tahun Lalu [September - Desember]</strong>
                                    </td>
                                    <td>Maret - Agustus Berjalan</td>
                                    <td>
                                        <input class="form-control" type="file" name="dokumen" required>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <button type="button" class="btn btn-sm btn-warning clear-data-btn"
                                                data-table="p_sister_ganjil_tl">Clear
                                                Data</button>
                                            <button type="submit" class="btn btn-sm btn-success mx-2">Simpan</button>
                                        </div>
                                    </td>
                                </form>
                            </tr>

                            <tr>
                                <form action="{{ route('admin.cutoff-sisternas.upload') }}" method="POST"
                                    enctype="multipart/form-data" class="uploadForm">
                                    @csrf
                                    <td>
                                        <input name="table" value="n_sister_genap_bj" type="hidden">
                                        <strong>Genap Berjalan [Maret - Agustus]</strong>
                                    </td>
                                    <td>September - Desember Berjalan</td>
                                    <td>
                                        <input class="form-control" type="file" name="dokumen" required>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <button type="button" class="btn btn-sm btn-warning clear-data-btn"
                                                data-table="n_sister_genap_bj">Clear
                                                Data</button>
                                            <button type="submit" class="btn btn-sm btn-success mx-2">Simpan</button>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-1">
                    <a href="{{ Storage::url('dokumen/contoh.csv') }}" target="_blank">Lihat contoh file CSV</a>
                </div>
            </div>
        </div>
        @endif

        <div class="col-lg-12 col-md-6 mb-3">
            <label class="form-label" for="selectTypeOpt">Pilih Data Sisternas</label>
            <div class="d-flex align-items-center">

                <select name="sisternas" id="sisternasSelect" class="form-select me-2 w-25" required>
                    <option value="">Pilih Data</option>
                    <option value="o_sister_genap_tl"
                        {{ request('sisternas') == 'o_sister_genap_tl' ? 'selected' : '' }}>Genap Tahun Lalu [Januari
                        - Februari]</option>
                    <option value="p_sister_ganjil_tl"
                        {{ request('sisternas') == 'p_sister_ganjil_tl' ? 'selected' : '' }}>Ganjil Tahun Lalu
                        [Maret - Agustus]</option>
                    <option value="n_sister_genap_bj"
                        {{ request('sisternas') == 'n_sister_genap_bj' ? 'selected' : '' }}>Genap Berjalan [September -
                        Desember]</option>
                </select>

                @if(auth()->user()->role !== 'pic')
                <button type="button" class="btn btn-primary ms-2" id="addDataBtn">
                    Tambah Data
                </button>
                @endif

            </div>
        </div>

        <div id="loading" style="display: none;">
            <div class="spinner"></div>
            <p>Loading...</p>
        </div>

        <div id="table-container"></div> <!-- Kontainer untuk tabel hasil -->

        <!-- coba -->
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">Data Sisternas</h5>
                        <div>
                            <button type="button" id="exportBackupBtn" class="btn btn-outline-secondary btn-sm">Export Backup ODS</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Tabel Data -->
                        <div class="table-responsive text-nowrap" style="overflow-x: auto;">
                            <table class="table table-sm table-bordered table-hover" id="cutoffTable">
                                <thead style="background-color: #dbdee0;">
                                    <tr>
                                        <th style="text-align: center;">NO</th>
                                        <th style="text-align: center;">NIDN</th>
                                        <th style="text-align: center;">NUPTK</th>
                                        <th style="text-align: center;">NO SERTIFIKAT</th>
                                        <th style="text-align: center;">NAMA DOSEN</th>
                                        <th style="text-align: center;">KODE PT</th>
                                        <th style="text-align: center;">PERGURUAN TINGGI</th>
                                        <th style="text-align: center;">PROGRAM STUDI</th>
                                        <th style="text-align: center;">KESIMPULAN BKD</th>
                                        <th style="text-align: center;">KEWAJIBAN KHUSUS</th>
                                        <th style="text-align: center;">KESIMPULAN</th>
                                        <th style="text-align: center;">KD</th>
                                        <th style="text-align: center;">KP</th>
                                        <th style="text-align: center;">POTONGAN PERIODIK</th>
                                        <th style="text-align: center;">AKSI</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Cut Off Data Sisternas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateForm">
                    <div class="mb-f3">
                        <label for="nidn" class="form-label">NIDN</label>
                        <input type="text" class="form-control" id="nidn" name="nidn" readonly style="background-color: #eceef1;">
                    </div>

                    <div class="mb-3">
                        <label for="nuptk" class="form-label">NUPTK</label>
                        <input type="text" class="form-control" id="nuptk" name="nuptk" readonly style="background-color: #eceef1;">
                    </div>

                    <div class="mb-3">
                        <label for="no_sertifikat" class="form-label">No Sertifikat</label>
                        <input type="text" class="form-control" id="no_sertifikat" name="no_sertifikat">
                    </div>

                    <div class="mb-3">
                        <label for="nama_dosen" class="form-label">Nama Dosen</label>
                        <input type="text" class="form-control" id="nama_dosen" name="nama_dosen">
                    </div>

                    <div class="mb-3">
                        <label for="kode_pt" class="form-label">Kode PT</label>
                        <input type="text" class="form-control" id="kode_pt" name="kode_pt">
                    </div>

                    <div class="mb-3">
                        <label for="pt" class="form-label">Perguruan Tinggi</label>
                        <input type="text" class="form-control" id="pt" name="pt">
                    </div>

                    <div class="mb-3">
                        <label for="prodi" class="form-label">Program Studi</label>
                        <input type="text" class="form-control" id="prodi" name="prodi">
                    </div>

                    <div class="mb-3">
                        <label for="kesimpulan_bkd" class="form-label">Kesimpulan BKD</label>
                        <select class="form-select" id="kesimpulan_bkd" name="kesimpulan_bkd">
                            <option value="">Pilih</option>
                            <option value="M">M</option>
                            <option value="TM">TM</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="kewajiban_khusus" class="form-label">Kewajiban Khusus</label>
                        <select class="form-select" id="kewajiban_khusus" name="kewajiban_khusus">
                            <option value="">Pilih</option>
                            <option value="Memenuhi">Memenuhi</option>
                            <option value="Tugas Belajar">Tugas Belajar</option>
                            <option value="Tidak Memenuhi">Tidak Memenuhi</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="kesimpulan" class="form-label">Kesimpulan</label>
                        <select class="form-select" id="kesimpulan" name="kesimpulan">
                            <option value="">Pilih</option>
                            <option value="Memenuhi">Memenuhi</option>
                            <option value="Tidak Memenuhi">Tidak Memenuhi</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="kd" class="form-label">KD</label>
                        <input type="number" step="0.01" class="form-control" id="kd" name="kd" min="0">
                    </div>

                    <div class="mb-3">
                        <label for="kp" class="form-label">KP</label>
                        <input type="number" step="0.01" class="form-control" id="kp" name="kp" min="0">
                    </div>

                    <div class="mb-3">
                        <label for="potongan_periodik" class="form-label">Potongan Periodik</label>
                        <input type="number" step="0.01" class="form-control" id="potongan_periodik" name="potongan_periodik" min="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="updateForm" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Tambah Data Sisternas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createForm">
                    <input type="hidden" name="sisternas" id="create_sisternas">
                    <div class="mb-3">
                        <label class="form-label">Data Sisternas Terpilih</label>
                        <input type="text" id="create_sisternas_label" class="form-control" value="" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="create_nidn" class="form-label">NIDN</label>
                        <input type="text" class="form-control" id="create_nidn" name="nidn" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_nuptk" class="form-label">NUPTK</label>
                        <input type="text" class="form-control" id="create_nuptk" name="nuptk" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_no_sertifikat" class="form-label">No Sertifikat</label>
                        <input type="text" class="form-control" id="create_no_sertifikat" name="no_sertifikat" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_nama_dosen" class="form-label">Nama Dosen</label>
                        <input type="text" class="form-control" id="create_nama_dosen" name="nama_dosen" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_kode_pt" class="form-label">Kode PT</label>
                        <input type="text" class="form-control" id="create_kode_pt" name="kode_pt" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_pt" class="form-label">Perguruan Tinggi</label>
                        <input type="text" class="form-control" id="create_pt" name="pt" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_prodi" class="form-label">Program Studi</label>
                        <input type="text" class="form-control" id="create_prodi" name="prodi" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_kesimpulan_bkd" class="form-label">Kesimpulan BKD</label>
                        <select class="form-select" id="create_kesimpulan_bkd" name="kesimpulan_bkd" required>
                            <option value="">Pilih</option>
                            <option value="M">M</option>
                            <option value="TM">TM</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="create_kewajiban_khusus" class="form-label">Kewajiban Khusus</label>
                        <select class="form-select" id="create_kewajiban_khusus" name="kewajiban_khusus" required>
                            <option value="">Pilih</option>
                            <option value="Memenuhi">Memenuhi</option>
                            <option value="Tugas Belajar">Tugas Belajar</option>
                            <option value="Tidak Memenuhi">Tidak Memenuhi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="create_kesimpulan" class="form-label">Kesimpulan</label>
                        <select class="form-select" id="create_kesimpulan" name="kesimpulan" required>
                            <option value="">Pilih</option>
                            <option value="Memenuhi">Memenuhi</option>
                            <option value="Tidak Memenuhi">Tidak Memenuhi</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="create_kd" class="form-label">KD</label>
                        <input type="number" step="0.01" class="form-control" id="create_kd" name="kd" required min="0">
                    </div>
                    <div class="mb-3">
                        <label for="create_kp" class="form-label">KP</label>
                        <input type="number" step="0.01" class="form-control" id="create_kp" name="kp" required min="0">
                    </div>
                    <div class="mb-3">
                        <label for="create_potongan_periodik" class="form-label">Potongan Periodik</label>
                        <input type="number" step="0.01" class="form-control" id="create_potongan_periodik" name="potongan_periodik" required min="0">
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const uploadForm = document.querySelectorAll('.uploadForm');
    uploadForm.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Mohon Tunggu...',
                html: `
                      <div class="d-flex justify-content-center align-items-center flex-column">
                          <div class="spinner-border spinner-border-lg text-primary" role="status">
                              <span class="visually-hidden">Loading...</span>
                          </div>
                          <div class="mt-2">Sedang mengupload data</div>
                      </div>
                  `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                backdrop: true
            });

            const formData = new FormData(form);
            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.headerMismatch) {
                        // Siapkan konten untuk Bootstrap modal seperti di admin/migrasi
                        const expectedBadges = (data.expectedColumns || []).map(c => `<span class=\"badge bg-label-primary text-dark me-1 mb-1\">${c}</span>`).join(' ');
                        const missingBadges = (data.missingColumns || []).map(c => `<span class=\"badge bg-label-warning text-dark me-1 mb-1\">${c}</span>`).join(' ');
                        const extraBadges = (data.extraColumns || []).map(c => `<span class=\"badge bg-label-info text-dark me-1 mb-1\">${c}</span>`).join(' ');

                        // Inject ke modal
                        const modalEl = document.getElementById('cutoffRejectedModal');
                        if (modalEl) {
                            modalEl.querySelector('.js-expected').innerHTML = expectedBadges;
                            const missEl = modalEl.querySelector('.js-missing');
                            const extraEl = modalEl.querySelector('.js-extra');
                            const countsEl = modalEl.querySelector('.js-counts');
                            if (countsEl) countsEl.textContent = `Diharapkan: ${data.expectedCount} kolom | Ditemukan: ${data.foundCount} kolom`;
                            if (missEl) missEl.innerHTML = missingBadges || '<span class=\"text-muted\">-</span>';
                            if (extraEl) extraEl.innerHTML = extraBadges || '<span class=\"text-muted\">-</span>';

                            // Tampilkan modal Bootstrap
                            if (window.bootstrap && bootstrap.Modal) {
                                const m = new bootstrap.Modal(modalEl);
                                m.show();
                            } else {
                                // Fallback
                                modalEl.classList.add('show');
                                modalEl.style.display = 'block';
                            }
                        }
                        return;
                    }

                    Swal.fire({
                        icon: data.success ? 'success' : 'error',
                        title: data.success ? 'Berhasil!' : 'Gagal!',
                        text: data.message,
                    });

                    if (data.success) {
                        console.log(data);
                        form.reset();
                        cutOffTable.ajax.reload()
                    }
                })
                .catch(() => {
                    Swal.close();
                    Swal.fire('Error!', 'Terjadi kesalahan saa  t mengupload data.',
                        'error');
                });
        });
    });

    //datatable
    const cutOffTable = $('#cutoffTable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        scrollCollapse: true,
        ajax: {
            url: '{{ route("admin.cutoff-sisternas") }}',
            data: function(d) {
                d.sisternas = $('#sisternasSelect').val() //kirim valuenya
            },
            ajax: null

        },
        columns: [{
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                searchable: false,
                orderable: false
            },
            {
                data: 'nidn',
                name: 'nidn',
                searchable: true
            },
            {
                data: 'nuptk',
                name: 'nuptk',
                searchable: true
            },
            {
                data: 'no_sertifikat',
                name: 'no_sertifikat'
            },
            {
                data: 'nama_dosen',
                name: 'nama_dosen'
            },
            {
                data: 'kode_pt',
                name: 'kode_pt'
            },
            {
                data: 'pt',
                name: 'pt'
            },
            {
                data: 'prodi',
                name: 'prodi'
            },
            {
                data: 'kesimpulan_bkd',
                name: 'kesimpulan_bkd'
            },
            {
                data: 'kewajiban_khusus',
                name: 'kewajiban_khusus'
            },
            {
                data: 'kesimpulan',
                name: 'kesimpulan'
            },
            {
                data: 'kd',
                name: 'kd'
            },
            {
                data: 'kp',
                name: 'kp'
            },
            {
                data: 'potongan_periodik',
                name: 'potongan periodik',
                searchable: false,
                orderable: false
            },
            {
                data: 'aksi',
                name: 'aksi',
                orderable: false,
                searchable: false
            }
        ],
        order: [
            [1, 'asc']
        ],
        responsive: true,
        language: {
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "→",
                previous: "←",
            },
            zeroRecords: "Data tidak ditemukan",
            infoEmpty: "Tidak ada data tersedia",
            searchPlaceholder: "Cari data...",
            search: "Cari Data:"
        },
    });

    $('#sisternasSelect').change(() => {
        cutOffTable.ajax.reload()
    });

    // Open create modal
    $('#addDataBtn').on('click', function() {
        const selected = $('#sisternasSelect').val();
        if (!selected) {
            Swal.fire('Pilih Data', 'Silakan pilih data sisternas terlebih dahulu.', 'info');
            return;
        }
        // Reset terlebih dahulu agar nilai yang diset tidak terhapus
        $('#createForm')[0].reset();
        // Set label tampilan dan hidden value setelah reset
        $('#create_sisternas').val(selected);
        const labelText = $('#sisternasSelect option:selected').text().trim();
        $('#create_sisternas_label').val(labelText).attr('value', labelText).attr('placeholder', labelText);
        $('#createModal').modal('show');
    });

    // Jaga-jaga: saat modal akan ditampilkan, sinkronkan lagi label & hidden
    $('#createModal').on('show.bs.modal', function () {
        const selected = $('#sisternasSelect').val();
        const labelText = $('#sisternasSelect option:selected').text().trim();
        $('#create_sisternas').val(selected);
        $('#create_sisternas_label').val(labelText).attr('value', labelText).attr('placeholder', labelText);
    });

    // Submit create form
    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        $('#createModal').modal('hide');
        Swal.fire({
            title: 'Mohon Tunggu...',
            html: `
          <div class="d-flex justify-content-center align-items-center flex-column">
            <div class="spinner-border spinner-border-lg text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Sedang menyimpan data...</div>
          </div>
        `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            backdrop: true
        });

        $.ajax({
            url: `{{ route('admin.cutoff-sisternas.create') }}`,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                sisternas: $('#create_sisternas').val(),
                nidn: $('#create_nidn').val(),
                nuptk: $('#create_nuptk').val(),
                no_sertifikat: $('#create_no_sertifikat').val(),
                nama_dosen: $('#create_nama_dosen').val(),
                kode_pt: $('#create_kode_pt').val(),
                pt: $('#create_pt').val(),
                prodi: $('#create_prodi').val(),
                kesimpulan_bkd: $('#create_kesimpulan_bkd').val(),
                kewajiban_khusus: $('#create_kewajiban_khusus').val(),
                kesimpulan: $('#create_kesimpulan').val(),
                kd: $('#create_kd').val(),
                kp: $('#create_kp').val(),
                potongan_periodik: $('#create_potongan_periodik').val(),
            },
            success: function(res) {
                cutOffTable.ajax.reload();
                Swal.fire('Berhasil!', res.message || 'Data berhasil ditambahkan.', 'success');
            },
            error: function(xhr) {
                let msg = 'Terjadi kesalahan saat menambahkan data.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire('Error!', msg, 'error');
            }
        });
    });

    // Handle clear button click
    $('.clear-data-btn').on('click', function() {
        var table = $(this).data('table');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('/admin/cutoff-sisternas/clear') }}/${table}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        Swal.fire('Berhasil!', res.message, 'success');
                        cutOffTable.ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error!',
                            'Terjadi kesalahan saat menghapus data.', 'error');
                    }
                });
            }
        });
    });

    // Handle edit button click
    $('#cutoffTable').on('click', '.edit-btn', function() {
        var data = cutOffTable.row($(this).parents('tr')).data();

        // data
        $('#nidn').val(data.nidn);
        $('#nuptk').val(data.nuptk || '');
        $('#no_sertifikat').val(data.no_sertifikat || '');
        $('#nama_dosen').val(data.nama_dosen || '');
        $('#kode_pt').val(data.kode_pt || '');
        $('#pt').val(data.pt || '');
        $('#prodi').val(data.prodi || '');
        $('#kesimpulan_bkd').val(data.kesimpulan_bkd || '');
        $('#kewajiban_khusus').val(data.kewajiban_khusus || '');
        $('#kesimpulan').val(data.kesimpulan || '');
        $('#kd').val(data.kd ?? '');
        $('#kp').val(data.kp ?? '');
        $('#potongan_periodik').val(data.potongan_periodik ?? '');

        // menamppilkan modal
        $('#editModal').modal('show');
    });
    //update data
    $('#updateForm').on('submit', function(e) {
        e.preventDefault();
        $('#editModal').modal('hide');
        Swal.fire({
            title: 'Mohon Tunggu...',
            html: `
          <div class="d-flex justify-content-center align-items-center flex-column">
            <div class="spinner-border spinner-border-lg text-success" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Sedang menyimpan data...</div>
          </div>
        `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            backdrop: true
        });

        $.ajax({
            url: `{{ route('admin.cutoff-sisternas.update') }}`,
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                nidn: $('#nidn').val(),
                sisternas: $('#sisternasSelect').val(),
                nuptk: $('#nuptk').val(),
                no_sertifikat: $('#no_sertifikat').val(),
                nama_dosen: $('#nama_dosen').val(),
                kode_pt: $('#kode_pt').val(),
                pt: $('#pt').val(),
                prodi: $('#prodi').val(),
                kesimpulan_bkd: $('#kesimpulan_bkd').val(),
                kewajiban_khusus: $('#kewajiban_khusus').val(),
                kesimpulan: $('#kesimpulan').val(),
                kd: $('#kd').val(),
                kp: $('#kp').val(),
                potongan_periodik: $('#potongan_periodik').val(),
            },
            success: function(res) {
                console.log(res);
                $('#editModal').modal('hide');
                cutOffTable.ajax.reload();
                Swal.fire('Berhasil!', 'Data berhasil diperbarui.', 'success');
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                Swal.fire('Error!', 'Terjadi kesalahan saat memperbarui data.', 'error');
            }
        });
    });

    $('#exportBackupBtn').on('click', function() {
        const selected = $('#sisternasSelect').val();
        if (!selected) {
            Swal.fire('Pilih Data', 'Silakan pilih data sisternas terlebih dahulu.', 'info');
            return;
        }
        const url = `{{ route('admin.cutoff-sisternas.export') }}?table=${selected}`;
        window.open(url, '_blank');
    });
});
</script>
{{-- Modal penolakan header untuk cutoff-sisternas (selaras dengan admin/migrasi) --}}
<div class="modal fade" id="cutoffRejectedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Ditolak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Jumlah dan penamaan kolom tidak sesuai dengan tabel.</p>
                <p class="mb-2 js-counts"></p>
                <div class="alert alert-primary">
                    <strong>Kolom yang diharapkan:</strong>
                    <div class="mt-2 js-expected"></div>
                </div>
                <div class="alert alert-warning">
                    <strong>Kolom yang tidak ada (missing):</strong>
                    <div class="mt-2 js-missing"></div>
                </div>
                <div class="alert alert-info">
                    <strong>Kolom yang tidak dikenal (extra):</strong>
                    <div class="mt-2 js-extra"></div>
                </div>
                <p class="mb-1">Silakan sesuaikan file CSV Anda agar sama persis dengan kolom tabel.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection
