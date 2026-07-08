@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')

    <div class="card" style="width: 100%; padding: 10px;">
        <h5 class="card-header text-start p-2">Daftar Pengguna Akun</h5>
        <hr>
        <div class="table-responsive text-nowrap">
            <div class="d-flex justify-content-end align-items-center mb-3 px-3">
                <div class="input-group me-3" style="max-width: 200px;">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="search" class="form-control" id="searchInput" placeholder="Search...">
                </div>
                <button class="btn btn-sm btn-primary" id="addPenggunaBtn" type="button" data-bs-toggle="modal"
                    data-bs-target="#modalPenggunaForm">
                    <i class="bx bx-plus bx-sm me-1"></i><span class="d-none d-sm-inline-block">Tambah</span>
                </button>
            </div>

            <table class="table table-sm table-hover" id="userTable">
                <thead style="background-color: #dbdee0;">
                    <tr>
                        <th>No</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Kontak</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ ucfirst($user->role) }}</td>
                            <td>
                                <span class="badge {{ $user->active == 1 ? 'bg-label-primary' : 'bg-label-danger' }} ">
                                    {{ $user->active == 1 ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td>{{ Str::mask($user->cp, '*', -3, 2) }}</td>
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
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalPenggunaForm" tabindex="-1" aria-labelledby="modalPenggunaFormLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPenggunaTitle">Tambah Pengguna Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="penggunaForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" id="penggunaId" name="id">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="text" class="form-control" name="email" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label>Password <span id="passwordRequiredStar" class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="password">
                                <span class="input-group-text" id="togglePassword" role="button" style="cursor: pointer;">
                                    <i class="bx bx-show" id="togglePasswordIcon"></i>
                                </span>
                            </div>
                            <small class="text-muted d-none" id="passwordHelpText">Kosongkan jika tidak ingin mengubah password.</small>
                        </div>
                        <div class="mb-3">
                            <label>Role</label>
                            <select class="form-control" name="role" id="role" required>
                                <option value="admin">Admin</option>
                                <option value="pic">PIC</option>
                                <option value="auditor">Auditor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Status</label>
                            <select class="form-control" name="active" id="active">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Kontak</label>
                            <input type="text" class="form-control" name="cp" id="cp">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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
                document.querySelectorAll("#userTable tbody tr").forEach(row => {
                    row.style.display = row.textContent.toLowerCase().includes(filter) ? "" :
                        "none";
                });
            });

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
