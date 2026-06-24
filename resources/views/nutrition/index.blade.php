@extends('layouts.bodytrack')

@section('title', 'Nutrição - BodyTrack')

@section('content')

<div class="page-title mb-4">
    Nutrição
</div>

<div class="nutrition-page-actions mb-4">
    <a href="{{ route('nutrition.meal-plan') }}" class="btn-outline-bodytrack">
        <i class="bi bi-calendar2-check me-2"></i>
        Plano alimentar diario
    </a>

    <a href="{{ route('nutrition.history') }}" class="btn-outline-bodytrack">
        <i class="bi bi-clock-history me-2"></i>
        Histórico alimentar completo
    </a>
</div>

@if($metrics['has_custom_goals'])
    <div class="body-profile-form-divider mb-4">
        <span>Metas personalizadas ativas</span>
        <small>Os macros abaixo estão usando os valores definidos no Perfil Corporal.</small>
    </div>
@endif

<div class="body-tabs mb-4" role="tablist">
    <button class="body-tab active" data-bs-toggle="tab" data-bs-target="#nutrition-summary" type="button">
        <i class="bi bi-pie-chart"></i>
        Resumo
    </button>

    <button class="body-tab" data-bs-toggle="tab" data-bs-target="#nutrition-builder" type="button">
        <i class="bi bi-plus-circle"></i>
        Montar refeição
    </button>

    <button class="body-tab" data-bs-toggle="tab" data-bs-target="#nutrition-history" type="button">
        <i class="bi bi-clock-history"></i>
        Histórico
    </button>
</div>

<div class="tab-content nutrition-tab-content">
<div class="tab-pane fade show active" id="nutrition-summary" role="tabpanel">
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="top-card">
            <div class="label">Proteína</div>
            <div class="value">
                {{ number_format($totals['protein'], 1) }}g
            </div>
            <small class="text-secondary">
                Meta: {{ $nutritionGoals['protein'] }}g
            </small>

            <div class="body-progress mt-3">
                <div class="body-progress-bar" style="width: {{ $nutritionGoals['protein'] > 0 ? min(100, ($totals['protein'] / $nutritionGoals['protein']) * 100) : 0 }}%"></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="top-card">
            <div class="label">Carboidratos</div>
            <div class="value">
                {{ number_format($totals['carbs'], 1) }}g
            </div>
            <small class="text-secondary">
                Meta: {{ $nutritionGoals['carbs'] }}g
            </small>

            <div class="body-progress mt-3">
                <div class="body-progress-bar" style="width: {{ $nutritionGoals['carbs'] > 0 ? min(100, ($totals['carbs'] / $nutritionGoals['carbs']) * 100) : 0 }}%"></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="top-card">
            <div class="label">Gorduras</div>
            <div class="value">
                {{ number_format($totals['fat'], 1) }}g
            </div>
            <small class="text-secondary">
                Meta: {{ $nutritionGoals['fat'] }}g
            </small>

            <div class="body-progress mt-3">
                <div class="body-progress-bar" style="width: {{ $nutritionGoals['fat'] > 0 ? min(100, ($totals['fat'] / $nutritionGoals['fat']) * 100) : 0 }}%"></div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="top-card">
            <div class="label">Calorias</div>
            <div class="value">
                {{ number_format($totals['calories'], 0) }}
            </div>
            <small class="text-secondary">
                Meta: {{ $nutritionGoals['calories'] }} kcal
            </small>

            <div class="body-progress mt-3">
                <div class="body-progress-bar" style="width: {{ $nutritionGoals['calories'] > 0 ? min(100, ($totals['calories'] / $nutritionGoals['calories']) * 100) : 0 }}%"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="top-card">
            <div class="label">Proteína por refeição</div>
            <div class="value">
                {{ $metrics['protein_per_meal'] }}g
            </div>
            <small class="text-secondary">
                Base: {{ $metrics['meals_per_day'] }} refeições/dia
            </small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="top-card">
            <div class="label">Dose útil</div>
            <div class="value">
                {{ $metrics['protein_dose_min'] }}-{{ $metrics['protein_dose_max'] }}g
            </div>
            <small class="text-secondary">
                Referência por refeição
            </small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="top-card">
            <div class="label">{{ $metrics['calorie_balance_label'] }}</div>
            <div class="value">
                @if($metrics['calorie_deficit'] > 0)
                    -{{ number_format($metrics['calorie_deficit'], 0) }}
                @elseif($metrics['calorie_surplus'] > 0)
                    +{{ number_format($metrics['calorie_surplus'], 0) }}
                @else
                    0
                @endif
            </div>
            <small class="text-secondary">
                Alvo: {{ number_format($nutritionGoals['calories'], 0) }} kcal
            </small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="top-card">
            <div class="label">IMC</div>
            <div class="value">
                {{ number_format($metrics['bmi'], 1) }}
            </div>
            <small class="text-secondary">
                {{ $metrics['bmi_category']['label'] }}
            </small>
        </div>
    </div>
</div>
</div>

<div class="tab-pane fade" id="nutrition-builder" role="tabpanel">
<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="panel">
            <h4>
                <i class="bi bi-plus-circle me-2 text-success"></i>
                Montar refeição
            </h4>

            <form method="POST" action="{{ route('nutrition.store') }}" enctype="multipart/form-data" class="mt-4">
                @csrf

                <div class="mb-3">
                    <label class="label mb-2">Tipo de refeição</label>

                    <select name="meal_type" id="mealTypeSelect" class="form-select body-input" required>
                        <option value="breakfast">Café da manhã</option>
                        <option value="lunch" selected>Almoço</option>
                        <option value="snack">Lanche</option>
                        <option value="dinner">Jantar</option>
                        <option value="supper">Ceia</option>
                    </select>
                </div>

                <div id="foodsContainer">
                    <div class="food-row mb-3">
                        <label class="label mb-2">Alimento</label>

                        <select name="foods[0][food_id]" class="form-select body-input food-select" required>
                            <option value="">Selecione</option>

                            @foreach($foods as $food)
                                <option
                                    value="{{ $food->id }}"
                                    data-protein="{{ $food->protein_per_100g }}"
                                    data-carbs="{{ $food->carbs_per_100g }}"
                                    data-fat="{{ $food->fat_per_100g }}"
                                    data-calories="{{ $food->calories_per_100g }}"
                                    data-unit-type="{{ $food->unit_type }}"
                                    data-grams-unit="{{ $food->grams_per_unit }}">
                                    {{ $food->name }}
                                </option>
                            @endforeach
                        </select>

                        <label class="label mt-3 mb-2">Quantidade</label>

                        <input type="number"
                               step="0.1"
                               name="foods[0][quantity]"
                               class="form-control body-input quantity-input"
                               placeholder="Ex: 150 ou 2"
                               required>
                    </div>
                </div>

                <button type="button" id="addFoodBtn" class="btn-outline-bodytrack mb-3">
                    <i class="bi bi-plus-circle me-2"></i>
                    Adicionar outro alimento
                </button>

                <div class="mb-3">
                    <label class="label mb-2">Foto da refeição opcional</label>

                    <input type="file"
                           name="photo"
                           class="form-control body-input"
                           accept="image/*">
                </div>

                <button type="submit" class="btn-bodytrack">
                    Registrar refeição
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="panel">
            <h4>
                <i class="bi bi-calculator me-2 text-success"></i>
                Prévia da refeição
            </h4>

            <div class="meal-target-panel mt-4">
                <div class="meal-target-header">
                    <div>
                        <span>Meta da refeição</span>
                        <strong id="mealTargetTitle">Almoço</strong>
                    </div>

                    <small id="mealTargetPercent">0%</small>
                </div>

                <div class="body-progress">
                    <div class="body-progress-bar" id="mealTargetProgress" style="width: 0%"></div>
                </div>

                <div class="meal-target-grid">
                    <div>
                        <span>Proteína</span>
                        <strong id="mealTargetProtein">0g</strong>
                        <small id="mealMissingProtein">Falta 0g</small>
                    </div>

                    <div>
                        <span>Carbo</span>
                        <strong id="mealTargetCarbs">0g</strong>
                        <small id="mealMissingCarbs">Falta 0g</small>
                    </div>

                    <div>
                        <span>Gordura</span>
                        <strong id="mealTargetFat">0g</strong>
                        <small id="mealMissingFat">Falta 0g</small>
                    </div>

                    <div>
                        <span>Calorias</span>
                        <strong id="mealTargetCalories">0</strong>
                        <small id="mealMissingCalories">Falta 0</small>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <div class="settings-mini-card">
                        <span>Proteína</span>
                        <strong id="previewProtein">0 g</strong>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="settings-mini-card">
                        <span>Carboidratos</span>
                        <strong id="previewCarbs">0 g</strong>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="settings-mini-card">
                        <span>Gorduras</span>
                        <strong id="previewFat">0 g</strong>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="settings-mini-card">
                        <span>Calorias</span>
                        <strong id="previewCalories">0 kcal</strong>
                    </div>
                </div>
            </div>

            <div class="nutrition-preview-list mt-4" id="previewList">
                <p class="text-secondary mb-0">
                    Adicione os alimentos da refeição para visualizar os macros.
                </p>
            </div>
        </div>
    </div>
</div>
</div>

@php
    $mealTypes = [
        'breakfast' => ['label' => 'Café da manhã', 'icon' => 'bi-sun-fill'],
        'lunch' => ['label' => 'Almoço', 'icon' => 'bi-egg-fried'],
        'snack' => ['label' => 'Lanche', 'icon' => 'bi-cup-hot'],
        'dinner' => ['label' => 'Jantar', 'icon' => 'bi-moon-stars-fill'],
        'supper' => ['label' => 'Ceia', 'icon' => 'bi-stars'],
    ];

    $groupedMeals = $meals->groupBy('meal_type');
@endphp

<div class="tab-pane fade" id="nutrition-history" role="tabpanel">
<div class="panel">
    <h4 class="mb-4">
        <i class="bi bi-journal-text me-2 text-success"></i>
        Histórico de hoje
    </h4>

    @if($meals->isEmpty())
        <p class="text-secondary mb-0">
            Nenhuma refeição registrada hoje.
        </p>
    @else
        @foreach($mealTypes as $type => $mealInfo)
            @if($groupedMeals->has($type))
                <div class="meal-group mb-4">
                    <div class="meal-group-header">
                        <div>
                            <i class="bi {{ $mealInfo['icon'] }}"></i>
                            <strong>{{ $mealInfo['label'] }}</strong>
                        </div>
                    </div>

                    <div class="meal-items">
                        @foreach($groupedMeals[$type] as $meal)
                            @php
                                $mealProtein = $meal->items->sum('protein');
                                $mealCarbs = $meal->items->sum('carbs');
                                $mealFat = $meal->items->sum('fat');
                                $mealCalories = $meal->items->sum('calories');
                            @endphp

                            <div class="meal-card meal-card-composed">
                                <div class="meal-card-main">
                                    @if($meal->photo_path)
                                        <div class="meal-photo">
                                            <img src="{{ asset('storage/' . $meal->photo_path) }}" alt="Foto da refeição">
                                        </div>
                                    @else
                                        <div class="meal-food-icon">
                                            <i class="bi {{ $mealInfo['icon'] }}"></i>
                                        </div>
                                    @endif

                                    <div>
                                        <h5>{{ $mealInfo['label'] }}</h5>

                                        <p>
                                            {{ $meal->items->count() }} alimento(s)
                                        </p>

                                        <div class="meal-food-list">
                                            @foreach($meal->items as $item)
                                                <span>
                                                    {{ $item->food->name }}
                                                    -
                                                    {{ number_format($item->quantity, 1) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="meal-macros">
                                    <div>
                                        <span>Proteína</span>
                                        <strong>{{ number_format($mealProtein, 1) }}g</strong>
                                    </div>

                                    <div>
                                        <span>Carbo</span>
                                        <strong>{{ number_format($mealCarbs, 1) }}g</strong>
                                    </div>

                                    <div>
                                        <span>Gordura</span>
                                        <strong>{{ number_format($mealFat, 1) }}g</strong>
                                    </div>

                                    <div>
                                        <span>Calorias</span>
                                        <strong>{{ number_format($mealCalories, 0) }}</strong>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('nutrition.destroy', $meal) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button class="meal-delete" type="submit">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>
</div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const foodsContainer = document.getElementById('foodsContainer');
    const addFoodBtn = document.getElementById('addFoodBtn');
    const mealTypeSelect = document.getElementById('mealTypeSelect');
    const firstFoodSelect = foodsContainer.querySelector('.food-select');
    const foodOptions = firstFoodSelect ? firstFoodSelect.innerHTML : '';
    const foodSearchUrl = "{{ route('nutrition.search-foods') }}";
    const mealPlanTargets = @json($mealPlanByType);

    let foodIndex = 1;

    function createFoodRow(index) {
        return `
            <div class="food-row mb-3">
                <label class="label mb-2">Alimento</label>

                <select name="foods[${index}][food_id]" class="form-select body-input food-select" required>
                    ${foodOptions}
                </select>

                <label class="label mt-3 mb-2">Quantidade</label>

                <input type="number"
                       step="0.1"
                       name="foods[${index}][quantity]"
                       class="form-control body-input quantity-input"
                       placeholder="Ex: 150 ou 2"
                       required>

                <button type="button" class="remove-food-btn mt-3">
                    <i class="bi bi-trash me-1"></i>
                    Remover alimento
                </button>
            </div>
        `;
    }

    function getSelectedFoodData(select) {
        if (select.tomselect && select.tomselect.getValue()) {
            return select.tomselect.options[select.tomselect.getValue()] || null;
        }

        const selected = select.options[select.selectedIndex];

        if (!selected || !selected.value) {
            return null;
        }

        return {
            id: selected.value,
            name: selected.text,
            protein_per_100g: selected.dataset.protein ?? 0,
            carbs_per_100g: selected.dataset.carbs ?? 0,
            fat_per_100g: selected.dataset.fat ?? 0,
            calories_per_100g: selected.dataset.calories ?? 0,
            unit_type: selected.dataset.unitType ?? 'grams',
            grams_per_unit: selected.dataset.gramsUnit ?? 1
        };
    }

    function formatNumber(value, decimals = 1) {
        return Number(value || 0).toLocaleString('pt-BR', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }

    function updateMealTarget(previewTotals) {
        const selectedType = mealTypeSelect ? mealTypeSelect.value : 'lunch';
        const plan = mealPlanTargets[selectedType] || mealPlanTargets.lunch || null;

        if (!plan) {
            return;
        }

        const afterSave = {
            protein: Number(plan.current.protein || 0) + previewTotals.protein,
            carbs: Number(plan.current.carbs || 0) + previewTotals.carbs,
            fat: Number(plan.current.fat || 0) + previewTotals.fat,
            calories: Number(plan.current.calories || 0) + previewTotals.calories,
        };

        const percent = Math.min(100, (afterSave.calories / Math.max(1, Number(plan.target.calories || 1))) * 100);
        const missing = {
            protein: Math.max(0, Number(plan.target.protein || 0) - afterSave.protein),
            carbs: Math.max(0, Number(plan.target.carbs || 0) - afterSave.carbs),
            fat: Math.max(0, Number(plan.target.fat || 0) - afterSave.fat),
            calories: Math.max(0, Number(plan.target.calories || 0) - afterSave.calories),
        };

        document.getElementById('mealTargetTitle').innerText = plan.label || 'Refeição';
        document.getElementById('mealTargetPercent').innerText = Math.round(percent) + '%';
        document.getElementById('mealTargetProgress').style.width = percent + '%';
        document.getElementById('mealTargetProtein').innerText = formatNumber(plan.target.protein, 0) + 'g';
        document.getElementById('mealTargetCarbs').innerText = formatNumber(plan.target.carbs, 0) + 'g';
        document.getElementById('mealTargetFat').innerText = formatNumber(plan.target.fat, 0) + 'g';
        document.getElementById('mealTargetCalories').innerText = formatNumber(plan.target.calories, 0);
        document.getElementById('mealMissingProtein').innerText = 'Falta ' + formatNumber(missing.protein, 1) + 'g';
        document.getElementById('mealMissingCarbs').innerText = 'Falta ' + formatNumber(missing.carbs, 1) + 'g';
        document.getElementById('mealMissingFat').innerText = 'Falta ' + formatNumber(missing.fat, 1) + 'g';
        document.getElementById('mealMissingCalories').innerText = 'Falta ' + formatNumber(missing.calories, 0);
    }

    function updatePreview() {
        let totalProtein = 0;
        let totalCarbs = 0;
        let totalFat = 0;
        let totalCalories = 0;

        const previewList = document.getElementById('previewList');
        let html = '';

        document.querySelectorAll('.food-row').forEach(row => {
            const select = row.querySelector('.food-select');
            const quantityInput = row.querySelector('.quantity-input');

            const foodData = getSelectedFoodData(select);
            const quantity = parseFloat(quantityInput.value) || 0;

            if (!foodData || quantity <= 0) {
                return;
            }

            const protein = parseFloat(foodData.protein_per_100g) || 0;
            const carbs = parseFloat(foodData.carbs_per_100g) || 0;
            const fat = parseFloat(foodData.fat_per_100g) || 0;
            const calories = parseFloat(foodData.calories_per_100g) || 0;

            const unitType = foodData.unit_type || 'grams';
            const gramsUnit = parseFloat(foodData.grams_per_unit) || 1;

            let grams = quantity;

            if (unitType === 'unit') {
                grams = quantity * gramsUnit;
            }

            const factor = grams / 100;

            const itemProtein = protein * factor;
            const itemCarbs = carbs * factor;
            const itemFat = fat * factor;
            const itemCalories = calories * factor;

            totalProtein += itemProtein;
            totalCarbs += itemCarbs;
            totalFat += itemFat;
            totalCalories += itemCalories;

            html += `
                <div class="preview-food-item">
                    <span>${foodData.name} (${quantity})</span>
                    <strong>${itemCalories.toFixed(0)} kcal</strong>
                </div>
            `;
        });

        document.getElementById('previewProtein').innerText = totalProtein.toFixed(1) + ' g';
        document.getElementById('previewCarbs').innerText = totalCarbs.toFixed(1) + ' g';
        document.getElementById('previewFat').innerText = totalFat.toFixed(1) + ' g';
        document.getElementById('previewCalories').innerText = totalCalories.toFixed(0) + ' kcal';
        updateMealTarget({
            protein: totalProtein,
            carbs: totalCarbs,
            fat: totalFat,
            calories: totalCalories
        });

        previewList.innerHTML = html || `
            <p class="text-secondary mb-0">
                Adicione os alimentos da refeição para visualizar os macros.
            </p>
        `;
    }

    function initTomSelect() {
        document.querySelectorAll('.food-select').forEach(select => {
            if (select.tomselect) {
                return;
            }

            new TomSelect(select, {
                valueField: 'id',
                labelField: 'name',
                searchField: 'name',
                create: false,
                placeholder: "Digite para buscar um alimento...",

                load: function(query, callback) {
                    if (query.length < 2) {
                        return callback();
                    }

                    fetch(`${foodSearchUrl}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(json => callback(json))
                        .catch(() => callback());
                },

                render: {
                    option: function(item, escape) {
                        const sourceBadge = item.source
                            ? `<span class="food-source-badge">${escape(item.source)}</span>`
                            : '';

                        return `
                            <div>
                                <strong>${escape(item.name)}</strong>
                                ${sourceBadge}
                                <br>
                                <small>
                                    P: ${item.protein_per_100g ?? 0}g |
                                    C: ${item.carbs_per_100g ?? 0}g |
                                    G: ${item.fat_per_100g ?? 0}g |
                                    ${item.calories_per_100g ?? 0} kcal
                                </small>
                            </div>
                        `;
                    },

                    item: function(item, escape) {
                        return `
                            <div>
                                ${escape(item.name)}
                            </div>
                        `;
                    },

                    no_results: function() {
                        return `
                            <div class="no-results">
                                Nenhum alimento encontrado.
                            </div>
                        `;
                    }
                },

                onChange: function() {
                    updatePreview();
                },

                onItemAdd: function() {
                    updatePreview();
                }
            });
        });
    }

    addFoodBtn.addEventListener('click', function () {
        foodsContainer.insertAdjacentHTML('beforeend', createFoodRow(foodIndex));
        foodIndex++;

        initTomSelect();
        updatePreview();
    });

    document.addEventListener('input', function (event) {
        if (event.target.classList.contains('quantity-input')) {
            updatePreview();
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('food-select')) {
            updatePreview();
        }

        if (event.target.id === 'mealTypeSelect') {
            updatePreview();
        }
    });

    document.addEventListener('click', function (event) {
        const removeButton = event.target.closest('.remove-food-btn');

        if (!removeButton) {
            return;
        }

        const row = removeButton.closest('.food-row');
        const select = row.querySelector('.food-select');

        if (select && select.tomselect) {
            select.tomselect.destroy();
        }

        row.remove();
        updatePreview();
    });

    initTomSelect();
    updatePreview();
});
</script>
@endsection
