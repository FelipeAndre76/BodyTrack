@extends('layouts.admin')

@section('title', 'Categorias')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="admin-title mb-0">Categorias</h1>
        <small class="text-secondary">Gerencie os grupos musculares</small>
    </div>

    <a href="{{ route('admin.categories.create') }}" class="btn-admin">
        <i class="bi bi-plus-circle"></i>
        Nova Categoria
    </a>
</div>

@if(session('success'))
<div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

<div class="exercise-grid">
    @forelse($categories as $category)
    <div class="exercise-admin-card">
        <div class="exercise-admin-image">
            <i class="bi {{ $category->icon ?? 'bi-activity' }}"></i>
        </div>

        <div class="exercise-admin-body">
            <span class="exercise-category">
                {{ $category->exercises_count }} exercício(s)
            </span>

            <h4>{{ $category->name }}</h4>

            <small class="text-secondary">
                {{ $category->name }}
            </small>

            <div class="exercise-actions">
                <a href="{{ route('admin.categories.edit', $category) }}" class="btn-edit">
                    <i class="bi bi-pencil"></i>
                </a>

              <form method="POST"
      action="{{ route('admin.categories.destroy', $category) }}"
      class="delete-category-form"
      data-exercises="{{ $category->exercises_count }}">

    @csrf
    @method('DELETE')

    <button type="submit" class="btn-delete">
        <i class="bi bi-trash"></i>
    </button>
</form>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="panel">Nenhuma categoria cadastrada.</div>
    @endforelse
</div>

<div class="mt-4">
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
