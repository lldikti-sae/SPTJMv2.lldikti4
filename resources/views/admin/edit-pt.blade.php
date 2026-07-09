@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Perguruan Tinggi - SPTJM Online')

@section('page-style')
<style>
/* ── Page Header ── */
.pt-page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 24px;
}
.pt-page-header .page-titles h4 {
    font-size: 1.35rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0 0 4px 0;
}
.pt-page-header .breadcrumb {
    margin: 0;
    font-size: 0.8rem;
    background: none;
    padding: 0;
}
.pt-page-header .breadcrumb-item a { color: #696cff; text-decoration: none; }
.pt-page-header .breadcrumb-item.active { color: #8592a3; }
.pt-page-header .breadcrumb-item + .breadcrumb-item::before { color: #8592a3; }

/* ── Card ── */
.edit-pt-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(44,62,80,0.07);
    overflow: hidden;
    max-width: 820px;
}
.edit-pt-card .card-header-custom {
    padding: 16px 24px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 10px;
}
.edit-pt-card .card-header-custom .icon-wrap {
    width: 36px; height: 36px;
    border-radius: 8px;
    background: rgba(105,108,255,0.12);
    display: flex; align-items: center; justify-content: center;
    color: #696cff;
    font-size: 1.1rem;
}
.edit-pt-card .card-header-custom h5 {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 700;
    color: #2c3e50;
}
.edit-pt-card .card-body-custom {
    padding: 24px;
}

/* ── Form Label ── */
.edit-pt-card .form-label-custom {
    font-size: 0.82rem;
    font-weight: 600;
    color: #5c6877;
    margin-bottom: 6px;
    display: block;
}
.edit-pt-card .form-control,
.edit-pt-card .form-select {
    font-size: 0.85rem;
    border-color: #d9dee3;
    border-radius: 6px;
    padding: 8px 12px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.edit-pt-card .form-control:focus,
.edit-pt-card .form-select:focus {
    border-color: #696cff;
    box-shadow: 0 0 0 3px rgba(105,108,255,0.15);
}
.edit-pt-card .form-control[readonly] {
    background-color: #f5f7fa;
    color: #8592a3;
}

/* ── Divider Row Label ── */
.edit-pt-card .row-divider {
    border-top: 1px solid #f0f2f5;
    padding-top: 18px;
    margin-top: 4px;
}

/* ── Buttons ── */
.btn-save-pt {
    background: #28c76f;
    border: none;
    color: #fff;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 9px 24px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s, box-shadow 0.2s;
}
.btn-save-pt:hover {
    background: #22a55e;
    color: #fff;
    box-shadow: 0 4px 12px rgba(40,199,111,0.3);
}
.btn-cancel-pt {
    background: #f8f9fa;
    border: 1px solid #d9dee3;
    color: #5c6877;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 9px 24px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s;
}
.btn-cancel-pt:hover {
    background: #e9ecef;
    color: #2c3e50;
}

/* ── Password toggle ── */
.password-wrap { position: relative; }
.password-wrap .toggle-pw {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #8592a3;
    font-size: 1rem;
}
</style>
@endsection

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- ── Page Header ── --}}
<div class="pt-page-header">
    <div class="page-titles">
        <h4>Edit Perguruan Tinggi</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Master Data</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.daftar-pt') }}">Perguruan Tinggi</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

{{-- ── Card ── --}}
<div class="edit-pt-card">
    <div class="card-header-custom">
        <div class="icon-wrap"><i class="bx bx-edit"></i></div>
        <h5>Form Edit Perguruan Tinggi</h5>
    </div>
    <div class="card-body-custom">
        <form action="{{ route('admin.daftar-pt.update', $data_pts->id) }}" method="POST"
            enctype="multipart/form-data" id="editDaftarPTForm">
            @csrf
            @method('PUT')

            <div class="row g-4">

                {{-- Kode PT --}}
                <div class="col-12 col-md-6">
                    <label class="form-label-custom">Kode PT</label>
                    <input type="text" class="form-control" name="kode_pts"
                        value="{{ $data_pts->kode_pts }}" readonly>
                    <small class="text-muted" style="font-size:0.75rem;">Kode PT tidak dapat diubah</small>
                </div>

                {{-- Status --}}
                <div class="col-12 col-md-6">
                    <label class="form-label-custom">Status</label>
                    <select class="form-select" name="aktif">
                        <option value="1" {{ $data_pts->aktif == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ $data_pts->aktif == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                {{-- Nama PT --}}
                <div class="col-12">
                    <label class="form-label-custom">Nama Perguruan Tinggi</label>
                    <input type="text" class="form-control" name="nama_pts"
                        value="{{ $data_pts->nama_pts }}" placeholder="Nama PT">
                </div>

                {{-- Nama Pimpinan --}}
                <div class="col-12 col-md-6">
                    <label class="form-label-custom">Nama Pimpinan</label>
                    <input type="text" class="form-control" name="nama_pimpinan"
                        value="{{ $data_pts->nama_pimpinan }}" placeholder="Nama Pimpinan">
                </div>

                {{-- Jabatan Pimpinan --}}
                <div class="col-12 col-md-6">
                    <label class="form-label-custom">Jabatan Pimpinan</label>
                    <input type="text" class="form-control" name="jabatan_pimpinan"
                        value="{{ $data_pts->jabatan_pimpinan }}" placeholder="Jabatan Pimpinan">
                </div>

                {{-- Alamat --}}
                <div class="col-12">
                    <label class="form-label-custom">Alamat Perguruan Tinggi</label>
                    <input type="text" class="form-control" name="alamat_pt"
                        value="{{ $data_pts->alamat_pt }}" placeholder="Alamat PT">
                </div>

                {{-- Wilayah --}}
                <div class="col-12 col-md-6">
                    <label class="form-label-custom">Wilayah</label>
                    @if(Auth::check() && Auth::user()->role === 'pic')
                        <input type="text" class="form-control" name="wilayah" value="{{ Auth::user()->email }}" readonly style="background-color: #f5f7fa;">
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

                {{-- Password --}}
                <div class="col-12 col-md-6">
                    <label class="form-label-custom">Password</label>
                    <div class="password-wrap">
                        <input type="password" id="password" class="form-control" name="password"
                            value="{{ $data_pts->password }}" style="padding-right: 40px;">
                        <span class="toggle-pw" id="clickIcon">
                            <i class="bx bx-show" id="toggleIcon"></i>
                        </span>
                    </div>
                </div>

                {{-- Dokumen --}}
                <div class="col-12">
                    <label class="form-label-custom">Upload Dokumen</label>
                    <input type="file" class="form-control" name="dokumen">
                    @if ($data_pts->dokumen)
                    <div class="mt-2" style="font-size:0.78rem; color:#8592a3;">
                        <span>Dokumen saat ini: <strong>{{ $data_pts->dokumen }}</strong></span>
                        @if($data_pts->tanggal_update)
                        &nbsp;|&nbsp;<span>Update: {{ $data_pts->tanggal_update }}</span>
                        @endif
                        &nbsp;&nbsp;
                        <a href="{{ asset('dokumen/' . $data_pts->dokumen) }}" target="_blank"
                            style="color:#696cff; text-decoration:none;">
                            <i class="bx bx-file"></i> Lihat Dokumen
                        </a>
                    </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="col-12 row-divider">
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn-cancel-pt"
                            onclick="window.location.href='{{ route('admin.daftar-pt') }}'">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </button>
                        <button type="submit" class="btn-save-pt">
                            <i class="bx bx-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Toggle password
    document.getElementById('clickIcon').addEventListener('click', () => {
        const pw   = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        const isHidden = pw.type === 'password';
        pw.type = isHidden ? 'text' : 'password';
        icon.classList.toggle('bx-show', !isHidden);
        icon.classList.toggle('bx-hide', isHidden);
    });

    @if(Session::has('edit-success'))
    Swal.fire({
        title: 'Berhasil!',
        text: "{{ Session::get('edit-success') }}",
        icon: 'success',
        customClass: { confirmButton: 'btn btn-primary' },
        buttonsStyling: false
    });
    @endif

    // Loading saat submit
    document.getElementById('editDaftarPTForm').addEventListener('submit', () => {
        Swal.fire({
            title: 'Mohon tunggu...',
            html: `<div class="d-flex justify-content-center align-items-center flex-column">
                       <div class="spinner-border text-success" role="status"></div>
                       <div class="mt-2">Sedang menyimpan data!</div>
                   </div>`,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false
        });
    });
});
</script>
@endsection