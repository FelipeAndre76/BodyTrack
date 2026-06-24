@extends('layouts.bodytrack')

@section('title', 'Fotos de Evolucao - BodyTrack')

@section('content')

<div class="gallery-page">
    <section class="gallery-hero">
        <div>
            <span class="gallery-kicker">
                <i class="bi bi-images"></i>
                Fotos de evolucao
            </span>

            <h1>Galeria corporal</h1>

            <p>
                Veja suas fotos de check-in por data e compare o primeiro registro com o mais recente.
            </p>
        </div>

        <div class="gallery-actions">
            <a href="{{ route('evolution.report') }}" class="gallery-secondary-action">
                <i class="bi bi-file-earmark-pdf"></i>
                Relatorio PDF
            </a>

            <a href="{{ route('check-ins.index') }}" class="gallery-action">
                <i class="bi bi-plus-circle"></i>
                Novo check-in
            </a>
        </div>
    </section>

    @if($firstPhoto && $latestPhoto && $firstPhoto->id !== $latestPhoto->id)
        <section class="gallery-compare-panel">
            <div class="gallery-section-header">
                <div>
                    <span>Comparador</span>
                    <h2>Antes e depois</h2>
                </div>

                <small>{{ $daysDiff }} dia(s) entre as fotos</small>
            </div>

            <div class="gallery-compare-grid">
                <article>
                    <span>Primeira foto</span>
                    <img src="{{ route('check-ins.photo', $firstPhoto) }}" alt="Primeira foto de evolucao">
                    <strong>{{ $firstPhoto->check_in_date->format('d/m/Y') }}</strong>
                    <small>{{ $firstPhoto->weight ? number_format((float) $firstPhoto->weight, 1, ',', '.') . 'kg' : 'Sem peso' }}</small>
                </article>

                <article>
                    <span>Foto mais recente</span>
                    <img src="{{ route('check-ins.photo', $latestPhoto) }}" alt="Foto mais recente de evolucao">
                    <strong>{{ $latestPhoto->check_in_date->format('d/m/Y') }}</strong>
                    <small>{{ $latestPhoto->weight ? number_format((float) $latestPhoto->weight, 1, ',', '.') . 'kg' : 'Sem peso' }}</small>
                </article>

                <div class="gallery-compare-result">
                    <span>Resultado</span>
                    <strong>
                        @if($weightDiff !== null)
                            {{ $weightDiff <= 0 ? '-' : '+' }}{{ number_format(abs($weightDiff), 1, ',', '.') }}kg
                        @else
                            Sem peso
                        @endif
                    </strong>
                    <small>Diferença entre os registros selecionados automaticamente.</small>
                </div>
            </div>
        </section>
    @endif

    <section class="gallery-list-panel">
        <div class="gallery-section-header">
            <div>
                <span>Registros</span>
                <h2>Todas as fotos</h2>
            </div>

            <small>{{ $photoCheckIns->count() }} foto(s)</small>
        </div>

        <div class="gallery-grid">
            @forelse($photoCheckIns->reverse() as $checkIn)
                <article class="gallery-card">
                    <button type="button"
                            class="gallery-photo-button"
                            data-bs-toggle="modal"
                            data-bs-target="#photoModal{{ $checkIn->id }}">
                        <img src="{{ route('check-ins.photo', $checkIn) }}" alt="Foto de evolucao">
                    </button>

                    <div class="gallery-card-body">
                        <span>{{ $checkIn->check_in_date->format('d/m/Y') }}</span>
                        <strong>{{ $checkIn->weight ? number_format((float) $checkIn->weight, 1, ',', '.') . 'kg' : 'Sem peso' }}</strong>

                        @if($checkIn->notes)
                            <small>{{ $checkIn->notes }}</small>
                        @else
                            <small>Check-in sem observacao.</small>
                        @endif
                    </div>
                </article>

                <div class="modal fade" id="photoModal{{ $checkIn->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-xl">
                        <div class="modal-content gallery-modal">
                            <div class="modal-header">
                                <div>
                                    <span>{{ $checkIn->check_in_date->format('d/m/Y') }}</span>
                                    <h5 class="modal-title">
                                        {{ $checkIn->weight ? number_format((float) $checkIn->weight, 1, ',', '.') . 'kg' : 'Foto de evolucao' }}
                                    </h5>
                                </div>

                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <img src="{{ route('check-ins.photo', $checkIn) }}" alt="Foto de evolucao ampliada">
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="gallery-empty">
                    <i class="bi bi-images"></i>
                    <strong>Nenhuma foto salva ainda</strong>
                    <span>Adicione uma foto no check-in semanal para montar sua galeria.</span>
                    <a href="{{ route('check-ins.index') }}" class="gallery-action">
                        Criar check-in
                    </a>
                </div>
            @endforelse
        </div>
    </section>
</div>

@endsection
