@extends('layouts/contentNavbarLayout')

@section('title', 'Koreksi Data - SPTJM Online')

@section('page-style')
<style>
.md2-page-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
.md2-page-header .page-titles h4 { font-size:1.35rem; font-weight:700; color:#2c3e50; margin:0 0 4px; }
.md2-page-header .breadcrumb { margin:0; font-size:0.8rem; background:none; padding:0; }
.md2-page-header .breadcrumb-item a { color:#696cff; text-decoration:none; }
.md2-page-header .breadcrumb-item.active { color:#8592a3; }
.md2-page-header .breadcrumb-item+.breadcrumb-item::before { color:#8592a3; }
</style>
@endsection

@section('content')
@php
  $months = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
  ];
@endphp

<div class="md2-page-header">
    <div class="page-titles">
        <h4>Koreksi Data</h4>
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Monitoring</a></li>
            <li class="breadcrumb-item active">Koreksi Data</li>
        </ol></nav>
    </div>
</div>

<div class="card" style="width: 100%; padding: 20px 10px 10px;">

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <form action="{{ route('admin.koreksi.cari') }}" method="POST" autocomplete="off">
    @csrf
    <div class="row mb-3 mx-2">
      <div class="col-sm-3">
        <label class="col-form-label"><b style="font-size: 12px;">NIDN / NUPTK</b></label>
        <input type="text" class="form-control" name="nidn" value="{{ old('nidn', $nidn ?? '') }}" placeholder="Masukkan NIDN/NUPTK" />
      </div>
      <div class="col-sm-3">
        <label class="col-form-label"><b style="font-size: 12px;">Bulan</b></label>
        <select class="form-control" name="bulan">
          @foreach ($months as $key => $label)
            <option value="{{ $key }}" {{ (string)($bulan ?? 1) === (string)$key ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-sm-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary">
          <span class="tf-icons bx bx-search"></span>&nbsp; Cari
        </button>
      </div>
    </div>
  </form>

  @if (!empty($result))
    <div class="px-3">
      <h6>Nama : {{ $result->Nama }}</h6>
      <h6>Pangkat Golongan: {{ $result->GolSelected }} - {{ $result->TahunSelected }}</h6>
      <hr>

      <form id="dataForm">
        @csrf
        <input type="hidden" name="nidn" id="nidn" value="{{ $nidn }}">
        <input type="hidden" name="bulan" id="bulan" value="{{ $bulan }}">

        <div class="row mb-3">
          <div class="col-sm-3">
            <label class="col-form-label"><strong style="font-size: 10px;">Gaji</strong></label>
            <input type="text" class="form-control" id="gaji" name="gaji" value="{{ $result->gaji }}" readonly />
          </div>
          <div class="col-sm-3">
            <label class="col-form-label"><strong style="font-size: 10px;">Kode Usulan</strong></label>
            <select name="kodeusulan" id="kodeusulan" class="form-control">
              @if (!empty($result->kode_usulan))
                <option value="{{ $result->kode_usulan }}">{{ $result->kode_usulan }}</option>
              @endif
              @foreach ($statusPerubahan as $status)
                @if($status !== ($result->kode_usulan ?? ''))
                  <option value="{{ $status }}">{{ $status }}</option>
                @endif
              @endforeach
            </select>
          </div>
          <div class="col-sm-3">
            <label class="col-form-label"><strong style="font-size: 10px;">Kode Cair</strong></label>
            <input type="text" class="form-control" id="kodecair" name="kodecair" value="{{ $result->kode_cair }}" />
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-sm-3">
            <label class="col-form-label"><strong style="font-size: 10px;">TPD</strong></label>
            <input type="number" class="form-control" id="tpd" name="tpd" value="{{ $result->tpd }}" />
          </div>
          <div class="col-sm-3">
            <label class="col-form-label"><strong style="font-size: 10px;">TKGB</strong></label>
            <input type="number" class="form-control" id="tkgb" name="tkgb" value="{{ $result->tkgb }}" />
          </div>
          <!--
          <div class="col-sm-3">
            <label class="col-form-label"><strong style="font-size: 10px;">Selisih TPD</strong></label>
            <input type="number" class="form-control" id="tpd_sel" name="tpd_sel" value="{{ $result->tpd_sel ?? '' }}" />
          </div>
          <div class="col-sm-3">
            <label class="col-form-label"><strong style="font-size: 10px;">Selisih TKGB</strong></label>
            <input type="number" class="form-control" id="tkgb_sel" name="tkgb_sel" value="{{ $result->tkgb_sel ?? '' }}" />
          </div>
          -->
        </div>

        <div class="d-flex justify-content-center">
          <button type="button" class="btn btn-success" id="saveButton">Simpan</button>
        </div>
      </form>
    </div>

    <!-- Modal Password -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true" autocomplete="off">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="passwordModalLabel">Konfirmasi Password</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="passwordInput" class="form-label">Masukkan Password</label>
              <input type="password" class="form-control" id="passwordInput" required>
            </div>
            <div id="errorMessage" class="text-danger" style="display: none;">Password tidak valid!</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-primary" id="confirmPasswordButton">Konfirmasi</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Success -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="successModalLabel">Berhasil</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Data berhasil disimpan.
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="successOkButton">OK</button>
          </div>
        </div>
      </div>
    </div>
  @endif
</div>

@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const saveButton = document.getElementById('saveButton');
    if (!saveButton) return;

    const confirmPasswordButton = document.getElementById('confirmPasswordButton');
    const passwordModalElement = document.getElementById('passwordModal');
    const passwordModal = new bootstrap.Modal(passwordModalElement);
    const errorMessage = document.getElementById('errorMessage');
    const passwordInput = document.getElementById('passwordInput');
  const form = document.getElementById('dataForm');
  const successModalElement = document.getElementById('successModal');
  const successModal = successModalElement ? new bootstrap.Modal(successModalElement) : null;
  const successOkButton = document.getElementById('successOkButton');

    passwordModalElement.addEventListener('hidden.bs.modal', function() {
      document.body.classList.remove('modal-open');
      const backdrop = document.querySelector('.modal-backdrop');
      if (backdrop) backdrop.remove();
    });

    saveButton.addEventListener('click', function(event) {
      event.preventDefault();
      errorMessage.style.display = 'none';
      errorMessage.textContent = 'Password tidak valid!';
      passwordInput.value = '';
      passwordModal.show();
    });

    confirmPasswordButton.addEventListener('click', function() {
      const password = passwordInput.value;
      if (!password) {
        errorMessage.textContent = 'Password tidak boleh kosong!';
        errorMessage.style.display = 'block';
        return;
      }

      const formData = new FormData(form);
      const data = {};
      formData.forEach((value, key) => { data[key] = value; });
      data.password = password;
      // Step 1: verify password first
      fetch("{{ route('admin.password-verifakan') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ password })
      }).then(async (resp) => {
        const res = await resp.json().catch(() => ({}));
        if (!resp.ok || !res.success) {
          throw new Error(res.message || 'Password salah.');
        }
        // Step 2: proceed to update
        return fetch("{{ route('admin.koreksi.verifikasi') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(data)
        });
      }).then(async (response) => {
        const resJson = await response.json().catch(() => ({}));
        if (!response.ok || !resJson.success) {
          throw new Error(resJson.message || 'Terjadi masalah pada server.');
        }
        return resJson;
      }).then((data) => {
        passwordModal.hide();
        if (data.redirect) {
          if (successModal) {
            const go = () => { window.location.href = data.redirect; };
            if (successOkButton) {
              successOkButton.addEventListener('click', go, { once: true });
            }
            successModalElement.addEventListener('hidden.bs.modal', go, { once: true });
            successModal.show();
            // Auto redirect after short delay as fallback
            setTimeout(go, 1500);
          } else {
            window.location.href = data.redirect;
          }
        }
      }).catch((err) => {
        errorMessage.textContent = err.message || 'Terjadi masalah';
        errorMessage.style.display = 'block';
      });
    });

    const inputsToValidate = ['gaji', 'tpd', 'tkgb'];
    inputsToValidate.forEach((id) => {
      const el = document.getElementById(id);
      if (el) {
        el.addEventListener('input', function() {
          this.value = this.value.replace(/[^0-9]/g, '');
        });
      }
    });
  });
</script>
@endsection
