@extends('layouts.bodytrack')

@section('title', 'Plano Alimentar - BodyTrack')

@section('content')

<div class="meal-plan-page">
    <section class="meal-plan-hero">
        <div>
            <span class="meal-plan-kicker">
                <i class="bi bi-calendar2-check"></i>
                Guia do dia
            </span>

            <h1>Plano Alimentar</h1>

            <p>
                Distribua suas metas de calorias e macros entre as refeicoes para chegar no fim do dia com mais controle.
            </p>
        </div>

        <div class="meal-plan-score">
            <span>{{ $metrics['calorie_balance_label'] }}</span>
            <strong>
                @if($metrics['calorie_deficit'] > 0)
                    -{{ number_format((float) $metrics['calorie_deficit'], 0, ',', '.') }}
                @elseif($metrics['calorie_surplus'] > 0)
                    +{{ number_format((float) $metrics['calorie_surplus'], 0, ',', '.') }}
                @else
                    0
                @endif
            </strong>
            <small>Comparado a manutencao diaria</small>
        </div>
    </section>

    <section class="meal-plan-overview">
        <article>
            <span>Proteina</span>
            <strong>{{ number_format((float) $dailyTotals['protein'], 1, ',', '.') }}g</strong>
            <small>Meta {{ $nutritionGoals['protein'] }}g</small>
            <div class="body-progress">
                <div class="body-progress-bar" style="width: {{ $dailyPercents['protein'] }}%"></div>
            </div>
        </article>

        <article>
            <span>Carboidratos</span>
            <strong>{{ number_format((float) $dailyTotals['carbs'], 1, ',', '.') }}g</strong>
            <small>Meta {{ $nutritionGoals['carbs'] }}g</small>
            <div class="body-progress">
                <div class="body-progress-bar" style="width: {{ $dailyPercents['carbs'] }}%"></div>
            </div>
        </article>

        <article>
            <span>Gorduras</span>
            <strong>{{ number_format((float) $dailyTotals['fat'], 1, ',', '.') }}g</strong>
            <small>Meta {{ $nutritionGoals['fat'] }}g</small>
            <div class="body-progress">
                <div class="body-progress-bar" style="width: {{ $dailyPercents['fat'] }}%"></div>
            </div>
        </article>

        <article>
            <span>Calorias</span>
            <strong>{{ number_format((float) $dailyTotals['calories'], 0, ',', '.') }}</strong>
            <small>Meta {{ $nutritionGoals['calories'] }} kcal</small>
            <div class="body-progress">
                <div class="body-progress-bar" style="width: {{ $dailyPercents['calories'] }}%"></div>
            </div>
        </article>
    </section>

    <section class="meal-plan-layout">
        <div class="meal-plan-panel is-large">
            <div class="meal-plan-header">
                <div>
                    <span>Distribuicao</span>
                    <h2>Metas por refeicao</h2>
                </div>

                <a href="{{ route('nutrition.index') }}" class="meal-plan-link">
                    Registrar refeicao
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="meal-plan-grid">
                @foreach($mealPlan as $meal)
                    <article class="meal-plan-card">
                        <div class="meal-plan-card-head">
                            <div class="meal-plan-icon">
                                <i class="bi {{ $meal['icon'] }}"></i>
                            </div>

                            <div>
                                <span>{{ $meal['label'] }}</span>
                                <strong>{{ number_format((float) $meal['target']['calories'], 0, ',', '.') }} kcal</strong>
                            </div>

                            <small>{{ number_format((float) $meal['percent'], 0, ',', '.') }}%</small>
                        </div>

                        <div class="body-progress">
                            <div class="body-progress-bar" style="width: {{ $meal['percent'] }}%"></div>
                        </div>

                        <div class="meal-plan-macros">
                            <div>
                                <span>Proteina</span>
                                <strong>{{ $meal['target']['protein'] }}g</strong>
                                <small>Falta {{ number_format((float) $meal['missing']['protein'], 1, ',', '.') }}g</small>
                            </div>

                            <div>
                                <span>Carbo</span>
                                <strong>{{ $meal['target']['carbs'] }}g</strong>
                                <small>Falta {{ number_format((float) $meal['missing']['carbs'], 1, ',', '.') }}g</small>
                            </div>

                            <div>
                                <span>Gordura</span>
                                <strong>{{ $meal['target']['fat'] }}g</strong>
                                <small>Falta {{ number_format((float) $meal['missing']['fat'], 1, ',', '.') }}g</small>
                            </div>
                        </div>

                        <div class="meal-plan-current">
                            <span>Registrado</span>
                            <strong>
                                {{ number_format((float) $meal['current']['protein'], 1, ',', '.') }}g P ·
                                {{ number_format((float) $meal['current']['calories'], 0, ',', '.') }} kcal
                            </strong>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <aside class="meal-plan-panel">
            <div class="meal-plan-header">
                <div>
                    <span>Orientacao</span>
                    <h2>Como usar hoje</h2>
                </div>
            </div>

            <div class="meal-plan-suggestion-list">
                @foreach($suggestions as $suggestion)
                    <article>
                        <i class="bi {{ $suggestion['icon'] }}"></i>

                        <div>
                            <strong>{{ $suggestion['title'] }}</strong>
                            <span>{{ $suggestion['text'] }}</span>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="meal-plan-note">
                <span>Dose util de proteina</span>
                <strong>{{ $metrics['protein_dose_min'] }}-{{ $metrics['protein_dose_max'] }}g</strong>
                <small>Referencia por refeicao para melhorar distribuicao ao longo do dia.</small>
            </div>

            <div class="meal-plan-note">
                <span>Estimativa semanal</span>
                <strong>
                    @if($metrics['estimated_weekly_weight_change'] < 0)
                        -{{ number_format(abs((float) $metrics['estimated_weekly_weight_change']), 2, ',', '.') }}kg
                    @elseif($metrics['estimated_weekly_weight_change'] > 0)
                        +{{ number_format((float) $metrics['estimated_weekly_weight_change'], 2, ',', '.') }}kg
                    @else
                        0kg
                    @endif
                </strong>
                <small>Projecao aproximada baseada no balanco calorico diario.</small>
            </div>
        </aside>
    </section>
</div>

@endsection
