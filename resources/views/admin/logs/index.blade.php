@extends('layouts.admin')

@section('title', 'Logs Administrativos')

@section('content')

<div class="admin-premium-header">
    <div>
        <span class="admin-kicker">Auditoria do sistema</span>
        <h1>Logs Administrativos</h1>
        <p>Acompanhe alterações críticas, gestão de usuários, fotos, exercícios e categorias.</p>
    </div>

    <div class="admin-header-actions">
        <a href="{{ route('admin.logs.export', request()->query()) }}"
           class="btn-admin-secondary"
           id="csvDownloadBtn">
            <i class="bi bi-download"></i>
            CSV
        </a>

        <a href="{{ route('admin.logs.pdf', request()->query()) }}"
           class="btn-admin">
            <i class="bi bi-file-earmark-pdf"></i>
            PDF
        </a>
    </div>
</div>

<div class="log-premium-stats mb-4">
    <div class="log-premium-stat main">
        <span>Total de registros</span>
        <strong>{{ $totalLogs }}</strong>
        <small>Histórico completo de ações administrativas</small>
    </div>

    <div class="log-premium-stat">
        <i class="bi bi-clock-history"></i>
        <span>Últimos 30 dias</span>
        <strong>{{ $logsLast30Days }}</strong>
    </div>

    <div class="log-premium-stat">
        <i class="bi bi-image"></i>
        <span>Ações em fotos</span>
        <strong>{{ $photoLogs }}</strong>
    </div>

    <div class="log-premium-stat warning">
        <i class="bi bi-shield-lock"></i>
        <span>Usuários/Admin</span>
        <strong>{{ $userLogs }}</strong>
    </div>
</div>

<div class="log-premium-toolbar mb-4">
    <div class="admin-log-dropdown" id="adminLogDropdown">
        <button type="button" class="btn-admin" id="adminLogDropdownBtn">
            <i class="bi bi-funnel"></i>
            Filtros
        </button>

        <div class="admin-log-dropdown-menu">
            <div class="admin-log-dropdown-title">
                Filtrar por ação
            </div>

            <div class="admin-log-dropdown-grid">
                @foreach($availableActions as $action => $label)
                    <label class="admin-log-dropdown-check">
                        <input type="checkbox"
                               class="admin-log-action-checkbox"
                               value="{{ $action }}"
                               {{ in_array($action, $selectedActions ?? []) ? 'checked' : '' }}>

                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <div class="admin-log-dropdown-footer">
                <button type="button" class="btn-admin-secondary" id="clearLogFilters">
                    Limpar
                </button>
            </div>
        </div>
    </div>

    <div class="log-premium-search">
        <i class="bi bi-search"></i>

        <input type="text"
               id="adminLogSearchInput"
               class="admin-log-search-input"
               placeholder="Buscar por administrador, ação ou descrição...">
    </div>
</div>

<div id="adminLogNoResults" class="admin-log-no-results">
    Nenhum log encontrado.
</div>

<div id="logsContainer">
    @include('admin.logs.partials.list', ['logs' => $logs])
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdown = document.getElementById('adminLogDropdown');
    const dropdownBtn = document.getElementById('adminLogDropdownBtn');
    const clearFiltersBtn = document.getElementById('clearLogFilters');

    const searchInput = document.getElementById('adminLogSearchInput');
    const noResults = document.getElementById('adminLogNoResults');
    const logsContainer = document.getElementById('logsContainer');

    const csvDownloadBtn = document.getElementById('csvDownloadBtn');
    const pdfDownloadBtn = document.getElementById('pdfDownloadBtn');

    function getSelectedActions() {
        return Array.from(document.querySelectorAll('.admin-log-action-checkbox:checked'))
            .map(checkbox => checkbox.value);
    }

    function buildQueryString() {
        const params = new URLSearchParams();

        getSelectedActions().forEach(action => {
            params.append('actions[]', action);
        });

        return params.toString();
    }

    function updateDownloadLinks() {
        const query = buildQueryString();

        csvDownloadBtn.href = "{{ route('admin.logs.export') }}" + (query ? '?' + query : '');
        pdfDownloadBtn.href = "{{ route('admin.logs.pdf') }}" + (query ? '?' + query : '');
    }

    async function loadFilteredLogs(url = null) {
        const query = buildQueryString();
        let requestUrl = url || "{{ route('admin.logs.filter') }}";

        if (!url) {
            requestUrl += query ? '?' + query : '';
        }

        logsContainer.style.opacity = '.45';

        try {
            const response = await fetch(requestUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const html = await response.text();

            logsContainer.innerHTML = html;
            logsContainer.style.opacity = '1';

            updateDownloadLinks();
            applySearchFilter();
        } catch (error) {
            logsContainer.style.opacity = '1';

            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Não foi possível carregar os logs.',
                background: '#050705',
                color: '#fff',
                confirmButtonColor: '#a3e635'
            });
        }
    }

    function applySearchFilter() {
        if (!searchInput) return;

        const term = searchInput.value.toLowerCase().trim();
        const cards = logsContainer.querySelectorAll('.log-timeline-item');

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
            noResults.classList.toggle('active', visibleCount === 0 && cards.length > 0);
        }
    }

    if (dropdownBtn) {
        dropdownBtn.addEventListener('click', function () {
            dropdown.classList.toggle('open');
        });
    }

    document.addEventListener('click', function (event) {
        if (!dropdown) return;

        if (!dropdown.contains(event.target)) {
            dropdown.classList.remove('open');
        }
    });

    document.querySelectorAll('.admin-log-action-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            loadFilteredLogs();
        });
    });

    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function () {
            document.querySelectorAll('.admin-log-action-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });

            loadFilteredLogs();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applySearchFilter);
    }

    document.addEventListener('click', function (event) {
        const paginationLink = event.target.closest('#logsContainer .pagination a');

        if (!paginationLink) return;

        event.preventDefault();
        loadFilteredLogs(paginationLink.href);
    });

    updateDownloadLinks();
});
</script>
@endsection
