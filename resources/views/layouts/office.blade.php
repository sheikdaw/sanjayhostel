<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sanjay PG Hostel')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap 5 CSS + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        /* Sanjay PG Hostel Theme Colors */
        :root {
            --sanjay-primary: #0A1E3F;
            --sanjay-gold: #C5A028;
            --sanjay-gold-light: #E8D5A3;
            --sanjay-dark: #061428;
            --sanjay-text-light: #F5F0E8;
        }

        /* Custom scrollbar for sidebar */
        .ol-sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .ol-sidebar::-webkit-scrollbar-track {
            background: var(--sanjay-dark);
        }

        .ol-sidebar::-webkit-scrollbar-thumb {
            background: var(--sanjay-gold);
            border-radius: 4px;
        }

        /* Active nav item styling */
        .ol-nav-item.active {
            background: rgba(197, 160, 40, 0.15);
            color: var(--sanjay-gold) !important;
            border-left: 3px solid var(--sanjay-gold);
        }

        .ol-nav-item.active i {
            color: var(--sanjay-gold) !important;
        }

        /* Ensure nav items with # work properly */
        .ol-nav-item {
            cursor: pointer;
        }

        /* Sub-menu styling */
        .ol-nav-sub {
            padding-left: 1.5rem;
            margin-top: 0.25rem;
            margin-bottom: 0.25rem;
        }
        .ol-nav-sub .ol-nav-item {
            padding: 0.35rem 1rem;
            font-size: 0.8rem;
            border-left: 2px solid transparent;
        }
        .ol-nav-sub .ol-nav-item:hover {
            border-left-color: var(--sanjay-gold);
        }
        .ol-nav-sub .ol-nav-item.active {
            border-left-color: var(--sanjay-gold);
        }
        .ol-nav-label-small {
            font-size: 0.75rem;
            opacity: 0.7;
        }
    </style>

    @stack('styles')
</head>

<body>

    {{-- Mobile backdrop overlay --}}
    <div class="ol-overlay" id="olOverlay" onclick="toggleSidebar()"></div>

    {{-- ═══════════════════════════════
         SIDEBAR
    ═══════════════════════════════ --}}
    <aside class="ol-sidebar" id="olSidebar">

        {{-- Brand --}}
        <div class="ol-brand">
            <div class="ol-brand-icon">
                <i class="bi bi-house-heart"></i>
            </div>
            <div class="ol-brand-text">
                <div class="ol-brand-name">Sanjay PG</div>
                <div class="ol-brand-sub">Mens & Womens Hostel</div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="ol-nav">

            <div class="ol-nav-section">Overview</div>

            @auth
                <a href="{{ route('admin.dashboard') }}" class="ol-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span class="ol-nav-label">Dashboard</span>
                </a>

                {{-- Admin & Account Menu - Full Access --}}
                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'account')

                    {{-- Hostel Management --}}
                    <div class="ol-nav-section">Hostel Management</div>

                    <a href="{{ route('admin.hostels.index') }}" class="ol-nav-item {{ request()->routeIs('admin.hostels.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span class="ol-nav-label">Hostels</span>
                    </a>

                    <a href="{{ route('admin.room-types.index') }}" class="ol-nav-item {{ request()->routeIs('admin.room-types.*') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i>
                        <span class="ol-nav-label">Room Types</span>
                    </a>

                    <a href="{{ route('admin.rooms.index') }}" class="ol-nav-item {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}">
                        <i class="bi bi-door-open"></i>
                        <span class="ol-nav-label">Rooms</span>
                    </a>

                    <a href="{{ route('admin.beds.index') }}" class="ol-nav-item {{ request()->routeIs('admin.beds.*') ? 'active' : '' }}">
                        <i class="bi bi-bed"></i>
                        <span class="ol-nav-label">Beds</span>
                    </a>

                    {{-- Resident Management --}}
                    <div class="ol-nav-section">Resident Management</div>

                    <a href="{{ route('admin.residents.index') }}" class="ol-nav-item {{ request()->routeIs('admin.residents.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i>
                        <span class="ol-nav-label">Residents</span>
                    </a>

                    {{-- Financial Management --}}
                    <div class="ol-nav-section">Financial Management</div>

                    <a href="{{ route('admin.payments.index') }}" class="ol-nav-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                        <i class="bi bi-credit-card"></i>
                        <span class="ol-nav-label">Payments</span>
                    </a>

                    {{-- Reports --}}
                    <div class="ol-nav-section">Reports</div>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-bar-chart-line"></i>
                        <span class="ol-nav-label">Occupancy Report</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-pie-chart"></i>
                        <span class="ol-nav-label">Financial Report</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="ol-nav-label">Payment Reports</span>
                    </a>

                    {{-- System Management --}}
                    <div class="ol-nav-section">System</div>

                    <a href="{{ route('admin.users.index') }}" class="ol-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i>
                        <span class="ol-nav-label">Users</span>
                    </a>
                    
                    <a href="{{ route('admin.employee.index') }}" class="ol-nav-item {{ request()->routeIs('admin.employee.*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i>
                        <span class="ol-nav-label">Employee</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-gear"></i>
                        <span class="ol-nav-label">Settings</span>
                    </a>

                    {{-- Support --}}
                    <div class="ol-nav-section">Support</div>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-chat-dots"></i>
                        <span class="ol-nav-label">Complaints</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-megaphone"></i>
                        <span class="ol-nav-label">Notices</span>
                    </a>

                {{-- Resident Menu --}}
                @elseif(Auth::user()->role == 'stay' || Auth::user()->role == 'resident')
                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-door-open"></i>
                        <span class="ol-nav-label">My Room</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-building"></i>
                        <span class="ol-nav-label">Facilities</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-tools"></i>
                        <span class="ol-nav-label">Maintenance</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-credit-card"></i>
                        <span class="ol-nav-label">Payments</span>
                    </a>

                    <div class="ol-nav-section">Community</div>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-calendar-event"></i>
                        <span class="ol-nav-label">Events</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-chat-dots"></i>
                        <span class="ol-nav-label">Complaints</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-megaphone"></i>
                        <span class="ol-nav-label">Notices</span>
                    </a>

                {{-- Staff/Warden Menu --}}
                @elseif(Auth::user()->role == 'staff' || Auth::user()->role == 'warden')
                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-people"></i>
                        <span class="ol-nav-label">Residents</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-clipboard-check"></i>
                        <span class="ol-nav-label">Attendance</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-tools"></i>
                        <span class="ol-nav-label">Maintenance</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-chat-dots"></i>
                        <span class="ol-nav-label">Complaints</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-person-plus"></i>
                        <span class="ol-nav-label">Visitors Log</span>
                    </a>

                {{-- Owner/Manager Menu --}}
                @elseif(Auth::user()->role == 'owner' || Auth::user()->role == 'manager')
                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-people"></i>
                        <span class="ol-nav-label">All Residents</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-currency-rupee"></i>
                        <span class="ol-nav-label">Finance</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-building"></i>
                        <span class="ol-nav-label">Properties</span>
                    </a>

                    <a href="#" class="ol-nav-item">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <span class="ol-nav-label">Reports</span>
                    </a>
                @endif
            @endauth

        </nav>

        {{-- User footer --}}
        <div class="ol-sidebar-footer">
            <div class="ol-user-avatar" style="background: var(--sanjay-gold); color: var(--sanjay-primary);">
                {{ strtoupper(substr(auth()->user()->name ?? 'R', 0, 2)) }}
            </div>
            <div class="ol-sidebar-footer-text">
                <div class="ol-user-name">{{ auth()->user()->name ?? 'Resident' }}</div>
                <div class="ol-user-role">{{ ucfirst(auth()->user()->role ?? 'Guest') }}</div>
            </div>
        </div>

    </aside>

    {{-- ═══════════════════════════════
         TOP HEADER
    ═══════════════════════════════ --}}
    <header class="ol-header" id="olHeader">

        <button class="ol-header-toggle" id="olToggleBtn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>

        <nav class="ol-breadcrumb" aria-label="Breadcrumb">
            <span>Sanjay PG</span>
            <i class="bi bi-chevron-right"></i>
            <span class="page-title">@yield('page_title', 'Dashboard')</span>
        </nav>

        <div class="ol-header-actions">



            {{-- User avatar dropdown --}}
            <div class="dropdown">
                <div class="ol-header-avatar" data-bs-toggle="dropdown" aria-expanded="false" role="button"
                    tabindex="0" style="background: var(--sanjay-gold); color: var(--sanjay-primary);">
                    {{ strtoupper(substr(auth()->user()->name ?? 'R', 0, 2)) }}
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border"
                    style="min-width:200px; border-radius:12px; font-size:0.82rem; margin-top:8px;">
                    <li>
                        <div class="px-3 py-2 border-bottom">
                            <div style="font-weight:600; color: var(--sanjay-primary);">
                                {{ auth()->user()->name ?? 'Resident Name' }}
                            </div>
                            <div style="font-size:0.72rem; color:#9ca3af;">
                                {{ auth()->user()->email ?? 'resident@sanjayhostel.com' }}</div>
                            <div style="font-size:0.65rem; color: var(--sanjay-gold); margin-top:2px;">
                                <i class="bi bi-house-heart"></i> Sanjay PG Hostel
                            </div>
                        </div>
                    </li>
                    <li><a class="dropdown-item py-2" href="#">
                            <i class="bi bi-person me-2 text-muted"></i>My Profile</a>
                    </li>
                    <li><a class="dropdown-item py-2" href="#">
                            <i class="bi bi-gear me-2 text-muted"></i>Settings</a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item py-2 text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    {{-- ═══════════════════════════════
         CUSTOM FLASH MESSAGES (Top-Right)
    ═══════════════════════════════ --}}
    <div id="flashMessageContainer"></div>

    {{-- ═══════════════════════════════
         MAIN CONTENT
    ═══════════════════════════════ --}}
    <main class="ol-main" id="olMain">
        @yield('content')
    </main>

    {{-- ═══════════════════════════════
         SEARCH MODAL
    ═══════════════════════════════ --}}
    <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
            <div class="modal-content"
                style="border-radius:16px; border:none; box-shadow:0 24px 48px rgba(0,0,0,0.12);">
                <div class="modal-body p-0">
                    <div class="d-flex align-items-center px-4 py-3 border-bottom gap-2">
                        <i class="bi bi-search" style="color:var(--sanjay-gold); font-size:16px;"></i>
                        <input type="text" class="form-control border-0 shadow-none p-0"
                            placeholder="Search residents, rooms, payments…" style="font-size:0.9rem;"
                            id="globalSearch" autocomplete="off">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            style="font-size:11px;"></button>
                    </div>
                    <div class="px-4 py-3" id="searchResults">
                        <p class="text-muted mb-0" style="font-size:0.8rem;">Start typing to search…</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════
         HELP MODAL
    ═══════════════════════════════ --}}
    <div class="modal fade" id="helpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:16px; border:none;">
                <div class="modal-header border-0"
                    style="background: var(--sanjay-primary); color: white; border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title"><i class="bi bi-question-circle me-2"></i>Help & Support</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h6 class="fw-bold" style="color: var(--sanjay-primary);">
                            <i class="bi bi-house-heart me-2" style="color: var(--sanjay-gold);"></i>Welcome to Sanjay
                            PG Hostel
                        </h6>
                        <p class="text-muted small">For any assistance regarding your stay, room, payments, or
                            facilities, please contact the hostel management.</p>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center gap-3 p-2 rounded" style="background: #f8f9fa;">
                            <i class="bi bi-telephone" style="color: var(--sanjay-gold); font-size: 18px;"></i>
                            <div>
                                <div class="fw-semibold" style="font-size: 13px;">Emergency Contact</div>
                                <div class="text-muted" style="font-size: 12px;">+91 98765 43210</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 p-2 rounded" style="background: #f8f9fa;">
                            <i class="bi bi-envelope" style="color: var(--sanjay-gold); font-size: 18px;"></i>
                            <div>
                                <div class="fw-semibold" style="font-size: 13px;">Email Support</div>
                                <div class="text-muted" style="font-size: 12px;">support@sanjayhostel.com</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 p-2 rounded" style="background: #f8f9fa;">
                            <i class="bi bi-clock" style="color: var(--sanjay-gold); font-size: 18px;"></i>
                            <div>
                                <div class="fw-semibold" style="font-size: 13px;">Office Hours</div>
                                <div class="text-muted" style="font-size: 12px;">24/7 Available</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn"
                        style="background: var(--sanjay-gold); color: white; border-radius: 30px; padding: 8px 24px;"
                        data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════
         NOTIFICATIONS OFFCANVAS
    ═══════════════════════════════ --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="notifOffcanvas"
        style="width:340px; border-left:1px solid #e5e7eb;">
        <div class="offcanvas-header border-bottom" style="padding:1rem 1.2rem;">
            <h6 class="offcanvas-title fw-bold" style="color: var(--sanjay-primary); font-size:0.9rem;">
                <i class="bi bi-bell me-2" style="color: var(--sanjay-gold);"></i>Notifications
            </h6>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            @stack('notifications')
            <div class="px-4 py-5 text-center">
                <i class="bi bi-bell-slash" style="font-size:36px; color:#d1d5db;"></i>
                <p class="mt-2 mb-0" style="font-size:0.8rem; color:#9ca3af;">No new notifications</p>
            </div>
        </div>
    </div>

    {{-- Custom JS --}}
    <script src="{{ asset('js/app.js') }}"></script>

    <script>
        // ── Sidebar toggle ──
        function toggleSidebar() {
            const sidebar = document.getElementById('olSidebar');
            const header = document.getElementById('olHeader');
            const main = document.getElementById('olMain');
            const overlay = document.getElementById('olOverlay');
            const isMobile = window.innerWidth <= 1100;

            if (isMobile) {
                const isOpen = sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('visible', isOpen);
                document.body.style.overflow = isOpen ? 'hidden' : '';
            } else {
                const isCollapsed = sidebar.classList.toggle('collapsed');
                header.classList.toggle('sidebar-collapsed', isCollapsed);
                main.classList.toggle('sidebar-collapsed', isCollapsed);
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }
        }

        // Close sidebar when overlay is clicked
        document.getElementById('olOverlay').addEventListener('click', function() {
            document.getElementById('olSidebar').classList.remove('mobile-open');
            this.classList.remove('visible');
            document.body.style.overflow = '';
        });

        // Close sidebar on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const sidebar = document.getElementById('olSidebar');
                const overlay = document.getElementById('olOverlay');
                if (sidebar.classList.contains('mobile-open')) {
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('visible');
                    document.body.style.overflow = '';
                }
            }
        });

        // Restore desktop collapse state on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth > 1100) {
                const wasCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
                if (wasCollapsed) {
                    document.getElementById('olSidebar').classList.add('collapsed');
                    document.getElementById('olHeader').classList.add('sidebar-collapsed');
                    document.getElementById('olMain').classList.add('sidebar-collapsed');
                }
            }
        });

        // Custom flash message function (top-right)
        function showFlashMessage(message, type = 'success') {
            const container = document.getElementById('flashMessageContainer');

            const config = {
                success: {
                    icon: 'bi-check-circle-fill',
                    bg: '#10b981',
                    border: '#059669'
                },
                error: {
                    icon: 'bi-exclamation-circle-fill',
                    bg: '#dc2626',
                    border: '#b91c1c'
                },
                info: {
                    icon: 'bi-info-circle-fill',
                    bg: '#3b82f6',
                    border: '#2563eb'
                }
            };

            const currentConfig = config[type] || config.success;

            const flashDiv = document.createElement('div');
            flashDiv.className = 'custom-flash-message';
            flashDiv.innerHTML = `
                <div class="d-flex align-items-center gap-2 p-3 rounded shadow-lg text-white"
                     style="background: ${currentConfig.bg}; border-left: 4px solid ${currentConfig.border};">
                    <i class="${currentConfig.icon}" style="font-size: 18px;"></i>
                    <div style="flex: 1; font-size: 13px; line-height: 1.4;">${message}</div>
                    <button type="button" class="btn-close btn-close-white" style="font-size: 10px;" onclick="this.closest('.custom-flash-message').remove()"></button>
                </div>
            `;

            container.appendChild(flashDiv);

            setTimeout(() => {
                if (flashDiv && flashDiv.parentNode) {
                    flashDiv.remove();
                }
            }, 5000);
        }

        // ── Global Search functionality ──
        $('#globalSearch').on('keyup', function() {
            const query = $(this).val();
            const resultsDiv = $('#searchResults');

            if (query.length < 2) {
                resultsDiv.html(
                    '<p class="text-muted mb-0" style="font-size:0.8rem;">Type at least 2 characters to search…</p>'
                    );
                return;
            }

            resultsDiv.html(`
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" style="width: 20px; height: 20px;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2" style="font-size:0.8rem;">Searching for "${query}"...</p>
                </div>
            `);

            // You can implement actual search logic here
            setTimeout(() => {
                resultsDiv.html(`
                    <div class="d-flex align-items-center gap-2 p-2 border-bottom">
                        <i class="bi bi-person" style="color: var(--sanjay-gold);"></i>
                        <div>
                            <div class="fw-semibold" style="font-size: 13px;">No results found</div>
                            <div class="text-muted" style="font-size: 11px;">Try adjusting your search term</div>
                        </div>
                    </div>
                `);
            }, 500);
        });

        // ── Close search modal on Escape ──
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const searchModal = bootstrap.Modal.getInstance(document.getElementById('searchModal'));
                if (searchModal) {
                    searchModal.hide();
                }
            }
        });

        // ── Prevent default for all # links ──
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('a[href="#"]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (!this.classList.contains('ol-nav-item')) {
                        showFlashMessage('Feature coming soon!', 'info');
                    }
                });
            });
        });

        // ── Active nav item highlighting ──
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            document.querySelectorAll('.ol-nav-item').forEach(function(item) {
                const href = item.getAttribute('href');
                if (href && href !== '#' && currentPath.includes(href)) {
                    item.classList.add('active');
                }
            });
        });
    </script>

    @stack('scripts')
</body>

</html>
