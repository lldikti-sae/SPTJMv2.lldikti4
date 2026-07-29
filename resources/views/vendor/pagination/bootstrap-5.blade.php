@if ($paginator->hasPages())
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 mb-2 w-100">
        {{-- Info Text (Rata Kiri) --}}
        <div class="text-muted" style="font-size: 0.83rem; font-weight: 500;">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} entri
        </div>

        {{-- Pagination Links (Rata Kanan) --}}
        <div>
            <ul class="pagination mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <span class="page-link" aria-hidden="true">‹</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">‹</a>
                    </li>
                @endif

                @php
                    $lastPage = $paginator->lastPage();
                    $currentPage = $paginator->currentPage();
                @endphp

                @if ($lastPage <= 6)
                    {{-- Show all pages if 6 or less --}}
                    @for ($i = 1; $i <= $lastPage; $i++)
                        @if ($i == $currentPage)
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $i }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a></li>
                        @endif
                    @endfor
                @else
                    {{-- More than 6 pages: show 1-5 ... lastPage or sliding window --}}
                    @if ($currentPage <= 4)
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i == $currentPage)
                                <li class="page-item active" aria-current="page"><span class="page-link">{{ $i }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a></li>
                            @endif
                        @endfor
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">…</span></li>
                        <li class="page-item"><a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a></li>
                    @elseif ($currentPage >= $lastPage - 3)
                        <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">…</span></li>
                        @for ($i = $lastPage - 4; $i <= $lastPage; $i++)
                            @if ($i == $currentPage)
                                <li class="page-item active" aria-current="page"><span class="page-link">{{ $i }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a></li>
                            @endif
                        @endfor
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">…</span></li>
                        @for ($i = $currentPage - 1; $i <= $currentPage + 1; $i++)
                            @if ($i == $currentPage)
                                <li class="page-item active" aria-current="page"><span class="page-link">{{ $i }}</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a></li>
                            @endif
                        @endfor
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">…</span></li>
                        <li class="page-item"><a class="page-link" href="{{ $paginator->url($lastPage) }}">{{ $lastPage }}</a></li>
                    @endif
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">›</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <span class="page-link" aria-hidden="true">›</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
@elseif ($paginator->total() > 0)
    <div class="mt-3 mb-2 w-100">
        <div class="text-muted" style="font-size: 0.83rem; font-weight: 500;">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} entri
        </div>
    </div>
@endif
