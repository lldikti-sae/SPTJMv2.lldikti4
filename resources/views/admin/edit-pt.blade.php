@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card">
  <div class="card-body">
    <form action="{{ route('admin.daftar-pt.update', $data_pts->id) }}" method="POST" enctype="multipart/form-data"
      id="editDaftarPTForm">
      @csrf
      @method('PUT')
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Kode PT</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" name="kode_pts" value="{{ $data_pts->kode_pts }}"
            style="background-color: #eceef1;" readonly>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Nama PT</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" name="nama_pts" value="{{ $data_pts->nama_pts }}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Nama Pimpinan</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" name="nama_pimpinan" value="{{ $data_pts->nama_pimpinan }}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Jabatan Pimpinan</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" name="jabatan_pimpinan"
            value="{{ $data_pts->jabatan_pimpinan }}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Alamat PT</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" name="alamat_pt" value="{{ $data_pts->alamat_pt }}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Wilayah</label>
        <div class="col-sm-10">
          @if(Auth::check() && Auth::user()->role === 'pic')
              <input type="text" class="form-control" name="wilayah" value="{{ Auth::user()->email }}" readonly style="background-color: #eceef1;">
          @else
              <select class="form-select" name="wilayah">
                @foreach ($users as $user)
                <option value="{{ $user }}" {{ $data_pts->wilayah == $user ? 'selected' : '' }}>
                  {{ $user }}
                </option>
                @endforeach
              </select>
          @endif
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Password</label>
        <div class="col-sm-10 position-relative">
          <input type="password" id="password" class="form-control" name="password"
            value="{{ $data_pts->password }}">
          <!-- Icon mata -->
          <span class="position-absolute top-50 end-0 translate-middle-y pe-4" style="cursor: pointer;"
            id="clickIcon">
            <i class="bx bx-show" id="toggleIcon"></i>
          </span>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Dokumen</label>
        <div class="col-sm-10">
          <input type="file" class="form-control" name="dokumen" required>
          @if ($data_pts->dokumen)
          <small class="form-text text-muted">
            Dokumen saat ini: {{ $data_pts->dokumen }}
          </small>
          <div>
            <small class="form-text text-muted">
              Tanggal terakhir update: {{ $data_pts->tanggal_update }}
            </small>
          </div>
          <a href="{{ asset('dokumen/' . $data_pts->dokumen) }}" target="_blank">
            <i class="bx bx-file"></i> Lihat Dokumen</a><br>
          @endif
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-2 col-form-label">Status</label>
        <div class="col-sm-10">
          <select class="form-select" name="aktif">
            <option value="1" {{ $data_pts->aktif == 1 ? 'selected' : '' }}>Aktif</option>
            <option value="0" {{ $data_pts->aktif == 0 ? 'selected' : '' }}>Tidak Aktif</option>
          </select>
        </div>
      </div>
      <div class="row justify-content-end mt-3">
        <div class="col-sm-10">
          <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-success mx-2">Simpan</button>
            <button type="button" class="btn btn-secondary mx-2"
              onclick="window.location.href='{{ route('admin.daftar-pt') }}';">Batal</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    //show & hide password
    $('#clickIcon').on('click', () => {
      const passwordInput = $('#password');
      const icon = $('#toggleIcon');

      if (passwordInput.attr('type') === "password") {
        passwordInput.attr('type', 'text');
        icon.removeClass("bx-show").addClass("bx-hide");
      } else {
        passwordInput.attr('type', 'password');
        icon.removeClass("bx-hide").addClass("bx-show");
      }
    });

    @if(Session::has('edit-success'))
    Swal.fire({
      title: 'Berhasil!',
      text: "{{ Session::get('edit-success') }}",
      icon: 'success',
      customClass: {
        confirmButton: 'btn btn-primary'
      },
      buttonsStyling: false
    });
    @endif

    const loadingAlert = () => {
      return Swal.fire({
        title: 'Mohon tunggu...',
        html: `
                        <div class="d-flex justify-content-center align-items-center flex-column">
                            <div class="spinner-border spinner-border-lg text-success" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Mohon tunggu <br> Sedang meyimpan data!</div>
                        </div>
                    `,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        backdrop: true
      });
    }
    const EditPtForm = $('#editDaftarPTForm').on("submit", () => {
      loadingAlert()
    })
  });
</script>
@endsection