@extends('layouts.admin')

@section('title', 'Fotos dos Aparelhos')

@section('content')

@section('content')

<div class="admin-premium-header">
    <div>
        <span class="admin-kicker">Central de mídia</span>
        <h1>Fotos dos Aparelhos</h1>
        <p>Gerencie as imagens dos exercícios e mantenha o catálogo visual do BodyTrack completo.</p>
    </div>

    <div class="admin-header-actions">
        <a href="{{ route('admin.exercises.index') }}" class="btn-admin-secondary">
            <i class="bi bi-activity"></i>
            Exercícios
        </a>

        <a href="{{ route('admin.dashboard') }}" class="btn-admin">
            <i class="bi bi-grid"></i>
            Dashboard
        </a>
    </div>
</div>

<div class="photo-premium-hero mb-4">
    <div class="photo-premium-progress">
        <div>
            <span>Progresso das fotos</span>
            <strong id="photoProgressText">{{ $photoProgress }}%</strong>
            <small id="photoProgressInfo">
                {{ $totalWithPhoto }} com foto / {{ $totalExercises }} exercícios
            </small>
        </div>

        <div class="photo-premium-ring">
            <div>{{ $photoProgress }}%</div>
        </div>
    </div>

    <div class="photo-progress-bar premium">
        <div id="photoProgressBar" style="width: {{ $photoProgress }}%"></div>
    </div>

    <div class="photo-premium-filters">
      <a href="{{ route('admin.photos.index', array_filter(['q' => request('q')])) }}"
   class="photo-premium-filter {{ request('status') ? '' : 'active' }}">
    <i class="bi bi-grid-3x3-gap"></i>
    <span>Todos</span>
    <strong>{{ $totalExercises }}</strong>
</a>

<a href="{{ route('admin.photos.index', array_filter(['status' => 'with-photo', 'q' => request('q')])) }}"
   class="photo-premium-filter {{ request('status') === 'with-photo' ? 'active' : '' }}">
    <i class="bi bi-image-fill"></i>
    <span>Com foto</span>
    <strong id="totalWithPhoto">{{ $totalWithPhoto }}</strong>
</a>

<a href="{{ route('admin.photos.index', array_filter(['status' => 'without-photo', 'q' => request('q')])) }}"
   class="photo-premium-filter warning {{ request('status') === 'without-photo' ? 'active' : '' }}">
    <i class="bi bi-camera"></i>
    <span>Sem foto</span>
    <strong id="totalWithoutPhoto">{{ $totalWithoutPhoto }}</strong>
</a>
    </div>
</div>

<form method="GET" action="{{ route('admin.photos.index') }}" class="photo-premium-search mb-4">
    @if(request('status'))
        <input type="hidden" name="status" value="{{ request('status') }}">
    @endif

    <i class="bi bi-search"></i>

    <input type="text"
           name="q"
           id="photoSearchInput"
           class="photo-search-input"
           value="{{ request('q') }}"
           placeholder="Buscar exercício ou categoria...">

    <button type="submit" class="photo-search-button">
        Buscar
    </button>
</form>

<div id="photoNoResults" class="photo-no-results premium">
    Nenhum exercício encontrado.
</div>

<div class="photo-premium-grid">
    @foreach($exercises as $exercise)

        <div class="photo-premium-card photo-card"
             data-exercise-id="{{ $exercise->id }}"
             data-search="{{ strtolower($exercise->name . ' ' . ($exercise->category->name ?? '')) }}">

            <div class="photo-premium-image photo-preview-box">
                @if($exercise->image_path)
                    <img src="{{ asset('storage/'.$exercise->image_path) }}"
                         class="photo-preview-img open-photo-modal"
                         data-full="{{ asset('storage/'.$exercise->image_path) }}"
                         data-title="{{ $exercise->name }}"
                         alt="{{ $exercise->name }}">
                @else
                    <div class="photo-premium-placeholder">
                        <i class="bi bi-image"></i>
                        <span>Sem foto</span>
                    </div>
                @endif
            </div>

            <div class="photo-premium-body">
                <div class="photo-premium-meta">
                    <span>{{ $exercise->category->name ?? 'Sem categoria' }}</span>

                    @if($exercise->image_path)
                        <strong class="photo-status complete">
                            <i class="bi bi-check-circle"></i>
                            Completo
                        </strong>
                    @else
                        <strong class="photo-status pending">
                            <i class="bi bi-exclamation-circle"></i>
                            Pendente
                        </strong>
                    @endif
                </div>

                <h3>{{ $exercise->name }}</h3>

                <form class="photo-upload-form"
                      action="{{ route('admin.photos.update', $exercise) }}"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="photo-drop-zone premium">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span>Arraste a imagem aqui ou clique</span>
                    </div>

                    <input type="file"
                           name="image"
                           class="photo-input photo-input-hidden"
                           accept="image/*"
                           required>

                    <button type="submit" class="btn-admin w-100">
                        <i class="bi bi-upload"></i>
                        Salvar foto
                    </button>
                </form>

                <button type="button"
                        class="btn-delete-photo mt-3"
                        data-url="{{ route('admin.photos.destroy', $exercise) }}"
                        style="{{ $exercise->image_path ? '' : 'display:none;' }}">
                    <i class="bi bi-trash"></i>
                    Remover foto
                </button>
            </div>
        </div>

    @endforeach
</div>
<div class="photo-pagination-wrapper">
    {{ $exercises->onEachSide(1)->links('pagination::bootstrap-5') }}
</div>
@endsection

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const photoSearchInput = document.getElementById('photoSearchInput');
        const photoNoResults = document.getElementById('photoNoResults');

        if (photoSearchInput) {
            photoSearchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.photo-card');

                let visibleCount = 0;

                cards.forEach(card => {
                    const searchText = card.dataset.search || '';

                    if (searchText.includes(term)) {
                        card.style.display = '';
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (photoNoResults) {
                    photoNoResults.classList.toggle('active', visibleCount === 0);
                }
            });
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const totalExercises = Number("{{ $totalExercises }}");

        function updatePhotoStats(delta) {
            const totalWithPhotoEl = document.getElementById('totalWithPhoto');
            const totalWithoutPhotoEl = document.getElementById('totalWithoutPhoto');
            const progressTextEl = document.getElementById('photoProgressText');
            const progressBarEl = document.getElementById('photoProgressBar');
            const progressInfoEl = document.getElementById('photoProgressInfo');

            let withPhoto = Number(totalWithPhotoEl.innerText);
            let withoutPhoto = Number(totalWithoutPhotoEl.innerText);

            withPhoto += delta;
            withoutPhoto -= delta;

            if (withPhoto < 0) withPhoto = 0;
            if (withoutPhoto < 0) withoutPhoto = 0;

            const progress = totalExercises > 0 ?
                Math.round((withPhoto / totalExercises) * 100) :
                0;

            totalWithPhotoEl.innerText = withPhoto;
            totalWithoutPhotoEl.innerText = withoutPhoto;
            progressTextEl.innerText = progress + '%';
            progressBarEl.style.width = progress + '%';
            progressInfoEl.innerText = `${withPhoto} com foto / ${totalExercises} exercícios`;
        }

        function showDeleteButton(button) {
            if (!button) return;

            button.classList.remove('d-none');
            button.hidden = false;
            button.style.removeProperty('display');
        }

        function hideDeleteButton(button) {
            if (!button) return;

            button.classList.add('d-none');
            button.hidden = true;
            button.style.display = 'none';
        }

        document.querySelectorAll('.photo-card').forEach(card => {
            const dropZone = card.querySelector('.photo-drop-zone');
            const input = card.querySelector('.photo-input');

            if (!dropZone || !input) return;

            dropZone.addEventListener('click', () => {
                input.click();
            });

            input.addEventListener('change', () => {
                if (input.files.length) {
                    dropZone.classList.add('has-file');
                    dropZone.querySelector('span').innerText = input.files[0].name;
                }
            });

            dropZone.addEventListener('dragover', e => {
                e.preventDefault();
                dropZone.classList.add('drag-over');
            });

            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('drag-over');
            });

            dropZone.addEventListener('drop', e => {
                e.preventDefault();
                dropZone.classList.remove('drag-over');

                const file = e.dataTransfer.files[0];

                if (!file) return;

                input.files = e.dataTransfer.files;

                dropZone.classList.add('has-file');
                dropZone.querySelector('span').innerText = file.name;
            });
        });



        document.querySelectorAll('.photo-upload-form').forEach(form => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const card = form.closest('.photo-card');
                const imageBox = card.querySelector('.photo-preview-box');
                const deleteBtn = card.querySelector('.btn-delete-photo');
                const input = form.querySelector('.photo-input');

                const hadPhoto = !!card.querySelector('.photo-preview-img');

                if (!input.files.length) {
                    Swal.fire({
                        icon: 'warning'
                        , title: 'Selecione uma imagem'
                        , background: '#050705'
                        , color: '#fff'
                        , confirmButtonColor: '#a3e635'
                    });
                    return;
                }

                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST'
                        , headers: {
                            'X-CSRF-TOKEN': csrfToken
                            , 'Accept': 'application/json'
                        }
                        , body: formData
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error();
                    }

                    imageBox.innerHTML = `
    <img src="${data.image_url}?v=${Date.now()}"
         class="photo-preview-img open-photo-modal"
         data-full="${data.image_url}?v=${Date.now()}"
         data-title="Foto do exercício"
         alt="Foto do exercício">
`;

                    input.value = '';
                    showDeleteButton(deleteBtn);
                    const dropZone = card.querySelector('.photo-drop-zone');

                    if (dropZone) {
                        dropZone.classList.remove('has-file');
                        dropZone.querySelector('span').innerText = 'Arraste a imagem aqui ou clique';
                    }

                    if (!hadPhoto) {
                        updatePhotoStats(1);
                    }

                    Swal.fire({
                        icon: 'success'
                        , title: 'Foto salva!'
                        , timer: 1200
                        , showConfirmButton: false
                        , background: '#050705'
                        , color: '#fff'
                        , iconColor: '#a3e635'
                    });

                } catch (error) {
                    Swal.fire({
                        icon: 'error'
                        , title: 'Erro'
                        , text: 'Não foi possível salvar a foto.'
                        , background: '#050705'
                        , color: '#fff'
                        , confirmButtonColor: '#a3e635'
                    });
                }
            });
        });

        document.querySelectorAll('.btn-delete-photo').forEach(button => {
            button.addEventListener('click', async function() {
                const card = button.closest('.photo-card');
                const imageBox = card.querySelector('.photo-preview-box');

                const hadPhoto = !!card.querySelector('.photo-preview-img');

                const result = await Swal.fire({
                    title: 'Remover foto?'
                    , text: 'A imagem será removida do exercício.'
                    , icon: 'warning'
                    , showCancelButton: true
                    , confirmButtonText: 'Sim, remover'
                    , cancelButtonText: 'Cancelar'
                    , confirmButtonColor: '#dc2626'
                    , cancelButtonColor: '#6b7280'
                    , background: '#050705'
                    , color: '#fff'
                });

                if (!result.isConfirmed) return;

                try {
                    const response = await fetch(button.dataset.url, {
                        method: 'POST'
                        , headers: {
                            'X-CSRF-TOKEN': csrfToken
                            , 'Accept': 'application/json'
                        }
                        , body: (() => {
                            const fd = new FormData();
                            fd.append('_method', 'DELETE');
                            return fd;
                        })()
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error();
                    }

                    imageBox.innerHTML = `<i class="bi bi-image photo-placeholder"></i>`;
                    hideDeleteButton(button);

                    if (hadPhoto) {
                        updatePhotoStats(-1);
                    }

                    Swal.fire({
                        icon: 'success'
                        , title: 'Foto removida!'
                        , timer: 1200
                        , showConfirmButton: false
                        , background: '#050705'
                        , color: '#fff'
                        , iconColor: '#a3e635'
                    });

                } catch (error) {
                    Swal.fire({
                        icon: 'error'
                        , title: 'Erro'
                        , text: 'Não foi possível remover a foto.'
                        , background: '#050705'
                        , color: '#fff'
                        , confirmButtonColor: '#a3e635'
                    });
                }
            });
        });

        document.addEventListener('click', function(event) {
            const image = event.target.closest('.open-photo-modal');

            if (!image) return;

            Swal.fire({
                title: image.dataset.title || 'Foto do aparelho'
                , imageUrl: image.dataset.full
                , imageAlt: image.dataset.title || 'Foto do aparelho'
                , background: '#050705'
                , color: '#fff'
                , confirmButtonColor: '#a3e635'
                , confirmButtonText: 'Fechar'
                , width: 700
            });
        });
    });

</script>
@endsection
