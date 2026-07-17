@extends('layouts/contentNavbarLayoutPic')

@section('title', 'SPTJM Online')

@section('content')

{{-- Page Header --}}
<div class="md-page-header">
    <div class="page-titles">
        <h3>Riwayat Pengajuan</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Monitoring</a></li>
                <li class="breadcrumb-item active">Riwayat Pengajuan</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card md-card">
    <div class="md-card-inner">
        <div class="table-responsive text-nowrap">
            <table id="riwayat-table" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>ID Usulan</th>
                        <th>Tanggal Usulan</th>
                        <th>Bulan</th>
                        <th>Nama PTS</th>
                        <th>Progres</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayats ?? [] as $item)
                    <tr>
                        <td>{{ $item->id_usulan ?? '-' }}</td>
                        <td>{{ $item->tanggal_usulan ?? '-' }}</td>
                        <td>{{ $item->bulan ?? '-' }}</td>
                        <td>{{ $item->nama_pts ?? '-' }}</td>
                        <td>{{ $item->status ?? '-' }}</td>
                        <td>
                            @php $storageBase = asset('storage'); @endphp
                            {{-- File button (icon only). If no file, show disabled icon button --}}
                            @php
                                $idUs = $item->id_usulan ?? '';
                                $prefix2 = strtoupper(substr(trim($idUs), 0, 2));
                                $prefix1 = strtoupper(substr(trim($idUs), 0, 1));
                                // determine folder based on prefix
                                if (in_array($prefix2, ['TB', 'TS'])) {
                                    $folder = $prefix2 === 'TB' ? 'uploadFile_TUKIN_B' : 'uploadFile_TUKIN_S';
                                } else {
                                    $folder = $prefix1 === 'B' ? 'uploadFile_SPTJM_B' : ($prefix1 === 'S' ? 'uploadFile_SPTJM_S' : '');
                                }
                                $filePath = $item->file ?? '';
                                if ($filePath && strpos($filePath, '/') === false && $folder) {
                                    $filePath = trim($folder, '/') . '/' . ltrim($filePath, '/');
                                }
                            @endphp
                            @if(!empty($item->file))
                                <a href="{{ $storageBase . '/' . $filePath }}" target="_blank" class="sptjm-icon-btn sptjm-btn-view" title="Lihat Dokumen">
                                    <i class="bx bx-file"></i>
                                </a>
                            @else
                                <button type="button" class="sptjm-icon-btn sptjm-btn-view" disabled style="opacity: 0.5; cursor: not-allowed;" title="Tidak ada dokumen">
                                    <i class="bx bx-file"></i>
                                </button>
                            @endif

                            {{-- Detail button (icon only) always shown if `no` exists --}}
                            @if(!empty($item->no))
                                <a href="{{ url('pic/detail-riwayat-pengajuan/' . $item->no) }}" class="sptjm-icon-btn sptjm-btn-info" title="Detail Pengajuan">
                                    <i class="bx bx-show"></i>
                                </a>
                            @else
                                <button type="button" class="sptjm-icon-btn sptjm-btn-info" disabled style="opacity: 0.5; cursor: not-allowed;" title="Detail tidak tersedia">
                                    <i class="bx bx-detail"></i>
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('#riwayat-table').DataTable({
            responsive: true,
            fixedHeader: true,
            processing: false,
            serverSide: false,
            dom: "<'md-toolbar'<'entries-wrap'l><'search-wrap'f>><'table-responsive text-nowrap't><'row dt-bottom-row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            order: [[1, 'desc']],
            columnDefs: [
                { targets: 1, type: 'date' }
            ],
            language: {
                lengthMenu: "Show _MENU_ entries",
                zeroRecords: "Tidak ada data yang cocok",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                paginate: { previous: "Sebelumnya", next: "Berikutnya" }
            }
        });
    });
</script>
@endsection
