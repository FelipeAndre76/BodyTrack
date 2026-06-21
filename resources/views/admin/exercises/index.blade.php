@extends('layouts.admin')

@section('title', 'Exercícios')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="admin-title mb-0">
            Exercícios
        </h1>

        <small class="text-secondary">
            Gerencie os exercícios cadastrados
        </small>
    </div>

    <a href="{{ route('admin.exercises.create') }}"
       class="btn-admin">
        <i class="bi bi-plus-circle"></i>
        Novo Exercício
    </a>

</div>

@if(session('success'))
    <div class="alert alert-success mb-4">
        {{ session('success') }}
    </div>
@endif

<div class="exercise-grid">

    @forelse($exercises as $exercise)

        <div class="exercise-admin-card">

            <div class="exercise-admin-image">

                @if($exercise->image_path)

                    <img src="{{ asset('storage/'.$exercise->image_path) }}"
                         alt="{{ $exercise->name }}">

                @else

                    <i class="bi bi-image"></i>

                @endif

            </div>

            <div class="exercise-admin-body">

                <span class="exercise-category">
                    {{ $exercise->category->name ?? 'Sem categoria' }}
                </span>

                <h4>
                    {{ $exercise->name }}
                </h4>

                <small>
                    {{ $exercise->machine_name }}
                </small>

                <div class="exercise-actions">

                    <a href="{{ route('admin.exercises.edit', $exercise) }}"
                       class="btn-edit">

                        <i class="bi bi-pencil"></i>

                    </a>

                    <form method="POST"
      action="{{ route('admin.exercises.destroy', $exercise) }}"
      class="delete-exercise-form">

    @csrf
    @method('DELETE')

    <button type="submit" class="btn-delete">
        <i class="bi bi-trash"></i>
    </button>
</form>

                </div>

            </div>

        </div>

    @empty

        <div class="panel">
            Nenhum exercício cadastrado.
        </div>

    @endforelse

</div>

<div class="mt-4">
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
