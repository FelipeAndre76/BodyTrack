<aside class="sidebar">
    <div class="brand-logo">
        <img src="{{ asset('assets/images/logo.png') }}" alt="BodyTrack Logo">
    </div>

    <a href="{{ route('dashboard') }}" class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i>
        Dashboard
    </a>

    <a href="{{ route('body-profile.create') }}" class="menu-link {{ request()->routeIs('body-profile.*') ? 'active' : '' }}">
        <i class="bi bi-person-badge"></i>
        Perfil Corporal
    </a>

    <a href="{{ route('weights.create') }}" class="menu-link {{ request()->routeIs('weights.*') ? 'active' : '' }}">
        <i class="bi bi-speedometer"></i>
        Pesagens
    </a>

    <a href="{{ route('nutrition.index') }}" class="menu-link {{ request()->routeIs('nutrition.*') ? 'active' : '' }}">
        <i class="bi bi-egg-fried"></i>
        Nutrição
    </a>

    <a href="{{ route('water.index') }}" class="menu-link {{ request()->routeIs('water.*') ? 'active' : '' }}">
        <i class="bi bi-droplet-half"></i>
        Água
    </a>

    <a href="#" class="menu-link">
        <i class="bi bi-activity"></i>
        Treinos
    </a>

    <a href="#" class="menu-link">
        <i class="bi bi-graph-up-arrow"></i>
        Evolução
    </a>

    <a href="{{ route('settings') }}" class="menu-link {{ request()->routeIs('settings') ? 'active' : '' }}">
        <i class="bi bi-gear"></i>
        Configurações
    </a>

    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button type="submit" class="menu-link logout-btn">
            <i class="bi bi-box-arrow-right"></i>
            Sair
        </button>
    </form>

</aside>
