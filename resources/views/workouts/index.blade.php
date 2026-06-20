@extends('layouts.bodytrack')

@section('title', 'Treinos - BodyTrack')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="page-title">Treinos</div>
        <p class="text-secondary mb-0">
            Escolha uma categoria e monte seu treino
        </p>
    </div>
</div>
<div class="panel mb-4">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="label mb-2">Data do treino</label>
            <input type="date" id="workoutDate" class="body-input modern-date" value="{{ now()->toDateString() }}">
        </div>

        <div class="col-md-4">
            <label class="label mb-2">Nome do treino</label>
            <input type="text" id="workoutName" class="form-control body-input" placeholder="Ex: Pull, Push, Legs">
        </div>

        <div class="col-md-4">
            <button
    type="button"
    id="saveWorkoutBtn"
    class="btn-bodytrack w-100">
    Salvar treino
</button>
        </div>
    </div>
</div>
<div class="panel my-workout-panel mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h4 class="mb-1">
                💪 Meu Treino de Hoje
            </h4>

            <p class="text-secondary mb-0">
                {{ count($currentWorkout) }} exercício(s) adicionados
            </p>
        </div>

        <div class="workout-summary-badge">
            {{ collect($currentWorkout)->sum('sets') }} séries
        </div>
    </div>

    <div id="currentWorkoutContainer">
        @forelse($currentWorkout as $item)
        <div class="workout-selected-card">
            <div>
                <strong>{{ $item['name'] }}</strong>

                <span>
                    {{ $item['sets'] }} séries • {{ $item['reps'] }} reps
                </span>
            </div>

            <button type="button" class="workout-remove-btn" data-item-id="{{ $item['id'] }}">
                <i class="bi bi-trash"></i>
            </button>
        </div>
        @empty
        <div class="empty-workout-state">
            <i class="bi bi-plus-circle"></i>
            <p>Nenhum exercício adicionado.</p>
            <small>Escolha uma categoria abaixo e adicione exercícios ao treino.</small>
        </div>
        @endforelse
    </div>
</div>

<input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

<div class="scroll-fade-wrapper mb-4">
    <button type="button" class="scroll-arrow scroll-left" data-scroll-target="categories">
        <i class="bi bi-chevron-left"></i>
    </button>

    <div class="workout-category-scroll" id="categories">
        @foreach($categories as $category)
        <button type="button" class="workout-category-pill" data-target="category-{{ $category->id }}">
            <i class="bi {{ $category->icon ?? 'bi-activity' }}"></i>
            <span>{{ $category->name }}</span>
            <small>{{ $category->exercises->count() }} exercícios</small>
        </button>
        @endforeach
    </div>

    <button type="button" class="scroll-arrow scroll-right" data-scroll-target="categories">
        <i class="bi bi-chevron-right"></i>
    </button>
</div>

@foreach($categories as $category)
<div class="workout-exercise-section {{ $loop->first ? 'active' : '' }}" id="category-{{ $category->id }}">
    <div class="panel">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">
                    <i class="bi {{ $category->icon ?? 'bi-activity' }} me-2 text-success"></i>
                    {{ $category->name }}
                </h4>

                <p class="text-secondary mb-0">
                    Selecione os exercícios para montar seu treino
                </p>
            </div>
        </div>

        <div class="scroll-fade-wrapper">
            <button type="button" class="scroll-arrow scroll-left" data-scroll-target="exercises-{{ $category->id }}">
                <i class="bi bi-chevron-left"></i>
            </button>

            <div class="exercise-horizontal-list" id="exercises-{{ $category->id }}">
                @foreach($category->exercises as $exercise)
                <div class="exercise-compact-card">
                    <div class="exercise-card-icon">
                        <i class="bi {{ $category->icon ?? 'bi-activity' }}"></i>
                    </div>

                    <h5>{{ $exercise->name }}</h5>

                    <small>{{ $category->name }}</small>

                    <div class="exercise-defaults mt-3">
                        <span>3 séries</span>
                        <span>10 reps</span>
                    </div>

                    <button type="button" class="btn-bodytrack w-100 mt-3 open-exercise-modal" data-exercise-id="{{ $exercise->id }}" data-exercise-name="{{ $exercise->name }}">
                        Adicionar
                    </button>
                </div>
                @endforeach
            </div>

            <button type="button" class="scroll-arrow scroll-right" data-scroll-target="exercises-{{ $category->id }}">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</div>
@endforeach
@endsection

<div class="modal fade" id="exerciseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content bg-dark border-success">

            <div class="modal-header border-success">
                <h5 class="modal-title" id="exerciseTitle">
                    Exercício
                </h5>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="modalExerciseId">

                <div class="mb-3">
                    <label>Séries</label>

                    <input type="number" id="modalSets" class="form-control body-input" value="3">
                </div>

                <div class="mb-3">
                    <label>Repetições</label>

                    <input type="number" id="modalReps" class="form-control body-input" value="10">
                </div>

                <div class="mb-3">
                    <label>Carga (kg)</label>

                    <input type="number" step="0.5" id="modalWeight" class="form-control body-input" value="0">
                </div>

                <div>
                    <label>Observação</label>

                    <textarea id="modalNotes" class="form-control body-input" rows="3"></textarea>
                </div>

            </div>

            <div class="modal-footer border-success">

                <button class="btn-bodytrack" id="confirmExerciseBtn">
                    Adicionar ao treino
                </button>

            </div>

        </div>

    </div>
</div>








@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.workout-category-pill');
        const sections = document.querySelectorAll('.workout-exercise-section');
        const csrfToken = document.getElementById('csrfToken').value;

        buttons.forEach((button, index) => {
            if (index === 0) {
                button.classList.add('active');
            }

            button.addEventListener('click', function() {
                const target = this.dataset.target;

                buttons.forEach(btn => btn.classList.remove('active'));
                sections.forEach(section => section.classList.remove('active'));

                this.classList.add('active');

                const targetSection = document.getElementById(target);

                if (targetSection) {
                    targetSection.classList.add('active');
                }
            });
        });

        document.querySelectorAll('.scroll-arrow').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.dataset.scrollTarget;
                const target = document.getElementById(targetId);

                if (!target) return;

                const direction = this.classList.contains('scroll-left') ? -1 : 1;

                target.scrollBy({
                    left: direction * 320
                    , behavior: 'smooth'
                });
            });
        });

        const modal = new bootstrap.Modal(
            document.getElementById('exerciseModal')
        );

        document.querySelectorAll('.open-exercise-modal')
            .forEach(button => {

                button.addEventListener('click', function() {

                    document.getElementById('modalExerciseId').value =
                        this.dataset.exerciseId;

                    document.getElementById('exerciseTitle').innerText =
                        this.dataset.exerciseName;

                    modal.show();
                });

            });

        document.getElementById('confirmExerciseBtn')
            .addEventListener('click', async function() {

                const exerciseId =
                    document.getElementById('modalExerciseId').value;

                const sets =
                    document.getElementById('modalSets').value;

                const reps =
                    document.getElementById('modalReps').value;

                const weight =
                    document.getElementById('modalWeight').value;

                const notes =
                    document.getElementById('modalNotes').value;

                try {

                    const response = await fetch(
                        "{{ route('workouts.add-exercise') }}", {
                            method: "POST",

                            headers: {
                                "Content-Type": "application/json"
                                , "X-CSRF-TOKEN": csrfToken
                                , "Accept": "application/json"
                            },

                            body: JSON.stringify({
                                exercise_id: exerciseId
                                , sets: sets
                                , reps: reps
                                , weight: weight
                                , notes: notes
                            })
                        }
                    );

                    const data = await response.json();

                    if (!data.success) {
                        throw new Error();
                    }

                    modal.hide();

                    Swal.fire({
                        icon: 'success'
                        , title: 'Adicionado'
                        , timer: 1000
                        , showConfirmButton: false
                    });

                    setTimeout(() => {
                        location.reload();
                    }, 600);

                } catch {

                    Swal.fire({
                        icon: 'error'
                        , title: 'Erro ao adicionar'
                    });

                }

            });

        document.querySelectorAll('.workout-remove-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const itemId = this.dataset.itemId;

                try {
                    const response = await fetch("{{ route('workouts.remove-exercise') }}", {
                        method: "POST"
                        , headers: {
                            "Content-Type": "application/json"
                            , "X-CSRF-TOKEN": csrfToken
                            , "Accept": "application/json"
                        }
                        , body: JSON.stringify({
                            id: itemId
                        })
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error('Erro ao remover exercício.');
                    }

                    Swal.fire({
                        icon: 'success'
                        , title: 'Removido!'
                        , text: 'Exercício removido do treino.'
                        , timer: 900
                        , showConfirmButton: false
                        , background: '#0b0f0c'
                        , color: '#fff'
                        , iconColor: '#a3e635'
                    });

                    setTimeout(() => {
                        location.reload();
                    }, 500);

                } catch (error) {
                    Swal.fire({
                        icon: 'error'
                        , title: 'Erro'
                        , text: 'Não foi possível remover o exercício.'
                        , background: '#0b0f0c'
                        , color: '#fff'
                        , confirmButtonColor: '#a3e635'
                    });
                }
            });
        });
        document.getElementById('saveWorkoutBtn')
.addEventListener('click', async function() {

    const workoutName =
        document.getElementById('workoutName').value;

    const workoutDate =
        document.getElementById('workoutDate').value;

    try {

        const response = await fetch(
            "{{ route('workouts.save') }}",
            {
                method: "POST",

                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    "Accept": "application/json"
                },

                body: JSON.stringify({
                    name: workoutName,
                    workout_date: workoutDate
                })
            }
        );

        const data = await response.json();

        if (!data.success) {
            throw new Error();
        }

        Swal.fire({
            icon: 'success',
            title: 'Treino salvo!',
            text: 'Treino registrado com sucesso.',
            background: '#0b0f0c',
            color: '#fff'
        });

        setTimeout(() => {
            location.reload();
        }, 1200);

    } catch {

        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: 'Não foi possível salvar o treino.'
        });
    }
});
    });

</script>
@endsection
