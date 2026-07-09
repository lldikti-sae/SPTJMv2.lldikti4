@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')

    <div class="md-page-header mb-4">
        <div class="page-titles">
            <h4 class="fw-bold mb-1" style="color: #0f2b5c;">Daftar Pengguna Akun</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Pengaturan</a></li>
                    <li class="breadcrumb-item active" style="color: #0f2b5c;">Daftar Pengguna Akun</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="md-card mt-4">
        <div class="md-card-inner">
            <div class="md-toolbar d-flex justify-content-between align-items-center mb-4">
                {{-- Kiri: Show Entries --}}
                <div class="dataTables_length d-flex align-items-center gap-2">
                    <label class="mb-0 d-flex align-items-center gap-2 text-secondary" style="font-size: 0.875rem; white-space: nowrap; font-weight: 500;">
                        Show
                        <select id="entriesPerPage" class="form-select" style="width: auto; min-width: 75px; border-radius: 8px; border: 1.5px solid #cbd5e1; font-size: 0.875rem; height: 38px; padding-top: 4px; padding-bottom: 4px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        entries
                    </label>
                </div>

                {{-- Kanan: Search + Tombol Tambah --}}
                <div class="d-flex align-items-center gap-2">
                    <div class="dataTables_filter">
                        <label class="mb-0">
                            <div class="input-group input-group-merge" style="min-width: 240px; border-radius: 8px; overflow: hidden; border: 1.5px solid #cbd5e1; height: 38px;">
                                <span class="input-group-text border-0 bg-white" id="basic-addon-search31" style="padding-left: 12px; padding-right: 8px;"><i class="bx bx-search text-muted" style="font-size: 1.1rem;"></i></span>
                                <input type="search" class="form-control border-0 shadow-none" id="searchInput" placeholder="Cari data pengguna..." aria-controls="userTable" aria-describedby="basic-addon-search31" style="font-size: 0.875rem; padding-left: 0; height: 100%;">
                            </div>
                        </label>
                    </div>
                    <button type="button" class="btn btn-primary rounded-2 d-inline-flex align-items-center justify-content-center fw-semibold px-4" id="addPenggunaBtn" data-bs-toggle="modal" data-bs-target="#modalPenggunaForm" style="background-color: #0f2b5c; border-color: #0f2b5c; white-space: nowrap; font-size: 0.875rem; height: 38px;">
                        <i class="bx bx-plus me-1" style="font-size: 1.1rem;"></i> Tambah
                    </button>
                </div>
            </div>

            <div class="md-table-wrap table-responsive text-nowrap">
                <table class="table table-hover align-middle sptjm-datatable" id="userTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">No</th>
                            <th class="text-uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">Email</th>
                            <th class="text-uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">Role</th>
                            <th class="text-uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">Status</th>
                            <th class="text-uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">Kontak</th>
                            <th class="text-uppercase text-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $user->email }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-secondary text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ $user->role }}</span>
                                </td>
                                <td>
                                    @if($user->active == 1)
                                        <span class="badge rounded-pill bg-label-success px-3 d-inline-flex align-items-center gap-2">
                                            <span class="bg-success rounded-circle" style="width: 6px; height: 6px;"></span> Aktif
                                        </span>
                                    @else
                                        <span class="badge rounded-pill bg-label-danger px-3 d-inline-flex align-items-center gap-2">
                                            <span class="bg-danger rounded-circle" style="width: 6px; height: 6px;"></span> Tidak Aktif
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $user->cp ? Str::mask($user->cp, '*', -3, 2) : '-' }}</td>
                                <td>
                                    <button class="sptjm-icon-btn sptjm-btn-edit edit-pengguna" data-id="{{ $user->id }}"
                                        data-email="{{ $user->email }}" data-role="{{ $user->role }}"
                                        data-active="{{ $user->active }}" data-cp="{{ $user->cp }}" 
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalPenggunaForm" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.pengguna-akun.destroy', $user->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="sptjm-icon-btn sptjm-btn-delete delete-pengguna" id="confirm-text"
                                            data-id="{{ $user->id }}" title="Hapus">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($users, 'links'))
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalPenggunaForm" tabindex="-1" aria-labelledby="modalPenggunaFormLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold" id="modalPenggunaTitle" style="color: #4b5563; font-size: 1.15rem;">Tambah Pengguna Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="penggunaForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" id="penggunaId" name="id">

                    <div class="modal-body px-4 pt-4 pb-4">
                        <div class="mb-4">
                            <label class="text-uppercase fw-bold text-secondary mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Email</label>
                            <input type="text" class="form-control px-3 py-2" name="email" id="email" style="border-radius: 8px; border: 1px solid #cbd5e1;" required>
                        </div>
                        <div class="mb-4">
                            <label class="text-uppercase fw-bold text-secondary mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Password <span id="passwordRequiredStar" class="text-danger">*</span></label>
                            <div class="input-group input-group-merge" style="border-radius: 8px; overflow: hidden; border: 1px solid #cbd5e1;">
                                <input type="password" class="form-control border-0 px-3 py-2 shadow-none" name="password" id="password">
                                <span class="input-group-text border-0 bg-white" id="togglePassword" role="button">
                                    <i class="bx bx-show text-muted" id="togglePasswordIcon"></i>
                                </span>
                            </div>
                            <small class="text-muted d-none mt-1 d-block" id="passwordHelpText">Kosongkan jika tidak ingin mengubah password.</small>
                        </div>
                        <div class="mb-4">
                            <label class="text-uppercase fw-bold text-secondary mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Role</label>
                            <select class="form-select px-3 py-2" name="role" id="role" style="border-radius: 8px; border: 1px solid #cbd5e1;" required>
                                <option value="admin">Admin</option>
                                <option value="pic">PIC</option>
                                <option value="auditor">Auditor</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="text-uppercase fw-bold text-secondary mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Status</label>
                            <select class="form-select px-3 py-2" name="active" id="active" style="border-radius: 8px; border: 1px solid #cbd5e1;">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="text-uppercase fw-bold text-secondary mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">Kontak</label>
                            <input type="text" class="form-control px-3 py-2" name="cp" id="cp" style="border-radius: 8px; border: 1px solid #cbd5e1;">
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn btn-primary rounded-2 px-4 fw-semibold d-inline-flex align-items-center" style="background-color: #0f2b5c; border-color: #0f2b5c;"><i class="bx bx-save me-2"></i> Simpan</button>
                        <button type="button" class="btn btn-secondary rounded-2 px-4 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // SweetAlert untuk Notifikasi Sukses
            @if (session('add-success'))
                Swal.fire({
                    title: 'Berhasil!',
                    text: '{{ session('add-success') }}',
                    icon: 'success',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            @endif

            @if (session('edit-success'))
                Swal.fire({
                    title: 'Berhasil!',
                    text: '{{ session('edit-success') }}',
                    icon: 'success',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            @endif

                @if (session('success'))
                    Swal.fire({
                        title: 'Berhasil!',
                        text: '{{ session('success') }}',
                        icon: 'success',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                @endif

                @if ($errors->any())
                    Swal.fire({
                        title: 'Terjadi Kesalahan',
                        html: {!! json_encode(implode('<br>', $errors->all())) !!},
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });

                    // Jika validasi gagal, buka ulang modal dan isi dengan old input
                    (function() {
                        var method = {!! json_encode(old('_method', 'POST')) !!};
                        var oldId = {!! json_encode(old('id')) !!};
                        var action = (method === 'PUT' && oldId) ? `/admin/pengguna-akun/${oldId}` : "{{ route('admin.pengguna-akun.store') }}";

                        document.getElementById('formMethod').value = method;
                        document.getElementById('penggunaForm').setAttribute('action', action);
                        document.getElementById('modalPenggunaTitle').innerText = (method === 'PUT') ? 'Edit Pengguna Akun' : 'Tambah Pengguna Akun';
                        document.getElementById('penggunaId').value = {!! json_encode(old('id')) !!};
                        document.getElementById('email').value = {!! json_encode(old('email')) !!};
                        document.getElementById('password').value = '';
                        document.getElementById('role').value = {!! json_encode(old('role', 'admin')) !!};
                        document.getElementById('active').value = {!! json_encode(old('active', '1')) !!};
                        document.getElementById('cp').value = {!! json_encode(old('cp')) !!};

                        var modalEl = document.getElementById('modalPenggunaForm');
                        if (modalEl) {
                            var modal = new bootstrap.Modal(modalEl);
                            modal.show();
                        }
                    })();
                @endif

            // Reset Modal Form Saat Tambah Data Baru
            // Tambah Data
            document.getElementById('addPenggunaBtn').addEventListener('click', function() {
                document.getElementById('formMethod').value = 'POST';
                document.getElementById('penggunaForm').setAttribute('action',
                    "{{ route('admin.pengguna-akun.store') }}");
                document.getElementById('penggunaForm').reset();
                
                // Password wajib saat tambah
                document.getElementById('password').required = true;
                document.getElementById('passwordRequiredStar').classList.remove('d-none');
                document.getElementById('passwordHelpText').classList.add('d-none');
            });

            // Edit Data
            document.body.addEventListener('click', function(e) {
                const editBtn = e.target.closest('.edit-pengguna');
                if (editBtn) {
                    let id = editBtn.dataset.id;
                    let email = editBtn.dataset.email;
                    let role = editBtn.dataset.role;
                    let active = editBtn.dataset.active;
                    let cp = editBtn.dataset.cp;

                    // Ubah judul modal menjadi Edit Pengguna Akun
                    document.getElementById('modalPenggunaTitle').innerText = 'Edit Pengguna Akun';

                    // Ubah Method menjadi PUT
                    document.getElementById('formMethod').value = 'PUT';

                    // Set Form Action ke Route Update
                    document.getElementById('penggunaForm').setAttribute('action',
                        `/admin/pengguna-akun/${id}`);

                    // Isi Data
                    document.getElementById('penggunaId').value = id;
                    document.getElementById('email').value = email;
                    
                    let pwdInput = document.getElementById('password');
                    pwdInput.value = ''; // Kosongkan password saat edit
                    pwdInput.required = false;
                    document.getElementById('passwordRequiredStar').classList.add('d-none');
                    document.getElementById('passwordHelpText').classList.remove('d-none');
                    
                    document.getElementById('role').value = role;
                    document.getElementById('active').value = active;
                    document.getElementById('cp').value = cp;
                }
            });





            // SweetAlert Konfirmasi Hapus Data
            document.querySelectorAll('.delete-pengguna').forEach(button => {
                button.addEventListener('click', function() {
                    let form = this.closest('.delete-form');
                    Swal.fire({
                        title: 'Apakah Anda Yakin?',
                        text: "Data yang dihapus tidak bisa dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'btn btn-danger me-1',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // Fitur Pencarian
            document.getElementById("searchInput").addEventListener("keyup", function() {
                var filter = this.value.toLowerCase();
                applyFilters();
            });

            // Fitur Show Entries
            var currentPage = 1;
            function applyFilters() {
                var filter = document.getElementById('searchInput').value.toLowerCase();
                var limit = parseInt(document.getElementById('entriesPerPage').value) || 10;
                var rows = Array.from(document.querySelectorAll("#userTable tbody tr"));
                var filtered = rows.filter(row => row.textContent.toLowerCase().includes(filter));
                var shown = 0;
                rows.forEach(row => { row.style.display = 'none'; });
                filtered.slice((currentPage - 1) * limit, currentPage * limit).forEach(row => {
                    row.style.display = '';
                    shown++;
                });
                // Update pagination info if element exists
                var info = document.getElementById('tableInfo');
                if (info) {
                    var start = filtered.length ? (currentPage - 1) * limit + 1 : 0;
                    var end = Math.min(currentPage * limit, filtered.length);
                    info.textContent = 'Showing ' + start + ' to ' + end + ' of ' + filtered.length + ' entries';
                }
            }

            document.getElementById('entriesPerPage').addEventListener('change', function() {
                currentPage = 1;
                applyFilters();
            });

            applyFilters();

            // Toggle show/hide password (eye icon)
            var togglePassword = document.getElementById('togglePassword');
            if (togglePassword) {
                var passwordInput = document.getElementById('password');
                var toggleIcon = document.getElementById('togglePasswordIcon');
                togglePassword.addEventListener('click', function() {
                    if (!passwordInput) return;
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        if (toggleIcon) {
                            toggleIcon.classList.remove('bx-show');
                            toggleIcon.classList.add('bx-hide');
                        }
                    } else {
                        passwordInput.type = 'password';
                        if (toggleIcon) {
                            toggleIcon.classList.remove('bx-hide');
                            toggleIcon.classList.add('bx-show');
                        }
                    }
                });
            }
        });
    </script>
@endsection
