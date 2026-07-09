@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('content')

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Master Data Kop Surat</h5>
  <hr>
  <div class="card-body">
    <form action="{{ route('admin.master-kop-surat.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <div class="col-md-12">
                <h6 class="fw-bold mb-3">Pengaturan Kop Surat</h6>
                
                <div class="mb-3">
                    <label class="form-label">Background Kop Surat PDF (Wajib)</label><br>
                    @if(isset($data) && $data->file_pdf_url)
                        <div class="alert alert-info">
                            <i class="bx bxs-file-pdf"></i> File PDF Kop Surat aktif: <a href="{{ asset($data->file_pdf_url) }}" target="_blank">Lihat PDF</a>
                        </div>
                    @endif
                    <input type="file" class="form-control" name="file_pdf" accept="application/pdf">
                    <small class="text-muted">Gunakan template PDF berukuran A4 (Maks 5MB).</small>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary" id="btnSimpan">
                <i class="bx bx-save me-1"></i> Simpan Pengaturan
            </button>
        </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert untuk Notifikasi Sukses
    @if(session('success'))
    Swal.fire({
      title: 'Berhasil!',
      text: "{{ session('success') }}",
      icon: 'success',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
    @endif

    @if($errors->any())
    Swal.fire({
      title: 'Gagal!',
      html: `{!! implode('<br>', $errors->all()) !!}`,
      icon: 'error',
      customClass: {
        confirmButton: 'btn btn-danger'
      },
      buttonsStyling: false
    });
    @endif

    document.getElementById('btnSimpan').addEventListener('click', function(e) {
        Swal.fire({
            title: 'Mohon tunggu...',
            html: `
                <div class="d-flex justify-content-center align-items-center flex-column">
                    <div class="spinner-border spinner-border-lg text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2">Sedang menyimpan data!</div>
                </div>
            `,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            backdrop: true
        });
    });
  });
</script>
@endsection
