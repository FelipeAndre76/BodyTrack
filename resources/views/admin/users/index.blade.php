@extends('layouts.admin')

@section('title', 'Usuários')

@section('content')

<div class="admin-title">
    Usuários
</div>

<div class="admin-user-stats-grid">
    <div class="admin-user-stat-card">
        <span>Total de Usuários</span>
        <strong>{{ $totalUsers }}</strong>
    </div>

    <div class="admin-user-stat-card admin">
        <span>Administradores</span>
        <strong>{{ $totalAdmins }}</strong>
    </div>

    <div class="admin-user-stat-card common">
        <span>Usuários Comuns</span>
        <strong>{{ $totalCommonUsers }}</strong>
    </div>
</div>

<div class="admin-user-search-wrapper">
    <div class="admin-user-search-box">
        <i class="bi bi-search"></i>

        <input type="text"
               id="adminUserSearchInput"
               class="admin-user-search-input"
               placeholder="Buscar usuário por nome ou e-mail...">
    </div>

    <div id="adminUserNoResults" class="admin-user-no-results">
        Nenhum usuário encontrado.
    </div>
</div>

<div class="admin-users-grid">
    @foreach($users as $user)

        <div class="admin-user-card"
             data-search="{{ strtolower($user->name . ' ' . $user->email) }}">

            <div class="admin-user-avatar">
                <i class="bi bi-person"></i>
            </div>

            <div class="admin-user-name">
                {{ mb_strtoupper($user->name, 'UTF-8') }}
            </div>

            <div class="admin-user-email">
                {{ $user->email }}
            </div>

            <div class="admin-user-badges">
                @if($user->is_active)
                    <div class="admin-user-status active">
                        <i class="bi bi-check-circle"></i>
                        Ativo
                    </div>
                @else
                    <div class="admin-user-status inactive">
                        <i class="bi bi-slash-circle"></i>
                        Bloqueado
                    </div>
                @endif

                @if($user->is_admin)
                    <div class="admin-user-role admin">
                        <i class="bi bi-shield-check"></i>
                        Administrador
                    </div>
                @else
                    <div class="admin-user-role user">
                        <i class="bi bi-person"></i>
                        Usuário
                    </div>
                @endif
            </div>

            <div class="admin-user-actions">

                @if(auth()->id() !== $user->id)
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
                @endif

                @if(auth()->id() !== $user->id)
                    <form method="POST"
                          action="{{ route('admin.users.toggle-admin', $user) }}"
                          class="user-toggle-admin-form"
                          data-user-name="{{ $user->name }}"
                          data-is-admin="{{ $user->is_admin ? '1' : '0' }}">

                        @csrf
                        @method('PATCH')

                        <button type="submit" class="btn-admin-mini green w-100">
                            {{ $user->is_admin ? 'Remover Admin' : 'Admin' }}
                        </button>
                    </form>
                @endif

                @if(auth()->id() !== $user->id)
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
                @endif

            </div>

        </div>

    @endforeach
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

    const searchInput = document.getElementById('adminUserSearchInput');
    const noResults = document.getElementById('adminUserNoResults');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.admin-user-card');

            let visibleCount = 0;

            cards.forEach(card => {
                const searchText = card.dataset.search || '';

                if (searchText.includes(term)) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (noResults) {
                noResults.classList.toggle('active', visibleCount === 0);
            }
        });
    }

});
</script>
@endsection
