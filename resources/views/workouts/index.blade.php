@extends('layouts.bodytrack')

@section('title', 'Treinos - BodyTrack')

@section('content')
@php
    $exerciseCount = collect($currentWorkout)->count();
    $setCount = collect($currentWorkout)->sum('sets');
    $repCount = collect($currentWorkout)->sum('reps');
    $loadTotal = collect($currentWorkout)->sum(function ($item) {
        return (float) ($item['weight'] ?? 0) * (int) ($item['sets'] ?? 0) * (int) ($item['reps'] ?? 0);
    });
@endphp

<div class="workout-builder-page">
    <section class="workout-builder-hero">
        <div>
            <span class="workout-builder-kicker">
                <i class="bi bi-activity"></i>
                Montagem de treino
            </span>

            <h1>Treinos</h1>

            <p>
                Escolha uma categoria, adicione exercicios e salve o treino do dia com series, repeticoes e carga.
            </p>
        </div>

        <div class="workout-builder-hero-card">
            <span>Treino em montagem</span>
            <strong>{{ $exerciseCount }}</strong>
            <small>{{ $setCount }} series adicionadas</small>
        </div>
    </section>

    <section class="workout-builder-stats">
        <article>
            <span>Exercicios</span>
            <strong id="workoutExerciseCount">{{ $exerciseCount }}</strong>
        </article>

        <article>
            <span>Series</span>
            <strong id="workoutSetCount">{{ $setCount }}</strong>
        </article>

        <article>
            <span>Repeticoes</span>
            <strong id="workoutRepCount">{{ $repCount }}</strong>
        </article>

        <article>
            <span>Volume estimado</span>
            <strong id="workoutVolumeCount">{{ number_format($loadTotal, 0, ',', '.') }}kg</strong>
        </article>
    </section>

    <section class="workout-plan-panel">
        <div class="workout-section-header">
            <div>
                <span>Plano de rodizio</span>
                <h2>{{ $workoutPlan->name ?? 'Plano semanal' }}</h2>
            </div>

            <strong class="workout-summary-badge">
                Sugestao: {{ $nextWorkoutName }}
            </strong>
        </div>

        <div class="workout-plan-layout">
            <form method="POST" action="{{ route('workouts.plan.save') }}" class="workout-plan-form">
                @csrf

                <div>
                    <label class="label mb-2">Nome do plano</label>
                    <input type="text"
                           name="name"
                           class="form-control body-input"
                           value="{{ old('name', $workoutPlan->name ?? 'Rodizio') }}"
                           placeholder="Ex: Rodizio, PPL, Semana A">
                    <small>Esse nome identifica o plano, nao o treino feito no dia.</small>
                </div>

                <div>
                    <label class="label mb-2">Sequencia do rodizio</label>
                    <input type="text"
                           name="rotation"
                           class="form-control body-input"
                           value="{{ implode(', ', $workoutPlan->rotation ?? ['Push', 'Pull', 'Legs']) }}"
                           placeholder="Ex: Push, Pull, Legs">
                    <small>Separe por virgula. O sistema so avanca quando um treino for salvo.</small>
                </div>

                <div>
                    <label class="label mb-2">Dias de descanso</label>

                    <div class="workout-rest-days">
                        @foreach([1 => 'Seg', 2 => 'Ter', 3 => 'Qua', 4 => 'Qui', 5 => 'Sex', 6 => 'Sab', 0 => 'Dom'] as $day => $label)
                            <label>
                                <input type="checkbox"
                                       name="rest_days[]"
                                       value="{{ $day }}"
                                       @checked(in_array($day, $workoutPlan->rest_days ?? []))>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn-bodytrack workout-save-button">
                    <i class="bi bi-arrow-repeat"></i>
                    Salvar rodizio
                </button>
            </form>

            <div class="workout-rotation-preview">
                @foreach($weeklyRotation as $day)
                    <article class="{{ $day['is_rest'] ? 'is-rest' : '' }} {{ $day['is_today'] ? 'is-today' : '' }}">
                        <span>{{ $day['weekday'] }}</span>
                        <strong>{{ $day['date']->format('d/m') }}</strong>
                        <small>{{ $day['name'] }}</small>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="workout-save-panel">
        <div class="workout-section-header">
            <div>
                <span>Dados do treino</span>
                <h2>Salvar treino</h2>
            </div>

            <a href="{{ route('workouts.history') }}" class="workout-history-link">
                Historico
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="workout-save-grid">
            <div>
                <label class="label mb-2">Data do treino</label>
                <input type="date" id="workoutDate" class="body-input modern-date" value="{{ now()->toDateString() }}">
            </div>

            <div>
                <label class="label mb-2">Nome do treino</label>
                <input type="text" id="workoutName" class="form-control body-input" value="" placeholder="Escolha abaixo ou digite o treino feito">

                @if(!empty($workoutPlan->rotation))
                    <div class="workout-name-options">
                        @foreach($workoutPlan->rotation as $option)
                            <button type="button" class="workout-name-option" data-workout-name="{{ $option }}">
                                {{ $option }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <button type="button" id="saveWorkoutBtn" class="btn-bodytrack workout-save-button">
                    <i class="bi bi-check-circle"></i>
                    Salvar treino
                </button>
            </div>
        </div>
    </section>

    <section class="workout-current-panel">
        <div class="workout-section-header">
            <div>
                <span>Treino atual</span>
                <h2>Exercicios adicionados</h2>
            </div>

            <strong class="workout-summary-badge" id="workoutCurrentSetBadge">
                {{ $setCount }} series
            </strong>
        </div>

        <div class="workout-selected-grid" id="currentWorkoutContainer">
            @forelse($currentWorkout as $item)
                <article class="workout-selected-card">
                    <div>
                        <span>Exercicio</span>
                        <strong>{{ $item['name'] }}</strong>
                        <small>{{ $item['sets'] }} series • {{ $item['reps'] }} reps • {{ number_format((float) ($item['weight'] ?? 0), 1, ',', '.') }}kg</small>
                    </div>

                    <button type="button" class="workout-remove-btn" data-item-id="{{ $item['id'] }}" aria-label="Remover exercicio">
                        <i class="bi bi-trash"></i>
                    </button>
                </article>
            @empty
                <div class="empty-workout-state">
                    <i class="bi bi-plus-circle"></i>
                    <p>Nenhum exercicio adicionado.</p>
                    <small>Escolha uma categoria abaixo e adicione exercicios ao treino.</small>
                </div>
            @endforelse
        </div>
    </section>

    <input type="hidden" id="csrfToken" value="{{ csrf_token() }}">

    <section class="workout-library-panel">
        <div class="workout-section-header">
            <div>
                <span>Biblioteca</span>
                <h2>Categorias</h2>
            </div>

            <small>{{ $categories->sum(fn ($category) => $category->exercises->count()) }} exercicios</small>
        </div>

        <div class="workout-search-panel">
            <i class="bi bi-search"></i>
            <input type="search"
                   id="exerciseSearchInput"
                   class="form-control body-input"
                   placeholder="Buscar exercicio pelo nome...">
        </div>

        <div class="scroll-fade-wrapper mb-4">
            <button type="button" class="scroll-arrow scroll-left" data-scroll-target="categories">
                <i class="bi bi-chevron-left"></i>
            </button>

            <div class="workout-category-scroll" id="categories">
                @foreach($categories as $category)
                    <button type="button" class="workout-category-pill" data-target="category-{{ $category->id }}">
                        <i class="bi {{ $category->icon ?? 'bi-activity' }}"></i>
                        <span>{{ $category->name }}</span>
                        <small>{{ $category->exercises->count() }} exercicios</small>
                    </button>
                @endforeach
            </div>

            <button type="button" class="scroll-arrow scroll-right" data-scroll-target="categories">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        @foreach($categories as $category)
            <div class="workout-exercise-section {{ $loop->first ? 'active' : '' }}" id="category-{{ $category->id }}">
                <div class="workout-category-header">
                    <div>
                        <span>Categoria selecionada</span>
                        <h3>
                            <i class="bi {{ $category->icon ?? 'bi-activity' }}"></i>
                            {{ $category->name }}
                        </h3>
                    </div>

                    <small>{{ $category->exercises->count() }} exercicios disponiveis</small>
                </div>

                <div class="scroll-fade-wrapper">
                    <button type="button" class="scroll-arrow scroll-left" data-scroll-target="exercises-{{ $category->id }}">
                        <i class="bi bi-chevron-left"></i>
                    </button>

                    <div class="exercise-horizontal-list" id="exercises-{{ $category->id }}">
                        @foreach($category->exercises as $exercise)
                            <article class="exercise-compact-card" data-exercise-search="{{ \Illuminate\Support\Str::lower($exercise->name . ' ' . $category->name) }}">
                                <div class="exercise-card-icon">
                                    <i class="bi {{ $category->icon ?? 'bi-activity' }}"></i>
                                </div>

                                <h5>{{ $exercise->name }}</h5>

                                <small>{{ $category->name }}</small>

                                <div class="exercise-defaults">
                                    <span>3 series</span>
                                    <span>10 reps</span>
                                </div>

                                <button type="button"
                                        class="btn-bodytrack w-100 open-exercise-modal"
                                        data-exercise-id="{{ $exercise->id }}"
                                        data-exercise-name="{{ $exercise->name }}">
                                    Adicionar
                                </button>
                            </article>
                        @endforeach
                    </div>

                    <button type="button" class="scroll-arrow scroll-right" data-scroll-target="exercises-{{ $category->id }}">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        @endforeach
    </section>
</div>

<div class="modal fade" id="exerciseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content workout-modal">
            <div class="modal-header">
                <div>
                    <span>Adicionar exercicio</span>
                    <h5 class="modal-title" id="exerciseTitle">Exercicio</h5>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="modalExerciseId">

                <div class="workout-modal-grid">
                    <div>
                        <label class="label mb-2">Series</label>
                        <input type="number" id="modalSets" class="form-control body-input" value="3">
                    </div>

                    <div>
                        <label class="label mb-2">Repeticoes</label>
                        <input type="number" id="modalReps" class="form-control body-input" value="10">
                    </div>

                    <div>
                        <label class="label mb-2">Carga (kg)</label>
                        <input type="number" step="0.5" id="modalWeight" class="form-control body-input" value="0">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="label mb-2">Observacao</label>
                    <textarea id="modalNotes" class="form-control body-input" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-bodytrack" id="confirmExerciseBtn">
                    <i class="bi bi-plus-circle"></i>
                    Adicionar ao treino
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.workout-category-pill');
        const sections = document.querySelectorAll('.workout-exercise-section');
        const csrfToken = document.getElementById('csrfToken').value;
        const currentWorkoutContainer = document.getElementById('currentWorkoutContainer');
        const exerciseSearchInput = document.getElementById('exerciseSearchInput');
        const workoutNameInput = document.getElementById('workoutName');

        function formatNumber(value, decimals = 0) {
            return Number(value || 0).toLocaleString('pt-BR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }

        function updateWorkoutSummary(summary) {
            if (!summary) {
                return;
            }

            document.getElementById('workoutExerciseCount').innerText = summary.exercises;
            document.getElementById('workoutSetCount').innerText = summary.sets;
            document.getElementById('workoutRepCount').innerText = summary.reps;
            document.getElementById('workoutVolumeCount').innerText = formatNumber(summary.volume) + 'kg';

            const badge = document.getElementById('workoutCurrentSetBadge');

            if (badge) {
                badge.innerText = summary.sets + ' series';
            }
        }

        function emptyWorkoutHtml() {
            return `
                <div class="empty-workout-state">
                    <i class="bi bi-plus-circle"></i>
                    <p>Nenhum exercicio adicionado.</p>
                    <small>Escolha uma categoria abaixo e adicione exercicios ao treino.</small>
                </div>
            `;
        }

        function itemHtml(item) {
            return `
                <article class="workout-selected-card">
                    <div>
                        <span>Exercicio</span>
                        <strong>${item.name}</strong>
                        <small>${item.sets} series - ${item.reps} reps - ${formatNumber(item.weight, 1)}kg</small>
                    </div>

                    <button type="button" class="workout-remove-btn" data-item-id="${item.id}" aria-label="Remover exercicio">
                        <i class="bi bi-trash"></i>
                    </button>
                </article>
            `;
        }

        function addCurrentWorkoutItem(item) {
            const emptyState = currentWorkoutContainer.querySelector('.empty-workout-state');

            if (emptyState) {
                currentWorkoutContainer.innerHTML = '';
            }

            currentWorkoutContainer.insertAdjacentHTML('beforeend', itemHtml(item));
        }

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

                exerciseSearchInput.value = '';
                document.querySelectorAll('.exercise-compact-card').forEach(card => card.classList.remove('d-none'));
            });
        });

        document.querySelectorAll('.scroll-arrow').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.dataset.scrollTarget;
                const target = document.getElementById(targetId);

                if (!target) return;

                const direction = this.classList.contains('scroll-left') ? -1 : 1;

                target.scrollBy({
                    left: direction * 320,
                    behavior: 'smooth'
                });
            });
        });

        document.querySelectorAll('.workout-name-option').forEach(button => {
            button.addEventListener('click', function() {
                workoutNameInput.value = this.dataset.workoutName || '';

                document.querySelectorAll('.workout-name-option').forEach(option => {
                    option.classList.remove('active');
                });

                this.classList.add('active');
            });
        });

        exerciseSearchInput.addEventListener('input', function() {
            const term = this.value.trim().toLowerCase();

            document.querySelectorAll('.exercise-compact-card').forEach(card => {
                const matches = !term || card.dataset.exerciseSearch.includes(term);
                card.classList.toggle('d-none', !matches);
            });

            if (term) {
                sections.forEach(section => section.classList.add('active'));
                buttons.forEach(btn => btn.classList.remove('active'));
            } else {
                sections.forEach((section, index) => section.classList.toggle('active', index === 0));
                buttons.forEach((button, index) => button.classList.toggle('active', index === 0));
            }
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
                    addCurrentWorkoutItem(data.item);
                    updateWorkoutSummary(data.summary);

                    Swal.fire({
                        icon: 'success',
                        title: 'Adicionado',
                        timer: 1000,
                        showConfirmButton: false,
                        background: '#0b0f0c',
                        color: '#fff',
                        iconColor: '#a3e635'
                    });

                } catch {

                    Swal.fire({
                        icon: 'error',
                        title: 'Erro ao adicionar',
                        background: '#0b0f0c',
                        color: '#fff',
                        confirmButtonColor: '#a3e635'
                    });

                }

            });

        document.querySelectorAll('.workout-remove-btn').forEach(button => {
            button.addEventListener('click', async function(event) {
                event.stopPropagation();
                const button = this;
                const itemId = button.dataset.itemId;

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

                    button.closest('.workout-selected-card').remove();
                    updateWorkoutSummary(data.summary);

                    if (data.summary.exercises < 1) {
                        currentWorkoutContainer.innerHTML = emptyWorkoutHtml();
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Removido!',
                        text: 'Exercicio removido do treino.',
                        timer: 900,
                        showConfirmButton: false,
                        background: '#0b0f0c',
                        color: '#fff',
                        iconColor: '#a3e635'
                    });

                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'Nao foi possivel remover o exercicio.',
                        background: '#0b0f0c',
                        color: '#fff',
                        confirmButtonColor: '#a3e635'
                    });
                }
            });
        });

        currentWorkoutContainer.addEventListener('click', async function(event) {
            const button = event.target.closest('.workout-remove-btn');

            if (!button || !button.closest('.workout-selected-card')) {
                return;
            }

            try {
                const response = await fetch("{{ route('workouts.remove-exercise') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        id: button.dataset.itemId
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error();
                }

                button.closest('.workout-selected-card').remove();
                updateWorkoutSummary(data.summary);

                if (data.summary.exercises < 1) {
                    currentWorkoutContainer.innerHTML = emptyWorkoutHtml();
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Removido!',
                    timer: 800,
                    showConfirmButton: false,
                    background: '#0b0f0c',
                    color: '#fff',
                    iconColor: '#a3e635'
                });
            } catch {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Nao foi possivel remover o exercicio.',
                    background: '#0b0f0c',
                    color: '#fff',
                    confirmButtonColor: '#a3e635'
                });
            }
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
            throw new Error(data.message || 'Nao foi possivel salvar o treino.');
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

    } catch (error) {

        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: error.message || 'Nao foi possivel salvar o treino.',
            background: '#0b0f0c',
            color: '#fff',
            confirmButtonColor: '#a3e635'
        });
    }
});
    });

</script>
@endsection
