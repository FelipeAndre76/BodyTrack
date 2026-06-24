@extends('layouts.bodytrack')

@section('title', 'Comunidade - BodyTrack')

@section('content')
    <livewire:community-feed :scope="$scope" :category="$category" />
@endsection

@section('scripts')
<script>
document.addEventListener('click', async function (event) {
    const button = event.target.closest('[data-share-url]');

    if (!button) {
        return;
    }

    const url = new URL(button.dataset.shareUrl, window.location.origin).toString();

    try {
        await navigator.clipboard.writeText(url);

        Swal.fire({
            icon: 'success',
            title: 'Link copiado',
            timer: 1000,
            showConfirmButton: false,
            background: '#0b0f0c',
            color: '#fff',
            iconColor: '#a3e635'
        });
    } catch {
        window.prompt('Copie o link do post:', url);
    }
});
</script>
@endsection
