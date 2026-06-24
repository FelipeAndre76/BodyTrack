@extends('layouts.admin')

@section('title', 'Categorias')

@section('content')

<div class="admin-premium-header">
    <div>
        <span class="admin-kicker">Organização do catálogo</span>
        <h1>Categorias</h1>
        <p>Gerencie grupos musculares e organize a biblioteca de exercícios do BodyTrack.</p>
    </div>

    <div class="admin-header-actions">
        <a href="{{ route('admin.exercises.index') }}" class="btn-admin-secondary">
            <i class="bi bi-activity"></i>
            Exercícios
        </a>

        <a href="{{ route('admin.categories.create') }}" class="btn-admin">
            <i class="bi bi-plus-circle"></i>
            Nova categoria
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

<div class="category-premium-stats mb-4">
    <div class="category-premium-stat main">
        <span>Total de categorias</span>
        <strong>{{ $totalCategories }}</strong>
        <small>Estrutura principal do catálogo de exercícios</small>
    </div>

    <div class="category-premium-stat">
        <i class="bi bi-diagram-3-fill"></i>
        <span>Com exercícios</span>
        <strong>{{ $categoriesWithExercises }}</strong>
    </div>

    <div class="category-premium-stat warning">
        <i class="bi bi-exclamation-circle"></i>
        <span>Vazias</span>
        <strong>{{ $emptyCategories }}</strong>
    </div>
</div>

<form method="GET" action="{{ route('admin.categories.index') }}" class="category-premium-filter mb-4">
    <div class="category-search-field">
        <i class="bi bi-search"></i>

        <input type="text"
               name="q"
               value="{{ request('q') }}"
               placeholder="Buscar por nome ou ícone...">
    </div>

    <select name="status">
        <option value="">Todas as categorias</option>
        <option value="with-exercises" {{ request('status') === 'with-exercises' ? 'selected' : '' }}>
            Com exercícios
        </option>
        <option value="empty" {{ request('status') === 'empty' ? 'selected' : '' }}>
            Vazias
        </option>
    </select>

    <button type="submit" class="btn-admin">
        <i class="bi bi-funnel"></i>
        Filtrar
    </button>

    <a href="{{ route('admin.categories.index') }}" class="btn-admin-secondary">
        <i class="bi bi-x-circle"></i>
        Limpar
    </a>
</form>

<div class="category-premium-grid">
    @forelse($categories as $category)
        <div class="category-premium-card">
            <div class="category-premium-icon">
                <i class="bi {{ $category->icon ?? 'bi-activity' }}"></i>
            </div>

            <div class="category-premium-body">
                <div class="category-premium-meta">
                    <span>{{ $category->exercises_count }} exercício(s)</span>

                    @if($category->exercises_count > 0)
                        <strong class="category-status active">
                            <i class="bi bi-check-circle"></i>
                            Em uso
                        </strong>
                    @else
                        <strong class="category-status empty">
                            <i class="bi bi-exclamation-circle"></i>
                            Vazia
                        </strong>
                    @endif
                </div>

                <h3>{{ $category->name }}</h3>

                <p>
                    Ícone: {{ $category->icon ?: 'bi-activity' }}
                </p>

                <div class="category-premium-actions">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn-edit" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </a>

                    <form method="POST"
                          action="{{ route('admin.categories.destroy', $category) }}"
                          class="delete-category-form"
                          data-exercises="{{ $category->exercises_count }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn-delete" title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="admin-empty-state">
            Nenhuma categoria encontrada.
        </div>
    @endforelse
</div>

<div class="photo-pagination-wrapper">
    {{ $categories->onEachSide(1)->links('pagination::bootstrap-5') }}
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-category-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const count = Number(form.dataset.exercises || 0);

            Swal.fire({
                title: count > 0 ? 'Excluir categoria e exercícios?' : 'Excluir categoria?',
                html: count > 0
                    ? `Esta categoria possui <b>${count}</b> exercício(s).<br>Todos serão excluídos também.`
                    : 'Esta ação não poderá ser desfeita.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: count > 0 ? 'Sim, excluir tudo' : 'Sim, excluir',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                background: '#050705',
                color: '#fff',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
