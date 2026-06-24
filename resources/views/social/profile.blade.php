@extends('layouts.bodytrack')

@section('title', $user->name . ' - Comunidade')

@section('content')
    <livewire:community-profile :user="$user" />
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
