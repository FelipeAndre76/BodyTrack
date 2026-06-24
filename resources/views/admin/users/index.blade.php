@extends('layouts.admin')

@section('title', 'Usuários')

@section('content')

<div class="admin-premium-header">
    <div>
        <span class="admin-kicker">Controle de acesso</span>
        <h1>Usuários</h1>
        <p>Gerencie contas, permissões administrativas e bloqueios de acesso ao BodyTrack.</p>
    </div>

    <div class="admin-header-actions">
        <a href="{{ route('admin.logs.index') }}" class="btn-admin-secondary">
            <i class="bi bi-list-check"></i>
            Logs
        </a>

        <a href="{{ route('admin.dashboard') }}" class="btn-admin">
            <i class="bi bi-grid"></i>
            Dashboard
        </a>
    </div>
</div>

<div class="user-premium-stats mb-4">
    <div class="user-premium-stat main">
        <span>Total de usuários</span>
        <strong>{{ $totalUsers }}</strong>
        <small>{{ $totalActiveUsers }} ativo(s) no sistema</small>
    </div>

    <div class="user-premium-stat">
        <i class="bi bi-shield-check"></i>
        <span>Administradores</span>
        <strong>{{ $totalAdmins }}</strong>
    </div>

    <div class="user-premium-stat">
        <i class="bi bi-person"></i>
        <span>Usuários comuns</span>
        <strong>{{ $totalCommonUsers }}</strong>
    </div>

    <div class="user-premium-stat warning">
        <i class="bi bi-slash-circle"></i>
        <span>Bloqueados</span>
        <strong>{{ $totalBlockedUsers }}</strong>
    </div>
</div>

<form method="GET" action="{{ route('admin.users.index') }}" class="user-premium-filter mb-4">
    <div class="user-search-field">
        <i class="bi bi-search"></i>

        <input type="text"
               name="q"
               value="{{ request('q') }}"
               placeholder="Buscar por nome ou e-mail...">
    </div>

    <select name="role">
        <option value="">Todos os perfis</option>
        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administradores</option>
        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Usuários comuns</option>
    </select>

    <select name="status">
        <option value="">Todos os status</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativos</option>
        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Bloqueados</option>
    </select>

    <button type="submit" class="btn-admin">
        <i class="bi bi-funnel"></i>
        Filtrar
    </button>

    <a href="{{ route('admin.users.index') }}" class="btn-admin-secondary">
        <i class="bi bi-x-circle"></i>
        Limpar
    </a>
</form>

<div class="user-premium-grid">
    @forelse($users as $user)
        <div class="user-premium-card">
            <div class="user-premium-top">
                <div class="user-premium-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>

                <div class="user-premium-status">
                    @if($user->is_active)
                        <span class="user-status active">
                            <i class="bi bi-check-circle"></i>
                            Ativo
                        </span>
                    @else
                        <span class="user-status blocked">
                            <i class="bi bi-slash-circle"></i>
                            Bloqueado
                        </span>
                    @endif
                </div>
            </div>

            <div class="user-premium-body">
                <h3>{{ mb_strtoupper($user->name, 'UTF-8') }}</h3>
                <p>{{ $user->email }}</p>

                <div class="user-premium-badges">
                    @if($user->is_admin)
                        <span class="user-role admin">
                            <i class="bi bi-shield-check"></i>
                            Administrador
                        </span>
                    @else
                        <span class="user-role common">
                            <i class="bi bi-person"></i>
                            Usuário comum
                        </span>
                    @endif
                </div>

                @if(auth()->id() !== $user->id)
                    <div class="user-premium-actions">
                        <form method="POST"
                              action="{{ route('admin.users.toggle-status', $user) }}"
                              class="user-toggle-status-form"
                              data-user-name="{{ $user->name }}"
                              data-is-active="{{ $user->is_active ? '1' : '0' }}">
                            @csrf
                            @method('PATCH')

                            <button type="submit" class="btn-admin-mini orange w-100">
                                {{ $user->is_active ? 'Bloquear' : 'Desbloquear' }}
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('admin.users.toggle-admin', $user) }}"
                              class="user-toggle-admin-form"
                              data-user-name="{{ $user->name }}"
                              data-is-admin="{{ $user->is_admin ? '1' : '0' }}">
                            @csrf
                            @method('PATCH')

                            <button type="submit" class="btn-admin-mini green w-100">
                                {{ $user->is_admin ? 'Remover admin' : 'Tornar admin' }}
                            </button>
                        </form>

                        <form method="POST"
                              action="{{ route('admin.users.destroy', $user) }}"
                              class="user-delete-form"
                              data-user-name="{{ $user->name }}">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn-admin-mini red w-100">
                                Excluir
                            </button>
                        </form>
                    </div>
                @else
                    <div class="user-current-account">
                        Esta é sua conta atual
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="admin-empty-state">
            Nenhum usuário encontrado.
        </div>
    @endforelse
</div>

<div class="photo-pagination-wrapper">
    {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    async function requestMasterPassword() {
        const passwordResult = await Swal.fire({
            title: 'Senha Administrativa',
            text: 'Digite a senha mestre para continuar.',
            input: 'password',
            inputPlaceholder: 'Senha administrativa',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#a3e635',
            cancelButtonColor: '#6b7280',
            background: '#050705',
            color: '#fff',
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off'
            }
        });

        if (!passwordResult.isConfirmed || !passwordResult.value) {
            return null;
        }

        return passwordResult.value;
    }

    function appendMasterPassword(form, password) {
        let input = form.querySelector('input[name="master_password"]');

        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'master_password';
            form.appendChild(input);
        }

        input.value = password;
    }

    document.querySelectorAll('.user-toggle-admin-form').forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const userName = form.dataset.userName;
            const isAdmin = form.dataset.isAdmin === '1';

            const result = await Swal.fire({
                title: isAdmin ? 'Remover administrador?' : 'Tornar administrador?',
                text: isAdmin
                    ? `${userName} perderá acesso ao painel administrativo.`
                    : `${userName} terá acesso ao painel administrativo.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: isAdmin ? 'Sim, remover' : 'Sim, tornar admin',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#a3e635',
                cancelButtonColor: '#6b7280',
                background: '#050705',
                color: '#fff'
            });

            if (!result.isConfirmed) return;

            const password = await requestMasterPassword();

            if (!password) return;

            appendMasterPassword(form, password);
            form.submit();
        });
    });

    document.querySelectorAll('.user-toggle-status-form').forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const userName = form.dataset.userName;
            const isActive = form.dataset.isActive === '1';

            const result = await Swal.fire({
                title: isActive ? 'Bloquear usuário?' : 'Desbloquear usuário?',
                text: isActive
                    ? `${userName} não poderá acessar o sistema.`
                    : `${userName} poderá acessar o sistema novamente.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: isActive ? 'Sim, bloquear' : 'Sim, desbloquear',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280',
                background: '#050705',
                color: '#fff'
            });

            if (!result.isConfirmed) return;

            const password = await requestMasterPassword();

            if (!password) return;

            appendMasterPassword(form, password);
            form.submit();
        });
    });

    document.querySelectorAll('.user-delete-form').forEach(form => {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const userName = form.dataset.userName;

            const result = await Swal.fire({
                title: 'Excluir usuário?',
                text: `${userName} será removido permanentemente.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                background: '#050705',
                color: '#fff',
                reverseButtons: true
            });

            if (!result.isConfirmed) return;

            const password = await requestMasterPassword();

            if (!password) return;

            appendMasterPassword(form, password);
            form.submit();
        });
    });
});
</script>
@endsection
