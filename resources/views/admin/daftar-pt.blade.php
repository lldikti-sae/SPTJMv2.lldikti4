@extends('layouts/contentNavbarLayout')

@section('title', 'SPTJM Online')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card" style="width: 100%; padding: 10px;">
  <h5 class="card-header text-start p-2">Daftar Perguruan Tinggi</h5>
  <hr>
  <div class="table-responsive text-nowrap">
    <div class="d-flex justify-content-end align-items-center mb-3 px-3">
      {{-- <div class="input-group me-3" id="DataTables_Table_0_filter" style="max-width: 200px;">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input type="search" class="form-control" id="searchInput" placeholder="Search..."
                        aria-controls="DataTables_Table_0">
                </div> --}}
      <button class="btn btn-sm btn-warning me-2" id="addSyncPTBtn" data-bs-toggle="modal"
        data-bs-target="#modalSync">
        <i class="bx bx-sync bx-sm me-1"></i> Sync
      </button>
      <button class="btn btn-sm btn-primary" id="addPTBtn" data-bs-toggle="modal" data-bs-target="#modalPTForm">
        <i class="bx bx-plus bx-sm me-1"></i> Tambah
      </button>
    </div>

    <table class="table table-sm table-hover" id="ptsTable">
      <thead style="background-color: #dbdee0;">
        <tr>
          <th>Kode PT</th>
          <th>Nama PT</th>
          <th>Wilayah</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>

</div>

<!-- Modal -->
<div class="modal fade" id="modalPTForm" tabindex="-1" aria-labelledby="modalPTFormLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPTTitle">Tambah Perguruan Tinggi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="ptForm" method="POST" action="{{ route('admin/daftar-pt.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="col mb-3">
            <label for="kodePTS" class="form-label">Kode Perguruan Tinggi</label>
            <input type="text" id="kodePTS" class="form-control @error('kode_pts') is-invalid @enderror"
              name="kode_pts" placeholder="Masukkan Kode PT" required pattern="^[1-9][0-9]*$"
              title="Hanya angka, tidak boleh diawali 0 atau mengandung spasi">
            @error('kode_pts')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Nama Perguruan Tinggi</label>
            <input type="text" class="form-control" id="nama_pts" name="nama_pts"
              placeholder="Masukkan Nama PT" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Nama Pimpinan</label>
            <input type="text" class="form-control" id="nama_pimpinan" name="nama_pimpinan"
              placeholder="Masukkan Nama Pimpinan" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Jabatan Pimpinan</label>
            <input type="text" class="form-control" id="jabatan_pimpinan" name="jabatan_pimpinan"
              placeholder="Masukkan Nama Jabatan Pimpinan" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Alamat Perguruan Tinggi</label>
            <input type="text" class="form-control" id="alamat_pt" name="alamat_pt"
              placeholder="Masukkan Alamat PT" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Wilayah</label>
            @if(Auth::check() && Auth::user()->role === 'pic')
                <input type="text" class="form-control" name="wilayah" value="{{ Auth::user()->email }}" readonly style="background-color: #eceef1;">
            @else
                <select class="form-select" id="wilayah" name="wilayah" required>
                  <option value="">-- Pilih Wilayah --</option>
                  @foreach ($users as $user)
                  <option value="{{ $user->email }}">{{ $user->email }}</option>
                  @endforeach
                </select>
            @endif
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password"
              placeholder="Masukkan Password" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" id="aktif" name="aktif" required>
              <option value="1">Aktif</option>
              <option value="0">Tidak Aktif</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Upload Dokumen</label>
            <input type="file" class="form-control" id="dokumen" name="dokumen" accept=".pdf,.doc,.docx">
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

<!-- Modal Sync -->
<div class="modal fade" id="modalSync" tabindex="-1" aria-labelledby="modalSyncFormLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalSyncTitle">Sinkronisasi Perguruan Tinggi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="syncPtForm" method="POST" action="{{ route('admin.daftar-pt.updateWilayah') }}">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label for="">Kode Perguruan Tinggi</label>
            <select name="kode_pts" id="kode_pts_sync" class="form-control" required>
              <option value="" selected>---Pilih---</option>
              @foreach ($kode_pts as $kode)
              <option value="{{ $kode->kode_pts }}" data-nama="{{ $kode->nama_pts ?? '' }}">
                {{ $kode->kode_pts }}
              </option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="">Nama Perguruan Tinggi</label>
            <input type="text" readonly class="form-control" id="nama_pts_sync" name="nama_pts"
              placeholder="Masukkan Nama PT" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Pemegang Wilayah Baru</label>
            @if(Auth::check() && Auth::user()->role === 'pic')
                <input type="text" class="form-control" name="pemegang_wilayah_baru" value="{{ Auth::user()->email }}" readonly style="background-color: #eceef1;">
            @else
                <select name="pemegang_wilayah_baru" id="pemegang_wilayah_baru" class="form-control" required>
                  <option value="" selected>--PILIH--</option>
                  @foreach ($users as $user)
                  <option value="{{ $user->email }}">{{ $user->email }}</option>
                  @endforeach
                </select>
            @endif
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
    const alert = (text = "Data berhasil tersimpan!", title = "Berhasil", icon = "success", warnaBtn =
      "btn btn-primary") => {
      return Swal.fire({
        title,
        text: text,
        icon: icon,
        confirmButtonText: 'OK',
        timer: 1500,
        timerProgressBar: true,
        customClass: {
          confirmButton: warnaBtn
        },
        buttonsStyling: false
      })
    }

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

    // Reset Modal Form Saat Tambah Data Baru
    document.getElementById('addPTBtn').addEventListener('click', function() {
      document.getElementById('modalPTTitle').innerText = 'Tambah Perguruan Tinggi';
      document.getElementById('ptForm').reset();
      document.getElementById('formMethod').value = 'POST';
      document.getElementById('ptForm').setAttribute('action',
        "{{ route('admin/daftar-pt.store') }}");
    });

    document.getElementById('kodePTS').addEventListener('input', function() {
      const input = this.value;
      const pattern = /^[1-9][0-9]*$/;
      if (!pattern.test(input)) {
        this.classList.add('is-invalid');
      } else {
        this.classList.remove('is-invalid');
      }
    });

    //Sync PT
    const syncForm = document.getElementById('syncPtForm');

    syncForm.addEventListener('submit', (e) => {
      e.preventDefault()
      //ttup modal
      const modalSync = document.getElementById('modalSync');
      const modalInstance = bootstrap.Modal.getInstance(modalSync);
      modalInstance.hide();
      loadingAlert();

      //ambil data dari form
      const formData = new FormData(syncForm)
      //fetching data
      const fetchingData = async () => {
        try {
          const data = await fetch(syncForm.action, {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: formData
          })
          const response = await data.json()
          Swal.close()
          if (!data.success) alert(response.message, 'Gagal!', 'error', 'btn btn-danger')
          alert(response.message)
          // window.location.href = "{{ route('admin.daftar-pt') }}"
          syncForm.reset();
          $('#ptsTable').DataTable().ajax.reload()
        } catch (error) {
          console.log(`err: ${error.message}`);
        }
      }

      fetchingData()
    });
    const kodePtsSync = document.getElementById("kode_pts_sync");
    const namaPtsSyncInput = document.getElementById("nama_pts_sync");
    if (kodePtsSync && namaPtsSyncInput) {
      // set initial value ketika modal dibuka
      const setNamaFromSelected = () => {
        const sel = kodePtsSync.options[kodePtsSync.selectedIndex];
        namaPtsSyncInput.value = sel ? (sel.getAttribute('data-nama') || '') : '';
      };
      setNamaFromSelected();

      // update ketika pilihan berubah
      kodePtsSync.addEventListener('change', setNamaFromSelected);
    }

    //simpan PT
    const ptForm = document.getElementById('ptForm')
    ptForm.addEventListener('submit', (e) => {
      e.preventDefault()
      // tutup modal form
      const modalPTForm = document.getElementById('modalPTForm')
      const modalInstancePTForm = bootstrap.Modal.getOrCreateInstance(modalPTForm)
      modalInstancePTForm.hide()

      // setelah modal benar-benar tertutup, baru tampilkan loading
      modalPTForm.addEventListener('hidden.bs.modal', function onHidden() {
        modalPTForm.removeEventListener('hidden.bs.modal', onHidden)
        loadingAlert()
      })

      //ambil data dari form
      const formData = new FormData(ptForm)
      const fechingData = async () => {
        try {
          const data = await fetch(ptForm.action, {
            method: "POST",
            headers: {
              "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: formData
          })

          const response = await data.json()
          Swal.close() //tutup loading
          if (!response.success) {
            alert(response.message ?? 'Terjadi kesalahan saat ubah data!', 'Gagal!',
              'success', 'btn btn-danger')
          }

          alert(response.message, 'Berhasil!')
          $('#ptsTable').DataTable().ajax.reload()
        } catch (error) {
          console.log(error.message);
        }
      }

      fechingData()
    })


    $('#ptsTable').DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      ajax: {
        url: "{{ route('admin.daftar-pt') }}"
      },
      columns: [{
          data: "kode_pts",
          name: "kode_pts"
        },
        {
          data: "nama_pts",
          name: "nama_pts"
        },
        {
          data: "wilayah",
          name: "wilayah"
        },
        {
          data: "aktif",
          name: "aktif",
          orderable: false,
          searchable: false
        },
        {
          data: "aksi",
          name: "aksi",
          orderable: false,
          searchable: false
        }
      ],
      language: {
        paginate: {
          first: "Awal",
          last: "Akhir",
          next: "→",
          previous: "←",
        },
        zeroRecords: "Data tidak ditemukan",
        infoEmpty: "Tidak ada data tersedia",
        searchPlaceholder: "Cari data...",
        search: "Cari:"
      },
    });
  });
</script>
@endsection