<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = '{{ csrf_token() }}';
    const photoInput = document.getElementById('socialPhotoInput');
    const uploadPreview = document.getElementById('socialUploadPreview');

    async function compressImage(file, maxWidth = 1080, quality = 0.72) {
        if (!file || !file.type.startsWith('image/')) {
            return file;
        }

        return new Promise((resolve) => {
            const reader = new FileReader();

            reader.onload = (event) => {
                const image = new Image();

                image.onload = () => {
                    const ratio = Math.min(1, maxWidth / image.width);
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');

                    canvas.width = Math.round(image.width * ratio);
                    canvas.height = Math.round(image.height * ratio);
                    context.drawImage(image, 0, 0, canvas.width, canvas.height);

                    canvas.toBlob((blob) => {
                        if (!blob || blob.size >= file.size) {
                            resolve(file);
                            return;
                        }

                        resolve(new File([blob], 'bodytrack-post.jpg', {
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

    function escapeHtml(value) {
        return String(value || '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    photoInput?.addEventListener('change', async function () {
        const file = this.files?.[0];

        if (!file) {
            return;
        }

        const compressed = await compressImage(file);
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(compressed);
        this.files = dataTransfer.files;

        const reader = new FileReader();

        reader.onload = (event) => {
            uploadPreview.innerHTML = `<img src="${event.target.result}" alt="Preview do post">`;
        };

        reader.readAsDataURL(compressed);
    });

    document.querySelectorAll('[data-like-url]').forEach(button => {
        button.addEventListener('click', async function () {
            const icon = this.querySelector('i');
            const counter = this.querySelector('[data-like-count]');

            try {
                const response = await fetch(this.dataset.likeUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Nao foi possivel curtir.');
                }

                this.classList.toggle('active', data.liked);
                icon.className = data.liked ? 'bi bi-heart-fill' : 'bi bi-heart';
                counter.innerText = data.likes_count;
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ops',
                    text: error.message,
                    background: '#0b0f0c',
                    color: '#fff',
                    confirmButtonColor: '#a3e635'
                });
            }
        });
    });

    document.querySelectorAll('[data-comment-form]').forEach(form => {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const input = this.querySelector('input[name="body"]');
            const body = input.value.trim();
            const card = this.closest('[data-post-card]');
            const list = card.querySelector('[data-comments-list]');
            const counter = card.querySelector('[data-comment-count]');

            if (!body) {
                return;
            }

            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ body })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Nao foi possivel comentar.');
                }

                input.value = '';
                counter.innerText = data.comments_count;
                list.insertAdjacentHTML('beforeend', `
                    <div class="social-comment">
                        <strong>${escapeHtml(data.comment.author)}</strong>
                        <span>${escapeHtml(data.comment.body)}</span>
                    </div>
                `);
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ops',
                    text: error.message,
                    background: '#0b0f0c',
                    color: '#fff',
                    confirmButtonColor: '#a3e635'
                });
            }
        });
    });

    document.querySelectorAll('[data-focus-comment]').forEach(button => {
        button.addEventListener('click', function () {
            this.closest('[data-post-card]')?.querySelector('input[name="body"]')?.focus();
        });
    });

    document.querySelectorAll('[data-share-url]').forEach(button => {
        button.addEventListener('click', async function () {
            const url = new URL(this.dataset.shareUrl, window.location.origin).toString();

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
    });

    document.querySelectorAll('.social-follow-btn').forEach(button => {
        button.addEventListener('click', async function () {
            try {
                const response = await fetch(this.dataset.followUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Nao foi possivel atualizar.');
                }

                this.innerText = data.following ? 'Seguindo' : 'Seguir';
                this.classList.toggle('active', data.following);
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ops',
                    text: error.message,
                    background: '#0b0f0c',
                    color: '#fff',
                    confirmButtonColor: '#a3e635'
                });
            }
        });
    });
});
</script>
