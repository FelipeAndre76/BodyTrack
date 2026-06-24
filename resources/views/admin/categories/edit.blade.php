@extends('layouts.admin')

@section('title', 'Editar Categoria')

@section('content')

<div class="admin-premium-header">
    <div>
        <span class="admin-kicker">Atualização do catálogo</span>
        <h1>Editar Categoria</h1>
        <p>Atualize o nome e o ícone do grupo muscular.</p>
    </div>

    <div class="admin-header-actions">
        <a href="{{ route('admin.categories.index') }}" class="btn-admin-secondary">
            <i class="bi bi-arrow-left"></i>
            Voltar
        </a>
    </div>
</div>

<div class="premium-form-shell">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf
        @method('PUT')

        <div class="premium-form-grid category-form-grid">
            <div class="premium-form-panel">
                <div class="premium-form-section-title">
                    <i class="bi bi-tags"></i>
                    <div>
                        <strong>Dados da categoria</strong>
                        <span>Nome e ícone usados no catálogo de exercícios.</span>
                    </div>
                </div>

                <div class="premium-field">
                    <label>Nome da categoria</label>
                    <input type="text"
                           name="name"
                           class="admin-input"
                           value="{{ $category->name }}"
                           required>
                </div>

                <div class="premium-field">
                    <label>Ícone Bootstrap</label>
                    <input type="text"
                           name="icon"
                           id="iconInput"
                           class="admin-input"
                           value="{{ $category->icon }}"
                           placeholder="Ex: bi-heart-pulse">
                </div>

                <button type="submit" class="btn-admin">
                    <i class="bi bi-check-circle"></i>
                    Atualizar categoria
                </button>
            </div>

            <div class="premium-form-panel">
                <div class="premium-form-section-title">
                    <i class="bi bi-eye"></i>
                    <div>
                        <strong>Prévia visual</strong>
                        <span>Confira como o ícone aparecerá no painel.</span>
                    </div>
                </div>

                <div class="category-icon-showcase">
                    <div class="category-icon-orb">
                        <i id="iconPreview" class="bi {{ $category->icon ?? 'bi-activity' }}"></i>
                    </div>

                    <strong id="categoryNamePreview">{{ $category->name }}</strong>
                    <span id="categoryIconPreviewText">{{ $category->icon ?: 'bi-activity' }}</span>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const iconInput = document.getElementById('iconInput');
    const iconPreview = document.getElementById('iconPreview');
    const iconText = document.getElementById('categoryIconPreviewText');

    const nameInput = document.querySelector('input[name="name"]');
    const namePreview = document.getElementById('categoryNamePreview');

    function updatePreview() {
        const icon = iconInput.value || 'bi-activity';
        const name = nameInput.value || 'Categoria';

        iconPreview.className = 'bi ' + icon;
        iconText.innerText = icon;
        namePreview.innerText = name;
    }

    iconInput.addEventListener('input', updatePreview);
    nameInput.addEventListener('input', updatePreview);
});
</script>
@endsection
