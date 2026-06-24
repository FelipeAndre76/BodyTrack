@php
    $isFollowingAuthor = isset($followingIdsForView)
        ? $followingIdsForView->contains($post->user_id)
        : auth()->user()->isFollowing($post->user);
@endphp

<article class="social-post-card" id="post-{{ $post->id }}" data-post-card="{{ $post->id }}">
    <header class="social-post-header">
        <a href="{{ route('community.profile', $post->user) }}" class="social-post-author">
            <span>{{ mb_substr($post->user->name, 0, 1) }}</span>
            <div>
                <strong>{{ $post->user->name }}</strong>
                <small>{{ $post->created_at->diffForHumans() }} - {{ ucfirst($post->category) }}</small>
            </div>
        </a>

        <div class="social-post-header-actions">
            @if($post->user_id !== auth()->id())
                <button type="button"
                        class="social-follow-btn {{ $isFollowingAuthor ? 'active' : '' }}"
                        data-follow-url="{{ route('community.users.follow', $post->user) }}">
                    {{ $isFollowingAuthor ? 'Seguindo' : 'Seguir' }}
                </button>
            @endif

            @if($post->user_id === auth()->id() || auth()->user()?->is_admin)
                <form method="POST" action="{{ route('community.posts.destroy', $post) }}" class="social-delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" aria-label="Excluir post">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            @endif
        </div>
    </header>

    <div class="social-post-image">
        <img src="{{ route('community.posts.photo', $post) }}" alt="Post de {{ $post->user->name }}" loading="lazy">
    </div>

    @if($post->caption)
        <p class="social-post-caption">{{ $post->caption }}</p>
    @endif

    <div class="social-post-actions">
        <button type="button"
                class="social-action-btn {{ $post->likedByUser(auth()->id()) ? 'active' : '' }}"
                data-like-url="{{ route('community.posts.like', $post) }}">
            <i class="bi {{ $post->likedByUser(auth()->id()) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
            <span data-like-count>{{ $post->likes_count }}</span>
        </button>

        <button type="button" class="social-action-btn" data-focus-comment="{{ $post->id }}">
            <i class="bi bi-chat"></i>
            <span data-comment-count>{{ $post->comments_count }}</span>
        </button>

        <button type="button" class="social-action-btn" data-share-url="{{ route('community.index') }}#post-{{ $post->id }}">
            <i class="bi bi-share"></i>
            <span>Compartilhar</span>
        </button>
    </div>

    <div class="social-comments" data-comments-list>
        @foreach($post->comments->reverse() as $comment)
            <div class="social-comment">
                <strong>{{ $comment->user->name }}</strong>
                <span>{{ $comment->body }}</span>
            </div>
        @endforeach
    </div>

    <form class="social-comment-form" data-comment-form action="{{ route('community.posts.comment', $post) }}">
        <input type="text" name="body" class="body-input" maxlength="500" placeholder="Comentar...">
        <button type="submit">
            <i class="bi bi-send"></i>
        </button>
    </form>
</article>
