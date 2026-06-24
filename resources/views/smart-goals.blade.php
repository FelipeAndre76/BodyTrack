@extends('layouts.bodytrack')

@section('title', 'Metas Inteligentes - BodyTrack')

@section('content')

<div class="smart-goals-page">
    <section class="smart-goals-hero">
        <div>
            <span class="smart-goals-kicker">
                <i class="bi bi-bullseye"></i>
                Plano diario
            </span>

            <h1>Metas Inteligentes</h1>

            <p>
                Veja seus alvos calculados, o progresso de hoje e os ajustes mais importantes para manter a evolucao consistente.
            </p>
        </div>

        <div class="smart-goals-score">
            <span>IMC atual</span>
            <strong>{{ number_format((float) $metrics['bmi'], 1, ',', '.') }}</strong>
            <small>{{ $metrics['bmi_category']['label'] }}</small>
        </div>
    </section>

    <section class="smart-goals-overview">
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

        <article>
            <span>Proteina diaria</span>
            <strong>{{ $nutritionGoals['protein'] }}g</strong>
            <small>{{ number_format((float) $metrics['protein_factor'], 1, ',', '.') }}g por kg corporal</small>
        </article>

        <article>
            <span>Agua base</span>
            <strong>{{ number_format((float) $metrics['water_goal'], 0, ',', '.') }}ml</strong>
            <small>{{ number_format((float) $metrics['training_water_goal'], 0, ',', '.') }}ml em dia de treino</small>
        </article>

        <article>
            <span>Tendencia de peso</span>
            <strong>
                @if($weightTrend['value'] !== null)
                    {{ $weightTrend['value'] < 0 ? '-' : '+' }}{{ number_format(abs((float) $weightTrend['value']), 1, ',', '.') }}kg
                @else
                    --
                @endif
            </strong>
            <small>{{ $weightTrend['description'] }}</small>
        </article>
    </section>

    <section class="smart-goals-panel">
        <div class="smart-goals-header">
            <div>
                <span>Hoje</span>
                <h2>Alvos principais</h2>
            </div>

            <a href="{{ route('body-profile.create') }}" class="smart-goals-link">
                Ajustar perfil
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="smart-goals-grid">
            @foreach($goalCards as $card)
                <article class="smart-goal-card goal-{{ $card['tone'] }}">
                    <div class="smart-goal-icon">
                        <i class="bi {{ $card['icon'] }}"></i>
                    </div>

                    <div>
                        <span>{{ $card['label'] }}</span>
                        <strong>
                            {{ number_format((float) $card['value'], $card['suffix'] === 'kcal' || $card['suffix'] === 'ml' || $card['suffix'] === '/semana' ? 0 : 1, ',', '.') }}{{ $card['suffix'] === '/semana' ? '' : ' ' . $card['suffix'] }}
                        </strong>
                        <small>Meta {{ number_format((float) $card['goal'], $card['suffix'] === 'kcal' || $card['suffix'] === 'ml' || $card['suffix'] === '/semana' ? 0 : 1, ',', '.') }}{{ $card['suffix'] === '/semana' ? ' por semana' : ' ' . $card['suffix'] }}</small>
                    </div>

                    <div class="body-progress">
                        <div class="body-progress-bar" style="width: {{ $card['percent'] }}%"></div>
                    </div>

                    <div class="smart-goal-footer">
                        <span>{{ number_format((float) $card['percent'], 0, ',', '.') }}%</span>
                        <small>{{ $card['note'] }}</small>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section class="smart-goals-layout">
        <div class="smart-goals-panel">
            <div class="smart-goals-header">
                <div>
                    <span>Prioridade</span>
                    <h2>Proximas acoes</h2>
                </div>
            </div>

            <div class="smart-goals-action-list">
                @foreach($dailyInsights as $insight)
                    <a href="{{ route($insight['route']) }}" class="smart-goals-action insight-{{ $insight['tone'] }}">
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

        <div class="smart-goals-panel">
            <div class="smart-goals-header">
                <div>
                    <span>Estrategia</span>
                    <h2>Ajustes recomendados</h2>
                </div>
            </div>

            <div class="smart-goals-strategy-list">
                @foreach($strategies as $strategy)
                    <article>
                        <i class="bi {{ $strategy['icon'] }}"></i>

                        <div>
                            <strong>{{ $strategy['title'] }}</strong>
                            <span>{{ $strategy['text'] }}</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</div>

@endsection
