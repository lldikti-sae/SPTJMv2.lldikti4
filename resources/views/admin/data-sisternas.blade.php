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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="card" style="width: 100%; padding: 10px;">
    <h5 class="card-header text-start p-2">Data Sisternas</h5>
    <hr>
    @if(auth()->user() instanceof \App\Models\User && auth()->user()->isAdmin())
    <form id="uploadForm" action="{{ route('admin.data-sisternas.store') }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-lg-3">
                <label class="form-label">Periode</label>
                <select class="form-select" name="periode">
                    <option value="[Maret - Agustus] Genap Tahun Lalu">[Maret - Agustus] Genap TL</option>
                    <option value="[September - Desember] Ganjil Tahun Lalu">[September - Desember] Ganjil TL</option>
                    <option value="[Maret - Agustus] Genap Berjalan">[Maret - Agustus] Genap BJ</option>
                </select>
            </div>

            <div class="col-lg-2">
                <label class="form-label">Bulan</label>
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

            <div class="col-lg-3">
                <label class="form-label">Upload Dokumen</label>
                <input class="form-control" type="file" name="dokumen" required>
            </div>

            <div class="col-lg-2">
                <label class="form-label">Tanggal Cut Off</label>
                <input class="form-control" type="date" name="tanggal" required>
            </div>

            <div class="col-lg-2">
                <label class="form-label">Tahun Pencairan</label>
                <select class="form-select" name="tahun" required id="tahunSelect">

                </select>
            </div>

            <div class="col-lg-2 mt-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </div>
    </form>

    <hr>
    @endif

    {{-- Tabel --}}
    <div class="table-responsive text-nowrap mt-4">
        <table class="table table-sm table-hover">
            <thead style="background-color: #dbdee0;">
                <tr>
                    <th>No</th>
                    <th>Tahun Pencairan</th>
                    <th>Tanggal Cut Off</th>
                    <th>Bulan</th>
                    <th>Periode</th>
                    <th>Dokumen</th>
                    @if(auth()->user() instanceof \App\Models\User && auth()->user()->isAdmin())
                    <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($dataSisternas as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->tahun }}</td>
                    <td>{{ $item->tanggal }}</td>
                    <td>{{ $item->periode }}</td>
                    <td>{{ $item->bulan }}</td>
                    <td>
                        <a href="{{ asset('storage/File_Data_Sisternas2/' . $item->dokumen) }}" target="_blank">
                            <i class="bx bx-file"></i> Lihat Dokumen</a>
                    </td>
                    @if(auth()->user() instanceof \App\Models\User && auth()->user()->isAdmin())
                    <td>
                        <form action="{{ route('data-sisternas.destroy', $item->id) }}" method="POST"
                            class="delete-form" onsubmit="return false;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger delete-sisternas">
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
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
@endsection
