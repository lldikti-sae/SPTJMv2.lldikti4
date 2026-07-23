<ul class="menu-sub">
    @if (isset($menu))
        @foreach ($menu as $submenu)
            {{-- active menu method --}}
            @php
                $activeClass = null;
                $active = 'active open';
                $currentRouteName = Route::currentRouteName();
                // Locked type propagates from parent: 'sptjm' or 'tukin'
                $lockedType = $lockedParent ?? null;
                if (isset($submenu->name) && isset($isSptjmOpen) && isset($isTukinOpen)) {
                    if ($submenu->name === 'Tunjangan Profesi' && !$isSptjmOpen) {
                        $lockedType = 'sptjm';
                    }
                    if ($submenu->name === 'Tunjangan Kinerja' && !$isTukinOpen) {
                        $lockedType = 'tukin';
                    }
                }

                // Cek kecocokan berdasarkan nama route, slug, atau URL path saat ini
                $isUrlActive = isset($submenu->url) && (request()->is(trim($submenu->url, '/')) || request()->is(trim($submenu->url, '/') . '/*'));

                if ($currentRouteName === $submenu->slug || $isUrlActive) {
                    $activeClass = 'active';
                } elseif (isset($submenu->submenu)) {
                    if (gettype($submenu->slug) === 'array') {
                        foreach ($submenu->slug as $slug) {
                            if (str_contains($currentRouteName, $slug) and strpos($currentRouteName, $slug) === 0) {
                                $activeClass = $active;
                            }
                        }
                    } else {
                        if (
                            str_contains($currentRouteName, $submenu->slug) and
                            strpos($currentRouteName, $submenu->slug) === 0
                        ) {
                            $activeClass = $active;
                        }
                    }
                }
            @endphp

            <li class="menu-item {{ $activeClass }}">
                @php
                    // For leaf nodes: if lockedType is set, do not navigate; show as muted and trigger modal
                    $isLeaf = isset($submenu->url) && !isset($submenu->submenu);
                    $href = isset($submenu->url) ? url($submenu->url) : 'javascript:void(0)';
                    $linkClasses = isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link';
                    $dataLocked = '';
                    $extraClass = '';
                    $extraStyle = '';
                    if ($isLeaf && $lockedType) {
                        $href = 'javascript:void(0)';
                        $dataLocked = 'data-locked="' . $lockedType . '"';
                        $extraClass = ' text-muted';
                        // keep clickable (no pointer-events none)
                        $extraStyle = '';
                    }
                @endphp
                <a href="{{ $href }}"
                    class="{{ $linkClasses }}{{ $extraClass }}"
                    {!! $dataLocked !!}
                    @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
                    @if (isset($submenu->icon))
                        <i class="{{ $submenu->icon }}"></i>
                    @endif
                    <div>{{ isset($submenu->name) ? __($submenu->name) : '' }}</div>
                    @isset($submenu->badge)
                        <div class="badge bg-{{ $submenu->badge[0] }} rounded-pill ms-auto">{{ $submenu->badge[1] }}</div>
                    @endisset
                </a>

                {{-- submenu --}}
                @if (isset($submenu->submenu))
                    @include('layouts.sections.menu.submenuPts', [
                        'menu' => $submenu->submenu,
                        'isSptjmOpen' => $isSptjmOpen ?? null,
                        'isTukinOpen' => $isTukinOpen ?? null,
                        'lockedParent' => $lockedType,
                    ])
                @endif
            </li>
        @endforeach

        {{-- Tambahkan item level 2 khusus untuk menu parent yang berisi kata "Dosen" --}}
        @if (isset($parentName) && str_contains(strtolower($parentName), 'dosen'))
            <li class="menu-item {{ Route::currentRouteName() === 'pts.nonaktifkan-dosen' ? 'active' : '' }}">
                <a href="{{ route('pts.nonaktifkan-dosen') }}" class="menu-link">
                    <div>{{ __('Nonaktifkan Status Aktif Dosen') }}</div>
                </a>
            </li>
        @endif
    @endif
</ul>
