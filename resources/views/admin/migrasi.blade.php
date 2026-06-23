@extends('layouts/contentNavbarLayout')

@section('title', 'Migrasi Data')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-12 col-lg-12">
      {{-- Notifikasi umum --}}
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Log NIDN hasil koreksi --}}
      @php($updatedNidn = session('koreksiUpdatedNidn', []))
      @php($notUpdatedNidn = session('koreksiNotUpdatedNidn', []))
      @php($notFoundNidn = session('koreksiNotFoundNidn', []))
      @php($invalidCells = session('koreksiInvalidCells', []))
      @if(!empty($updatedNidn) || !empty($notUpdatedNidn) || !empty($notFoundNidn))
        <div class="card mb-3">
          <div class="card-header">
            <h5 class="mb-0">Log Koreksi NIDN</h5>
          </div>
          <div class="card-body">
            @if(!empty($updatedNidn))
              <p class="mb-1"><strong>NIDN yang datanya berhasil diperbarui ({{ count($updatedNidn) }}):</strong></p>
              <div class="mb-2" style="max-height:220px; overflow-y:auto;">
                @foreach($updatedNidn as $nidn)
                  <span class="badge bg-label-success text-dark me-1 mb-1">{{ $nidn }}</span>
                @endforeach
              </div>
            @endif
            @if(!empty($notUpdatedNidn))
              <p class="mb-1"><strong>NIDN yang ada di tabel tetapi tidak mengalami perubahan ({{ count($notUpdatedNidn) }}):</strong></p>
              <div class="mb-2" style="max-height: 200px; overflow-y: auto;">
                @foreach($notUpdatedNidn as $nidn)
                  <span class="badge bg-label-secondary text-dark me-1 mb-1">{{ $nidn }}</span>
                @endforeach
              </div>
            @endif
            @if(!empty($notFoundNidn))
              <p class="mb-1"><strong>NIDN yang tidak ditemukan di tabel ({{ count($notFoundNidn) }}):</strong></p>
              <div style="max-height:220px; overflow-y:auto;">
                @foreach($notFoundNidn as $nidn)
                  <span class="badge bg-label-warning text-dark me-1 mb-1">{{ $nidn }}</span>
                @endforeach
              </div>
            @endif

            @if(!empty($invalidCells))
              <hr>
              <p class="mb-1"><strong>Contoh nilai yang tidak valid / terpotong (maks 20):</strong></p>
              <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-sm table-striped mb-0">
                  <thead>
                    <tr>
                      <th style="width: 160px;">Key</th>
                      <th style="width: 140px;">Kolom</th>
                      <th>Nilai</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($invalidCells as $it)
                      <tr>
                        <td>{{ $it['key'] ?? '-' }}</td>
                        <td>{{ $it['column'] ?? '-' }}</td>
                        <td class="text-muted">{{ $it['value'] ?? '-' }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <div class="form-text mt-2">
                Nilai seperti <em>01/01/2019</em> pada kolom masa kerja (Tahun1..Tahun12) akan di-skip karena kolom tersebut hanya menerima angka.
              </div>
            @endif
          </div>
        </div>
      @endif

      {{-- Card Import Penuh --}}
      <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Upload CSV ke Database</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.migrasi.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label for="dataset" class="form-label">Pilih Dataset</label>
              <select class="form-select" id="dataset" name="dataset" required>
                @foreach($datasets as $key => $dataset)
                  <option value="{{ $key }}" {{ old('dataset', 's_transaksi_2') === $key ? 'selected' : '' }}>
                    {{ $dataset['label'] }} (tabel {{ $dataset['table'] }})
                  </option>
                @endforeach
              </select>
              <div class="form-text">
                Pilih tabel tujuan migrasi. Pastikan header CSV sesuai dengan kolom tabel.
              </div>
            </div>
            <div class="mb-2">
              @foreach($datasets as $key => $dataset)
                <div class="small text-muted dataset-hint" data-dataset="{{ $key }}" style="display: {{ old('dataset', 's_transaksi_2') === $key ? 'block' : 'none' }};">
                  Total kolom: {{ $dataset['expectedCount'] }} &middot; Nama tabel: {{ $dataset['table'] }}
                </div>
              @endforeach
            </div>
            <div class="mb-3">
              <label for="file" class="form-label">File CSV / Excel</label>
              <input type="file" class="form-control" id="file" name="file" accept=".csv,.xlsx,.xls" required>
              <div class="form-text">
                Pastikan baris pertama adalah header yang sesuai dengan kolom tabel.
                Format yang didukung: <strong>.csv</strong>, <strong>.xlsx</strong>, <strong>.xls</strong>.
                Jika memakai CSV, pemisah "," atau ";" didukung.
              </div>
              <div class="alert alert-info mt-2 small">
                <strong>Format yang didukung & tips:</strong>
                <ul class="mb-0 mt-1">
                  <li><strong>.csv</strong> — Disarankan menyimpan sebagai <em>CSV UTF-8 (Comma delimited)</em>; header harus persis sama dengan kolom tabel.</li>
                  <li><strong>.xlsx / .xls</strong> — Aplikasi akan membaca worksheet pertama; header harus persis sama seperti nama kolom di tabel.</li>
                  <li>Pemisah CSV yang didukung: koma (,) dan titik-koma (;). Nilai kosong akan dianggap <em>NULL</em>.</li>
                </ul>
              </div>
            </div>
            <button type="submit" class="btn btn-primary">Import</button>
          </form>
        </div>
      </div>
      {{-- Card Koreksi Data Berdasarkan NIDN --}}
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Koreksi Data</h5>
        </div>
        <div class="card-body">
          <form action="{{ route('admin.migrasi.koreksi') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label for="dataset_koreksi" class="form-label">Pilih Dataset</label>
              <select class="form-select" id="dataset_koreksi" name="dataset_koreksi" required>
                @foreach($datasets as $key => $dataset)
                  <option value="{{ $key }}" {{ old('dataset_koreksi', 's_transaksi_2') === $key ? 'selected' : '' }}>
                    {{ $dataset['label'] }} (tabel {{ $dataset['table'] }})
                  </option>
                @endforeach
              </select>
              <div class="form-text">
                File CSV koreksi minimal harus memiliki kolom <strong>NIDN/nidn</strong> (atau <strong>NUPTK/nuptk</strong> untuk s_transaksi_2) dan kolom lain yang ingin diperbarui.
                Hanya kolom yang dikenali di tabel yang akan di-update.
                Untuk nilai kolom: <strong>BLANK</strong> = tidak diubah, <strong>NULL</strong> = di-set menjadi NULL.
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">Kunci Koreksi</label>
              <select class="form-select" name="koreksi_key" id="koreksi_key">
                <!-- Options populated by JS -->
              </select>
              <div class="form-text small">Pilih kolom kunci yang akan digunakan untuk mencari baris pada tabel saat melakukan koreksi.</div>
            </div>
            <div class="mb-3">
              <label for="file_koreksi" class="form-label">File CSV Koreksi</label>
              <input type="file" class="form-control" id="file_koreksi" name="file" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-warning">Proses</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
{{-- Modal penolakan ketika header tidak sesuai --}}
@if(session('headerMismatch'))
<div class="modal fade" id="importRejectedModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Import Ditolak</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">Jumlah dan penamaan kolom tidak sesuai dengan tabel {{ session('datasetLabel') }}.</p>
        <p class="mb-2"><strong>Diharapkan:</strong> {{ session('expectedCount') }} kolom &nbsp; | &nbsp; <strong>Ditemukan:</strong> {{ session('foundCount') }} kolom</p>

        @php($missing = session('missingColumns', []))
        @php($extra = session('extraColumns', []))

        @if(!empty($missing))
          <div class="alert alert-warning">
            <strong>Kolom yang tidak ada di file (missing):</strong>
            <div class="mt-2">
              @foreach($missing as $col)
                <span class="badge bg-label-warning text-dark me-1 mb-1">{{ $col }}</span>
              @endforeach
            </div>
          </div>
        @endif

        @if(!empty($extra))
          <div class="alert alert-info">
            <strong>Kolom yang tidak dikenal (extra di file):</strong>
            <div class="mt-2">
              @foreach($extra as $col)
                <span class="badge bg-label-info text-dark me-1 mb-1">{{ $col }}</span>
              @endforeach
            </div>
          </div>
        @endif

        <p class="mb-1">Silakan sesuaikan file CSV Anda agar sama persis dengan kolom tabel.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endif

{{-- Modal progress import --}}
<div class="modal fade" id="importProgressModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Mengimpor Data</h5>
      </div>
      <div class="modal-body">
        <p class="mb-3">Proses import sedang berjalan. Mohon tunggu hingga selesai…</p>
        <div class="progress" role="progressbar" aria-label="Import progress" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
        </div>
      </div>
      <div class="modal-footer">
        <small class="text-muted">Jangan tutup halaman ini selama proses berlangsung.</small>
      </div>
    </div>
  </div>
 </div>

{{-- Modal progress koreksi --}}
<div class="modal fade" id="koreksiProgressModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Memproses Koreksi</h5>
      </div>
      <div class="modal-body">
        <p class="mb-3">Proses koreksi sedang berjalan. Mohon tunggu hingga selesai…</p>
        <div class="progress" role="progressbar" aria-label="Koreksi progress" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
          <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
        </div>
      </div>
      <div class="modal-footer">
        <small class="text-muted">Jangan tutup halaman ini selama proses berlangsung.</small>
      </div>
    </div>
  </div>
</div>

{{-- Modal password akses halaman --}}
<div class="modal fade" id="migrasiPasswordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Verifikasi Akses</h5>
      </div>
      <div class="modal-body">
        <p class="mb-3">Masukkan password untuk membuka halaman migrasi.</p>
        <div class="mb-3">
          <label for="migrasiPasswordInput" class="form-label">Password</label>
          <input type="password" class="form-control" id="migrasiPasswordInput" autocomplete="off" spellcheck="false">
          <div class="invalid-feedback" id="migrasiPasswordError">Password salah, coba lagi.</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" id="migrasiPasswordBack">Kembali</button>
        <button type="button" class="btn btn-primary" id="migrasiPasswordSubmit">Masuk</button>
      </div>
    </div>
  </div>
</div>

@section('page-script')
  @if(session('headerMismatch'))
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var el = document.getElementById('importRejectedModal');
      if (el) {
        if (window.bootstrap && bootstrap.Modal) {
          var modal = new bootstrap.Modal(el);
          modal.show();
        } else {
          // Fallback jika bootstrap tidak terload
          el.classList.add('show');
          el.style.display = 'block';
        }
      }
    });
  </script>
  @endif
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      // Password gate
      (function() {
        var modalEl = document.getElementById('migrasiPasswordModal');
        if (!modalEl) return;

        // Only show modal if server-side session hasn't unlocked migrasi
        var serverUnlocked = {{ session('migrasi_unlocked') ? 'true' : 'false' }};
        if (serverUnlocked) {
          // already unlocked for this session; do nothing
          return;
        }

        var input = document.getElementById('migrasiPasswordInput');
        var error = document.getElementById('migrasiPasswordError');
        var submitBtn = document.getElementById('migrasiPasswordSubmit');
        var hideError = function() {
          if (error) {
            error.style.display = 'none';
          }
          if (input) {
            input.classList.remove('is-invalid');
          }
        };
        var showError = function(msg) {
          if (error) {
            error.textContent = msg || 'Password salah, coba lagi.';
            error.style.display = 'block';
          }
          if (input) {
            input.classList.add('is-invalid');
            input.focus();
            input.select();
          }
        };

        var verify = function() {
          if (!input) return;
          var value = (input.value || '').trim();
          if (value === '') {
            showError('Masukkan password.');
            return;
          }

          // Call server to verify password and set session flag
          fetch("{{ route('admin.migrasi.unlock') }}", {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ password: value })
          }).then(function(res) {
            return res.json().then(function(json) { return { ok: res.ok, status: res.status, body: json }; });
          }).then(function(result) {
            if (result.ok && result.body && result.body.success) {
              hideError();
              if (window.bootstrap && bootstrap.Modal) {
                var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
              } else {
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
              }
              input.value = '';
            } else {
              var msg = (result.body && result.body.message) ? result.body.message : 'Password salah.';
              showError(msg);
            }
          }).catch(function(err) {
            showError('Gagal memverifikasi. Coba lagi.');
            console.error(err);
          });
        };

        if (window.bootstrap && bootstrap.Modal) {
          var modalInstance = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
          modalInstance.show();
        } else {
          modalEl.classList.add('show');
          modalEl.style.display = 'block';
        }

        if (input) {
          input.focus();
          input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
              e.preventDefault();
              verify();
            } else {
              hideError();
            }
          });
        }
        if (submitBtn) {
          submitBtn.addEventListener('click', function() {
            verify();
          });
        }
        var backBtn = document.getElementById('migrasiPasswordBack');
        if (backBtn) {
          backBtn.addEventListener('click', function() {
            if (document.referrer) {
              window.location.href = document.referrer;
            } else {
              window.history.back();
            }
          });
        }
      })();

      var form = document.querySelector('form[action="{{ route('admin.migrasi.import') }}"]');
      if (!form) return;
      var datasetSelect = document.getElementById('dataset');
      if (datasetSelect) {
        var hintBlocks = document.querySelectorAll('.dataset-hint');
        var refreshHint = function(selected) {
          hintBlocks.forEach(function(el) {
            if (el.getAttribute('data-dataset') === selected) {
              el.style.display = 'block';
            } else {
              el.style.display = 'none';
            }
          });
        };
        datasetSelect.addEventListener('change', function() {
          refreshHint(this.value);
        });
        refreshHint(datasetSelect.value);
      }
      form.addEventListener('submit', function () {
        // Disable submit button to prevent double submission
        var btn = form.querySelector('button[type="submit"]');
        if (btn) {
          btn.disabled = true;
          btn.innerHTML = 'Mengimpor...';
        }

        var el = document.getElementById('importProgressModal');
        if (el) {
          if (window.bootstrap && bootstrap.Modal) {
            var modal = new bootstrap.Modal(el, { backdrop: 'static', keyboard: false });
            modal.show();
          } else {
            // Fallback display if Bootstrap JS not loaded
            el.classList.add('show');
            el.style.display = 'block';
          }
        }
      });

      // Koreksi form: show loading modal and disable submit to prevent double submission
      var koreksiForm = document.querySelector('form[action="{{ route('admin.migrasi.koreksi') }}"]');
      if (koreksiForm) {
        koreksiForm.addEventListener('submit', function () {
          var kbtn = koreksiForm.querySelector('button[type="submit"]');
          if (kbtn) {
            kbtn.disabled = true;
            kbtn.innerHTML = 'Memproses...';
          }

          var kel = document.getElementById('koreksiProgressModal');
          if (kel) {
            if (window.bootstrap && bootstrap.Modal) {
              var kmodal = new bootstrap.Modal(kel, { backdrop: 'static', keyboard: false });
              kmodal.show();
            } else {
              kel.classList.add('show');
              kel.style.display = 'block';
            }
          }
        });
      }
      
      // Dynamic Koreksi Key options based on selected dataset
      var datasetKoreksi = document.getElementById('dataset_koreksi');
      var koreksiKey = document.getElementById('koreksi_key');
      var oldKoreksiKey = '{{ old("koreksi_key", "nidn") }}';
      
      if (datasetKoreksi && koreksiKey) {
        var updateKoreksiKeyOptions = function() {
          var dataset = datasetKoreksi.value;
          koreksiKey.innerHTML = ''; // clear options
          
          if (dataset === 'q_sptjm') {
            var opt = document.createElement('option');
            opt.value = 'id_usulan';
            opt.text = 'ID Usulan';
            opt.selected = true; // For q_sptjm, only id_usulan is valid as per request
            koreksiKey.appendChild(opt);
          } else { // s_transaksi_2
            var opt1 = document.createElement('option');
            opt1.value = 'nidn';
            opt1.text = 'NIDN (default)';
            opt1.selected = (oldKoreksiKey === 'nidn');
            koreksiKey.appendChild(opt1);
            
            var opt2 = document.createElement('option');
            opt2.value = 'nuptk';
            opt2.text = 'NUPTK';
            opt2.selected = (oldKoreksiKey === 'nuptk');
            koreksiKey.appendChild(opt2);
          }
        };
        
        datasetKoreksi.addEventListener('change', updateKoreksiKeyOptions);
        updateKoreksiKeyOptions(); // initial load
      }
    });
  </script>
@endsection
@endsection