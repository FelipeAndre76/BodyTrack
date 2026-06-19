@extends('layouts.bodytrack')

@section('title', 'Água - BodyTrack')

@section('content')

@php
    $percent = $goal > 0 ? ($waterToday / $goal) * 100 : 0;
    $percent = min(100, $percent);

    $remaining = max(0, $goal - $waterToday);
@endphp

<div class="page-title mb-4">
    Controle de Água
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="panel">
            <h4>
                <i class="bi bi-droplet-half me-2 text-success"></i>
                Meta diária
            </h4>

            <div class="value mt-3" id="waterTotal">
    {{ $waterToday }} ml / {{ $goal }} ml
</div>

            <div class="body-progress mt-4">
               <div id="waterProgress" class="body-progress-bar" style="width: {{ $percent }}%"></div>
            </div>

           <p class="text-secondary mt-3 mb-1" id="waterPercent">
    {{ number_format($percent, 1) }}% da meta concluída hoje.
</p>

            <p class="text-secondary mb-0">
              Faltam <strong class="text-success" id="waterRemaining">{{ $remaining }} ml</strong> para atingir sua meta.
            </p>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="panel">
            <h4>Adicionar Água</h4>

            <form method="POST" action="{{ route('water.store') }}" class="mt-4">
                @csrf

                <div class="water-options">
                    <button name="amount_ml" value="250" class="water-card">
                        <i class="bi bi-cup-straw"></i>
                        <span>Copo Pequeno</span>
                        <strong>250 ml</strong>
                    </button>

                    <button name="amount_ml" value="500" class="water-card">
                        <i class="bi bi-cup"></i>
                        <span>Copo Médio</span>
                        <strong>500 ml</strong>
                    </button>

                    <button name="amount_ml" value="750" class="water-card">
                        <i class="bi bi-droplet"></i>
                        <span>Garrafa</span>
                        <strong>750 ml</strong>
                    </button>

                    <button name="amount_ml" value="1000" class="water-card">
                        <i class="bi bi-droplet-fill"></i>
                        <span>Garrafa Grande</span>
                        <strong>1000 ml</strong>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="panel">
    <h4>Histórico</h4>

    <div class="table-responsive mt-4">
        <table class="table table-dark table-borderless align-middle">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Quantidade</th>
                    <th class="text-end">Ação</th>
                </tr>
            </thead>

            <tbody>
                @forelse($waterLogs as $log)
                   <tr id="water-row-{{ $log->id }}">
    <td>{{ date('d/m/Y', strtotime($log->recorded_at)) }}</td>
    <td>{{ $log->amount_ml }} ml</td>
    <td class="text-end">
        <form method="POST"
              action="{{ route('water.destroy', $log) }}"
              class="delete-water-form">
            @csrf
            @method('DELETE')

            <button type="submit" class="btn-delete">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    </td>
</tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-secondary">
                            Nenhum registro de água ainda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-water-form').forEach(form => {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const result = await Swal.fire({
                title: 'Remover registro?',
                text: 'Esse consumo de água será excluído do histórico.',
                icon: 'warning',
                background: '#0b0f0c',
                color: '#fff',
                iconColor: '#ef4444',
                showCancelButton: true,
                confirmButtonText: 'Sim, remover',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#374151'
            });

            if (!result.isConfirmed) return;

            const response = await fetch(form.getAttribute('action'), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            });

            const data = await response.json();

            if (data.success) {
                form.closest('tr').remove();

                document.getElementById('waterTotal').innerText =
                    `${data.waterToday} ml / ${data.goal} ml`;

                document.getElementById('waterProgress').style.width =
                    `${data.percent}%`;

                document.getElementById('waterPercent').innerText =
                    `${data.percent}% da meta concluída hoje.`;

                document.getElementById('waterRemaining').innerText =
                    `${data.remaining} ml`;

                Swal.fire({
                    title: 'Removido!',
                    text: 'Registro excluído com sucesso.',
                    icon: 'success',
                    background: '#0b0f0c',
                    color: '#fff',
                    iconColor: '#a3e635',
                    confirmButtonColor: '#a3e635'
                });
            }
        });
    });
});
</script>
@endsection
@endsection
