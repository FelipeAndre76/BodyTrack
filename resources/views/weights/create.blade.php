@extends('layouts.bodytrack')

@section('title', 'Registrar Pesagem')

@section('content')

<div class="weight-page">
    <section class="weight-hero">
        <div>
            <span class="weight-kicker">
                <i class="bi bi-speedometer2"></i>
                Evolucao corporal
            </span>

            <h1>Registrar Pesagem</h1>

            <p>
                Atualize seu peso e acompanhe sua evolucao em direcao a meta definida no perfil corporal.
            </p>
        </div>

        <div class="weight-current-card">
            <span>Peso atual</span>
            <strong>{{ number_format((float) $latestWeight, 1, ',', '.') }}kg</strong>
            <small>Meta: {{ number_format((float) $goalWeight, 1, ',', '.') }}kg</small>
        </div>
    </section>

    <section class="weight-summary-grid">
        <article class="weight-summary-card">
            <span>Peso inicial</span>
            <strong>{{ number_format((float) $startWeight, 1, ',', '.') }}kg</strong>
        </article>

        <article class="weight-summary-card">
            <span>Eliminado</span>
            <strong>{{ number_format((float) $weightLost, 1, ',', '.') }}kg</strong>
        </article>

        <article class="weight-summary-card">
            <span>Falta para meta</span>
            <strong>{{ number_format((float) $remainingWeight, 1, ',', '.') }}kg</strong>
        </article>

        <article class="weight-summary-card">
            <span>Progresso</span>
            <strong>{{ number_format((float) $progressPercent, 0, ',', '.') }}%</strong>
        </article>
    </section>

    <section class="weight-progress-panel">
        <div class="weight-progress-header">
            <div>
                <span>Meta corporal</span>
                <strong>{{ number_format((float) $progressPercent, 1, ',', '.') }}% concluido</strong>
            </div>

            <small>
                {{ number_format((float) $latestWeight, 1, ',', '.') }}kg de
                {{ number_format((float) $goalWeight, 1, ',', '.') }}kg
            </small>
        </div>

        <div class="body-progress">
            <div class="body-progress-bar" style="width: {{ $progressPercent }}%"></div>
        </div>
    </section>

    <div class="weight-content-grid">
        <section class="weight-form-panel">
            <div class="weight-section-header">
                <div>
                    <span>Novo registro</span>
                    <h2>Salvar pesagem</h2>
                </div>

                <i class="bi bi-plus-circle"></i>
            </div>

            <form action="{{ route('weights.store') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="label mb-2">Peso atual (kg)</label>

                        <input
                            type="number"
                            step="0.01"
                            name="weight"
                            class="form-control body-input"
                            value="{{ old('weight') }}"
                            placeholder="Ex: 110.10"
                            required>
                    </div>

                    <div class="col-md-6">
                        <label class="label mb-2">Data da pesagem</label>

                        <input
                            type="date"
                            name="recorded_at"
                            value="{{ old('recorded_at', date('Y-m-d')) }}"
                            class="form-control body-input"
                            required>
                    </div>
                </div>

                @if($errors->any())
                    <div class="weight-form-alert">
                        Confira os campos e tente salvar novamente.
                    </div>
                @endif

                <button class="btn-bodytrack weight-submit-button">
                    <i class="bi bi-check-circle"></i>
                    Salvar pesagem
                </button>
            </form>
        </section>

        <section class="weight-history-panel">
            <div class="weight-section-header">
                <div>
                    <span>Ultimos registros</span>
                    <h2>Historico</h2>
                </div>

                <small>{{ $weightLogs->count() }} registro(s)</small>
            </div>

            <div class="weight-history-list">
                @forelse($weightLogs as $index => $log)
                    @php
                        $previousLog = $weightLogs->get($index + 1);
                        $difference = $previousLog ? (float) $log->weight - (float) $previousLog->weight : 0;
                    @endphp

                    <article class="weight-history-card">
                        <div class="weight-history-date">
                            <i class="bi bi-calendar2-check"></i>
                            <span>{{ $log->recorded_at->format('d/m/Y') }}</span>
                        </div>

                        <strong>{{ number_format((float) $log->weight, 1, ',', '.') }}kg</strong>

                        @if($previousLog)
                            <small class="{{ $difference <= 0 ? 'is-positive' : 'is-warning' }}">
                                {{ $difference <= 0 ? '-' : '+' }}{{ number_format(abs($difference), 1, ',', '.') }}kg desde o registro anterior
                            </small>
                        @else
                            <small>Primeiro registro da lista</small>
                        @endif
                    </article>
                @empty
                    <div class="weight-empty-state">
                        <i class="bi bi-journal-plus"></i>
                        <strong>Nenhuma pesagem registrada</strong>
                        <span>Salve sua primeira pesagem para montar o historico.</span>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>

@endsection
