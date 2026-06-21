<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - BodyTrack')</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="brand-logo mb-5">
                <img src="{{ asset('assets/images/logo.png') }}" alt="BodyTrack">
            </div>
            <a href="{{ route('admin.dashboard') }}" class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid"></i>
                Dashboard Admin
            </a>
            <a href="{{ route('admin.exercises.index') }}" class="menu-link">
                <i class="bi bi-activity"></i>
                Exercícios
            </a>
            <a href="{{ route('admin.categories.index') }}" class="menu-link">
                <i class="bi bi-tags"></i>
                Categorias
            </a>
            <a href="{{ route('admin.photos.index') }}" class="menu-link {{ request()->routeIs('admin.photos.*') ? 'active' : '' }}">
                <i class="bi bi-image"></i>
                Fotos dos Aparelhos
            </a>
            <a href="{{ route('admin.users.index') }}" class="menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                Usuários
            </a>
            <a href="{{ route('admin.logs.index') }}" class="menu-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                <i class="bi bi-list-check"></i>
                     Logs
            </a>
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="bi bi-arrow-left-circle"></i>
                Voltar para o App
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="menu-link logout-btn">
                    <i class="bi bi-box-arrow-right"></i>
                    Sair
                </button>
            </form>
        </aside>
        <main class="admin-content">
            @yield('content')
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('scripts')
</body>
</html>
