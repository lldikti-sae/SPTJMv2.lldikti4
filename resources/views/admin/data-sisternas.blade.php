@php
    $layout = 'layouts/contentNavbarLayout';
    if(auth()->user() instanceof \App\Models\APts) {
        $layout = 'layouts/contentNavbarLayoutPts';
    } elseif(auth()->user() instanceof \App\Models\User && auth()->user()->isPIC()) {
        $layout = 'layouts/contentNavbarLayoutPic';
    }
@endphp
@extends($layout)

@section('title', 'SPTJM Online')

@section('content')
<style>
    .card-sisternas {
        border: 1.5px solid #dbeafe !important;
        box-shadow: 0 10px 30px rgba(26, 86, 219, 0.15) !important;
        border-radius: 12px !important;
        border-left: 6px solid #1a56db !important;
        padding: 24px !important;
        background: #ffffff !important;
    }
    .custom-file-upload {
        display: flex;
        align-items: center;
        border: 1px solid #dbeafe;
        border-radius: 6px;
        background-color: #f8fafc;
        padding: 4px 8px;
        cursor: pointer;
        font-size: 0.85rem;
        color: #334155;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="mb-4">
    <h3 class="fw-bold text-dark mb-1" style="color: #0f2b5c !important;">Data Sisternas</h3>
    <p class="text-muted mb-0">Kelola sinkronisasi data Sisternas untuk periode pencairan</p>
</div>

<div class="card-sisternas card">
    @if(auth()->user() instanceof \App\Models\User && auth()->user()->isAdmin())
    <form id="uploadForm" action="{{ route('admin.data-sisternas.store') }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="row align-items-end g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Periode</label>
                <select class="form-select" name="periode">
                    <option value="[Maret - Agustus] Genap Tahun Lalu">[Maret - Agustus] Genap TL</option>
                    <option value="[September - Desember] Ganjil Tahun Lalu">[September - Desember] Ganjil TL</option>
                    <option value="[Maret - Agustus] Genap Berjalan">[Maret - Agustus] Genap BJ</option>
                </select>
            </div>

            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Bulan</label>
                <select class="form-select" name="bulan">
                    <option value="Januari">[01] Januari</option>
                    <option value="Februari">[02] Februari</option>
                    <option value="Maret">[03] Maret</option>
                    <option value="April">[04] April</option>
                    <option value="Mei">[05] Mei</option>
                    <option value="Juni">[06] Juni</option>
                    <option value="Juli">[07] Juli</option>
                    <option value="Agustus">[08] Agustus</option>
                    <option value="September">[09] September</option>
                    <option value="Oktober">[10] Oktober</option>
                    <option value="November">[11] November</option>
                    <option value="Desember">[12] Desember</option>
                </select>
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Upload Dokumen</label>
                <div class="input-group">
                    <input class="form-control" type="file" name="dokumen" required id="dokumenFile" style="display:none;" onchange="updateFileName(this)">
                    <button class="btn btn-outline-primary" type="button" onclick="document.getElementById('dokumenFile').click()" style="border-color: #cbd5e1; color: #4b5563; font-weight: 600; font-size: 0.85rem; padding: 6px 12px; border-top-left-radius: 6px; border-bottom-left-radius: 6px; background-color: #f3f4f6;">Pilih File</button>
                    <input type="text" class="form-control" id="file-name-val" value="Tidak ada file terpilih" readonly style="background: #ffffff; font-size: 0.85rem; color: #64748b; border-color: #cbd5e1;">
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Tanggal Cut Off</label>
                <input class="form-control" type="date" name="tanggal" required style="border-color: #cbd5e1;">
            </div>

            <div class="col-lg-2 col-md-6">
                <label class="form-label fw-bold text-dark text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Tahun Pencairan</label>
                <select class="form-select" name="tahun" required id="tahunSelect">
                </select>
            </div>
        </div>
        
        <div class="d-flex justify-content-end mb-4">
            <button type="submit" class="btn btn-success px-4 py-2 fw-bold" style="background-color: #28c76f; border-color: #28c76f; font-size: 0.875rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px;">
                <i class="bx bx-save" style="font-size: 1.15rem;"></i> Simpan Data
            </button>
        </div>
    </form>
    @endif

    {{-- Tabel --}}
    <div class="table-responsive text-nowrap mt-2">
        <table class="table table-hover md2-table" id="sisternasTable" style="margin-bottom: 0 !important;">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">No</th>
                    <th style="text-align: center;">Tahun Pencairan</th>
                    <th style="text-align: center;">Tanggal Cut Off</th>
                    <th style="text-align: center;">Bulan</th>
                    <th style="text-align: center;">Periode</th>
                    <th style="text-align: center;">Dokumen</th>
                    @if(auth()->user() instanceof \App\Models\User && auth()->user()->isAdmin())
                    <th style="width: 10%; text-align: center;">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($dataSisternas as $key => $item)
                <tr>
                    <td style="text-align: center;"><span class="fw-semibold text-primary">{{ $key + 1 }}</span></td>
                    <td style="text-align: center;"><span class="fw-bold text-dark">{{ $item->tahun }}</span></td>
                    <td style="text-align: center;"><span class="fw-bold text-dark">{{ $item->tanggal }}</span></td>
                    <td style="text-align: center;"><span class="fw-bold text-dark">{{ $item->periode }}</span></td>
                    <td style="text-align: center;"><span class="fw-bold text-dark">{{ $item->bulan }}</span></td>
                    <td style="text-align: center;">
                        <a href="{{ asset('storage/File_Data_Sisternas2/' . $item->dokumen) }}" target="_blank" class="text-primary fw-semibold" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="bx bx-file" style="font-size: 1.15rem;"></i> Lihat Dokumen</a>
                    </td>
                    @if(auth()->user() instanceof \App\Models\User && auth()->user()->isAdmin())
                    <td style="text-align: center;">
                        <form action="{{ route('data-sisternas.destroy', $item->id) }}" method="POST"
                            class="delete-form d-inline" onsubmit="return false;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="sptjm-icon-btn sptjm-btn-delete delete-sisternas" title="Hapus">
                                <i class="bx bx-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function updateFileName(input) {
    var fileName = input.files[0] ? input.files[0].name : "Tidak ada file terpilih";
    document.getElementById('file-name-val').value = fileName;
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#sisternasTable').DataTable({
        pageLength: 10,
        order: [[0, 'asc']]
    });

    //tahun select
    const select = document.getElementById("tahunSelect");
    const currentYear = new Date().getFullYear();

    // tampilkan tahun sekarang dan tahun sebelumnya
    for (let year = currentYear; year >= currentYear - 1; year--) {
        let option = document.createElement("option");
        option.value = year;
        option.textContent = year;
        select.appendChild(option);
    }

    //alert
    const alert = (message, type) => {
        return Swal.fire({
            title: message,
            icon: type,
            timer: 1500,
            showConfirmButton: true,
        });
    };

    //alert hapus
    @if(session('success')) {
        alert("{{ session('success') }}", "success");
    }
    @endif

    // Konfirmasi Hapus Data
    document.querySelectorAll('.delete-sisternas').forEach(button => {
        button.addEventListener('click', function() {
            let form = this.closest('.delete-form');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-danger me-1',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    //menampilkan loading
                    Swal.fire({
                        title: 'Mohon tunggu...',
                        html: `
<div class="d-flex justify-content-center align-items-center flex-column">
    <div class="spinner-border spinner-border-lg text-danger" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="mt-2">Sedang menghapus data</div>
</div>
`,
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        backdrop: true
                    });
                    form.submit();
                }
            });
        });
    });

    // Upload Form
    const form = document.getElementById('uploadForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Mohon tunggu...',
                html: `
<div class="d-flex justify-content-center align-items-center flex-column">
    <div class="spinner-border spinner-border-lg text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="mt-2">Sedang mengupload data</div>
</div>
`,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                backdrop: true
            });

            const formData = new FormData(form);
            fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then((data) => {
                    if (data.status) {
                        alert(data.message, "success")
                            .then(() => window.location.reload());
                    } else {
                        alert("Data gagal tersimpan!", "error");
                    }
                })
                .catch((err) => {
                    console.log("error: ", err);
                    window.location.reload();
                });
        });
    }
});
</script>
@endsection
