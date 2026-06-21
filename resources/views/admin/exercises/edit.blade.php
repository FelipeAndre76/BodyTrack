@extends('layouts.admin')

@section('title', 'Editar Exercício')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h1 class="admin-title mb-0">
            Editar Exercício
        </h1>

        <small class="text-secondary">
            Atualize os dados do exercício
        </small>
    </div>

    <a href="{{ route('admin.exercises.index') }}" class="btn-admin-secondary">

        <i class="bi bi-arrow-left"></i>
        Voltar

    </a>

</div>

<div class="admin-form-card">

    <form method="POST" action="{{ route('admin.exercises.update', $exercise) }}" enctype="multipart/form-data">

        @csrf
        @method('PUT')

        <div class="row g-4">

            <div class="col-md-6">

                <label class="admin-label">
                    Categoria
                </label>

                <select name="exercise_category_id" class="admin-input" required>

                    @foreach($categories as $category)

                    <option value="{{ $category->id }}" {{ $exercise->exercise_category_id == $category->id ? 'selected' : '' }}>

                        {{ $category->name }}

                    </option>

                    @endforeach

                </select>

            </div>

            <div class="col-md-6">

                <label class="admin-label">
                    Nome do Exercício
                </label>

                <input type="text" name="name" value="{{ $exercise->name }}" class="admin-input" required>

            </div>

            <div class="col-md-6">

                <label class="admin-label">
                    Nome do Aparelho
                </label>

                <input type="text" name="machine_name" value="{{ $exercise->machine_name }}" class="admin-input">

            </div>

            <div class="col-md-6">

                <label class="admin-label">
                    Nova Foto
                </label>

                <input type="file" name="image" class="admin-input" accept="image/*">

            </div>

            <div class="col-md-6">

                <label class="admin-label">
                    Foto Atual
                </label>

                <div class="image-preview-container">

                    @if($exercise->image_path)

                    <img id="imagePreview" src="{{ asset('storage/'.$exercise->image_path) }}" style="display:block;">

                    @else

                    <img id="imagePreview" src="" style="display:none;">

                    @endif

                </div>

            </div>

            <div class="col-12">

                <label class="admin-label">
                    Descrição
                </label>

                <textarea name="description" class="admin-input" rows="5">{{ $exercise->description }}</textarea>

            </div>

            <div class="col-12">

                <button type="submit" class="btn-admin">

                    <i class="bi bi-check-circle"></i>
                    Atualizar Exercício

                </button>

            </div>

        </div>

    </form>

</div>

@endsection

@section('scripts')

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const input = document.querySelector('input[name="image"]');
        const preview = document.getElementById('imagePreview');

        if (!input || !preview) return;

        input.addEventListener('change', (event) => {

            const file = event.target.files[0];

            if (!file) return;

            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';

        });

    });

</script>

@endsection
