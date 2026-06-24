@extends('layouts.bodytrack')

@section('title', 'Evolucao - BodyTrack')

@section('content')

@php
    $waterPercent = $waterGoal > 0 ? min(100, ($waterToday / $waterGoal) * 100) : 0;
    $proteinPercent = $nutritionGoals['protein'] > 0 ? min(100, (($todayNutrition->protein ?? 0) / $nutritionGoals['protein']) * 100) : 0;
    $caloriesPercent = $nutritionGoals['calories'] > 0 ? min(100, (($todayNutrition->calories ?? 0) / $nutritionGoals['calories']) * 100) : 0;
@endphp

<div class="evolution-page">
    <section class="evolution-hero">
        <div>
            <span class="evolution-kicker">
                <i class="bi bi-graph-up-arrow"></i>
                Visao geral
            </span>

            <h1>Evolucao</h1>

            <p>
                Acompanhe peso, nutricao, agua e treinos em uma unica tela para entender o ritmo da sua jornada.
            </p>
        </div>

        <div class="evolution-hero-side">
            <div class="evolution-hero-score">
                <span>Progresso corporal</span>
                <strong>{{ number_format((float) $progressPercent, 0, ',', '.') }}%</strong>
                <small>{{ number_format((float) $weightLost, 1, ',', '.') }}kg eliminados</small>
            </div>

            <a href="{{ route('evolution.report') }}" class="evolution-report-button">
                <i class="bi bi-file-earmark-pdf"></i>
                Baixar PDF
            </a>
        </div>
    </section>

    <section class="evolution-summary-grid">
        <article>
            <span>Peso atual</span>
            <strong>{{ number_format((float) $latestWeight, 1, ',', '.') }}kg</strong>
            <small>Meta {{ number_format((float) $goalWeight, 1, ',', '.') }}kg</small>
        </article>

        <article>
            <span>IMC atual</span>
            <strong>{{ number_format((float) $imc, 1, ',', '.') }}</strong>
            <small>{{ $metrics['bmi_category']['label'] }}</small>
        </article>

        <article>
            <span>Agua hoje</span>
            <strong>{{ number_format((float) $waterToday, 0, ',', '.') }}ml</strong>
            <small>{{ number_format((float) $waterPercent, 0, ',', '.') }}% da meta</small>
        </article>

        <article>
            <span>Treinos</span>
            <strong>{{ $workoutsLast14Days }}</strong>
            <small>Ultimos 14 dias</small>
        </article>
    </section>

    <section class="evolution-summary-grid">
        <article>
            <span>Proteina diaria</span>
            <strong>{{ $nutritionGoals['protein'] }}g</strong>
            <small>{{ number_format((float) $metrics['protein_factor'], 1, ',', '.') }}g/kg</small>
        </article>

        <article>
            <span>Proteina/refeicao</span>
            <strong>{{ $metrics['protein_per_meal'] }}g</strong>
            <small>Dose util {{ $metrics['protein_dose_min'] }}-{{ $metrics['protein_dose_max'] }}g</small>
        </article>

        <article>
            <span>TMB estimada</span>
            <strong>{{ number_format((float) $metrics['bmr'], 0, ',', '.') }}</strong>
            <small>Calorias em repouso</small>
        </article>

        <article>
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
            <small>Alvo {{ number_format((float) $nutritionGoals['calories'], 0, ',', '.') }} kcal</small>
        </article>
    </section>

    <section class="evolution-progress-panel">
        <div>
            <span>Meta corporal</span>
            <strong>{{ number_format((float) $remainingWeight, 1, ',', '.') }}kg restantes</strong>
            <small>{{ number_format((float) $startWeight, 1, ',', '.') }}kg inicial para {{ number_format((float) $goalWeight, 1, ',', '.') }}kg meta</small>
        </div>

        <div class="body-progress">
            <div class="body-progress-bar" style="width: {{ $progressPercent }}%"></div>
        </div>
    </section>

    <section class="evolution-insights-panel">
        <div class="evolution-card-header">
            <div>
                <span>Recomendacoes automaticas</span>
                <h2>O que ajustar hoje</h2>
            </div>

            <small>{{ count($dailyInsights) }} sugestao(oes)</small>
        </div>

        <div class="evolution-insights-grid">
            @foreach($dailyInsights as $insight)
                <a href="{{ route($insight['route']) }}" class="evolution-insight-card insight-{{ $insight['tone'] }}">
                    <i class="bi {{ $insight['icon'] }}"></i>
                    <strong>{{ $insight['title'] }}</strong>
                    <span>{{ $insight['description'] }}</span>
                    <small>{{ $insight['action'] }}</small>
                </a>
            @endforeach
        </div>
    </section>

    <section class="evolution-weekly-panel">
        <div class="evolution-card-header">
            <div>
                <span>Metas semanais</span>
                <h2>Consistencia dos ultimos 7 dias</h2>
            </div>

            <strong class="evolution-weekly-score">{{ number_format((float) $weeklyScore, 0, ',', '.') }}%</strong>
        </div>

        <div class="evolution-weekly-grid">
            <article>
                <div>
                    <span>Proteina media</span>
                    <strong>{{ number_format((float) $weeklyProteinAverage, 1, ',', '.') }}g</strong>
                    <small>Meta {{ $weeklyGoals['protein'] }}g/dia</small>
                </div>

                <div class="body-progress">
                    <div class="body-progress-bar" style="width: {{ $weeklyAdherence['protein'] }}%"></div>
                </div>
            </article>

            <article>
                <div>
                    <span>Calorias media</span>
                    <strong>{{ number_format((float) $weeklyCaloriesAverage, 0, ',', '.') }}</strong>
                    <small>Meta {{ $weeklyGoals['calories'] }} kcal/dia</small>
                </div>

                <div class="body-progress">
                    <div class="body-progress-bar" style="width: {{ $weeklyAdherence['calories'] }}%"></div>
                </div>
            </article>

            <article>
                <div>
                    <span>Agua media</span>
                    <strong>{{ number_format((float) $weeklyWaterAverage, 0, ',', '.') }}ml</strong>
                    <small>Meta {{ number_format((float) $weeklyGoals['water'], 0, ',', '.') }}ml/dia</small>
                </div>

                <div class="body-progress">
                    <div class="body-progress-bar" style="width: {{ $weeklyAdherence['water'] }}%"></div>
                </div>
            </article>

            <article>
                <div>
                    <span>Treinos</span>
                    <strong>{{ $weeklyWorkouts }}</strong>
                    <small>Meta {{ $weeklyGoals['workouts'] }} por semana</small>
                </div>

                <div class="body-progress">
                    <div class="body-progress-bar" style="width: {{ $weeklyAdherence['workouts'] }}%"></div>
                </div>
            </article>
        </div>
    </section>

    <section class="evolution-checkin-panel">
        <div class="evolution-card-header">
            <div>
                <span>Check-in semanal</span>
                <h2>Percepcao da evolucao</h2>
            </div>

            <a href="{{ route('check-ins.index') }}" class="evolution-checkin-link">
                Registrar check-in
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="evolution-checkin-content">
            <div class="evolution-checkin-photo">
                @if($latestCheckIn?->hasPhoto())
                    <img src="{{ route('check-ins.photo', $latestCheckIn) }}" alt="Foto do ultimo check-in">
                @else
                    <i class="bi bi-image"></i>
                @endif
            </div>

            <div class="evolution-checkin-summary">
                <span>Ultimo registro</span>
                <strong>{{ $latestCheckIn?->check_in_date?->format('d/m/Y') ?? 'Nenhum check-in salvo' }}</strong>
                <small>
                    @if($latestCheckIn?->notes)
                        {{ $latestCheckIn->notes }}
                    @else
                        Registre energia, humor, sono e foto para acompanhar contexto alem do peso.
                    @endif
                </small>
            </div>

            <div class="evolution-checkin-metrics">
                <article>
                    <span>Energia</span>
                    <strong>{{ number_format((float) $checkInAverages['energy'], 1, ',', '.') }}/5</strong>
                </article>

                <article>
                    <span>Humor</span>
                    <strong>{{ number_format((float) $checkInAverages['mood'], 1, ',', '.') }}/5</strong>
                </article>

                <article>
                    <span>Sono</span>
                    <strong>{{ number_format((float) $checkInAverages['sleep'], 1, ',', '.') }}/5</strong>
                </article>
            </div>
        </div>
    </section>

    <section class="evolution-chart-grid">
        <article class="evolution-chart-card is-large">
            <div class="evolution-card-header">
                <div>
                    <span>Peso</span>
                    <h2>Curva corporal</h2>
                </div>

                <small>{{ count($chartWeights) }} registro(s)</small>
            </div>

            <div id="evolutionWeightChart"></div>
        </article>

        <article class="evolution-chart-card">
            <div class="evolution-card-header">
                <div>
                    <span>Agua</span>
                    <h2>Hidratacao</h2>
                </div>

                <small>14 dias</small>
            </div>

            <div id="evolutionWaterChart"></div>
        </article>

        <article class="evolution-chart-card">
            <div class="evolution-card-header">
                <div>
                    <span>Nutricao</span>
                    <h2>Proteina</h2>
                </div>

                <small>{{ number_format((float) $proteinPercent, 0, ',', '.') }}% hoje</small>
            </div>

            <div id="evolutionProteinChart"></div>
        </article>

        <article class="evolution-chart-card">
            <div class="evolution-card-header">
                <div>
                    <span>Calorias</span>
                    <h2>Energia</h2>
                </div>

                <small>{{ number_format((float) $caloriesPercent, 0, ',', '.') }}% hoje</small>
            </div>

            <div id="evolutionCaloriesChart"></div>
        </article>

        <article class="evolution-chart-card">
            <div class="evolution-card-header">
                <div>
                    <span>Treinos</span>
                    <h2>Frequencia</h2>
                </div>

                <small>{{ number_format((float) $workoutVolume, 0, ',', '.') }}kg volume</small>
            </div>

            <div id="evolutionWorkoutChart"></div>
        </article>
    </section>

    <section class="evolution-timeline-panel">
        <div class="evolution-card-header">
            <div>
                <span>Linha do tempo</span>
                <h2>Ultimas atualizacoes</h2>
            </div>

            <small>{{ $recentEvents->count() }} eventos</small>
        </div>

        <div class="evolution-timeline-list">
            @forelse($recentEvents as $event)
                <article class="evolution-timeline-item">
                    <div class="evolution-timeline-icon">
                        <i class="bi {{ $event['icon'] }}"></i>
                    </div>

                    <div>
                        <strong>{{ $event['title'] }}</strong>
                        <span>{{ $event['description'] }}</span>
                    </div>

                    <small>{{ $event['date'] }}</small>
                </article>
            @empty
                <div class="evolution-empty-state">
                    <i class="bi bi-stars"></i>
                    <strong>Nenhum evento ainda</strong>
                    <span>Registre peso, agua, refeicoes ou treinos para preencher sua evolucao.</span>
                </div>
            @endforelse
        </div>
    </section>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const green = '#a3e635';
    const blue = '#38bdf8';
    const amber = '#fbbf24';
    const pink = '#fb7185';
    const chartText = '#9ca3af';
    const gridColor = 'rgba(255,255,255,.08)';

    function baseChartOptions(type, height) {
        return {
            chart: {
                type: type,
                height: height,
                toolbar: { show: false },
                foreColor: chartText,
                background: 'transparent'
            },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 4
            },
            dataLabels: { enabled: false },
            tooltip: { theme: 'dark' }
        };
    }

    new ApexCharts(document.querySelector('#evolutionWeightChart'), {
        ...baseChartOptions('area', 340),
        series: [{
            name: 'Peso',
            data: @json($chartWeights)
        }],
        xaxis: {
            categories: @json($chartWeightDates),
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        colors: [green],
        stroke: { curve: 'smooth', width: 4 },
        fill: {
            type: 'gradient',
            gradient: { opacityFrom: .45, opacityTo: .05 }
        }
    }).render();

    new ApexCharts(document.querySelector('#evolutionWaterChart'), {
        ...baseChartOptions('bar', 260),
        series: [{
            name: 'Agua',
            data: @json($waterSeries)
        }],
        xaxis: { categories: @json($chartDates) },
        colors: [blue],
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '45%'
            }
        }
    }).render();

    new ApexCharts(document.querySelector('#evolutionProteinChart'), {
        ...baseChartOptions('line', 260),
        series: [{
            name: 'Proteina',
            data: @json($proteinSeries)
        }],
        xaxis: { categories: @json($chartDates) },
        colors: [green],
        stroke: { curve: 'smooth', width: 4 }
    }).render();

    new ApexCharts(document.querySelector('#evolutionCaloriesChart'), {
        ...baseChartOptions('area', 260),
        series: [{
            name: 'Calorias',
            data: @json($caloriesSeries)
        }],
        xaxis: { categories: @json($chartDates) },
        colors: [amber],
        stroke: { curve: 'smooth', width: 4 },
        fill: {
            type: 'gradient',
            gradient: { opacityFrom: .35, opacityTo: .04 }
        }
    }).render();

    new ApexCharts(document.querySelector('#evolutionWorkoutChart'), {
        ...baseChartOptions('bar', 260),
        series: [{
            name: 'Treinos',
            data: @json($workoutSeries)
        }],
        xaxis: { categories: @json($chartDates) },
        colors: [pink],
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '45%'
            }
        }
    }).render();
});
</script>
@endsection
