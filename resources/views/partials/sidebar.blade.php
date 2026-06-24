<aside class="sidebar">
    <div class="brand-logo">
        <img src="{{ asset('assets/images/logo.png') }}" alt="BodyTrack Logo">
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-section">
            <span class="sidebar-section-title">Visão geral</span>

            <a href="{{ route('dashboard') }}" class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('evolution.index') }}" class="menu-link {{ request()->routeIs('evolution.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i>
                <span>Evolução</span>
            </a>

            <a href="{{ route('smart-goals.index') }}" class="menu-link {{ request()->routeIs('smart-goals.*') ? 'active' : '' }}">
                <i class="bi bi-bullseye"></i>
                <span>Metas Inteligentes</span>
            </a>

            <a href="{{ route('weekly-summary.index') }}" class="menu-link {{ request()->routeIs('weekly-summary.*') ? 'active' : '' }}">
                <i class="bi bi-calendar2-week"></i>
                <span>Resumo Semanal</span>
            </a>
        </div>

        <div class="sidebar-section">
            <span class="sidebar-section-title">Comunidade</span>

            <a href="{{ route('community.index') }}" class="menu-link {{ request()->routeIs('community.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Feed Social</span>
            </a>
        </div>

        <div class="sidebar-section">
            <span class="sidebar-section-title">Corpo</span>

            <a href="{{ route('body-profile.create') }}" class="menu-link {{ request()->routeIs('body-profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i>
                <span>Perfil Corporal</span>
            </a>

            <a href="{{ route('weights.create') }}" class="menu-link {{ request()->routeIs('weights.*') ? 'active' : '' }}">
                <i class="bi bi-speedometer"></i>
                <span>Pesagens</span>
            </a>

            <a href="{{ route('check-ins.index') }}" class="menu-link {{ request()->routeIs('check-ins.index') ? 'active' : '' }}">
                <i class="bi bi-clipboard2-pulse"></i>
                <span>Check-in Semanal</span>
            </a>

            <a href="{{ route('check-ins.gallery') }}" class="menu-link {{ request()->routeIs('check-ins.gallery') ? 'active' : '' }}">
                <i class="bi bi-images"></i>
                <span>Fotos de Evolução</span>
            </a>

            <a href="{{ route('water.index') }}" class="menu-link {{ request()->routeIs('water.*') ? 'active' : '' }}">
                <i class="bi bi-droplet-half"></i>
                <span>Água</span>
            </a>
        </div>

        <div class="sidebar-section">
            <span class="sidebar-section-title">Nutrição</span>

            <a href="{{ route('nutrition.index') }}" class="menu-link {{ request()->routeIs('nutrition.index') ? 'active' : '' }}">
                <i class="bi bi-egg-fried"></i>
                <span>Registrar Refeição</span>
            </a>

            <a href="{{ route('nutrition.meal-plan') }}" class="menu-link {{ request()->routeIs('nutrition.meal-plan') ? 'active' : '' }}">
                <i class="bi bi-calendar2-check"></i>
                <span>Plano Alimentar</span>
            </a>

            <a href="{{ route('nutrition.history') }}" class="menu-link {{ request()->routeIs('nutrition.history') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i>
                <span>Histórico Alimentar</span>
            </a>

            <a href="{{ route('foods.mine') }}" class="menu-link {{ request()->routeIs('foods.*') ? 'active' : '' }}">
                <i class="bi bi-basket"></i>
                <span>Meus Alimentos</span>
            </a>
        </div>

        <div class="sidebar-section">
            <span class="sidebar-section-title">Treinos</span>

            <a href="{{ route('workouts.index') }}" class="menu-link {{ request()->routeIs('workouts.index') ? 'active' : '' }}">
                <i class="bi bi-activity"></i>
                <span>Montar Treino</span>
            </a>

            <a href="{{ route('workouts.history') }}" class="menu-link {{ request()->routeIs('workouts.history') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>Histórico de Treinos</span>
            </a>
        </div>

        <div class="sidebar-section">
            <span class="sidebar-section-title">Sistema</span>

            <a href="{{ route('settings') }}" class="menu-link {{ request()->routeIs('settings') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span>Configurações</span>
            </a>

            <a href="{{ route('notifications.index') }}" class="menu-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                <i class="bi bi-bell"></i>
                <span>Notificações</span>
            </a>

            @if(auth()->user()?->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="menu-link admin-access-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i>
                    <span>Dashboard Admin</span>
                </a>
            @endif
        </div>
    </nav>

    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button type="submit" class="menu-link logout-btn">
            <i class="bi bi-box-arrow-right"></i>
            Sair
        </button>
    </form>

</aside>
