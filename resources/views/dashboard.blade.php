@extends('layouts.bodytrack')

@section('title', 'BodyTrack - Dashboard')


@php
$progressPercent = 0;

if ($startWeight > $goalWeight) {
$totalToLose = $startWeight - $goalWeight;
$alreadyLost = $startWeight - $latestWeight;
$progressPercent = $totalToLose > 0 ? ($alreadyLost / $totalToLose) * 100 : 0;
}

$progressPercent = max(0, min(100, $progressPercent));

@endphp
@section('content')

<div class="premium-hero mb-4">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <p class="hero-welcome mb-1">Olá, {{ Auth::user()->name }} 👋</p>

            <h1 class="hero-title mb-2">
                Você já eliminou {{ number_format($weightLost, 1) }} kg
            </h1>

            <p class="hero-subtitle mb-0">
                Faltam apenas {{ number_format($remainingWeight, 1) }} kg para atingir sua meta.
            </p>
        </div>

        <a href="{{ route('weights.create') }}" class="hero-button">
            <i class="bi bi-plus-circle me-2"></i>
            Nova Pesagem
        </a>
    </div>

    <div class="hero-stats mt-4">
        <div>
            <span>Peso Atual</span>
            <strong>{{ number_format($latestWeight, 1) }} kg</strong>
        </div>

        <div>
            <span>Meta</span>
            <strong>{{ number_format($goalWeight, 1) }} kg</strong>
        </div>

        <div>
            <span>Progresso</span>
            <strong>{{ number_format($progressPercent, 1) }}%</strong>
        </div>
    </div>

    <div class="body-progress mt-4">
        <div class="body-progress-bar" style="width: {{ $progressPercent }}%"></div>
    </div>
</div>

<div class="motivation-card mb-4">
    <i class="bi bi-trophy"></i>

    <div>
        <h4>Excelente trabalho!</h4>
        <p>
            Você já eliminou 14.1 kg.
            Está mais da metade do caminho até sua meta.
        </p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="top-card">
            <div class="label">
                <i class="bi bi-speedometer me-1"></i>
                Peso Atual
            </div>
            <div class="value">{{ number_format($latestWeight, 1) }} kg</div>
            <small class="text-secondary">Atualizado hoje</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="top-card">
            <div class="label">
                <i class="bi bi-arrow-down-circle me-1"></i>
                Peso Perdido
            </div>
            <div class="value">{{ number_format($weightLost, 1) }} kg</div>
            <small class="text-secondary">Desde o início</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="top-card">
            <div class="label">
                <i class="bi bi-bullseye me-1"></i>
                Meta Restante
            </div>
            <div class="value">{{ number_format($remainingWeight, 1) }} kg</div>
            <small class="text-secondary">Até 95 kg</small>
        </div>
    </div>

    <div class="col-md-3">
        <div class="top-card">
            <div class="label">
                <i class="bi bi-calculator me-1"></i>
                IMC Atual
            </div>
            <div class="value">{{ number_format($imc, 2) }}</div>
            <small class="text-secondary">Baseado em 1.80 m</small>
        </div>
    </div>
</div>

<div class="panel nutrition-dashboard-panel mt-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-egg-fried me-2 text-success"></i>
                Nutrição de Hoje
            </h4>
            <p class="text-secondary mb-0">
                Acompanhe seus macros registrados nas refeições de hoje
            </p>
        </div>

        <a href="{{ route('nutrition.index') }}" class="hero-button">
            Registrar refeição
        </a>
    </div>

    <div class="row g-4">
        @php
            $nutritionCards = [
                [
                    'icon' => '🥩',
                    'label' => 'Proteína',
                    'value' => $todayNutrition->protein ?? 0,
                    'goal' => $nutritionGoals['protein'],
                    'suffix' => 'g',
                ],
                [
                    'icon' => '🍚',
                    'label' => 'Carboidratos',
                    'value' => $todayNutrition->carbs ?? 0,
                    'goal' => $nutritionGoals['carbs'],
                    'suffix' => 'g',
                ],
                [
                    'icon' => '🥑',
                    'label' => 'Gorduras',
                    'value' => $todayNutrition->fat ?? 0,
                    'goal' => $nutritionGoals['fat'],
                    'suffix' => 'g',
                ],
                [
                    'icon' => '🔥',
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
                    <div class="nutrition-icon">{{ $card['icon'] }}</div>

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

    <div class="col-lg-12">
        <div class="panel">
            <div class="collapse-header" data-bs-toggle="collapse" data-bs-target="#weightCollapse" aria-expanded="false" aria-controls="weightCollapse">

                <h4>
                    <i class="bi bi-graph-down-arrow me-2 text-success"></i>
                    Evolução de Peso
                </h4>

                <i class="bi bi-chevron-down"></i>
            </div>

            <div class="collapse" id="weightCollapse">
                <div class="mt-4">
                    <div id="weightChart"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="panel">
            <div class="collapse-header" data-bs-toggle="collapse" data-bs-target="#proteinCollapse" aria-expanded="false" aria-controls="proteinCollapse">

                <h4>
                    <i class="bi bi-egg-fried me-2 text-success"></i>
                    Meta de Proteína
                </h4>

                <i class="bi bi-chevron-down"></i>
            </div>

            <div class="collapse" id="proteinCollapse">
                <div class="mt-4">
                    <div id="proteinChart"></div>

                    <div class="mt-3">
                        <div class="label">Consumido hoje</div>
                        <div class="value">135g / 180g</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const green = "#a3e635";

        let weightChartRendered = false;
        let proteinChartRendered = false;

        function renderWeightChart() {
            if (weightChartRendered) return;

            new ApexCharts(document.querySelector("#weightChart"), {
                chart: {
                    type: 'area'
                    , height: 320
                    , toolbar: {
                        show: false
                    }
                    , foreColor: '#9ca3af'
                }
                , series: [{
                    name: 'Peso'
                    , data: @json($chartWeights)
                }]
                , xaxis: {
                    categories: @json($chartDates)
                }
                , colors: [green]
                , stroke: {
                    curve: 'smooth'
                    , width: 4
                }
                , fill: {
                    type: 'gradient'
                    , gradient: {
                        opacityFrom: 0.45
                        , opacityTo: 0.05
                    }
                }
                , grid: {
                    borderColor: 'rgba(255,255,255,.08)'
                }
            }).render();

            weightChartRendered = true;
        }

        function renderProteinChart() {
            if (proteinChartRendered) return;

            new ApexCharts(document.querySelector("#proteinChart"), {
                chart: {
                    type: 'radialBar'
                    , height: 300
                }
                , series: [75]
                , labels: ['Proteína']
                , colors: [green]
                , plotOptions: {
                    radialBar: {
                        hollow: {
                            size: '65%'
                        }
                        , dataLabels: {
                            value: {
                                color: '#fff'
                                , fontSize: '32px'
                                , fontWeight: 800
                            }
                            , name: {
                                color: '#9ca3af'
                            }
                        }
                    }
                }
            }).render();

            proteinChartRendered = true;
        }

        document
            .getElementById('weightCollapse')
            .addEventListener('shown.bs.collapse', renderWeightChart);

        document
            .getElementById('proteinCollapse')
            .addEventListener('shown.bs.collapse', renderProteinChart);
    });

</script>
@endsection
