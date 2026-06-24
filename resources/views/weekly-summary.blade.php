@extends('layouts.bodytrack')

@section('title', 'Resumo Semanal - BodyTrack')

@section('content')

<div class="weekly-summary-page">
    <section class="weekly-summary-hero">
        <div>
            <span class="weekly-summary-kicker">
                <i class="bi bi-calendar2-week"></i>
                Ultimos 7 dias
            </span>

            <h1>Resumo Semanal</h1>

            <p>
                Uma leitura direta da sua semana: consistencia, nutricao, agua, treinos, peso e pontos para melhorar.
            </p>
        </div>

        <div class="weekly-summary-score">
            <span>Aderencia geral</span>
            <strong>{{ number_format((float) $weeklyScore, 0, ',', '.') }}%</strong>
            <small>Media dos ultimos 7 dias</small>
        </div>
    </section>

    <section class="weekly-summary-overview">
        <article>
            <span>Proteina media</span>
            <strong>{{ number_format((float) $averages['protein'], 1, ',', '.') }}g</strong>
            <small>Meta {{ $nutritionGoals['protein'] }}g/dia</small>
        </article>

        <article>
            <span>Agua media</span>
            <strong>{{ number_format((float) $averages['water'], 0, ',', '.') }}ml</strong>
            <small>Meta {{ number_format((float) $metrics['water_goal'], 0, ',', '.') }}ml/dia</small>
        </article>

        <article>
            <span>Treinos</span>
            <strong>{{ $totals['workouts'] }}</strong>
            <small>Meta base 3 por semana</small>
        </article>

        <article>
            <span>Peso</span>
            <strong>
                @if($weightDiff !== null)
                    {{ $weightDiff <= 0 ? '-' : '+' }}{{ number_format(abs((float) $weightDiff), 1, ',', '.') }}kg
                @else
                    --
                @endif
            </strong>
            <small>Variacao no periodo</small>
        </article>
    </section>

    <section class="weekly-summary-layout">
        <div class="weekly-summary-panel is-large">
            <div class="weekly-summary-header">
                <div>
                    <span>Performance</span>
                    <h2>Consistencia por dia</h2>
                </div>

                <small>Melhor dia: {{ $bestDay['label'] ?? '--' }}</small>
            </div>

            <div id="weeklyScoreChart"></div>
        </div>

        <div class="weekly-summary-panel">
            <div class="weekly-summary-header">
                <div>
                    <span>Destaques</span>
                    <h2>Leitura da semana</h2>
                </div>
            </div>

            <div class="weekly-highlight-list">
                @foreach($highlights as $highlight)
                    <article class="weekly-highlight-item highlight-{{ $highlight['tone'] }}">
                        <i class="bi {{ $highlight['icon'] }}"></i>

                        <div>
                            <strong>{{ $highlight['title'] }}</strong>
                            <span>{{ $highlight['text'] }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="weekly-summary-panel">
        <div class="weekly-summary-header">
            <div>
                <span>Detalhamento</span>
                <h2>Dia a dia</h2>
            </div>

            <a href="{{ route('smart-goals.index') }}" class="weekly-summary-link">
                Ver metas inteligentes
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="weekly-day-grid">
            @foreach($days as $day)
                <article class="weekly-day-card">
                    <div class="weekly-day-head">
                        <div>
                            <span>{{ $day['weekday'] }}</span>
                            <strong>{{ $day['label'] }}</strong>
                        </div>

                        <small>{{ number_format((float) $day['score'], 0, ',', '.') }}%</small>
                    </div>

                    <div class="body-progress">
                        <div class="body-progress-bar" style="width: {{ $day['score'] }}%"></div>
                    </div>

                    <div class="weekly-day-metrics">
                        <span>Proteina <strong>{{ number_format((float) $day['protein'], 1, ',', '.') }}g</strong></span>
                        <span>Agua <strong>{{ number_format((float) $day['water'], 0, ',', '.') }}ml</strong></span>
                        <span>Calorias <strong>{{ number_format((float) $day['calories'], 0, ',', '.') }}</strong></span>
                        <span>Treinos <strong>{{ $day['workouts'] }}</strong></span>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="weekly-summary-layout">
        <div class="weekly-summary-panel">
            <div class="weekly-summary-header">
                <div>
                    <span>Macros</span>
                    <h2>Medias nutricionais</h2>
                </div>
            </div>

            <div id="weeklyNutritionChart"></div>
        </div>

        <div class="weekly-summary-panel">
            <div class="weekly-summary-header">
                <div>
                    <span>Check-ins</span>
                    <h2>Registros da semana</h2>
                </div>

                <small>{{ $checkIns->count() }} check-in(s)</small>
            </div>

            <div class="weekly-checkin-list">
                @forelse($checkIns as $checkIn)
                    <article>
                        <strong>{{ $checkIn->check_in_date->format('d/m/Y') }}</strong>
                        <span>
                            Energia {{ $checkIn->energy_level ?? '-' }}/5 · Humor {{ $checkIn->mood_level ?? '-' }}/5 · Sono {{ $checkIn->sleep_quality ?? '-' }}/5
                        </span>
                        <small>{{ $checkIn->notes ?: 'Sem observacao.' }}</small>
                    </article>
                @empty
                    <div class="weekly-empty-state">
                        <i class="bi bi-clipboard2-pulse"></i>
                        <strong>Nenhum check-in nesta semana</strong>
                        <span>Registrar um check-in melhora a leitura do resumo semanal.</span>
                    </div>
                @endforelse
            </div>
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
    const chartText = '#9ca3af';
    const gridColor = 'rgba(255,255,255,.08)';

    new ApexCharts(document.querySelector('#weeklyScoreChart'), {
        chart: {
            type: 'area',
            height: 320,
            toolbar: { show: false },
            foreColor: chartText,
            background: 'transparent'
        },
        series: [{
            name: 'Aderencia',
            data: @json($scoreSeries)
        }],
        xaxis: {
            categories: @json($chartLabels),
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            max: 100,
            labels: {
                formatter: function (value) {
                    return Math.round(value) + '%';
                }
            }
        },
        colors: [green],
        stroke: { curve: 'smooth', width: 4 },
        fill: {
            type: 'gradient',
            gradient: { opacityFrom: .42, opacityTo: .04 }
        },
        grid: {
            borderColor: gridColor,
            strokeDashArray: 4
        },
        dataLabels: { enabled: false },
        tooltip: { theme: 'dark' }
    }).render();

    new ApexCharts(document.querySelector('#weeklyNutritionChart'), {
        chart: {
            type: 'bar',
            height: 300,
            toolbar: { show: false },
            foreColor: chartText,
            background: 'transparent'
        },
        series: [
            { name: 'Proteina', data: @json($proteinSeries) },
            { name: 'Agua em litros', data: @json($waterSeries) }
        ],
        xaxis: {
            categories: @json($chartLabels),
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        colors: [green, blue],
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '50%'
            }
        },
        grid: {
            borderColor: gridColor,
            strokeDashArray: 4
        },
        dataLabels: { enabled: false },
        tooltip: { theme: 'dark' }
    }).render();
});
</script>
@endsection
