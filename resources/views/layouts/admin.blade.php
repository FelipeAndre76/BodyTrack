<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - BodyTrack')</title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>
<body>
    <div class="admin-mobile-topbar">
        <div class="admin-mobile-brand">
            <img src="{{ asset('assets/images/logo.png') }}" alt="BodyTrack">
            <span>Admin</span>
        </div>

        <button type="button" class="admin-mobile-menu-btn" id="adminMobileMenuBtn">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="admin-layout">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-sidebar-header">
                <div class="brand-logo">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="BodyTrack">
                </div>

                <span>Painel administrativo</span>
            </div>

            <nav class="admin-nav">
                <div class="admin-nav-label">Principal</div>

                <a href="{{ route('admin.dashboard') }}"
                   class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Usuários</span>
                </a>

                <div class="admin-nav-label">Catálogo</div>

                <a href="{{ route('admin.exercises.index') }}"
                   class="menu-link {{ request()->routeIs('admin.exercises.*') ? 'active' : '' }}">
                    <i class="bi bi-activity"></i>
                    <span>Exercícios</span>
                </a>

                <a href="{{ route('admin.categories.index') }}"
                   class="menu-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i>
                    <span>Categorias</span>
                </a>

                <a href="{{ route('admin.photos.index') }}"
                   class="menu-link {{ request()->routeIs('admin.photos.*') ? 'active' : '' }}">
                    <i class="bi bi-image"></i>
                    <span>Fotos dos Aparelhos</span>
                </a>

                <div class="admin-nav-label">Relatórios</div>

                <a href="{{ route('admin.logs.index') }}"
                   class="menu-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                    <i class="bi bi-list-check"></i>
                    <span>Logs</span>
                </a>
            </nav>

            <div class="admin-sidebar-footer">
                <a href="{{ route('dashboard') }}" class="menu-link secondary">
                    <i class="bi bi-arrow-left-circle"></i>
                    <span>Voltar para o App</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="menu-link logout-btn">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Sair</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>

        <main class="admin-content">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuBtn = document.getElementById('adminMobileMenuBtn');
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('adminSidebarBackdrop');

            function closeSidebar() {
                sidebar.classList.remove('open');
                backdrop.classList.remove('open');
            }

            if (menuBtn && sidebar && backdrop) {
                menuBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('open');
                    backdrop.classList.toggle('open');
                });

                backdrop.addEventListener('click', closeSidebar);

                document.querySelectorAll('.admin-sidebar .menu-link').forEach(link => {
                    link.addEventListener('click', closeSidebar);
                });
            }
        });
    </script>

    @yield('scripts')
</body>
</html>
