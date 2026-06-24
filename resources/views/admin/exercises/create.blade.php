@extends('layouts.admin')

@section('title', 'Novo Exercício')

@section('content')

<div class="admin-premium-header">
    <div>
        <span class="admin-kicker">Cadastro técnico</span>
        <h1>Novo Exercício</h1>
        <p>Adicione um exercício ao catálogo com categoria, aparelho, descrição e imagem.</p>
    </div>

    <div class="admin-header-actions">
        <a href="{{ route('admin.exercises.index') }}" class="btn-admin-secondary">
            <i class="bi bi-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<div class="premium-form-shell">
    <form method="POST" action="{{ route('admin.exercises.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="premium-form-grid">
            <div class="premium-form-panel">
                <div class="premium-form-section-title">
                    <i class="bi bi-activity"></i>
                    <div>
                        <strong>Informações do exercício</strong>
                        <span>Dados principais exibidos no catálogo.</span>
                    </div>
                </div>

                <div class="premium-field">
                    <label>Categoria</label>
                    <select name="exercise_category_id" class="admin-input" required>
                        <option value="">Selecione uma categoria</option>

                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="premium-field">
                    <label>Nome do exercício</label>
                    <input type="text" name="name" class="admin-input" placeholder="Ex: Supino reto" required>
                </div>

                <div class="premium-field">
                    <label>Nome do aparelho</label>
                    <input type="text" name="machine_name" class="admin-input" placeholder="Ex: Máquina Hammer">
                </div>

                <div class="premium-field">
                    <label>Descrição</label>
                    <textarea name="description" class="admin-input" rows="5" placeholder="Descrição, observações ou instruções do exercício"></textarea>
                </div>
            </div>

            <div class="premium-form-panel">
                <div class="premium-form-section-title">
                    <i class="bi bi-image"></i>
                    <div>
                        <strong>Foto do aparelho</strong>
                        <span>Imagem usada nas telas de treino e catálogo.</span>
                    </div>
                </div>

                <div class="premium-image-preview">
                    <img id="imagePreview" src="" alt="Prévia da imagem">
                    <div id="imagePlaceholder" class="premium-image-placeholder">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span>Selecione uma imagem</span>
                    </div>
                </div>

                <div class="premium-field">
                    <label>Arquivo da imagem</label>
                    <input type="file" name="image" class="admin-input" accept="image/*">
                </div>

                <button type="submit" class="btn-admin w-100">
                    <i class="bi bi-check-circle"></i>
                    Salvar exercício
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
    const placeholder = document.getElementById('imagePlaceholder');

    if (!input || !preview || !placeholder) return;

    input.addEventListener('change', (event) => {
        const file = event.target.files[0];

        if (!file) return;

        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
        placeholder.style.display = 'none';
    });
});
</script>
@endsection
