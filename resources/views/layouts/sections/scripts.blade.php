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
<!-- ─── SPTJM GLOBAL DATATABLE DEFAULTS ─── -->
<script>
    if (typeof $ !== 'undefined' && $.fn && $.fn.dataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            dom: '<"md-toolbar d-flex justify-content-between align-items-center mb-0"<"entries-wrap"l><"search-wrap"f>>rt<"dataTables_bottom mt-3"ip>',
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
                infoEmpty: 'Tidak ada data tersedia',
                info: 'Menampilkan _START_-_END_ dari _TOTAL_ entri'
            }
        });
    }
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

		const applyOpenKeysWithoutAnimation = (keys) => {
			const menuEl = getMenuEl();
			if (!menuEl || !Array.isArray(keys) || !keys.length) return;

			const toggles = menuEl.querySelectorAll('a.menu-toggle');
			toggles.forEach((a) => {
				const key = getToggleKey(a);
				if (!key || !keys.includes(key)) return;

				// Open this item and all ancestors (no menu.js API calls to avoid animations)
				let item = a.closest ? a.closest('.menu-item') : null;
				while (item) {
					item.classList.add('open');
					item = item.parentElement && item.parentElement.closest ? item.parentElement.closest('.menu-item') : null;
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

		const enforceSingleRootOpen = () => {
			// Only needed for PTS: current route sets `active open` on one root item,
			// while persisted state could open another root item after navigation.
			if (scope !== 'pts') return;
			const menuEl = getMenuEl();
			if (!menuEl) return;
			const menuInner = menuEl.querySelector('.menu-inner');
			if (!menuInner) return;

			// Root items are direct children of .menu-inner
			const rootOpenItems = Array.from(menuInner.querySelectorAll(':scope > .menu-item.open'));
			if (rootOpenItems.length <= 1) return;

			// Prefer the root item that contains the active link/item
			let keep = rootOpenItems.find((it) => it.classList.contains('active') || it.querySelector('.menu-link.active') || it.querySelector('.menu-item.active'));
			if (!keep) keep = rootOpenItems[0];

			const instance = menuEl.menuInstance || (window.Helpers && window.Helpers.mainMenu) || null;
			rootOpenItems.forEach((item) => {
				if (item === keep) return;
				try {
					const toggle = item.querySelector(':scope > a.menu-toggle');
					if (toggle && instance && typeof instance.close === 'function') {
						instance.close(toggle, true, true);
					} else {
						item.classList.remove('open');
						item.querySelectorAll('.menu-item.open').forEach((child) => child.classList.remove('open'));
					}
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
		});

		window.addEventListener('beforeunload', saveState);
	})();
</script>

<script src="{{ asset(mix('assets/js/main.js')) }}"></script>

<!-- END: Theme JS-->

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
    // Note: main.js already binds these — we only need the Helpers patch above.
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
})();
</script>
