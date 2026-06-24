@extends('layouts.bodytrack')

@section('title', 'BodyTrack - Dashboard')

@section('content')

<div class="user-dashboard-hero mb-4">
    <div class="user-dashboard-hero-content">
        <div>
            <span class="dashboard-kicker">Resumo de hoje</span>

            <h1>
                Olá, {{ Auth::user()->name }}
            </h1>

            <p>
                Você já eliminou <strong>{{ number_format($weightLost, 1) }} kg</strong>
                e completou <strong>{{ number_format($progressPercent, 1) }}%</strong> da sua meta.
            </p>
        </div>

        <div class="dashboard-hero-actions">
            <a href="{{ route('weights.create') }}" class="hero-button">
                <i class="bi bi-plus-circle me-2"></i>
                Nova pesagem
            </a>

            <a href="{{ route('workouts.index') }}" class="btn-outline-bodytrack dashboard-outline-action">
                <i class="bi bi-activity me-2"></i>
                Treinar
            </a>
        </div>
    </div>

    <div class="hero-stats mt-4">
        <div>
            <span>Peso atual</span>
            <strong>{{ number_format($latestWeight, 1) }} kg</strong>
        </div>

        <div>
            <span>Meta</span>
            <strong>{{ number_format($goalWeight, 1) }} kg</strong>
        </div>

        <div>
            <span>Restante</span>
            <strong>{{ number_format(max(0, $remainingWeight), 1) }} kg</strong>
        </div>
    </div>

    <div class="body-progress mt-4">
        <div class="body-progress-bar" style="width: {{ $progressPercent }}%"></div>
    </div>
</div>

<div class="dashboard-action-grid mb-4">
    <a href="{{ route('nutrition.index') }}" class="dashboard-action-card">
        <i class="bi bi-egg-fried"></i>
        <span>Registrar refeição</span>
    </a>

    <a href="{{ route('water.index') }}" class="dashboard-action-card">
        <i class="bi bi-droplet"></i>
        <span>Registrar água</span>
    </a>

    <a href="{{ route('workouts.index') }}" class="dashboard-action-card">
        <i class="bi bi-activity"></i>
        <span>Montar treino</span>
    </a>

    <a href="{{ route('workouts.history') }}" class="dashboard-action-card">
        <i class="bi bi-clock-history"></i>
        <span>Histórico</span>
    </a>

    <a href="{{ route('smart-goals.index') }}" class="dashboard-action-card">
        <i class="bi bi-bullseye"></i>
        <span>Metas inteligentes</span>
    </a>

    <a href="{{ route('weekly-summary.index') }}" class="dashboard-action-card">
        <i class="bi bi-calendar2-week"></i>
        <span>Resumo semanal</span>
    </a>
</div>

<div class="dashboard-metrics-grid mb-4">
    <div class="dashboard-metric-card">
        <i class="bi bi-speedometer2"></i>
        <span>Peso atual</span>
        <strong>{{ number_format($latestWeight, 1) }} kg</strong>
        <small>IMC {{ number_format($imc, 1) }} · {{ $metrics['bmi_category']['label'] }}</small>
    </div>

    <div class="dashboard-metric-card">
        <i class="bi bi-arrow-down-circle"></i>
        <span>Peso perdido</span>
        <strong>{{ number_format($weightLost, 1) }} kg</strong>
        <small>Desde o início</small>
    </div>

    <div class="dashboard-metric-card">
        <i class="bi bi-droplet"></i>
        <span>Água hoje</span>
        <strong>{{ number_format($waterToday) }} ml</strong>
        <small>Meta {{ number_format($waterGoal) }} ml</small>
    </div>

    <div class="dashboard-metric-card">
        <i class="bi bi-activity"></i>
        <span>Treinos</span>
        <strong>{{ $workoutsLast30Days }}</strong>
        <small>últimos 30 dias</small>
    </div>
</div>

<div class="motivation-card mb-4">
    <i class="bi bi-trophy"></i>

    <div>
        <h4>
            @if($progressPercent >= 100)
                Meta alcançada!
            @elseif($progressPercent >= 50)
                Você está mais da metade do caminho!
            @else
                Continue construindo consistência.
            @endif
        </h4>

        <p>
            Progresso atual: {{ number_format($progressPercent, 1) }}%.
            @if($remainingWeight > 0)
                Faltam {{ number_format($remainingWeight, 1) }} kg para sua meta.
            @else
                Você já atingiu ou passou da meta definida.
            @endif
        </p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="panel dashboard-panel-premium">
            <div class="dashboard-panel-header">
                <div>
                    <span>Evolução corporal</span>
                    <h4>Peso ao longo do tempo</h4>
                </div>

                <a href="{{ route('weights.create') }}" class="dashboard-text-link">
                    Nova pesagem
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div id="weightChart"></div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="panel dashboard-panel-premium">
            <div class="dashboard-panel-header">
                <div>
                    <span>Hidratação</span>
                    <h4>Água de hoje</h4>
                </div>

                <strong class="dashboard-panel-value">{{ number_format($waterPercent, 0) }}%</strong>
            </div>

            <div id="waterChart"></div>

            <div class="dashboard-mini-summary">
                <div>
                    <span>Consumido</span>
                    <strong>{{ number_format($waterToday) }} ml</strong>
                </div>

                <div>
                    <span>Meta</span>
                    <strong>{{ number_format($waterGoal) }} ml</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel nutrition-dashboard-panel mb-4">
    <div class="dashboard-panel-header">
        <div>
            <span>Nutrição de hoje</span>
            <h4>Macros registrados</h4>
        </div>

        <a href="{{ route('nutrition.index') }}" class="dashboard-text-link">
            Registrar refeição
            <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row g-4">
        @php
            $nutritionCards = [
                [
                    'icon' => 'bi-egg-fried',
                    'label' => 'Proteína',
                    'value' => $todayNutrition->protein ?? 0,
                    'goal' => $nutritionGoals['protein'],
                    'suffix' => 'g',
                ],
                [
                    'icon' => 'bi-basket',
                    'label' => 'Carboidratos',
                    'value' => $todayNutrition->carbs ?? 0,
                    'goal' => $nutritionGoals['carbs'],
                    'suffix' => 'g',
                ],
                [
                    'icon' => 'bi-droplet-half',
                    'label' => 'Gorduras',
                    'value' => $todayNutrition->fat ?? 0,
                    'goal' => $nutritionGoals['fat'],
                    'suffix' => 'g',
                ],
                [
                    'icon' => 'bi-fire',
                    'label' => 'Calorias',
                    'value' => $todayNutrition->calories ?? 0,
                    'goal' => $nutritionGoals['calories'],
                    'suffix' => 'kcal',
                ],
            ];
        @endphp

        @foreach($nutritionCards as $card)
            @php
                $percent = $card['goal'] > 0 ? min(100, ($card['value'] / $card['goal']) * 100) : 0;
            @endphp

            <div class="col-md-6 col-xl-3">
                <div class="nutrition-mini-card">
                    <div class="nutrition-icon">
                        <i class="bi {{ $card['icon'] }}"></i>
                    </div>

                    <div class="label">{{ $card['label'] }}</div>

                    <div class="value">
                        {{ number_format($card['value'], $card['suffix'] === 'kcal' ? 0 : 1) }}{{ $card['suffix'] === 'kcal' ? '' : 'g' }}
                    </div>

                    <small class="text-secondary">
                        Meta: {{ $card['goal'] }} {{ $card['suffix'] }}
                    </small>

                    <div class="body-progress mt-3">
                        <div class="body-progress-bar" style="width: {{ $percent }}%"></div>
                    </div>

                    <small class="nutrition-percent">
                        {{ number_format($percent, 1) }}%
                    </small>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-6">
        <div class="panel dashboard-panel-premium">
            <div class="dashboard-panel-header">
                <div>
                    <span>Treino</span>
                    <h4>Status do treino</h4>
                </div>

                <a href="{{ route('workouts.index') }}" class="dashboard-text-link">
                    Abrir treino
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="dashboard-status-list">
                <div class="dashboard-status-item">
                    <i class="bi bi-calendar-check"></i>
                    <div>
                        <strong>{{ $todayWorkouts }} treino(s) hoje</strong>
                        <span>
                            @if($lastWorkout)
                                Último treino em {{ $lastWorkout->workout_date->format('d/m/Y') }}
                            @else
                                Nenhum treino registrado ainda.
                            @endif
                        </span>
                    </div>
                </div>

                <div class="dashboard-status-item">
                    <i class="bi bi-egg-fried"></i>
                    <div>
                        <strong>{{ $mealsToday }} alimento(s) registrado(s)</strong>
                        <span>Continue atualizando suas refeições do dia.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="panel dashboard-panel-premium">
            <div class="dashboard-panel-header">
                <div>
                    <span>Alertas inteligentes</span>
                    <h4>Prioridade de hoje</h4>
                </div>
            </div>

            <div class="dashboard-status-list">
                @foreach($dailyInsights as $insight)
                    <a href="{{ route($insight['route']) }}" class="dashboard-status-item link insight-{{ $insight['tone'] }}">
                        <i class="bi {{ $insight['icon'] }}"></i>
                        <div>
                            <strong>{{ $insight['title'] }}</strong>
                            <span>{{ $insight['description'] }}</span>
                            <small>{{ $insight['action'] }}</small>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const green = "#a3e635";

    new ApexCharts(document.querySelector("#weightChart"), {
        chart: {
            type: 'area',
            height: 330,
            toolbar: { show: false },
            foreColor: '#9ca3af',
            background: 'transparent'
        },
        series: [{
            name: 'Peso',
            data: @json($chartWeights)
        }],
        xaxis: {
            categories: @json($chartDates),
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        colors: [green],
        stroke: {
            curve: 'smooth',
            width: 4
        },
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: 0.45,
                opacityTo: 0.05
            }
        },
        grid: {
            borderColor: 'rgba(255,255,255,.08)',
            strokeDashArray: 4
        },
        dataLabels: { enabled: false },
        tooltip: { theme: 'dark' }
    }).render();

    new ApexCharts(document.querySelector("#waterChart"), {
        chart: {
            type: 'radialBar',
            height: 280,
            background: 'transparent'
        },
        series: [{{ number_format($waterPercent, 1, '.', '') }}],
        labels: ['Água'],
        colors: [green],
        plotOptions: {
            radialBar: {
                hollow: { size: '68%' },
                track: { background: 'rgba(163,230,53,.10)' },
                dataLabels: {
                    name: {
                        color: '#9ca3af',
                        fontSize: '13px',
                        fontWeight: 900
                    },
                    value: {
                        color: '#fff',
                        fontSize: '32px',
                        fontWeight: 900,
                        formatter: function (value) {
                            return Math.round(value) + '%';
                        }
                    }
                }
            }
        }
    }).render();
});
</script>
@endsection
