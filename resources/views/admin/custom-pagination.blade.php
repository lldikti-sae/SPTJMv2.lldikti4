@if ($paginator->hasPages())
    <nav>
        <ul class="pagination">
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">‹</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}">‹</a></li>
            @endif

            @php
                // Tampilkan maksimal 5 nomor halaman (2 sebelum, 1 aktif, 2 sesudah)
                $start = max(1, $paginator->currentPage() - 2);
                $end = min($paginator->lastPage(), $paginator->currentPage() + 2);
                
                // Sesuaikan jika di awal atau di akhir agar tetap tampil 5 angka jika memungkinkan
                if ($end - $start < 4) {
                    if ($start == 1) {
                        $end = min($paginator->lastPage(), 5);
                    } elseif ($end == $paginator->lastPage()) {
                        $start = max(1, $paginator->lastPage() - 4);
                    }
                }
            @endphp
            
            @for ($i = $start; $i <= $end; $i++)
                @if ($i == $paginator->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a></li>
                @endif
            @endfor

            @if ($paginator->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}">›</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">›</span></li>
            @endif
        </ul>
    </nav>
@endif
