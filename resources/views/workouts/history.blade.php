@extends('layouts.bodytrack')

@section('title', 'Historico de Treinos')

@section('content')
@php
    $allWorkouts = $workouts->flatten(1);
    $totalWorkouts = $allWorkouts->count();
    $totalExercises = $allWorkouts->sum(fn ($workout) => $workout->items->count());
    $totalSets = $allWorkouts->sum(fn ($workout) => $workout->items->sum('sets'));
    $totalLoad = $allWorkouts->sum(function ($workout) {
        return $workout->items->sum(function ($item) {
            return (float) ($item->weight ?? 0) * (int) ($item->sets ?? 0) * (int) ($item->reps ?? 0);
        });
    });
    $lastWorkout = $allWorkouts->sortByDesc('workout_date')->first();
@endphp

<div class="training-history-page">
    <section class="training-history-hero">
        <div>
            <span class="section-kicker">
                <i class="bi bi-clock-history"></i>
                Evolucao de treino
            </span>

            <h1>Historico de Treinos</h1>

            <p>
                Veja seus treinos salvos, acompanhe volume, exercicios e cargas registradas.
            </p>
        </div>

        <a href="{{ route('workouts.index') }}" class="training-history-action">
            <i class="bi bi-plus-lg"></i>
            Novo treino
        </a>
    </section>

    <section class="training-history-stats">
        <article class="training-history-stat">
            <span>Treinos salvos</span>
            <strong>{{ $totalWorkouts }}</strong>
        </article>

        <article class="training-history-stat">
            <span>Exercicios feitos</span>
            <strong>{{ $totalExercises }}</strong>
        </article>

        <article class="training-history-stat">
            <span>Series registradas</span>
            <strong>{{ $totalSets }}</strong>
        </article>

        <article class="training-history-stat">
            <span>Volume estimado</span>
            <strong>{{ number_format($totalLoad, 0, ',', '.') }}kg</strong>
        </article>
    </section>

    @if($lastWorkout)
        <section class="training-history-highlight">
            <div class="training-history-highlight-icon">
                <i class="bi bi-lightning-charge"></i>
            </div>

            <div>
                <span>Ultimo treino registrado</span>
                <strong>{{ $lastWorkout->name }}</strong>
                <small>{{ $lastWorkout->workout_date->format('d/m/Y') }} com {{ $lastWorkout->items->count() }} exercicio(s)</small>
            </div>
        </section>
    @endif

    @forelse($workouts as $date => $dayWorkouts)
        <section class="training-history-day">
            <div class="training-history-day-header">
                <div>
                    <span>Dia de treino</span>
                    <h2>{{ $date }}</h2>
                </div>

                <small>{{ $dayWorkouts->count() }} treino(s) salvo(s)</small>
            </div>

            <div class="training-history-grid">
                @foreach($dayWorkouts as $workout)
                    <article class="training-history-card">
                        <header class="training-history-card-header">
                            <div>
                                <span>{{ $workout->workout_date->format('d/m/Y') }}</span>
                                <h3>{{ $workout->name }}</h3>
                            </div>

                            <strong>{{ $workout->items->count() }} itens</strong>
                        </header>

                        <div class="training-exercise-slider"
                             data-training-slider="workout-{{ $workout->id }}"
                             data-current-index="0">
                            @foreach($workout->items as $index => $item)
                                <div class="training-exercise-slide {{ $index === 0 ? 'active' : '' }}">
                                    <div class="training-exercise-media">
                                        @if($item->exercise && $item->exercise->image_path)
                                            <img src="{{ asset($item->exercise->image_path) }}" alt="{{ $item->exercise->name }}">
                                        @else
                                            <div class="training-exercise-placeholder">
                                                <i class="bi bi-activity"></i>
                                            </div>
                                        @endif

                                        <span>{{ $index + 1 }} / {{ $workout->items->count() }}</span>
                                    </div>

                                    <div class="training-exercise-content">
                                        <small>Exercicio</small>
                                        <h4>{{ $item->exercise->name ?? 'Exercicio removido' }}</h4>

                                        <div class="training-exercise-metrics">
                                            <div>
                                                <span>Series</span>
                                                <strong>{{ $item->sets }}</strong>
                                            </div>

                                            <div>
                                                <span>Reps</span>
                                                <strong>{{ $item->reps }}</strong>
                                            </div>

                                            <div>
                                                <span>Carga</span>
                                                <strong>{{ number_format((float) $item->weight, 1, ',', '.') }}kg</strong>
                                            </div>
                                        </div>

                                        @if($item->notes)
                                            <p class="training-exercise-notes">
                                                {{ $item->notes }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($workout->items->count() > 1)
                            <div class="training-slider-controls">
                                <button type="button"
                                        class="training-slider-button training-prev"
                                        data-target="workout-{{ $workout->id }}"
                                        aria-label="Exercicio anterior">
                                    <i class="bi bi-chevron-left"></i>
                                </button>

                                <button type="button"
                                        class="training-slider-button training-next"
                                        data-target="workout-{{ $workout->id }}"
                                        aria-label="Proximo exercicio">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <section class="training-history-empty">
            <i class="bi bi-calendar2-plus"></i>
            <h2>Nenhum treino registrado ainda</h2>
            <p>Monte seu primeiro treino para acompanhar sua evolucao por aqui.</p>
            <a href="{{ route('workouts.index') }}" class="training-history-action">
                <i class="bi bi-plus-lg"></i>
                Criar treino
            </a>
        </section>
    @endforelse
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function showTrainingSlide(sliderId, direction) {
        const slider = document.querySelector(`[data-training-slider="${sliderId}"]`);

        if (!slider) {
            return;
        }

        const slides = slider.querySelectorAll('.training-exercise-slide');

        if (!slides.length) {
            return;
        }

        let currentIndex = Number(slider.dataset.currentIndex || 0);
        slides[currentIndex].classList.remove('active');

        currentIndex += direction;

        if (currentIndex < 0) {
            currentIndex = slides.length - 1;
        }

        if (currentIndex >= slides.length) {
            currentIndex = 0;
        }

        slides[currentIndex].classList.add('active');
        slider.dataset.currentIndex = currentIndex;
    }

    document.querySelectorAll('.training-prev').forEach(button => {
        button.addEventListener('click', function () {
            showTrainingSlide(this.dataset.target, -1);
        });
    });

    document.querySelectorAll('.training-next').forEach(button => {
        button.addEventListener('click', function () {
            showTrainingSlide(this.dataset.target, 1);
        });
    });
});
</script>
@endsection
