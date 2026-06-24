@extends('layouts.bodytrack')

@section('title', 'Perfil Corporal')

@section('content')

@php
    $goalLabels = [
        'weight_loss' => 'Emagrecimento',
        'muscle_gain' => 'Ganho de massa',
        'body_recomposition' => 'Recomposicao corporal',
    ];

    $goalDescriptions = [
        'weight_loss' => 'Foco em reduzir peso com consistencia.',
        'muscle_gain' => 'Foco em evoluir massa e performance.',
        'body_recomposition' => 'Foco em melhorar composicao corporal.',
    ];

    $activityLabels = [
        'sedentary' => 'Sedentario',
        'light' => 'Leve',
        'moderate' => 'Moderado',
        'high' => 'Intenso',
        'athlete' => 'Atleta',
    ];

    $selectedGoal = old('goal', $profile?->goal);
    $selectedNutritionGoal = old('nutrition_goal', $profile?->nutrition_goal ?? $selectedGoal);
    $selectedActivity = old('activity_level', $profile?->activity_level ?? 'light');
    $selectedGender = old('gender', $profile?->gender ?? 'not_informed');
@endphp

<div class="body-profile-page">
    <section class="body-profile-hero">
        <div>
            <span class="body-profile-kicker">
                <i class="bi bi-person-lines-fill"></i>
                Perfil corporal
            </span>

            <h1>Dados do corpo</h1>

            <p>
                Configure seus dados principais para deixar dashboard, agua, nutricao e pesagens mais precisas.
            </p>
        </div>

        <div class="body-profile-goal-card">
            <span>Objetivo atual</span>
            <strong>{{ $goalLabels[$selectedGoal] ?? 'Nao definido' }}</strong>
            <small>
                {{ $metrics['has_custom_goals'] ? 'Metas personalizadas ativas.' : ($goalDescriptions[$selectedGoal] ?? 'Defina seu objetivo para personalizar o acompanhamento.') }}
            </small>
        </div>
    </section>

    <section class="body-profile-summary">
        <article>
            <span>Peso atual</span>
            <strong>{{ number_format((float) ($profile?->current_weight ?? 0), 1, ',', '.') }}kg</strong>
        </article>

        <article>
            <span>Peso meta</span>
            <strong>{{ number_format((float) ($profile?->goal_weight ?? 0), 1, ',', '.') }}kg</strong>
        </article>

        <article>
            <span>IMC</span>
            <strong>{{ number_format((float) $imc, 1, ',', '.') }}</strong>
            <small class="body-profile-card-note">{{ $metrics['bmi_category']['label'] }}</small>
        </article>

        <article>
            <span>Falta para meta</span>
            <strong>{{ number_format((float) $remainingWeight, 1, ',', '.') }}kg</strong>
        </article>
    </section>

    <section class="body-profile-progress">
        <div>
            <span>Progresso corporal</span>
            <strong>{{ number_format((float) $progressPercent, 1, ',', '.') }}% concluido</strong>
            <small>{{ number_format((float) $weightLost, 1, ',', '.') }}kg eliminados desde o inicio</small>
        </div>

        <div class="body-progress">
            <div class="body-progress-bar" style="width: {{ $progressPercent }}%"></div>
        </div>
    </section>

    <section class="body-profile-metrics-grid">
        <article>
            <span>Manutencao estimada</span>
            <strong>{{ number_format((float) $metrics['maintenance_calories'], 0, ',', '.') }} kcal</strong>
            <small>Gasto diario estimado para manter o peso</small>
        </article>

        <article>
            <span>{{ $metrics['calorie_balance_label'] }}</span>
            <strong>
                @if($metrics['calorie_deficit'] > 0)
                    -{{ number_format((float) $metrics['calorie_deficit'], 0, ',', '.') }} kcal
                @elseif($metrics['calorie_surplus'] > 0)
                    +{{ number_format((float) $metrics['calorie_surplus'], 0, ',', '.') }} kcal
                @else
                    0 kcal
                @endif
            </strong>
            <small>Meta em uso: {{ number_format((float) $nutritionGoals['calories'], 0, ',', '.') }} kcal</small>
        </article>

        <article>
            <span>Proteina diaria</span>
            <strong>{{ $nutritionGoals['protein'] }}g</strong>
            <small>{{ number_format((float) $metrics['protein_factor'], 1, ',', '.') }}g/kg conforme objetivo</small>
        </article>

        <article>
            <span>Proteina por refeicao</span>
            <strong>{{ $metrics['protein_per_meal'] }}g</strong>
            <small>Dose util: {{ $metrics['protein_dose_min'] }}g a {{ $metrics['protein_dose_max'] }}g</small>
        </article>

    </section>

    <section class="body-profile-custom-panel">
        <div class="body-profile-section-header">
            <div>
                <span>Metas em uso</span>
                <h2>{{ $metrics['has_custom_goals'] ? 'Personalizadas' : 'Calculadas automaticamente' }}</h2>
            </div>

            <i class="bi bi-bullseye"></i>
        </div>

        <div class="body-profile-custom-grid">
            <article>
                <span>Proteina</span>
                <strong>{{ $nutritionGoals['protein'] }}g</strong>
                <small>Calculado: {{ $metrics['calculated_goals']['protein'] }}g</small>
            </article>

            <article>
                <span>Carboidratos</span>
                <strong>{{ $nutritionGoals['carbs'] }}g</strong>
                <small>Calculado: {{ $metrics['calculated_goals']['carbs'] }}g</small>
            </article>

            <article>
                <span>Gorduras</span>
                <strong>{{ $nutritionGoals['fat'] }}g</strong>
                <small>Calculado: {{ $metrics['calculated_goals']['fat'] }}g</small>
            </article>

            <article>
                <span>Calorias</span>
                <strong>{{ number_format((float) $nutritionGoals['calories'], 0, ',', '.') }} kcal</strong>
                <small>Manutencao: {{ number_format((float) $metrics['maintenance_calories'], 0, ',', '.') }} kcal</small>
            </article>

            <article>
                <span>Agua</span>
                <strong>{{ number_format((float) $metrics['water_goal'], 0, ',', '.') }}ml</strong>
                <small>Calculado: {{ number_format((float) $metrics['calculated_goals']['water'], 0, ',', '.') }}ml</small>
            </article>
        </div>
    </section>

    <section class="body-profile-form-panel">
        <div class="body-profile-section-header">
            <div>
                <span>Configuracao</span>
                <h2>Atualizar perfil</h2>
            </div>

            <i class="bi bi-sliders"></i>
        </div>

        <form action="{{ route('body-profile.store') }}" method="POST">
            @csrf

            <div class="body-profile-form-grid">
                <div>
                    <label class="label mb-2">Altura (m)</label>
                    <input type="number"
                           step="0.01"
                           name="height"
                           value="{{ old('height', $profile?->height) }}"
                           class="form-control body-input"
                           placeholder="Ex: 1.80"
                           required>
                </div>

                <div>
                    <label class="label mb-2">Data de nascimento</label>
                    <input type="date"
                           name="birth_date"
                           value="{{ old('birth_date', $profile?->birth_date?->format('Y-m-d')) }}"
                           class="form-control body-input">
                </div>

                <div>
                    <label class="label mb-2">Sexo para calculo metabolico</label>

                    <select class="form-select body-input"
                            name="gender">
                        <option value="not_informed" @selected($selectedGender === 'not_informed')>
                            Prefiro nao informar
                        </option>

                        <option value="male" @selected($selectedGender === 'male')>
                            Masculino
                        </option>

                        <option value="female" @selected($selectedGender === 'female')>
                            Feminino
                        </option>
                    </select>
                </div>

                <div>
                    <label class="label mb-2">Nivel de atividade</label>

                    <select class="form-select body-input"
                            name="activity_level"
                            required>
                        @foreach($activityLabels as $value => $label)
                            <option value="{{ $value }}" @selected($selectedActivity === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label mb-2">Peso inicial (kg)</label>
                    <input type="number"
                           step="0.01"
                           name="start_weight"
                           value="{{ old('start_weight', $profile?->start_weight) }}"
                           class="form-control body-input"
                           placeholder="Ex: 123"
                           required>
                </div>

                <div>
                    <label class="label mb-2">Peso atual (kg)</label>
                    <input type="number"
                           step="0.01"
                           name="current_weight"
                           value="{{ old('current_weight', $profile?->current_weight) }}"
                           class="form-control body-input"
                           placeholder="Ex: 111.3"
                           required>
                </div>

                <div>
                    <label class="label mb-2">Peso meta (kg)</label>
                    <input type="number"
                           step="0.01"
                           name="goal_weight"
                           value="{{ old('goal_weight', $profile?->goal_weight) }}"
                           class="form-control body-input"
                           placeholder="Ex: 95"
                           required>
                </div>

                <div>
                    <label class="label mb-2">Objetivo</label>

                    <select class="form-select body-input"
                            name="goal"
                            required>
                        <option value="">Selecione</option>

                        <option value="weight_loss" @selected($selectedGoal === 'weight_loss')>
                            Emagrecimento
                        </option>

                        <option value="muscle_gain" @selected($selectedGoal === 'muscle_gain')>
                            Ganho de massa
                        </option>

                        <option value="body_recomposition" @selected($selectedGoal === 'body_recomposition')>
                            Recomposicao corporal
                        </option>
                    </select>
                </div>

                <div>
                    <label class="label mb-2">Objetivo nutricional</label>

                    <select class="form-select body-input"
                            name="nutrition_goal"
                            required>
                        <option value="weight_loss" @selected($selectedNutritionGoal === 'weight_loss')>
                            Emagrecimento
                        </option>

                        <option value="muscle_gain" @selected($selectedNutritionGoal === 'muscle_gain')>
                            Ganho de massa
                        </option>

                        <option value="body_recomposition" @selected($selectedNutritionGoal === 'body_recomposition')>
                            Recomposicao corporal
                        </option>
                    </select>
                </div>

                <div>
                    <label class="label mb-2">Refeicoes por dia</label>
                    <input type="number"
                           min="2"
                           max="8"
                           name="meals_per_day"
                           value="{{ old('meals_per_day', $profile?->meals_per_day ?? 4) }}"
                           class="form-control body-input"
                           required>
                </div>

                <div class="body-profile-form-divider">
                    <span>Metas personalizadas</span>
                    <small>Deixe vazio para usar o calculo automatico. Preencha calorias para definir um deficit ou superavit manual.</small>
                </div>

                <div>
                    <label class="label mb-2">Proteina diaria (g)</label>
                    <input type="number"
                           name="custom_protein_goal"
                           value="{{ old('custom_protein_goal', $profile?->custom_protein_goal) }}"
                           class="form-control body-input"
                           placeholder="{{ $metrics['calculated_goals']['protein'] }}">
                </div>

                <div>
                    <label class="label mb-2">Carboidratos diarios (g)</label>
                    <input type="number"
                           name="custom_carbs_goal"
                           value="{{ old('custom_carbs_goal', $profile?->custom_carbs_goal) }}"
                           class="form-control body-input"
                           placeholder="{{ $metrics['calculated_goals']['carbs'] }}">
                </div>

                <div>
                    <label class="label mb-2">Gorduras diarias (g)</label>
                    <input type="number"
                           name="custom_fat_goal"
                           value="{{ old('custom_fat_goal', $profile?->custom_fat_goal) }}"
                           class="form-control body-input"
                           placeholder="{{ $metrics['calculated_goals']['fat'] }}">
                </div>

                <div>
                    <label class="label mb-2">Meta de calorias diarias (kcal)</label>
                    <input type="number"
                           name="custom_calories_goal"
                           value="{{ old('custom_calories_goal', $profile?->custom_calories_goal) }}"
                           class="form-control body-input"
                           placeholder="{{ $metrics['calculated_goals']['calories'] }}">
                    <small class="body-profile-field-note">
                        Ex: se sua manutencao for {{ number_format((float) $metrics['maintenance_calories'], 0, ',', '.') }} kcal e voce quer comer 1300 kcal, preencha 1300.
                    </small>
                </div>

                <div>
                    <label class="label mb-2">Agua diaria (ml)</label>
                    <input type="number"
                           name="custom_water_goal"
                           value="{{ old('custom_water_goal', $profile?->custom_water_goal) }}"
                           class="form-control body-input"
                           placeholder="{{ $metrics['calculated_goals']['water'] }}">
                </div>
            </div>

            @if($errors->any())
                <div class="body-profile-alert">
                    Confira os campos obrigatorios antes de salvar.
                </div>
            @endif

            <button class="btn-bodytrack body-profile-submit">
                <i class="bi bi-check-circle"></i>
                Salvar perfil
            </button>
        </form>
    </section>
</div>

@endsection
