@extends('layouts.bodytrack')

@section('title', 'Histórico de Treinos')

@section('content')



<div class="workout-history-page">
    <div class="page-title mb-4">
        Histórico de Treinos
    </div>

    @forelse($workouts as $date => $dayWorkouts)



        <div class="workout-history-grid mb-5">
            @foreach($dayWorkouts as $workout)

                <div class="workout-history-item">
                    <div class="workout-history-header">
                        <div class="workout-history-title">
                            <h4>{{ $workout->name }}</h4>
                            <span>{{ $workout->items->count() }} exercício(s)</span>
                        </div>

                        <span class="workout-history-badge">
                            {{ $workout->workout_date->format('d/m/Y') }}
                        </span>
                    </div>

                    <div class="workout-card-slider"
                         data-slider="workout-{{ $workout->id }}"
                         data-current-index="0">

                        @foreach($workout->items as $index => $item)
                            <div class="workout-exercise-card {{ $index === 0 ? 'active' : '' }}">
                                <div class="workout-card-counter">
                                    {{ $index + 1 }} / {{ $workout->items->count() }}
                                </div>

                                <div class="workout-card-image">
                                    @if($item->exercise->image_path)
                                        <img src="{{ asset($item->exercise->image_path) }}" alt="{{ $item->exercise->name }}">
                                    @else
                                        <div class="workout-card-placeholder">
                                            <i class="bi bi-activity"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="workout-card-body">
                                    <span class="workout-card-date">
                                        {{ $workout->workout_date->format('d/m/Y') }}
                                    </span>

                                    <h5>{{ $item->exercise->name }}</h5>

                                    <p>{{ $workout->name }}</p>

                                    <div class="workout-card-stats">
                                        <div class="workout-card-stat">
                                            <strong>{{ $item->sets }}</strong>
                                            <span>séries</span>
                                        </div>

                                        <div class="workout-card-stat">
                                            <strong>{{ $item->reps }}</strong>
                                            <span>reps</span>
                                        </div>

                                        <div class="workout-card-stat">
                                            <strong>{{ number_format($item->weight, 2) }}kg</strong>
                                            <span>carga</span>
                                        </div>
                                    </div>

                                    @if($item->notes)
                                        <small class="workout-card-notes">
                                            {{ $item->notes }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($workout->items->count() > 1)
                        <div class="workout-slider-controls">
                            <button type="button"
                                    class="workout-slider-button workout-prev"
                                    data-target="workout-{{ $workout->id }}">
                                <i class="bi bi-chevron-left"></i>
                            </button>

                            <button type="button"
                                    class="workout-slider-button workout-next"
                                    data-target="workout-{{ $workout->id }}">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    @endif
                </div>

            @endforeach
        </div>

    @empty
        <div class="panel">
            <p class="text-secondary mb-0">
                Nenhum treino registrado ainda.
            </p>
        </div>
    @endforelse
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function showWorkoutCard(sliderId, direction) {
        const slider = document.querySelector(`[data-slider="${sliderId}"]`);

        if (!slider) {
            return;
        }

        const cards = slider.querySelectorAll('.workout-exercise-card');

        if (!cards.length) {
            return;
        }

        let currentIndex = parseInt(slider.dataset.currentIndex || 0);

        cards[currentIndex].classList.remove('active');

        currentIndex += direction;

        if (currentIndex < 0) {
            currentIndex = cards.length - 1;
        }

        if (currentIndex >= cards.length) {
            currentIndex = 0;
        }

        cards[currentIndex].classList.add('active');
        slider.dataset.currentIndex = currentIndex;
    }

    document.querySelectorAll('.workout-prev').forEach(button => {
        button.addEventListener('click', function () {
            showWorkoutCard(this.dataset.target, -1);
        });
    });

    document.querySelectorAll('.workout-next').forEach(button => {
        button.addEventListener('click', function () {
            showWorkoutCard(this.dataset.target, 1);
        });
    });
});
</script>
@endsection
