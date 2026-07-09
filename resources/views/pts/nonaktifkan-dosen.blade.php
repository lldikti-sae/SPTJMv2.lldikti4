@extends('layouts/contentNavbarLayoutPts')

@section('title', 'SPTJM Online')

@section('content')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="card" style="width: 100%; padding: 10px;">
        <h5 class="card-header text-start p-2">Nonaktifkan Status Aktif Dosen</h5>
        <hr>
        <!-- Menggunakan search bawaan DataTables; input pencarian eksternal dihapus -->

        <div class="table-responsive text-nowrap">
            <table class="table table-sm table-hover" id="dosenTable">
                <thead style="background-color: #dbdee0;">
                    <tr>
                        <th>No</th>
                        <th>NIDN</th>
                        <th>NUPTK</th>
                        <th>Nama Dosen</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0"></tbody>
            </table>
        </div>

        <!-- Modal konfirmasi toggle aktif -->
        <div class="modal fade" id="confirmToggleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirmToggleMessage">Apakah Anda yakin ingin mengganti status keaktifan dosen ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="confirmToggleYes">Ya, lanjutkan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Setup CSRF for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var table = $('#dosenTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: '{{ route('pts.nonaktifkan-dosen.data') }}',
                    type: 'POST'
                },
                columns: [
                    { data: null, orderable: false, searchable: false },
                    { data: 'nidn' },
                    { data: 'nuptk' },
                    { data: 'nama' },
                    { data: 'jabatan', defaultContent: '-' },
                    { data: 'aktif', orderable: false, searchable: false },
                    { data: 'actions', orderable: false, searchable: false },
                ],
                order: [[3, 'asc']],
                lengthMenu: [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
                drawCallback: function(settings) {
                    // nothing here; we use delegated event handlers below
                },
                columnDefs: [
                    {
                        targets: 0,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    }
                ]
            });

            // Delegated handler: show confirmation modal when tombol diklik
                    var identifierToToggle = null;
            var actionToPerform = null;
            $(document).on('click', '.btn-toggle', function(e) {
                e.preventDefault();
                identifierToToggle = $(this).data('identifier');
                actionToPerform = $(this).data('action'); // 'activate' or 'deactivate'
                // update modal message based on action
                var rowName = $(this).closest('tr').find('td:eq(3)').text();
                // escape content to avoid XSS since we'll set HTML with line breaks
                var safeName = $('<div/>').text(rowName).html();
                var safeId = $('<div/>').text(identifierToToggle).html();
                if (actionToPerform === 'activate') {
                    $('#confirmToggleMessage').html('Apakah Anda yakin ingin mengaktifkan<br>"' + safeName + '" (ID: ' + safeId + ')?');
                    $('#confirmToggleYes').removeClass('btn-danger btn-primary').addClass('btn-success').text('Ya, Aktifkan');
                } else {
                    $('#confirmToggleMessage').html('Apakah Anda yakin ingin menonaktifkan<br>"' + safeName + '" (ID: ' + safeId + ')?');
                    $('#confirmToggleYes').removeClass('btn-success btn-primary').addClass('btn-danger').text('Ya, Nonaktifkan');
                }
                var modal = new bootstrap.Modal(document.getElementById('confirmToggleModal'));
                modal.show();
            });

            // On confirm, call toggle endpoint
            $('#confirmToggleYes').on('click', function() {
                if (!identifierToToggle) return;
                var $btn = $(this);
                $btn.prop('disabled', true);
                var originalText = $btn.text();
                $btn.text('Memproses...');
                $.post('{{ route('pts.nonaktifkan-dosen.toggle') }}', { identifier: identifierToToggle })
                    .done(function(resp) {
                        if (resp.success) {
                            Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Status keaktifan telah diubah.' });
                            var bsModal = bootstrap.Modal.getInstance(document.getElementById('confirmToggleModal'));
                            if (bsModal) bsModal.hide();
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: resp.message || 'Terjadi kesalahan.' });
                        }
                    })
                    .fail(function(xhr) {
                        var msg = 'Terjadi kesalahan.';
                        if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        Swal.fire({ icon: 'error', title: 'Gagal', text: msg });
                    })
                    .always(function() {
                        $btn.prop('disabled', false).text(originalText);
                        identifierToToggle = null;
                        actionToPerform = null;
                        // reset confirm button to neutral style
                        $btn.removeClass('btn-danger btn-success').addClass('btn-primary').text('Ya, lanjutkan');
                    });
            });
        });
    </script>

@endsection
