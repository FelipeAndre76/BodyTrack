@extends('layouts.admin')

@section('title', 'Novo Exercício')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="admin-title mb-0">Novo Exercício</h1>
        <small class="text-secondary">Cadastre um exercício ou aparelho novo</small>
    </div>

    <a href="{{ route('admin.exercises.index') }}" class="btn-admin-secondary">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.exercises.store') }}" enctype="multipart/form-data">

        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="admin-label">Categoria</label>
                <select name="exercise_category_id" class="admin-input" required>
                    <option value="">Selecione uma categoria</option>

                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="admin-label">Nome do exercício</label>
                <input type="text" name="name" class="admin-input" placeholder="Ex: Supino Reto" required>
            </div>

            <div class="col-md-6">
                <label class="admin-label">Nome do aparelho</label>
                <input type="text" name="machine_name" class="admin-input" placeholder="Ex: Máquina Hammer">
            </div>

            <div class="col-md-6">
                <label class="admin-label">Foto do aparelho</label>
                <input type="file" name="image" class="admin-input" accept="image/*">
            </div>
           <div class="col-md-6">
    <div class="image-preview-container">
        <img id="imagePreview" src="">
    </div>
</div>

            <div class="col-12">
                <label class="admin-label">Descrição</label>
                <textarea name="description" class="admin-input" rows="4" placeholder="Descrição ou observação do exercício"></textarea>
            </div>

            <div class="col-12">
                <button type="submit" class="btn-admin">
                    <i class="bi bi-check-circle"></i>
                    Salvar Exercício
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
