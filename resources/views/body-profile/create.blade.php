@extends('layouts.bodytrack')

@section('title', 'Perfil Corporal')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="page-title">Perfil Corporal</div>
        <p class="text-secondary mb-0">
            Configure seus dados iniciais
        </p>
    </div>
</div>

<div class="panel">

    <form action="{{ route('body-profile.store') }}" method="POST">
        @csrf

        <div class="row g-4">

            <div class="col-md-6">
                <label class="label mb-2">Altura (m)</label>
                <input type="number"
                       step="0.01"
                       name="height"
                       class="form-control body-input"
                       placeholder="Ex: 1.80">
            </div>

            <div class="col-md-6">
                <label class="label mb-2">Data de Nascimento</label>
                <input type="date"
                       name="birth_date"
                       class="form-control body-input">
            </div>

            <div class="col-md-6">
                <label class="label mb-2">Peso Inicial (kg)</label>
                <input type="number"
                       step="0.01"
                       name="start_weight"
                       class="form-control body-input"
                       placeholder="Ex: 123">
            </div>

            <div class="col-md-6">
                <label class="label mb-2">Peso Atual (kg)</label>
                <input type="number"
                       step="0.01"
                       name="current_weight"
                       class="form-control body-input"
                       placeholder="Ex: 111.3">
            </div>

            <div class="col-md-6">
                <label class="label mb-2">Peso Meta (kg)</label>
                <input type="number"
                       step="0.01"
                       name="goal_weight"
                       class="form-control body-input"
                       placeholder="Ex: 95">
            </div>

            <div class="col-md-6">
                <label class="label mb-2">Objetivo</label>

                <select class="form-select body-input"
                        name="goal">

                    <option value="">
                        Selecione
                    </option>

                    <option value="weight_loss">
                        Emagrecimento
                    </option>

                    <option value="muscle_gain">
                        Ganho de Massa
                    </option>

                    <option value="body_recomposition">
                        Recomposição Corporal
                    </option>

                </select>
            </div>

        </div>

        <div class="mt-4">
            <button class="btn-bodytrack">
                Salvar Perfil
            </button>
        </div>

    </form>

</div>

@endsection
