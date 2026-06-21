@extends('layouts.admin')

@section('title', 'Nova Categoria')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="admin-title mb-0">Nova Categoria</h1>
        <small class="text-secondary">Cadastre um grupo muscular</small>
    </div>

    <a href="{{ route('admin.categories.index') }}" class="btn-admin-secondary">
        <i class="bi bi-arrow-left"></i>
        Voltar
    </a>
</div>

<div class="admin-form-card">
    <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf

        <div class="row g-4">
            <div class="col-md-6">
                <label class="admin-label">Nome da categoria</label>
                <input type="text"
                       name="name"
                       class="admin-input"
                       placeholder="Ex: Peito"
                       required>
            </div>

            <div class="col-md-6">
                <label class="admin-label">Ícone Bootstrap</label>
                <input type="text"
                       name="icon"
                       id="iconInput"
                       class="admin-input"
                       placeholder="Ex: bi-heart-pulse">
            </div>

            <div class="col-12">
                <label class="admin-label">Prévia do ícone</label>
                <div class="admin-icon-preview">
                    <i id="iconPreview" class="bi bi-activity"></i>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn-admin">
                    <i class="bi bi-check-circle"></i>
                    Salvar Categoria
                </button>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('iconInput');
    const preview = document.getElementById('iconPreview');

    input.addEventListener('input', () => {
        preview.className = 'bi ' + (input.value || 'bi-activity');
    });
});
</script>
@endsection
