  <!-- BEGIN: Vendor JS-->
<script src="{{ asset(mix('assets/vendor/libs/jquery/jquery.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/popper/popper.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/js/bootstrap.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/js/menu.js')) }}"></script>
<!-- DataTables Bootstrap 5 JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
<!-- ——— SPTJM GLOBAL DATATABLE DEFAULTS ——— -->
<script>
    if (typeof $ !== 'undefined' && $.fn && $.fn.dataTable) {
        $.fn.dataTable.ext.errMode = 'none';
        $.extend(true, $.fn.dataTable.defaults, {
            /* DOM layout:
               l = length (Show Entries) — kiri
               f = filter (Search)       — kanan
               r = processing
               t = table
               i = info
               p = pagination
            */
            dom: '<"d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"lf><"table-responsive text-nowrap"rt><"d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3"ip>',
            lengthMenu: [[10, 15, 25, 50, 100, 250, 500], [10, 15, 25, 50, 100, 250, 500]],
            pageLength: 10,
            language: {
                search: "",
                searchPlaceholder: "Cari data...",
                lengthMenu: "Show _MENU_ Entries",
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: '→',
                    previous: '←'
                },
                zeroRecords: 'Data tidak ditemukan',
                infoEmpty: 'Menampilkan 0 entri',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ entri',
                infoFiltered: '(disaring dari _MAX_ total entri)'
            }
        });
    }
</script>
<!-- ——— SPTJM: Prevent hover-ghost icon effect across page navigation ——— -->
<script>
    (function() {
        window.addEventListener('beforeunload', function() {
            document.documentElement.classList.add('page-transitioning');
        });
        document.addEventListener('click', function(e) {
            var link = e.target && e.target.closest ? e.target.closest('a[href], button[type="submit"]') : null;
            if (link && !link.getAttribute('data-bs-toggle')) {
                document.documentElement.classList.add('page-transitioning');
            }
        }, true);
    })();
</script>
<!-- Vendors JS -->
@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
<script>
	(function () {
		'use strict';

		const getScope = () => {
			const path = (window.location && window.location.pathname) ? window.location.pathname : '';
			const first = path.replace(/^\/+/, '').split('/')[0] || 'root';
			// keep it short & deterministic
			return first.toLowerCase();
		};

		const scope = getScope();
		const STORAGE_OPEN = 'sptjm.sidebar.' + scope + '.openKeys';
		const STORAGE_SCROLL = 'sptjm.sidebar.' + scope + '.scrollTop';
		const STORAGE_PREFIX = 'sptjm.sidebar.';

		const clearAllSidebarState = () => {
			try {
				for (let i = localStorage.length - 1; i >= 0; i--) {
					const key = localStorage.key(i);
					if (key && key.indexOf(STORAGE_PREFIX) === 0) {
						localStorage.removeItem(key);
					}
				}
			} catch (e) {
				// ignore
			}
		};

		const isLoginPage = () => {
			const path = (window.location && window.location.pathname) ? window.location.pathname.toLowerCase() : '';
			if (path.includes('/login')) return true;
			// fallback heuristic: any form that looks like a login form
			const form = document.querySelector('form');
			if (!form) return false;
			const action = (form.getAttribute('action') || '').toLowerCase();
			return action.includes('login');
		};

		const isDashboardPage = () => {
			// Only match URLs that end exactly with "/dashboard" (no extra suffix)
			const path = (window.location && window.location.pathname) ? window.location.pathname.toLowerCase() : '';
			return /(^|\/)dashboard$/.test(path);
		};

		const closeAllOpenMenuItems = () => {
			const menuEl = getMenuEl();
			if (!menuEl) return;
			menuEl.querySelectorAll('.menu-item.open').forEach((item) => item.classList.remove('open'));
		};

		/**
		 * Kembalikan open-state dari localStorage HANYA untuk submenu
		 * yang ROOT-nya sudah di-render oleh server sebagai 'open'.
		 *
		 * Kenapa perlu filter ini?
		 * ─ Server PHP sudah tahu menu mana yang aktif berdasarkan URL.
		 * ─ Jika user pindah dari "Proses Pembayaran" ke "Data Dosen",
		 *   server merender "Data Dosen" sebagai active-open dan
		 *   "Proses Pembayaran" tanpa open.
		 * ─ Tanpa filter, localStorage akan membuka ulang "Proses Pembayaran"
		 *   padahal user sudah pindah menu — inilah sumber bug.
		 * ─ Dengan filter, hanya submenu dalam cabang yang SAMA dengan
		 *   halaman aktif yang dipulihkan.
		 */
		const applyOpenKeysWithoutAnimation = (keys) => {
			const menuEl = getMenuEl();
			if (!menuEl || !Array.isArray(keys) || !keys.length) return;

			// Kumpulkan root-level .menu-item yang sudah di-mark 'open' oleh server (PHP).
			// Ini adalah "kebenaran" navigasi saat ini berdasarkan URL yang sedang aktif.
			const menuInner = menuEl.querySelector('.menu-inner');
			const serverOpenRoots = new Set(
				menuInner
					? Array.from(menuInner.querySelectorAll(':scope > .menu-item.open'))
					: []
			);

			const toggles = menuEl.querySelectorAll('a.menu-toggle');
			toggles.forEach((a) => {
				const key = getToggleKey(a);
				if (!key || !keys.includes(key)) return;

				let item = a.closest ? a.closest('.menu-item') : null;
				if (!item) return;

				// Temukan root ancestor dari item ini (level paling atas di menu-inner)
				let rootItem = item;
				let parentCheck = rootItem.parentElement
					? rootItem.parentElement.closest('.menu-item')
					: null;
				while (parentCheck) {
					rootItem = parentCheck;
					parentCheck = rootItem.parentElement
						? rootItem.parentElement.closest('.menu-item')
						: null;
				}

				// GUARD: Hanya pulihkan jika root menu-nya server-mark sebagai open.
				// Jika serverOpenRoots kosong (tidak ada menu aktif, misal halaman lain),
				// tidak ada state yang dipulihkan — ini perilaku yang benar.
				if (!serverOpenRoots.has(rootItem)) return;

				// Buka item ini dan semua ancestor-nya (tanpa animasi)
				while (item) {
					item.classList.add('open');
					item = item.parentElement && item.parentElement.closest
						? item.parentElement.closest('.menu-item')
						: null;
				}
			});
		};

		const getMenuEl = () => document.getElementById('layout-menu');
		const getMenuInner = () => {
			const menuEl = getMenuEl();
			return menuEl ? menuEl.querySelector('.menu-inner') : null;
		};

		const getToggleKey = (toggleLink) => {
			if (!toggleLink) return '';
			const parts = [];
			let current = toggleLink;
			while (current) {
				if (current.matches && current.matches('a.menu-toggle')) {
					const label = (current.textContent || '').replace(/\s+/g, ' ').trim();
					if (label) parts.unshift(label);
				}

				// go up to parent menu item, then find parent menu item toggle
				const item = current.closest ? current.closest('.menu-item') : null;
				if (!item) break;

				const parentSub = item.parentElement && item.parentElement.closest ? item.parentElement.closest('.menu-item') : null;
				if (!parentSub) break;
				current = parentSub.querySelector(':scope > a.menu-toggle');
			}
			return parts.join(' > ');
		};

		const collectOpenKeys = () => {
			const menuEl = getMenuEl();
			if (!menuEl) return [];

			const openItems = menuEl.querySelectorAll('.menu-item.open > a.menu-toggle');
			const keys = [];
			openItems.forEach((a) => {
				const key = getToggleKey(a);
				if (key) keys.push(key);
			});
			// de-dup
			return Array.from(new Set(keys));
		};

		const restoreOpenKeys = (keys) => {
			const menuEl = getMenuEl();
			if (!menuEl || !Array.isArray(keys) || !keys.length) return;

			const instance = menuEl.menuInstance || (window.Helpers && window.Helpers.mainMenu) || null;
			const toggles = menuEl.querySelectorAll('a.menu-toggle');
			toggles.forEach((a) => {
				const key = getToggleKey(a);
				if (!key) return;
				if (keys.includes(key)) {
					try {
						if (instance && typeof instance.open === 'function') {
							instance.open(a, false);
						} else {
							const item = a.closest('.menu-item');
							if (item) item.classList.add('open');
						}
					} catch (e) {
						// ignore
					}
				}
			});
		};

		/**
		 * Pastikan hanya SATU root menu yang terbuka — menu yang memiliki
		 * item aktif. Jika localStorage membuka menu lain, tutup paksa.
		 * Ini adalah safety-net global setelah applyOpenKeysWithoutAnimation.
		 */
		const enforceSingleRootOpen = () => {
			const menuEl = getMenuEl();
			if (!menuEl) return;
			const menuInner = menuEl.querySelector('.menu-inner');
			if (!menuInner) return;

			// Root items = direct children of .menu-inner
			const rootOpenItems = Array.from(menuInner.querySelectorAll(':scope > .menu-item.open'));
			if (rootOpenItems.length <= 1) return;

			// Preferensikan root item yang memiliki child active (server truth)
			let keep = rootOpenItems.find(
				(it) => it.classList.contains('active')
					|| it.querySelector('.menu-link.active')
					|| it.querySelector('.menu-item.active')
			);
			if (!keep) keep = rootOpenItems[0];

			// Tutup paksa semua root item yang bukan 'keep', termasuk semua child-nya
			rootOpenItems.forEach((item) => {
				if (item === keep) return;
				try {
					// Tutup semua submenu di dalamnya
					item.querySelectorAll('.menu-item.open').forEach((child) => {
						child.classList.remove('open');
						child.style.height = '';
						child.style.overflow = '';
					});
					item.classList.remove('open');
					item.style.height = '';
					item.style.overflow = '';
				} catch (e) {
					// ignore
				}
			});
		};

		const saveState = () => {
			try {
				localStorage.setItem(STORAGE_OPEN, JSON.stringify(collectOpenKeys()));
			} catch (e) {
				// ignore
			}

			try {
				const inner = getMenuInner();
				if (inner) localStorage.setItem(STORAGE_SCROLL, String(inner.scrollTop || 0));
			} catch (e) {
				// ignore
			}
		};

		// Pre-apply state BEFORE main.js/menu.js runs, so the UI doesn't flicker
		// from closed -> open when navigating.
		(function preApplySidebarState() {
			if (isLoginPage()) {
				clearAllSidebarState();
				return;
			}

			if (!getMenuEl()) return;

			if (isDashboardPage()) {
				clearAllSidebarState();
				closeAllOpenMenuItems();
				return;
			}

			let openKeys = [];
			try {
				openKeys = JSON.parse(localStorage.getItem(STORAGE_OPEN) || '[]');
			} catch (e) {
				openKeys = [];
			}

			applyOpenKeysWithoutAnimation(openKeys);
			enforceSingleRootOpen();

			// Apply stored scroll early to reduce visible jump
			try {
				const inner = getMenuInner();
				const top = parseInt(localStorage.getItem(STORAGE_SCROLL) || '0', 10);

				// If we are restoring a non-zero scroll position, prevent the theme
				// from auto-scrolling to the active item (it causes a visible jump).
				if (isFinite(top) && top > 0) {
					window.__sptjmSkipScrollToActive = true;
				}
				if (inner && isFinite(top)) {
					inner.scrollTop = top;
				}
			} catch (e) {
				// ignore
			}
		})();

		document.addEventListener('DOMContentLoaded', function () {
			// If login page, we already cleared state; nothing else to do.
			if (isLoginPage()) return;

			// Re-enable transitions after initial sidebar state is applied
			try {
				setTimeout(() => {
					document.documentElement.classList.remove('sptjm-no-menu-transition');
				}, 0);
			} catch (e) {
				// ignore
			}

			// Restore scroll after menu + PerfectScrollbar are initialized.
			if (!isDashboardPage()) {
				try {
					const inner = getMenuInner();
					const top = parseInt(localStorage.getItem(STORAGE_SCROLL) || '0', 10);
					if (inner && isFinite(top)) {
						// only set if different to avoid visible jump
						if (Math.abs((inner.scrollTop || 0) - top) > 2) {
							setTimeout(() => {
								inner.scrollTop = top;
							}, 0);
						}
					}
				} catch (e) {
					// ignore
				}
			}

			const menuEl = getMenuEl();
			if (menuEl) {
				// Reset persisted sidebar state when user clicks logout
				document.addEventListener('click', function (e) {
					const a = e.target && e.target.closest ? e.target.closest('a') : null;
					if (!a) return;
					const href = (a.getAttribute('href') || '').toLowerCase();
					// common patterns: /logout, route name contains logout, etc.
					if (href.includes('logout')) {
						clearAllSidebarState();
					}
				});

				// Save when user expands/collapses submenus
				menuEl.addEventListener('click', function (e) {
					const toggle = e.target && e.target.closest ? e.target.closest('a.menu-toggle') : null;
					if (toggle) {
						// after Menu handles click
						setTimeout(saveState, 0);
					}
				});
			}

			const inner = getMenuInner();
			if (inner) {
				inner.addEventListener('scroll', function () {
					// lightweight throttle
					window.clearTimeout(inner.__sptjmScrollT);
					inner.__sptjmScrollT = window.setTimeout(() => {
						try {
							localStorage.setItem(STORAGE_SCROLL, String(inner.scrollTop || 0));
						} catch (e) {
							// ignore
						}
					}, 150);
				});
			}

			// ──────────────────────────────────────────────────────────────────────
			// ACCORDION SYNC: Tutup semua submenu dari menu lain saat user klik
			// link leaf (bukan toggle). Ini memastikan saveState() pada beforeunload
			// menyimpan state yang bersih — hanya menu aktif yang tersimpan.
			// Tanpa ini: user klik "Eligible" lalu navigasi ke "Data Dosen" →
			// beforeunload menyimpan "Proses Pembayaran" sebagai open → bug restore.
			// ──────────────────────────────────────────────────────────────────────
			if (menuEl) {
				menuEl.addEventListener('click', function (e) {
					// Hanya proses klik pada link biasa (BUKAN toggle accordion)
					const link = e.target && e.target.closest
						? e.target.closest('a.menu-link')
						: null;
					if (!link) return;
					if (link.classList.contains('menu-toggle')) return; // accordion click, skip

					// Temukan root menu yang mengandung link ini
					const clickedRootItem = (() => {
						let item = link.closest ? link.closest('.menu-item') : null;
						if (!item) return null;
						let rootItem = item;
						let parent = rootItem.parentElement
							? rootItem.parentElement.closest('.menu-item')
							: null;
						while (parent) {
							rootItem = parent;
							parent = rootItem.parentElement
								? rootItem.parentElement.closest('.menu-item')
								: null;
						}
						return rootItem;
					})();

					// Tutup semua root menu LAIN beserta seluruh child-nya
					const menuInnerEl = getMenuInner();
					if (menuInnerEl) {
						menuInnerEl.querySelectorAll(':scope > .menu-item.open').forEach((rootItem) => {
							if (rootItem === clickedRootItem) return;
							rootItem.querySelectorAll('.menu-item.open').forEach((child) => {
								child.classList.remove('open');
								child.style.height = '';
								child.style.overflow = '';
							});
							rootItem.classList.remove('open');
							rootItem.style.height = '';
							rootItem.style.overflow = '';
						});
					}

					// Simpan state bersih segera setelah penutupan (sebelum navigasi)
					setTimeout(saveState, 0);
				});
			}
		});

		window.addEventListener('beforeunload', saveState);
	})();
</script>

<script src="{{ asset(mix('assets/js/main.js')) }}"></script>

<!-- ================================================================
     SPTJM: Menu Nested Submenu Overlap Fix
     Root cause: menu.js hanya menganimasikan .menu-item yang diklik,
     tapi tidak memperbarui ancestor .menu-item yang sudah open.
     Saat submenu child dibuka, ancestor bisa terjebak di pixel-height
     lama → sibling menu di bawahnya tidak terdorong ke bawah.
     Fix: setelah setiap animasi selesai, hapus height/overflow inline
     dari semua ancestor .menu-item yang sudah open.
     ================================================================ -->
<script>
(function () {
    'use strict';

    /**
     * Hapus style height & overflow dari semua ancestor .menu-item yang sudah open.
     * Ini memastikan parent selalu expand ke height:auto setelah child selesai animasi.
     */
    function clearAncestorHeights(startItem) {
        var parent = startItem && startItem.parentElement
            ? startItem.parentElement.closest('.menu-item')
            : null;
        while (parent) {
            // Hanya clear jika parent sudah open dan TIDAK sedang dianimasikan
            if (parent.classList.contains('open') && !parent.classList.contains('menu-item-animating')) {
                parent.style.height = '';
                parent.style.overflow = '';
            }
            parent = parent.parentElement
                ? parent.parentElement.closest('.menu-item')
                : null;
        }
    }

    /**
     * Patch menu.js setelah ia terinisialisasi (main.js memanggil new Menu()).
     * Kita intercept _toggleAnimation agar clearAncestorHeights dipanggil
     * setelah animasi item yang diklik selesai.
     */
    function patchMenuInstance() {
        var menuEl = document.getElementById('layout-menu');
        if (!menuEl || !menuEl.menuInstance) return false;

        var instance = menuEl.menuInstance;
        var originalOnOpened = instance._onOpened;
        var originalOnClosed  = instance._onClosed;

        // Setelah submenu dibuka, bersihkan height ancestor
        instance._onOpened = function (menu, item, link, sub) {
            if (typeof originalOnOpened === 'function') originalOnOpened(menu, item, link, sub);
            // Beri sedikit jeda agar menu.js clearItemStyle() selesai dahulu
            setTimeout(function () { clearAncestorHeights(item); }, 20);
        };

        // Setelah submenu ditutup, bersihkan juga (untuk konsistensi)
        instance._onClosed = function (menu, item, link, sub) {
            if (typeof originalOnClosed === 'function') originalOnClosed(menu, item, link, sub);
            setTimeout(function () { clearAncestorHeights(item); }, 20);
        };

        return true;
    }

    // Coba patch segera (jika main.js sudah jalan secara sinkron)
    if (!patchMenuInstance()) {
        // Jika belum siap, tunggu DOMContentLoaded lalu coba lagi
        document.addEventListener('DOMContentLoaded', function () {
            if (!patchMenuInstance()) {
                // Fallback: polling ringan selama maks 2 detik
                var attempts = 0;
                var interval = setInterval(function () {
                    attempts++;
                    if (patchMenuInstance() || attempts >= 20) clearInterval(interval);
                }, 100);
            }
        });
    }

    // ─── Fallback tambahan: event listener langsung di menu ───────────────
    // Jika patch di atas gagal (mis. menu.js diupdate), fallback ini tetap bekerja.
    document.addEventListener('DOMContentLoaded', function () {
        var menuEl = document.getElementById('layout-menu');
        if (!menuEl) return;

        menuEl.addEventListener('click', function (e) {
            var toggle = e.target && e.target.closest
                ? e.target.closest('a.menu-toggle')
                : null;
            if (!toggle) return;

            var clickedItem = toggle.closest('.menu-item');
            if (!clickedItem) return;

            // Jalankan setelah menu.js memproses klik dan setelah animasi selesai (~380ms)
            setTimeout(function () { clearAncestorHeights(clickedItem); }, 400);
        });
    });
})();
</script>



<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

<!-- ================================================================
     SPTJM: Desktop Sidebar Toggle Patch
     Sneat free template's _setCollapsed() only handles mobile.
     This script patches Helpers.toggleCollapsed for desktop screens.
     ================================================================ -->
<script>
(function () {
    'use strict';

    var STORAGE_KEY = 'sptjm.sidebar.collapsed';

    function isDesktop() {
        return (window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth) >= 1200;
    }

    function isCollapsedDesktop() {
        return document.documentElement.classList.contains('layout-menu-collapsed');
    }

    function collapseDesktop() {
        document.documentElement.classList.add('layout-menu-collapsed');
        try { localStorage.setItem(STORAGE_KEY, '1'); } catch(e) {}
    }

    function expandDesktop() {
        document.documentElement.classList.remove('layout-menu-collapsed');
        try { localStorage.removeItem(STORAGE_KEY); } catch(e) {}
    }

    function toggleDesktop() {
        if (isCollapsedDesktop()) {
            expandDesktop();
        } else {
            collapseDesktop();
        }
        // Trigger resize so layout recalculates
        setTimeout(function() {
            window.dispatchEvent(new Event('resize'));
        }, 300);
    }

    // Restore previous state on page load
    function restoreState() {
        if (!isDesktop()) return;
        try {
            if (localStorage.getItem(STORAGE_KEY) === '1') {
                document.documentElement.classList.add('layout-menu-collapsed');
            }
        } catch(e) {}
    }

    // Patch Helpers.toggleCollapsed to support desktop
    function patchHelpers() {
        if (window.Helpers && typeof window.Helpers.toggleCollapsed === 'function') {
            var _original = window.Helpers.toggleCollapsed.bind(window.Helpers);
            window.Helpers.toggleCollapsed = function(animate) {
                if (isDesktop()) {
                    toggleDesktop();
                } else {
                    _original(animate);
                }
            };
        }
    }

    // Bind click events on all .layout-menu-toggle elements
    // Note: main.js already binds these â€” we only need the Helpers patch above.
    // We intentionally skip re-binding here to avoid double-fire.
    function bindToggles() {
        // no-op: main.js handles .layout-menu-toggle clicks via patched Helpers.toggleCollapsed
    }

    // Run
    restoreState();
    document.addEventListener('DOMContentLoaded', function() {
        patchHelpers();
        bindToggles();
    });

    // --- SPTJM GLOBAL UI STANDARDIZATION ---
    function applySptjmGlobalStyles() {
        // 1. Badge Status / Jenis Coloring
        $('span.badge, span.badge-jenis, button.badge-bulan, span#hdr-status-jenis, span#hdr-status-aktif').each(function() {
            var text = $(this).text().trim().toLowerCase();
            
            // STATUS PEGAWAI: PNS / NON PNS = Biru
            if (text === 'pns' || text === 'non pns' || text === 'non-pns') {
                $(this).css({
                    'background-color': '#e0e7ff',
                    'color': '#1a56db',
                    'border': '1px solid rgba(26,86,219,0.2)',
                    'min-width': '90px',
                    'display': 'inline-flex',
                    'align-items': 'center',
                    'justify-content': 'center',
                    'text-align': 'center'
                }).removeClass('bg-label-danger bg-label-warning bg-label-secondary bg-label-success');

                // Align center inside table cells
                if ($(this).parent().is('td')) {
                    $(this).parent().attr('style', function(i, s) { return (s || '') + ' text-align: center !important;'; });
                }
            }
            // STATUS KEAKTIFAN: Aktif = Hijau
            else if (text === 'aktif' || text === 'ya' || text === 'eligible') {
                $(this).css({
                    'background-color': '#dcfce7',
                    'color': '#16a34a',
                    'border': '1px solid rgba(22,163,74,0.2)'
                }).removeClass('bg-label-danger bg-label-warning bg-label-secondary bg-label-primary');
            } 
            // STATUS KEAKTIFAN: Tidak Aktif / Merah
            else if (text.includes('tidak') || text === 'tolak' || text.includes('gagal') || text.includes('belum')) {
                $(this).css({
                    'background-color': '#fee2e2',
                    'color': '#dc2626',
                    'border': '1px solid rgba(220,38,38,0.2)'
                }).removeClass('bg-label-primary bg-label-success bg-label-info bg-label-warning bg-label-secondary');
            }
        });

        // 2. Link Lihat Dokumen -> Teks biru
        $('a').each(function() {
            if ($(this).text().trim().toLowerCase().includes('lihat dokumen')) {
                $(this).addClass('link-lihat-dokumen').removeClass('text-white btn-primary btn-outline-primary');
                $(this).css({'color': '#1a56db', 'font-weight': '600', 'border': 'none', 'background': 'transparent', 'box-shadow': 'none'});
                $(this).find('i').css('color', '#1a56db');
            }
        });

        // 3. Tombol Sinkronisasi -> Kuning/Orange
        $('button, a').each(function() {
            var text = $(this).text().trim().toLowerCase();
            if (text.includes('sinkronisasi') || text.includes('sinkron')) {
                if ($(this).hasClass('btn') || $(this).attr('type') === 'button' || $(this).attr('class') && $(this).attr('class').includes('sptjm-')) {
                    $(this).removeClass('btn-primary btn-success btn-sptjm-primary btn-sptjm-success btn-outline-primary bg-primary bg-success');
                    $(this).addClass('btn-sptjm-sinkron');
                }
            }
        });

        // 4. Badge Bank Standardization
        $('td').each(function() {
            var $el = $(this);
            if ($el.children().length > 0) return; // Ignore if it has children, only format raw text
            var text = $el.text().trim().toUpperCase();
            var bg = null;
            if (text === 'BTN') bg = '#ef4444'; // Merah
            else if (text === 'BNI') bg = '#f97316'; // Oranye
            else if (text === 'MANDIRI') bg = '#f59e0b'; // Kuning
            else if (text === 'BSI') bg = '#10b981'; // Hijau
            else if (text === 'BRI') bg = '#1a56db'; // Biru
            else if (text === 'BJB') bg = '#64748b'; // Abu-abu
            
            if (bg) {
                $el.html('<span class="badge-bank" style="display:inline-flex; align-items:center; justify-content:center; text-align:center; min-width:75px; background:'+bg+'; color:#fff !important; border-radius:6px; padding:3px 8px; font-size:0.72rem; font-weight:700;">' + text + '</span>');
                $el.attr('style', function(i, s) { return (s || '') + ' text-align: center !important;'; }); // Align center inside table cell
            }
        });
        
        $('span.badge-bank').each(function() {
            var $el = $(this);
            var text = $el.text().trim().toUpperCase();
            var bg = null;
            if (text === 'BTN') bg = '#ef4444'; // Merah
            else if (text === 'BNI') bg = '#f97316'; // Oranye
            else if (text === 'MANDIRI') bg = '#f59e0b'; // Kuning
            else if (text === 'BSI') bg = '#10b981'; // Hijau
            else if (text === 'BRI') bg = '#1a56db'; // Biru
            else if (text === 'BJB') bg = '#64748b'; // Abu-abu
            
            if (bg) {
                $el.css({
                    'background-color': bg,
                    'color': '#fff',
                    'border-radius': '6px',
                    'padding': '3px 8px',
                    'font-size': '0.72rem',
                    'font-weight': '700',
                    'display': 'inline-flex',
                    'min-width': '75px',
                    'align-items': 'center',
                    'justify-content': 'center',
                    'text-align': 'center'
                });
                
                // Align center inside table cells
                if ($el.parent().is('td')) {
                    $el.parent().attr('style', function(i, s) { return (s || '') + ' text-align: center !important;'; });
                }
            }
        });
    }
    
    if (typeof $ !== 'undefined') {
        function mergePageHeadersIntoCards() {
            $('.pt-page-header, .sptjm-page-header, .md-page-header, .md2-page-header, .vdd-page-header').each(function() {
                var $header = $(this);
                if ($header.hasClass('merged-card-header')) return;
                
                // Find next sibling card or content-wrapper containing a card
                var $card = $header.nextAll('.card, .pt-card, .md-card, .md2-card, .sptjm-card, .sptjm-table-card, .content-wrapper').first();
                if (!$card.length) {
                    $card = $header.nextAll().find('.card, .pt-card, .md-card, .md2-card, .sptjm-card, .sptjm-table-card').first();
                } else if ($card.hasClass('content-wrapper')) {
                    $card = $card.find('.card, .pt-card, .md-card, .md2-card, .sptjm-card, .sptjm-table-card').first();
                }
                
                if ($card.length) {
                    $card.prepend($header);
                    $header.addClass('merged-card-header');
                }
            });

            // Standardize cards with legacy embedded headers (e.g. Kop Surat, Penandatangan, Complain, SKPP)
            $('.card').each(function() {
                var $card = $(this);
                if ($card.closest('.modal').length) return; // Ignore modals
                
                var $header = $card.find('h5.card-header, .card-header').first();
                if ($header.length && !$header.hasClass('merged-card-header') && !$header.hasClass('modal-header')) {
                    if ($header.closest('.card-body').length) return; // Ignore nested headers
                    
                    var $hr = $header.next('hr');
                    if (!$hr.length) {
                        $hr = $header.parent().next('hr');
                    }
                    
                    var titleText = $header.clone().children().remove().end().text().trim();
                    var $parentDiv = $header.parent();
                    var $actionBtn = null;
                    if (!$parentDiv.hasClass('card') && ($parentDiv.hasClass('d-flex') || ($parentDiv.is('div') && $parentDiv.css('display') === 'flex'))) {
                        $actionBtn = $parentDiv.find('button, a').not($header);
                        $header = $parentDiv;
                    }
                    
                    $header.addClass('merged-card-header');
                    $header.html('<div class="page-titles"><h1>' + titleText + '</h1></div>');
                    
                    if ($actionBtn && $actionBtn.length) {
                        var $btnContainer = $('<div class="d-flex align-items-center gap-2"></div>');
                        $btnContainer.append($actionBtn);
                        $header.append($btnContainer);
                    }
                    
                    if ($hr.length) {
                        $hr.hide();
                    }
                    
                    $card.css('padding', '0');
                    if (!$header.parent().is($card) && !$header.parent().parent().is($card)) {
                        $card.prepend($header);
                    }
                }
            });
        }

        $(document).ready(function() {
            applySptjmGlobalStyles();
            mergePageHeadersIntoCards();
            
            // Ensure styles apply after any ajax calls or datatable renders
            $(document).ajaxComplete(function() {
                applySptjmGlobalStyles();
                mergePageHeadersIntoCards();
            });
            
            if ($.fn.dataTable) {
                // Ensure drawCallback triggers the function globally for all DataTables
                $(document).on('draw.dt', function() {
                    applySptjmGlobalStyles();
                    mergePageHeadersIntoCards();
                });
            }
        });
    }
})();
</script>
