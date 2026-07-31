@extends('layouts/contentNavbarLayout')

@section('title', 'Koreksi Data - SPTJM Online')

@section('page-style')
<style>
.md2-page-header { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px; }

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
        <h1>Koreksi Data</h1>
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

  <form id="formSearchKoreksi" action="{{ route('admin.koreksi.cari') }}" method="POST" autocomplete="off">
    @csrf
    <div class="row mb-3 mx-2 align-items-end">
      <div class="col-md-6">
        <label class="form-label"><b style="font-size: 12px;">NIDN / NUPTK</b></label>
        <input type="text" class="form-control" name="nidn" id="inputNidnSearch" value="{{ old('nidn', $nidn ?? '') }}" placeholder="Masukkan NIDN/NUPTK" required />
      </div>
      <div class="col-md-4">
        <label class="form-label"><b style="font-size: 12px;">Bulan</b></label>
        <select class="form-select" name="bulan" id="selectBulanSearch" required>
          @foreach ($months as $key => $label)
            <option value="{{ $key }}" {{ (string)($bulan ?? 1) === (string)$key ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">
          <span class="tf-icons bx bx-search"></span>&nbsp; Cari
        </button>
      </div>
    </div>
  </form>

  @if (!empty($result))
  <hr class="my-2">
  
  <div class="row mb-2 mx-2 mt-3">
    <label class="col-sm-2 col-form-label py-1" style="font-size:13px;font-weight:600;">NIDN - Nama</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" readonly style="background:#eceef1;font-size:14px;font-weight:600;" value="{{ $nidn }} - {{ $result->Nama }}">
    </div>
  </div>
  <div class="row mb-4 mx-2">
    <label class="col-sm-2 col-form-label py-1" style="font-size:13px;font-weight:600;">Pangkat Golongan</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" readonly style="background:#eceef1;font-size:14px;" value="{{ $result->GolSelected }} - {{ $result->TahunSelected }}">
    </div>
  </div>

  <style>.mp-tbl{font-size:12px;line-height:1.3}.mp-tbl th,.mp-tbl td{padding:5px!important;vertical-align:middle}</style>
  <div class="table-responsive text-nowrap mt-2 mx-2 mb-4" style="overflow:auto;">
    <table class="table table-bordered table-hover table-sm mp-tbl" style="width: 100%;">
      <thead class="table-light">
        <tr>
          <th class="text-center">Tahun</th>
          <th class="text-center">Bulan</th>
          <th class="text-center">Kode Usulan</th>
          <th class="text-center">Gol/MK</th>
          <th class="text-center">Gaji</th>
          <th class="text-center">Kotor TPD</th>
          <th class="text-center">Pajak TPD</th>
          <th class="text-center">Bersih TPD</th>
          <th class="text-center">No SP2D</th>
          <th class="text-center">Tgl SP2D</th>
          <th class="text-center">Selisih</th>
          <th class="text-center">Status</th>
          <th class="text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="text-center">{{ $tahun ?? session('tahun') ?? '-' }}</td>
          <td class="text-center">{{ $months[$bulan] ?? $bulan }}</td>
          <td class="text-center">{{ $result->kode_usulan ?? '-' }}</td>
          <td class="text-center">{{ $result->GolSelected ?? '-' }} - {{ $result->TahunSelected ?? '-' }}</td>
          <td class="text-end">{{ number_format($result->gaji ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($result->tpd ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($result->pajak_tpd ?? 0, 0, ',', '.') }}</td>
          <td class="text-end">{{ number_format($result->bersih_tpd ?? 0, 0, ',', '.') }}</td>
          <td class="text-center">{{ $result->no_sp2d ?? '-' }}</td>
          <td class="text-center">{{ $result->tgl_sp2d ?? '-' }}</td>
          <td class="text-end text-success fw-bold">{{ number_format($result->tpd_sel ?? 0, 0, ',', '.') }}</td>
          <td class="text-center">
            @php
              $st = $result->status ?? null;
              $statusMap = [
                'usulan'=>['bg-label-warning','Usulan'],
                'proses'=>['bg-label-info','Proses'],
                'kurang'=>['bg-label-danger','Kurang'],
                'lebih'=>['bg-label-secondary','Lebih'],
                'selesai'=>['bg-label-success','Selesai']
              ];
            @endphp
            @if($st)
              @if(str_starts_with($st, 'kode:'))
                <span class="badge bg-label-primary" style="font-size: 10px;">{{ substr($st, 5) }}</span>
              @else
                <span class="badge {{ $statusMap[$st][0] ?? 'bg-label-primary' }}" style="font-size: 10px;">{{ $statusMap[$st][1] ?? $st }}</span>
              @endif
            @else
              -
            @endif
          </td>
          <td class="text-center">
            <button type="button" class="sptjm-icon-btn sptjm-btn-edit" data-bs-toggle="modal" data-bs-target="#editModal" title="Edit">
              <i class="bx bx-pencil"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Modal Edit -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="dataForm">
          @csrf
          <input type="hidden" name="nidn" id="nidn" value="{{ $nidn }}">
          <input type="hidden" name="bulan" id="bulan" value="{{ $bulan }}">
          <input type="hidden" name="tahun" id="tahun" value="{{ $tahun ?? session('tahun') }}">

          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel">Koreksi Data (Bulan: {{ $months[$bulan] ?? $bulan }})</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="row mb-3">
                <div class="col-sm-3">
                  <label class="form-label" style="font-size: 11px; font-weight: 700;">KODE USULAN</label>
                  <select name="kodeusulan" id="kodeusulan" class="form-select form-select-sm">
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
                  <label class="form-label" style="font-size: 11px; font-weight: 700;">GAJI</label>
                  <input type="text" class="form-control form-control-sm" id="gaji" name="gaji" value="{{ $result->gaji }}" />
                </div>
                <div class="col-sm-3">
                  <label class="form-label" style="font-size: 11px; font-weight: 700;">NO SP2D</label>
                  <input type="text" class="form-control form-control-sm" id="nosp2d" name="nosp2d" value="{{ $result->no_sp2d ?? '' }}" />
                </div>
                <div class="col-sm-3">
                  <label class="form-label" style="font-size: 11px; font-weight: 700;">TGL SP2D</label>
                  <input type="date" class="form-control form-control-sm" id="tglsp2d" name="tglsp2d" value="{{ $result->tgl_sp2d ?? '' }}" />
                </div>
              </div>

              <div class="row">
                <div class="col-sm-3">
                  <label class="form-label" style="font-size: 11px; font-weight: 700;">KOTOR TPD</label>
                  <input type="text" class="form-control form-control-sm" id="tpd" name="tpd" value="{{ $result->tpd }}" />
                </div>
                <div class="col-sm-3">
                  <label class="form-label" style="font-size: 11px; font-weight: 700;">PAJAK TPD</label>
                  <input type="text" class="form-control form-control-sm" id="pajak_tpd" name="pajak_tpd" value="{{ $result->pajak_tpd ?? '' }}" />
                </div>
                <div class="col-sm-3">
                  <label class="form-label" style="font-size: 11px; font-weight: 700;">BERSIH TPD</label>
                  <input type="text" class="form-control form-control-sm" id="bersih_tpd" name="bersih_tpd" value="{{ $result->bersih_tpd ?? '' }}" />
                </div>
                <div class="col-sm-3">
                  <label class="form-label" style="font-size: 11px; font-weight: 700;">SELISIH</label>
                  <input type="text" class="form-control form-control-sm" id="selisih" name="selisih" value="{{ $result->tpd_sel ?? '' }}" />
                </div>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="button" class="btn btn-primary" id="saveButton">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
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
    const formSearch = document.getElementById('formSearchKoreksi');
    const selectBulan = document.getElementById('selectBulanSearch');
    const inputNidn = document.getElementById('inputNidnSearch');

    function autoSubmitFilter() {
      if (inputNidn && inputNidn.value.trim() !== '') {
        if (formSearch) formSearch.submit();
      }
    }

    if (selectBulan) {
      selectBulan.addEventListener('change', autoSubmitFilter);
    }

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

    const inputsToValidate = ['gaji', 'tpd', 'tkgb', 'pajak_tpd', 'bersih_tpd', 'selisih'];
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
