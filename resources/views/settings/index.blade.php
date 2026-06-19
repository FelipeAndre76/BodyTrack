@extends('layouts.bodytrack')

@section('title', 'Configurações - BodyTrack')

@section('content')

@php
    $profile = Auth::user()->profile;
@endphp

<div class="page-title mb-4">
    Configurações
</div>

<div class="row g-4">

    <div class="col-lg-6">
        <div class="panel">
            <h4>
                <i class="bi bi-person-circle me-2 text-success"></i>
                Dados da Conta
            </h4>

            <div class="mt-4">
                <div class="label">Nome</div>
                <div class="value" style="font-size: 24px;">
                    {{ Auth::user()->name }}
                </div>
            </div>

            <div class="mt-4">
                <div class="label">Email</div>
                <strong>{{ Auth::user()->email }}</strong>
            </div>

            <a href="{{ route('profile.edit') }}" class="btn-bodytrack d-inline-block mt-4 text-decoration-none">
                Editar Perfil
            </a>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel">
            <h4>
                <i class="bi bi-bullseye me-2 text-success"></i>
                Metas Corporais
            </h4>

            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <div class="settings-mini-card">
                        <span>Peso Inicial</span>
                        <strong>{{ number_format($profile?->start_weight ?? 0, 1) }} kg</strong>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="settings-mini-card">
                        <span>Peso Atual</span>
                        <strong>{{ number_format($profile?->current_weight ?? 0, 1) }} kg</strong>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="settings-mini-card">
                        <span>Peso Meta</span>
                        <strong>{{ number_format($profile?->goal_weight ?? 0, 1) }} kg</strong>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="settings-mini-card">
                        <span>Altura</span>
                        <strong>{{ number_format($profile?->height ?? 0, 2) }} m</strong>
                    </div>
                </div>
            </div>

            <a href="{{ route('body-profile.create') }}" class="btn-bodytrack d-inline-block mt-4 text-decoration-none">
                Atualizar Metas
            </a>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel">
            <h4>
                <i class="bi bi-speedometer2 me-2 text-success"></i>
                Preferências do Dashboard
            </h4>

            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <div class="settings-mini-card">
                        <span>Meta de Água</span>
                        <strong>4000 ml</strong>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="settings-mini-card">
                        <span>Meta de Proteína</span>
                        <strong>180 g</strong>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="settings-mini-card">
                        <span>Meta de Passos</span>
                        <strong>10000</strong>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="settings-mini-card">
                        <span>Objetivo</span>
                        <strong>
                            @if($profile?->goal === 'weight_loss')
                                Emagrecimento
                            @elseif($profile?->goal === 'muscle_gain')
                                Ganho de massa
                            @elseif($profile?->goal === 'body_recomposition')
                                Recomposição
                            @else
                                Não definido
                            @endif
                        </strong>
                    </div>
                </div>
            </div>

            <button class="btn-bodytrack mt-4">
                Editar Preferências
            </button>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel">
            <h4>
                <i class="bi bi-capsule me-2 text-success"></i>
                Saúde e Medicamentos
            </h4>

            <p class="text-secondary mt-3">
                Em breve você poderá registrar medicamentos, doses e datas de início.
            </p>

            <div class="settings-mini-card mt-3">
                <span>Exemplo</span>
                <strong>Tirzepatida, Durateston, Vitaminas</strong>
            </div>

            <button class="btn-bodytrack mt-4">
                Adicionar Medicamento
            </button>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel">
            <h4>
                <i class="bi bi-file-earmark-medical me-2 text-success"></i>
                Exames
            </h4>

            <p class="text-secondary mt-3">
                Envie exames em PDF ou imagem para organizar seus marcadores laboratoriais.
            </p>

            <button class="btn-bodytrack mt-3">
                Enviar Exame
            </button>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="panel">
            <h4>
                <i class="bi bi-shield-lock me-2 text-success"></i>
                Segurança
            </h4>

            <p class="text-secondary mt-3">
                Gerencie senha, sessões e dados da conta.
            </p>

            <a href="{{ route('profile.edit') }}" class="btn-bodytrack d-inline-block mt-3 text-decoration-none">
                Segurança da Conta
            </a>
        </div>
    </div>

</div>

@endsection
