@extends('layouts.admin')

@section('title', 'Exercícios')

@section('content')

<div class="admin-premium-header">
    <div>
        <span class="admin-kicker">Catálogo técnico</span>
        <h1>Exercícios</h1>
        <p>Gerencie exercícios, aparelhos, categorias e fotos usadas no treino dos usuários.</p>
    </div>

    <div class="admin-header-actions">
        <a href="{{ route('admin.photos.index') }}" class="btn-admin-secondary">
            <i class="bi bi-image"></i>
            Fotos
        </a>

        <a href="{{ route('admin.exercises.create') }}" class="btn-admin">
            <i class="bi bi-plus-circle"></i>
            Novo exercício
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="exercise-premium-stats mb-4">
    <div class="exercise-premium-stat main">
        <span>Total de exercícios</span>
        <strong>{{ $totalExercises }}</strong>
        <small>{{ $totalCategories }} categoria(s) cadastrada(s)</small>
    </div>

    <div class="exercise-premium-stat">
        <i class="bi bi-image-fill"></i>
        <span>Com foto</span>
        <strong>{{ $totalWithPhoto }}</strong>
    </div>

    <div class="exercise-premium-stat warning">
        <i class="bi bi-camera"></i>
        <span>Sem foto</span>
        <strong>{{ $totalWithoutPhoto }}</strong>
    </div>
</div>

<form method="GET" action="{{ route('admin.exercises.index') }}" class="exercise-premium-filter mb-4">
    <div class="exercise-search-field">
        <i class="bi bi-search"></i>
        <input type="text"
               name="q"
               value="{{ request('q') }}"
               placeholder="Buscar por exercício, aparelho ou categoria...">
    </div>

    <select name="category">
        <option value="">Todas as categorias</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                {{ $category->name }}
            </option>
        @endforeach
    </select>

    <select name="photo">
        <option value="">Todas as fotos</option>
        <option value="with-photo" {{ request('photo') === 'with-photo' ? 'selected' : '' }}>Com foto</option>
        <option value="without-photo" {{ request('photo') === 'without-photo' ? 'selected' : '' }}>Sem foto</option>
    </select>

    <button type="submit" class="btn-admin">
        <i class="bi bi-funnel"></i>
        Filtrar
    </button>

    <a href="{{ route('admin.exercises.index') }}" class="btn-admin-secondary">
        <i class="bi bi-x-circle"></i>
        Limpar
    </a>
</form>

<div class="exercise-premium-grid">
    @forelse($exercises as $exercise)
        <div class="exercise-premium-card">
            <div class="exercise-premium-image">
                @if($exercise->image_path)
                    <img src="{{ asset('storage/'.$exercise->image_path) }}" alt="{{ $exercise->name }}">
                @else
                    <div class="exercise-premium-placeholder">
                        <i class="bi bi-image"></i>
                        <span>Sem foto</span>
                    </div>
                @endif
            </div>

            <div class="exercise-premium-body">
                <div class="exercise-premium-meta">
                    <span>{{ $exercise->category->name ?? 'Sem categoria' }}</span>

                    @if($exercise->image_path)
                        <strong class="photo-status complete">
                            <i class="bi bi-check-circle"></i>
                            Completo
                        </strong>
                    @else
                        <strong class="photo-status pending">
                            <i class="bi bi-exclamation-circle"></i>
                            Pendente
                        </strong>
                    @endif
                </div>

                <h3>{{ $exercise->name }}</h3>

                <p>
                    {{ $exercise->machine_name ?: 'Sem aparelho informado' }}
                </p>

                <div class="exercise-premium-actions">
                    <a href="{{ route('admin.exercises.edit', $exercise) }}" class="btn-edit" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </a>

                    <form method="POST"
                          action="{{ route('admin.exercises.destroy', $exercise) }}"
                          class="delete-exercise-form">
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
            Nenhum exercício encontrado.
        </div>
    @endforelse
</div>

<div class="photo-pagination-wrapper">
    {{ $exercises->onEachSide(1)->links('pagination::bootstrap-5') }}
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-exercise-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Excluir exercício?',
                text: 'Esta ação não poderá ser desfeita.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                background: '#050705',
                color: '#ffffff',
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
