@extends('layouts.bodytrack')

@section('title', 'Meus Alimentos - BodyTrack')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <div class="page-title mb-1">Meus Alimentos</div>
        <p class="text-secondary mb-0">
            Gerencie os alimentos que você cadastrou por foto ou manualmente.
        </p>
    </div>

    <button type="button" class="btn-bodytrack" data-bs-toggle="modal" data-bs-target="#scanFoodModal">
        <i class="bi bi-camera me-2"></i>
        Cadastrar por foto
    </button>
</div>

<div class="modal fade" id="scanFoodModal" tabindex="-1" aria-labelledby="scanFoodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-success">
            <div class="modal-header border-success">
                <h5 class="modal-title" id="scanFoodModalLabel">
                    <i class="bi bi-camera me-2 text-success"></i>
                    Cadastrar alimento por foto
                </h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <form id="scanFoodForm"
                  method="POST"
                  action="{{ route('nutrition.scan-label') }}"
                  enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="label mb-2">Nome do alimento</label>
                            <input type="text"
                                   name="name"
                                   class="form-control body-input"
                                   placeholder="Ex: Requeijão Light"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="label mb-2">Foto da tabela nutricional</label>
                            <input type="file"
                                   name="label_photo"
                                   class="form-control body-input"
                                   accept="image/*"
                                   data-image-preset="nutritionLabel"
                                   data-image-status="nutritionLabelOptimizeStatus"
                                   required>
                            <small class="image-compress-hint" id="nutritionLabelOptimizeStatus">
                                A foto sera reduzida antes do envio sem cortar a tabela.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-success">
                    <button type="button" class="btn-outline-bodytrack" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn-bodytrack">
                        <i class="bi bi-magic me-2"></i>
                        Ler tabela nutricional
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmScannedFoodModal" tabindex="-1" aria-labelledby="confirmScannedFoodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark border-success">
            <div class="modal-header border-success">
                <h5 class="modal-title" id="confirmScannedFoodModalLabel">
                    <i class="bi bi-check-circle me-2 text-success"></i>
                    Confirmar alimento detectado
                </h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <form id="confirmScannedFoodForm" method="POST" action="{{ route('nutrition.store-scanned-food') }}">
                @csrf
                <input type="hidden" name="raw_text" id="scannedRawText">
                <input type="hidden" name="label_photo_path" id="scannedLabelPhotoPath">

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="label mb-2">Nome</label>
                            <input type="text"
                                   name="name"
                                   id="scannedName"
                                   class="form-control body-input"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="label mb-2">Porção detectada em gramas/ml</label>
                            <input type="number"
                                   step="0.1"
                                   name="serving_size"
                                   id="scannedServingSize"
                                   class="form-control body-input"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="label mb-2">Calorias</label>
                            <input type="number"
                                   step="0.1"
                                   name="calories"
                                   id="scannedCalories"
                                   class="form-control body-input"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="label mb-2">Proteína</label>
                            <input type="number"
                                   step="0.1"
                                   name="protein"
                                   id="scannedProtein"
                                   class="form-control body-input"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="label mb-2">Carboidratos</label>
                            <input type="number"
                                   step="0.1"
                                   name="carbs"
                                   id="scannedCarbs"
                                   class="form-control body-input"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="label mb-2">Gorduras</label>
                            <input type="number"
                                   step="0.1"
                                   name="fat"
                                   id="scannedFat"
                                   class="form-control body-input"
                                   required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-success">
                    <button type="button" class="btn-outline-bodytrack" data-bs-dismiss="modal">
                        Ajustar depois
                    </button>

                    <button type="submit" class="btn-bodytrack">
                        Confirmar e salvar alimento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<div class="row g-4">
    @forelse($foods as $food)
        <div class="col-xl-6">
            <div class="user-food-card">
                <div class="user-food-photo">
                    @if($food->label_photo_path)
                        <img src="{{ asset('storage/' . $food->label_photo_path) }}" alt="Tabela nutricional de {{ $food->name }}">
                    @else
                        <div class="user-food-photo-placeholder">
                            <i class="bi bi-image"></i>
                        </div>
                    @endif
                </div>

                <div class="user-food-content">
                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <div>
                            <span class="user-food-source">
                                {{ $food->source === 'ocr' ? 'OCR nutricional' : 'Cadastro manual' }}
                            </span>
                            <h4>{{ $food->name }}</h4>
                        </div>

                        <small class="text-secondary text-end">
                            {{ $food->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>

                    <div class="user-food-macros">
                        <div>
                            <span>Calorias</span>
                            <strong>{{ number_format($food->calories_per_100g, 0, ',', '.') }}</strong>
                            <small>kcal/100g</small>
                        </div>

                        <div>
                            <span>Proteína</span>
                            <strong>{{ number_format($food->protein_per_100g, 1, ',', '.') }}g</strong>
                            <small>por 100g</small>
                        </div>

                        <div>
                            <span>Carbo</span>
                            <strong>{{ number_format($food->carbs_per_100g, 1, ',', '.') }}g</strong>
                            <small>por 100g</small>
                        </div>

                        <div>
                            <span>Gordura</span>
                            <strong>{{ number_format($food->fat_per_100g, 1, ',', '.') }}g</strong>
                            <small>por 100g</small>
                        </div>
                    </div>

                    @if($food->latestScanLog)
                        <div class="user-food-serving mt-3">
                            <span>Última leitura:</span>
                            <strong>{{ number_format($food->latestScanLog->serving_size, 0, ',', '.') }}g/ml</strong>
                            <span>{{ number_format($food->latestScanLog->calories, 0, ',', '.') }} kcal</span>
                            <span>{{ number_format($food->latestScanLog->protein, 1, ',', '.') }}g proteína</span>
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="button"
                                class="btn-bodytrack"
                                data-bs-toggle="modal"
                                data-bs-target="#editFoodModal{{ $food->id }}">
                            <i class="bi bi-pencil-square me-2"></i>
                            Editar
                        </button>

                        <button type="submit"
                                form="delete-food-{{ $food->id }}"
                                class="btn-outline-bodytrack"
                                onclick="return confirm('Deseja excluir este alimento?')">
                            <i class="bi bi-trash me-2"></i>
                            Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editFoodModal{{ $food->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content bg-dark border-success">
                    <div class="modal-header border-success">
                        <h5 class="modal-title">
                            <i class="bi bi-pencil-square me-2 text-success"></i>
                            Editar alimento
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>

                    <form method="POST" action="{{ route('foods.update', $food) }}">
                        @csrf
                        @method('PUT')

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="label mb-2">Nome</label>
                                    <input type="text" name="name" class="form-control body-input" value="{{ $food->name }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="label mb-2">Calorias/100g</label>
                                    <input type="number" step="0.1" name="calories_per_100g" class="form-control body-input" value="{{ $food->calories_per_100g }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="label mb-2">Proteína/100g</label>
                                    <input type="number" step="0.1" name="protein_per_100g" class="form-control body-input" value="{{ $food->protein_per_100g }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="label mb-2">Carbo/100g</label>
                                    <input type="number" step="0.1" name="carbs_per_100g" class="form-control body-input" value="{{ $food->carbs_per_100g }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="label mb-2">Gordura/100g</label>
                                    <input type="number" step="0.1" name="fat_per_100g" class="form-control body-input" value="{{ $food->fat_per_100g }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-success">
                            <button type="button" class="btn-outline-bodytrack" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn-bodytrack">Salvar alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <form id="delete-food-{{ $food->id }}" method="POST" action="{{ route('foods.destroy', $food) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @empty
        <div class="col-12">
            <div class="panel">
                <p class="text-secondary mb-0">
                    Você ainda não cadastrou alimentos próprios.
                </p>
            </div>
        </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $foods->links('pagination::bootstrap-5') }}
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const scanFoodForm = document.getElementById('scanFoodForm');
    const confirmScannedFoodForm = document.getElementById('confirmScannedFoodForm');
    const scanFoodModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('scanFoodModal'));
    const confirmScannedFoodModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('confirmScannedFoodModal'));

    function setButtonLoading(button, loadingText, isLoading) {
        if (!button) {
            return;
        }

        if (isLoading) {
            button.dataset.originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${loadingText}`;
            return;
        }

        button.disabled = false;
        button.innerHTML = button.dataset.originalText || button.innerHTML;
    }

    function fillScannedFoodModal(food) {
        document.getElementById('scannedName').value = food.name || '';
        document.getElementById('scannedServingSize').value = food.serving_size || 100;
        document.getElementById('scannedCalories').value = food.calories || 0;
        document.getElementById('scannedProtein').value = food.protein || 0;
        document.getElementById('scannedCarbs').value = food.carbs || 0;
        document.getElementById('scannedFat').value = food.fat || 0;
        document.getElementById('scannedRawText').value = food.raw_text || '';
        document.getElementById('scannedLabelPhotoPath').value = food.label_photo_path || '';
    }

    function updateNotificationBell(count) {
        if (count === undefined || count === null) {
            return;
        }

        const bellButton = document.querySelector('.notification-bell-btn');
        let countBadge = document.getElementById('notificationBellCount');

        if (!bellButton) {
            return;
        }

        if (Number(count) <= 0) {
            countBadge?.remove();
            return;
        }

        if (!countBadge) {
            countBadge = document.createElement('span');
            countBadge.id = 'notificationBellCount';
            bellButton.appendChild(countBadge);
        }

        countBadge.innerText = count;
    }

    scanFoodForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const button = scanFoodForm.querySelector('button[type="submit"]');
        setButtonLoading(button, 'Lendo tabela...', true);

        try {
            const response = await fetch(scanFoodForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(scanFoodForm)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Não foi possível ler a tabela nutricional.');
            }

            fillScannedFoodModal(data.food);
            scanFoodModal.hide();
            confirmScannedFoodModal.show();
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Leitura não concluída',
                text: error.message
            });
        } finally {
            setButtonLoading(button, 'Lendo tabela...', false);
        }
    });

    confirmScannedFoodForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        const button = confirmScannedFoodForm.querySelector('button[type="submit"]');
        setButtonLoading(button, 'Salvando...', true);

        try {
            const response = await fetch(confirmScannedFoodForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(confirmScannedFoodForm)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Não foi possível salvar o alimento.');
            }

            updateNotificationBell(data.unread_notifications);
            confirmScannedFoodModal.hide();

            Swal.fire({
                icon: 'success',
                title: 'Alimento salvo',
                text: data.message,
                timer: 1200,
                showConfirmButton: false
            });

            setTimeout(() => {
                window.location.reload();
            }, 900);
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Cadastro não concluído',
                text: error.message
            });
        } finally {
            setButtonLoading(button, 'Salvando...', false);
        }
    });
});
</script>
@endsection
