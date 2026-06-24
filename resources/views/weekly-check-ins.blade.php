@extends('layouts.bodytrack')

@section('title', 'Check-in Semanal - BodyTrack')

@section('content')

<div class="checkin-page">
    <section class="checkin-hero">
        <div>
            <span class="checkin-kicker">
                <i class="bi bi-clipboard2-pulse"></i>
                Check-in semanal
            </span>

            <h1>Registro da semana</h1>

            <p>
                Combine peso, foto, energia, humor, sono e observacoes para acompanhar sua evolucao com mais contexto.
            </p>
        </div>

        <div class="checkin-hero-card">
            <span>Ultimo check-in</span>
            <strong>{{ $latestCheckIn?->check_in_date?->format('d/m/Y') ?? 'Novo' }}</strong>
            <small>
                @if($weightChange !== null)
                    {{ $weightChange <= 0 ? '-' : '+' }}{{ number_format(abs($weightChange), 1, ',', '.') }}kg desde o anterior
                @else
                    Registre sua semana
                @endif
            </small>
        </div>
    </section>

    <section class="checkin-summary-grid">
        <article>
            <span>Energia media</span>
            <strong>{{ number_format($averages['energy'], 1, ',', '.') }}/5</strong>
            <small>Ultimos 4 check-ins</small>
        </article>

        <article>
            <span>Humor medio</span>
            <strong>{{ number_format($averages['mood'], 1, ',', '.') }}/5</strong>
            <small>Ultimos 4 check-ins</small>
        </article>

        <article>
            <span>Sono medio</span>
            <strong>{{ number_format($averages['sleep'], 1, ',', '.') }}/5</strong>
            <small>Ultimos 4 check-ins</small>
        </article>

        <article>
            <span>Total salvo</span>
            <strong>{{ $checkIns->count() }}</strong>
            <small>Check-ins registrados</small>
        </article>
    </section>

    <section class="checkin-form-panel">
        <div class="checkin-section-header">
            <div>
                <span>Novo registro</span>
                <h2>Como foi sua semana?</h2>
            </div>

            <i class="bi bi-plus-circle"></i>
        </div>

        <form method="POST" action="{{ route('check-ins.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="checkin-form-grid">
                <div>
                    <label class="label mb-2">Data</label>
                    <input type="date" name="check_in_date" value="{{ old('check_in_date', now()->toDateString()) }}" class="form-control body-input" required>
                </div>

                <div>
                    <label class="label mb-2">Peso atual (kg)</label>
                    <input type="number" step="0.01" name="weight" value="{{ old('weight') }}" class="form-control body-input" placeholder="Ex: 110.4">
                </div>

                <div>
                    <label class="label mb-2">Energia</label>
                    <select name="energy_level" class="form-select body-input">
                        <option value="">Selecione</option>
                        @for($score = 1; $score <= 5; $score++)
                            <option value="{{ $score }}" @selected(old('energy_level') == $score)>{{ $score }}/5</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="label mb-2">Humor</label>
                    <select name="mood_level" class="form-select body-input">
                        <option value="">Selecione</option>
                        @for($score = 1; $score <= 5; $score++)
                            <option value="{{ $score }}" @selected(old('mood_level') == $score)>{{ $score }}/5</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="label mb-2">Sono</label>
                    <select name="sleep_quality" class="form-select body-input">
                        <option value="">Selecione</option>
                        @for($score = 1; $score <= 5; $score++)
                            <option value="{{ $score }}" @selected(old('sleep_quality') == $score)>{{ $score }}/5</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="label mb-2">Foto opcional</label>
                    <input type="file"
                           name="photo"
                           id="checkInPhotoInput"
                           class="form-control body-input"
                           accept="image/*"
                           data-image-preset="checkIn"
                           data-image-status="checkInImageOptimizeStatus">
                    <small class="checkin-photo-hint image-compress-hint" id="checkInImageOptimizeStatus">
                        A foto será otimizada no navegador e limitada a 768KB.
                    </small>
                </div>

                <div class="checkin-form-full">
                    <label class="label mb-2">Observacoes</label>
                    <textarea name="notes" class="form-control body-input" rows="4" placeholder="Ex: semana com treino forte, retenção, mais fome, sono melhor...">{{ old('notes') }}</textarea>
                </div>
            </div>

            @if($errors->any())
                <div class="checkin-alert">
                    Confira os campos antes de salvar.
                </div>
            @endif

            <button type="submit" class="btn-bodytrack checkin-submit">
                <i class="bi bi-check-circle"></i>
                Salvar check-in
            </button>
        </form>
    </section>

    <section class="checkin-history-panel">
        <div class="checkin-section-header">
            <div>
                <span>Historico</span>
                <h2>Check-ins salvos</h2>
            </div>

            <small>{{ $checkIns->count() }} registro(s)</small>
        </div>

        <div class="checkin-list">
            @forelse($checkIns as $checkIn)
                <article class="checkin-card">
                    <div class="checkin-card-media">
                        @if($checkIn->hasPhoto())
                            <img src="{{ route('check-ins.photo', $checkIn) }}" alt="Foto do check-in">
                        @else
                            <i class="bi bi-image"></i>
                        @endif
                    </div>

                    <div class="checkin-card-content">
                        <span>{{ $checkIn->check_in_date->format('d/m/Y') }}</span>
                        <h3>{{ $checkIn->weight ? number_format((float) $checkIn->weight, 1, ',', '.') . 'kg' : 'Sem peso informado' }}</h3>

                        <div class="checkin-card-scores">
                            <div><small>Energia</small><strong>{{ $checkIn->energy_level ?? '-' }}/5</strong></div>
                            <div><small>Humor</small><strong>{{ $checkIn->mood_level ?? '-' }}/5</strong></div>
                            <div><small>Sono</small><strong>{{ $checkIn->sleep_quality ?? '-' }}/5</strong></div>
                        </div>

                        @if($checkIn->notes)
                            <p>{{ $checkIn->notes }}</p>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('check-ins.destroy', $checkIn) }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit" class="btn-delete" aria-label="Excluir check-in">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </article>
            @empty
                <div class="checkin-empty">
                    <i class="bi bi-clipboard2-plus"></i>
                    <strong>Nenhum check-in registrado</strong>
                    <span>Salve seu primeiro check-in para acompanhar sua evolucao semanal.</span>
                </div>
            @endforelse
        </div>
    </section>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('checkInPhotoInput');

    if (!input) {
        return;
    }

    input.addEventListener('change', async function () {
        const file = input.files?.[0];

        if (!file || !file.type.startsWith('image/')) {
            return;
        }

        const compressed = await compressImage(file, 900, 0.72);

        if (!compressed) {
            return;
        }

        const transfer = new DataTransfer();
        transfer.items.add(compressed);
        input.files = transfer.files;
    });

    function compressImage(file, maxWidth, quality) {
        return new Promise(resolve => {
            const image = new Image();
            const reader = new FileReader();

            reader.onload = event => {
                image.onload = () => {
                    const ratio = Math.min(1, maxWidth / image.width);
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.round(image.width * ratio);
                    canvas.height = Math.round(image.height * ratio);

                    const context = canvas.getContext('2d');
                    context.drawImage(image, 0, 0, canvas.width, canvas.height);

                    canvas.toBlob(blob => {
                        if (!blob) {
                            resolve(null);
                            return;
                        }

                        resolve(new File([blob], 'check-in.jpg', {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        }));
                    }, 'image/jpeg', quality);
                };

                image.src = event.target.result;
            };

            reader.readAsDataURL(file);
        });
    }
});
</script>
@endsection
