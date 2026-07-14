@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')

<div class="card" style="width: 100%; padding: 10px;">
  <div class="d-flex justify-content-between align-items-center p-2">
    <h5 class="card-header m-0 p-0 text-start">Master Data Penandatangan</h5>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#modalTambahPejabat">
      <i class="bx bx-plus me-1"></i> Tambah Pejabat
    </button>
  </div>
  <hr>
  <div class="card-body">
    <div class="table-responsive text-nowrap">
        <table class="table table-striped" id="tablePejabat">
            <thead>
                <tr>
                    <th>Urutan</th>
                    <th>Nama Penandatangan</th>
                    <th>NIP</th>
                    <th>Jabatan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pejabatList as $pejabat)
                <tr>
                    <td>{{ $pejabat->urutan }}</td>
                    <td>{{ $pejabat->nama }}</td>
                    <td>{{ $pejabat->nip }}</td>
                    <td>{{ $pejabat->jabatan }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input toggle-status" type="checkbox" data-id="{{ $pejabat->id }}" {{ $pejabat->is_aktif ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                            <span class="badge rounded-pill {{ $pejabat->is_aktif ? 'bg-label-primary' : 'bg-label-danger' }} status-badge-{{ $pejabat->id }}">
                                {{ $pejabat->is_aktif ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-1 justify-content-start">
                            <button type="button" class="sptjm-icon-btn sptjm-btn-edit btn-edit" 
                                data-id="{{ $pejabat->id }}"
                                data-urutan="{{ $pejabat->urutan }}"
                                data-nama="{{ $pejabat->nama }}"
                                data-nip="{{ $pejabat->nip }}"
                                data-jabatan="{{ $pejabat->jabatan }}"
                                title="Edit">
                                <i class="bx bx-edit-alt"></i>
                            </button>
                            
                            <form action="{{ route('admin.master-penandatangan.destroy', $pejabat->id) }}" method="POST" class="d-inline form-hapus">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="sptjm-icon-btn sptjm-btn-delete btn-hapus" title="Hapus">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambahPejabat" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Pejabat Penandatangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.master-penandatangan.store') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Pejabat Ke (Urutan)</label>
                <input type="number" class="form-control" name="urutan" required>
                <small class="text-muted">Misal: 1 (KPA), 2 (Bendahara), 3 (PPK)</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Nama Penandatangan</label>
                <input type="text" class="form-control" name="nama" required>
            </div>
            <div class="mb-3">
                <label class="form-label">NIP Penandatangan</label>
                <input type="text" class="form-control" name="nip">
            </div>
            <div class="mb-3">
                <label class="form-label">Jabatan Penandatangan</label>
                <input type="text" class="form-control" name="jabatan" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEditPejabat" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Pejabat Penandatangan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="formEditPejabat" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Pejabat Ke (Urutan)</label>
                <input type="number" class="form-control" id="edit_urutan" name="urutan" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nama Penandatangan</label>
                <input type="text" class="form-control" id="edit_nama" name="nama" required>
            </div>
            <div class="mb-3">
                <label class="form-label">NIP Penandatangan</label>
                <input type="text" class="form-control" id="edit_nip" name="nip">
            </div>
            <div class="mb-3">
                <label class="form-label">Jabatan Penandatangan</label>
                <input type="text" class="form-control" id="edit_jabatan" name="jabatan" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
  $(document).ready(function() {
    $('#tablePejabat').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        }
    });

    // SweetAlert untuk Notifikasi Sukses
    @if(session('success'))
    Swal.fire({
      title: 'Berhasil!',
      text: "{{ session('success') }}",
      icon: 'success',
      customClass: { confirmButton: 'btn btn-primary' },
      buttonsStyling: false
    });
    @endif

    @if($errors->any())
    Swal.fire({
      title: 'Gagal!',
      html: `{!! implode('<br>', $errors->all()) !!}`,
      icon: 'error',
      customClass: { confirmButton: 'btn btn-danger' },
      buttonsStyling: false
    });
    @endif

    // Modal Edit
    $('.btn-edit').on('click', function() {
        var id = $(this).data('id');
        $('#edit_urutan').val($(this).data('urutan'));
        $('#edit_nama').val($(this).data('nama'));
        $('#edit_nip').val($(this).data('nip'));
        $('#edit_jabatan').val($(this).data('jabatan'));
        
        var url = "{{ route('admin.master-penandatangan.update', ':id') }}";
        url = url.replace(':id', id);
        $('#formEditPejabat').attr('action', url);
        
        $('#modalEditPejabat').modal('show');
    });

    // Konfirmasi Hapus
    $('.btn-hapus').on('click', function(e) {
        var form = $(this).closest('form');
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data pejabat akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-danger me-2',
                cancelButton: 'btn btn-outline-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    // Toggle Status
    $('.toggle-status').on('change', function() {
        var id = $(this).data('id');
        var isChecked = $(this).is(':checked');
        var url = "{{ route('admin.master-penandatangan.toggle-status', ':id') }}".replace(':id', id);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    var badge = $('.status-badge-' + id);
                    if (response.is_aktif) {
                        badge.removeClass('bg-label-danger').addClass('bg-label-primary').text('Aktif');
                    } else {
                        badge.removeClass('bg-label-primary').addClass('bg-label-danger').text('Tidak Aktif');
                    }
                    
                    Swal.fire({
                        toast: true,
                        position: 'bottom-end',
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            },
            error: function() {
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'error',
                    title: 'Gagal mengubah status',
                    showConfirmButton: false,
                    timer: 2000
                });
                // Revert switch state
                $('.toggle-status[data-id="'+id+'"]').prop('checked', !isChecked);
            }
        });
    });
  });
</script>
@endsection
