@extends('layouts.admin')

@section('title', 'Logs Administrativos')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="admin-title mb-0">Logs Administrativos</h1>
        <small class="text-secondary">Histórico de ações realizadas no painel admin</small>
    </div>

    <a href="{{ route('admin.logs.export') }}" class="btn-admin">
        <i class="bi bi-download"></i>
        Baixar Relatório
    </a>
</div>

<div class="admin-log-search-wrapper">
    <div class="admin-log-search-box">
        <i class="bi bi-search"></i>

        <input type="text"
               id="adminLogSearchInput"
               class="admin-log-search-input"
               placeholder="Buscar por administrador, ação ou descrição...">
    </div>

    <div id="adminLogNoResults" class="admin-log-no-results">
        Nenhum log encontrado.
    </div>
</div>

<div class="admin-log-list">

    @forelse($logs as $log)

        <div class="admin-log-card"
             data-search="{{ strtolower(($log->admin->name ?? 'admin removido') . ' ' . $log->action . ' ' . $log->description) }}">

            <div class="admin-log-icon">
                @if($log->action === 'toggle_admin')
                    <i class="bi bi-shield-check"></i>
                @elseif($log->action === 'toggle_status')
                    <i class="bi bi-slash-circle"></i>
                @elseif($log->action === 'delete_user')
                    <i class="bi bi-trash"></i>
                @else
                    <i class="bi bi-list-check"></i>
                @endif
            </div>

            <div class="admin-log-content">
                <div class="admin-log-header">
                    <strong>{{ $log->admin->name ?? 'Admin removido' }}</strong>

                    <span>
                        {{ $log->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>

                <div class="admin-log-action">
                    {{ strtoupper(str_replace('_', ' ', $log->action)) }}
                </div>

                <p>
                    {{ $log->description }}
                </p>
            </div>

        </div>

    @empty

        <div class="admin-form-card">
            <p class="text-secondary mb-0">
                Nenhum log administrativo registrado ainda.
            </p>
        </div>

    @endforelse

</div>

<div class="mt-4">
    {{ $logs->onEachSide(1)->links('pagination::bootstrap-5') }}
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('adminLogSearchInput');
    const noResults = document.getElementById('adminLogNoResults');

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const term = this.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.admin-log-card');

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
});
</script>
@endsection
