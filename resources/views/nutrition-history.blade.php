@extends('layouts.bodytrack')

@section('title', 'Historico Alimentar - BodyTrack')

@section('content')

<div class="nutrition-history-page">
    <section class="nutrition-history-hero">
        <div>
            <span class="nutrition-history-kicker">
                <i class="bi bi-journal-text"></i>
                Historico alimentar
            </span>

            <h1>Consumo por periodo</h1>

            <p>
                Filtre refeicoes, veja totais e acompanhe a media de macros no periodo selecionado.
            </p>
        </div>

        <a href="{{ route('nutrition.index') }}" class="nutrition-history-action">
            <i class="bi bi-plus-circle"></i>
            Registrar refeicao
        </a>
    </section>

    <section class="nutrition-history-filter-panel">
        <form method="GET" action="{{ route('nutrition.history') }}" class="nutrition-history-filters">
            <div>
                <label class="label mb-2">Inicio</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="form-control body-input">
            </div>

            <div>
                <label class="label mb-2">Fim</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="form-control body-input">
            </div>

            <div>
                <label class="label mb-2">Refeicao</label>
                <select name="meal_type" class="form-select body-input">
                    <option value="">Todas</option>
                    @foreach($mealTypes as $type => $info)
                        <option value="{{ $type }}" @selected($filters['meal_type'] === $type)>
                            {{ $info['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label mb-2">Alimento</label>
                <select name="food_id" class="form-select body-input">
                    <option value="">Todos</option>
                    @foreach($foods as $food)
                        <option value="{{ $food->id }}" @selected((string) $filters['food_id'] === (string) $food->id)>
                            {{ $food->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-bodytrack nutrition-history-filter-button">
                <i class="bi bi-funnel"></i>
                Filtrar
            </button>
        </form>
    </section>

    <section class="nutrition-history-summary">
        <article>
            <span>Proteina total</span>
            <strong>{{ number_format($totals['protein'], 1, ',', '.') }}g</strong>
            <small>Media {{ number_format($averages['protein'], 1, ',', '.') }}g/dia</small>
        </article>

        <article>
            <span>Carboidratos</span>
            <strong>{{ number_format($totals['carbs'], 1, ',', '.') }}g</strong>
            <small>Media {{ number_format($averages['carbs'], 1, ',', '.') }}g/dia</small>
        </article>

        <article>
            <span>Gorduras</span>
            <strong>{{ number_format($totals['fat'], 1, ',', '.') }}g</strong>
            <small>Media {{ number_format($averages['fat'], 1, ',', '.') }}g/dia</small>
        </article>

        <article>
            <span>Calorias</span>
            <strong>{{ number_format($totals['calories'], 0, ',', '.') }}</strong>
            <small>Media {{ number_format($averages['calories'], 0, ',', '.') }} kcal/dia</small>
        </article>
    </section>

    <section class="nutrition-history-target-panel">
        <div>
            <span>Comparativo diario</span>
            <strong>{{ $days }} dia(s) analisado(s)</strong>
            <small>Meta diaria em uso: {{ $nutritionGoals['protein'] }}g proteina, {{ $nutritionGoals['calories'] }} kcal</small>
        </div>

        <div class="nutrition-history-target-grid">
            <div>
                <span>Proteina media</span>
                <strong>{{ number_format(min(100, ($averages['protein'] / max(1, $nutritionGoals['protein'])) * 100), 0, ',', '.') }}%</strong>
            </div>

            <div>
                <span>Calorias media</span>
                <strong>{{ number_format(min(100, ($averages['calories'] / max(1, $nutritionGoals['calories'])) * 100), 0, ',', '.') }}%</strong>
            </div>
        </div>
    </section>

    <section class="nutrition-history-list-panel">
        <div class="nutrition-history-section-header">
            <div>
                <span>Registros</span>
                <h2>Refeicoes encontradas</h2>
            </div>

            <small>{{ $meals->count() }} refeicao(oes)</small>
        </div>

        <div class="nutrition-history-list">
            @forelse($meals as $meal)
                @php
                    $mealInfo = $mealTypes[$meal->meal_type] ?? ['label' => 'Refeicao', 'icon' => 'bi-egg-fried'];
                    $mealProtein = $meal->items->sum('protein');
                    $mealCarbs = $meal->items->sum('carbs');
                    $mealFat = $meal->items->sum('fat');
                    $mealCalories = $meal->items->sum('calories');
                @endphp

                <article class="nutrition-history-meal-card">
                    <div class="nutrition-history-meal-main">
                        @if($meal->photo_path)
                            <img src="{{ asset('storage/' . $meal->photo_path) }}" alt="Foto da refeicao">
                        @else
                            <div class="nutrition-history-meal-icon">
                                <i class="bi {{ $mealInfo['icon'] }}"></i>
                            </div>
                        @endif

                        <div>
                            <span>{{ $meal->meal_date->format('d/m/Y') }}</span>
                            <h3>{{ $mealInfo['label'] }}</h3>
                            <small>{{ $meal->items->count() }} alimento(s)</small>
                        </div>
                    </div>

                    <div class="nutrition-history-foods">
                        @foreach($meal->items as $item)
                            <span>{{ $item->food?->name ?? 'Alimento removido' }} · {{ number_format($item->quantity, 1, ',', '.') }}</span>
                        @endforeach
                    </div>

                    <div class="nutrition-history-meal-macros">
                        <div>
                            <span>Proteina</span>
                            <strong>{{ number_format($mealProtein, 1, ',', '.') }}g</strong>
                        </div>

                        <div>
                            <span>Carbo</span>
                            <strong>{{ number_format($mealCarbs, 1, ',', '.') }}g</strong>
                        </div>

                        <div>
                            <span>Gordura</span>
                            <strong>{{ number_format($mealFat, 1, ',', '.') }}g</strong>
                        </div>

                        <div>
                            <span>Kcal</span>
                            <strong>{{ number_format($mealCalories, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </article>
            @empty
                <div class="nutrition-history-empty">
                    <i class="bi bi-journal-plus"></i>
                    <strong>Nenhuma refeicao encontrada</strong>
                    <span>Ajuste os filtros ou registre uma nova refeicao.</span>
                </div>
            @endforelse
        </div>
    </section>
</div>

@endsection
