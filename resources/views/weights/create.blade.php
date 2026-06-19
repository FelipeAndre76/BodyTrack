@extends('layouts.bodytrack')

@section('title', 'Registrar Pesagem')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <div class="page-title">Registrar Pesagem</div>
        <p class="text-secondary mb-0">
            Adicione um novo peso para acompanhar sua evolução
        </p>
    </div>
</div>

<div class="panel">

    <form action="{{ route('weights.store') }}" method="POST">
        @csrf

        <div class="row g-4">

            <div class="col-md-6">
                <label class="label mb-2">
                    Peso Atual (kg)
                </label>

                <input
                    type="number"
                    step="0.01"
                    name="weight"
                    class="form-control body-input"
                    placeholder="Ex: 110.10"
                    required>
            </div>

            <div class="col-md-6">
                <label class="label mb-2">
                    Data da Pesagem
                </label>

                <input
                    type="date"
                    name="recorded_at"
                    value="{{ date('Y-m-d') }}"
                    class="form-control body-input"
                    required>
            </div>

        </div>

        <div class="mt-4">
            <button class="btn-bodytrack">
                <i class="bi bi-check-circle me-2"></i>
                Salvar Pesagem
            </button>
        </div>

    </form>

</div>

@endsection
