@extends('layouts/contentNavbarLayout')

@section('title', 'Background Login')

@section('content')
    {{-- Page Header --}}
    <div class="md-page-header mb-4">
        <div class="page-titles">
            <h4 class="fw-bold mb-1" style="color: #0f2b5c;">Background Login</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Pengaturan</a></li>
                    <li class="breadcrumb-item active">Background Login</li>
                </ol>
            </nav>
        </div>
    </div>

    {{-- Flash messages will be shown using SptjmAlert modal --}}

    {{-- Main Unified Card --}}
    <div class="md-card">
        <div class="md-card-inner">
            <h5 class="mb-4">Upload Background</h5>
            <form action="{{ route('admin.background.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="text-uppercase fw-semibold text-secondary mb-1" style="font-size: 0.75rem;" for="image">Pilih Gambar (maks 5MB)</label>
                        <input class="form-control" type="file" id="image" name="image" accept="image/*" required>
                    </div>
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary rounded-pill d-inline-flex align-items-center justify-content-center">
                            <i class="bx bx-upload me-1"></i> Upload
                        </button>
                    </div>
                </div>
            </form>

            <hr class="my-4">

            <h5 class="mb-4">Pengaturan Login</h5>
            <form id="settingsForm" action="{{ route('admin.background.settings') }}" method="POST">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="text-uppercase fw-semibold text-secondary mb-2 d-block" style="font-size: 0.75rem;">Header Login (Logo & Tulisan)</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="header_mode" id="header_default" value="default"
                                {{ ($headerMode ?? 'default') === 'default' ? 'checked' : '' }}>
                            <label class="form-check-label" for="header_default">Tampilkan seperti semula (logo + tulisan)</label>
                        </div>

                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="header_mode" id="header_hide" value="hide"
                                {{ ($headerMode ?? 'default') === 'hide' ? 'checked' : '' }}>
                            <label class="form-check-label" for="header_hide">Hide logo dan tulisan</label>
                        </div>

                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="header_mode" id="header_corner" value="corner"
                                {{ ($headerMode ?? 'default') === 'corner' ? 'checked' : '' }}>
                            <label class="form-check-label" for="header_corner">Pindahkan kedua logo ke pojok kiri atas & hide tulisan</label>
                        </div>
                    </div>
                </div>

                <label class="text-uppercase fw-semibold text-secondary mb-2 d-block" style="font-size: 0.75rem;">Background Login</label>

                <div class="md-table-wrap table-responsive text-nowrap">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 90px;">Preview</th>
                                <th>Nama File</th>
                                <th style="width: 140px;">Status</th>
                                <th style="width: 80px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <img class="img-thumbnail" src="{{ asset('background/' . ($defaultFilename ?? 'background_login.png')) }}" alt="Default background" style="width: 80px; height: 50px; object-fit: cover;">
                                </td>
                                <td>
                                    <div class="fw-semibold">Default</div>
                                    <div class="text-muted small">{{ $defaultFilename ?? 'background_login.png' }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="form-check m-0">
                                            <input class="form-check-input" type="radio" name="selected" value="default" id="bg_default"
                                                {{ ($selected ?? '') === ($defaultFilename ?? '') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="bg_default">Pilih</label>
                                        </div>
                                        @if (($selected ?? '') === ($defaultFilename ?? 'background_login.png'))
                                                <span class="badge rounded-pill bg-label-success">Dipakai</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted small">-</span>
                                </td>
                            </tr>

                            @foreach (($backgrounds ?? []) as $file)
                                <tr>
                                    <td>
                                        <img class="img-thumbnail" src="{{ asset('background/' . $file) }}" alt="{{ $file }}" style="width: 80px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>{{ $file }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="form-check m-0">
                                                <input class="form-check-input" type="radio" name="selected" value="{{ $file }}" id="bg_{{ md5($file) }}"
                                                    {{ ($selected ?? '') === $file ? 'checked' : '' }}>
                                                <label class="form-check-label" for="bg_{{ md5($file) }}">Pilih</label>
                                            </div>
                                            @if (($selected ?? '') === $file)
                                                    <span class="badge rounded-pill bg-label-success">Dipakai</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="sptjm-icon-btn sptjm-btn-delete js-delete-bg" title="Hapus"
                                            data-form="delete_bg_{{ md5($file) }}" data-file="{{ $file }}">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-start">
                    <button type="submit" form="settingsForm" class="btn btn-primary rounded-pill d-inline-flex align-items-center"><i class="bx bx-save me-1"></i> Simpan Pengaturan</button>
                </div>

            </form>

            @foreach (($backgrounds ?? []) as $file)
                <form id="delete_bg_{{ md5($file) }}" action="{{ route('admin.background.destroy', ['filename' => $file]) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        </div>
    </div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    try {
      if (window.SptjmAlert && typeof window.SptjmAlert.fromFlash === 'function') {
        window.SptjmAlert.fromFlash({
          success: @json(session('success')),
          error: @json(session('error')),
          warning: @json(session('warning')),
          info: @json(session('info')),
        });
      }

      const validationErrors = @json($errors->any() ? $errors->all() : []);
      if (validationErrors && validationErrors.length && window.SptjmAlert && typeof window.SptjmAlert.error === 'function') {
        const html = '<ul style="text-align:left;margin:0;padding-left:1.2rem;">' +
          validationErrors.map(e => '<li>' + String(e) + '</li>').join('') +
          '</ul>';
        window.SptjmAlert.error('Validasi Gagal', html);
      }

      document.querySelectorAll('.js-delete-bg').forEach(function (btn) {
        btn.addEventListener('click', async function () {
          const formId = btn.getAttribute('data-form');
          const file = btn.getAttribute('data-file') || '';
          const form = formId ? document.getElementById(formId) : null;
          if (!form) return;

          if (window.SptjmAlert && typeof window.SptjmAlert.question === 'function') {
            const r = await window.SptjmAlert.question('Konfirmasi', 'Hapus background ini?\n' + file);
            if (r && r.isConfirmed) form.submit();
          } else {
            if (confirm('Hapus background ini?\n' + file)) form.submit();
          }
        });
      });
    } catch (e) {
      // no-op
    }
  });
</script>
@endsection
