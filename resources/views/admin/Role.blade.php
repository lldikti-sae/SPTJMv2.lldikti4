@extends('layouts/contentNavbarLayout')

@section('title', 'Hak Akses PIC')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')

    <div class="card" style="width: 100%; padding: 10px;">
        <h5 class="card-header text-start p-2">Hak Akses Modul Admin untuk PIC</h5>
        <hr>
        <div class="table-responsive text-nowrap">
            <div class="d-flex justify-content-end align-items-center mb-3 px-3">
                <div class="input-group me-3" style="max-width: 200px;">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="search" class="form-control" id="searchInput" placeholder="Search...">
                </div>
            </div>

            <table class="table table-sm table-hover" id="picTable">
                <thead style="background-color: #dbdee0;">
                    <tr>
                        <th>No</th>
                        <th>Email PIC</th>
                        <th>Status</th>
                        <th>Akses Saat Ini</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->active == 1 ? 'bg-label-primary' : 'bg-label-danger' }} ">
                                    {{ $user->active == 1 ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $perms = $user->admin_permissions ?? [];
                                @endphp
                                @if(empty($perms))
                                    <span class="text-muted"><i class="bx bx-x"></i> Tidak ada akses</span>
                                @else
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($perms as $perm)
                                            <span class="badge bg-label-success">{{ ucwords(str_replace('-', ' ', $perm)) }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info edit-akses" data-id="{{ $user->id }}"
                                    data-email="{{ $user->email }}"
                                    data-permissions='{{ json_encode($user->admin_permissions ?? []) }}'
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalAksesForm">
                                    <i class="bx bx-check-shield"></i> Atur Akses
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Edit Akses -->
    <div class="modal fade" id="modalAksesForm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Atur Akses PIC: <span id="picEmailDisplay" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="aksesForm" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="modal-body">
                        <p class="text-muted mb-4">Pilih modul Admin yang diizinkan untuk diakses oleh PIC ini.</p>
                        
                        <div class="list-group">
                            <label class="list-group-item d-flex gap-2">
                                <input class="form-check-input flex-shrink-0 admin-permission-cb" type="checkbox" name="admin_permissions[]" value="data-dosen">
                                <span>
                                    <strong>Data Dosen</strong>
                                    <small class="d-block text-muted">Melihat dan mengelola Data Dosen / Master Dosen</small>
                                </span>
                            </label>
                            <label class="list-group-item d-flex gap-2">
                                <input class="form-check-input flex-shrink-0 admin-permission-cb" type="checkbox" name="admin_permissions[]" value="skpp">
                                <span>
                                    <strong>SKPP</strong>
                                    <small class="d-block text-muted">Melihat dan memproses data SKPP</small>
                                </span>
                            </label>
                            <label class="list-group-item d-flex gap-2">
                                <input class="form-check-input flex-shrink-0 admin-permission-cb" type="checkbox" name="admin_permissions[]" value="kekurangan-bayar">
                                <span>
                                    <strong>Kekurangan Bayar</strong>
                                    <small class="d-block text-muted">Akses ke modul Kurang/Lebih Bayar</small>
                                </span>
                            </label>
                            <label class="list-group-item d-flex gap-2">
                                <input class="form-check-input flex-shrink-0 admin-permission-cb" type="checkbox" name="admin_permissions[]" value="rekap-pencairan">
                                <span>
                                    <strong>Rekap Pencairan</strong>
                                    <small class="d-block text-muted">Melihat Rekapitulasi Pencairan</small>
                                </span>
                            </label>
                            <label class="list-group-item d-flex gap-2">
                                <input class="form-check-input flex-shrink-0 admin-permission-cb" type="checkbox" name="admin_permissions[]" value="sinkronisasi">
                                <span>
                                    <strong>Sinkronisasi</strong>
                                    <small class="d-block text-muted">Akses ke Sinkronisasi Data (Pajak, dll)</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan Hak Akses</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('success'))
                Swal.fire({
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    title: 'Terjadi Kesalahan',
                    text: '{{ session('error') }}',
                    icon: 'error',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
            @endif

            // Edit Data
            document.body.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.edit-akses');
                if (editBtn) {
                    let id = editBtn.dataset.id;
                    let email = editBtn.dataset.email;
                    let perms = [];
                    try {
                        perms = JSON.parse(editBtn.dataset.permissions || '[]');
                    } catch(e) {}

                    document.getElementById('picEmailDisplay').innerText = email;
                    document.getElementById('aksesForm').setAttribute('action', `/admin/hak-akses-pic/${id}`);
                    
                    // Uncheck all first
                    document.querySelectorAll('.admin-permission-cb').forEach(cb => {
                        cb.checked = false;
                    });
                    
                    // Check according to data
                    document.querySelectorAll('.admin-permission-cb').forEach(cb => {
                        if (perms.includes(cb.value)) {
                            cb.checked = true;
                        }
                    });
                }
            });

            // Fitur Pencarian
            document.getElementById("searchInput").addEventListener("keyup", function() {
                var filter = this.value.toLowerCase();
                document.querySelectorAll("#picTable tbody tr").forEach(row => {
                    row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
                });
            });
        });
    </script>
@endsection
