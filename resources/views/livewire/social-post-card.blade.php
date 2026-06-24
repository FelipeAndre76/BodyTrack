@php
    $isFollowingAuthor = $followingIds->contains($post->user_id);
    $isLiked = $post->likedByUser(auth()->id());
    $isSaved = $post->savedByUser(auth()->id());
@endphp

<article class="social-post-card" id="post-{{ $post->id }}" data-post-card="{{ $post->id }}" wire:key="post-{{ $post->id }}">
    <header class="social-post-header">
        <a href="{{ route('community.profile', $post->user) }}" class="social-post-author" wire:navigate>
            <span>
                @if($post->user->hasAvatar())
                    <img src="{{ route('community.users.avatar', $post->user) }}" alt="Avatar de {{ $post->user->name }}">
                @else
                    {{ mb_substr($post->user->name, 0, 1) }}
                @endif
            </span>
            <div>
                <strong>{{ $post->user->name }}</strong>
                <small>{{ $post->created_at->diffForHumans() }} - {{ ucfirst($post->category) }}</small>
            </div>
        </a>

        <div class="social-post-header-actions">
            @if($post->user_id !== auth()->id())
                <button type="button"
                        class="social-follow-btn {{ $isFollowingAuthor ? 'active' : '' }}"
                        wire:click="toggleFollow({{ $post->user_id }})"
                        wire:loading.attr="disabled">
                    {{ $isFollowingAuthor ? 'Seguindo' : 'Seguir' }}
                </button>
            @endif

            @if($post->user_id === auth()->id() || auth()->user()?->is_admin)
                <button type="button"
                        class="social-delete-button"
                        wire:click="askDeletePost({{ $post->id }})"
                        aria-label="Excluir post">
                    <i class="bi bi-trash"></i>
                </button>
            @endif
        </div>
    </header>

    <button type="button"
            class="social-post-image social-post-open is-{{ $post->display_format ?? 'square' }}"
            data-bs-toggle="modal"
            data-bs-target="#postModal{{ $post->id }}"
            wire:ignore>
        <img src="{{ route('community.posts.photo', $post) }}" alt="Post de {{ $post->user->name }}" loading="lazy">
    </button>

    <div class="social-post-compact-footer">
        <div class="social-post-inline-actions">
            <button type="button"
                    class="{{ $isLiked ? 'active' : '' }}"
                    wire:click="toggleLike({{ $post->id }})"
                    wire:loading.attr="disabled">
                <i class="bi {{ $isLiked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                <span>{{ $post->likes_count }}</span>
            </button>

            <button type="button" data-bs-toggle="modal" data-bs-target="#postModal{{ $post->id }}">
                <i class="bi bi-chat"></i>
                <span>{{ $post->comments_count }}</span>
            </button>

            <button type="button" data-share-url="{{ route('community.index') }}#post-{{ $post->id }}">
                <i class="bi bi-send"></i>
            </button>

            <button type="button"
                    class="ms-auto {{ $isSaved ? 'active' : '' }}"
                    wire:click="toggleSave({{ $post->id }})"
                    wire:loading.attr="disabled">
                <i class="bi {{ $isSaved ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
            </button>
        </div>

        @if($post->likes_count > 0)
            <strong class="social-post-like-line">
                Curtido por {{ $post->likes_count }} pessoa(s)
            </strong>
        @endif

        @if($post->caption)
            <p class="social-post-inline-caption">
                <a href="{{ route('community.profile', $post->user) }}" wire:navigate>{{ $post->user->name }}</a>
                {{ \Illuminate\Support\Str::limit($post->caption, 160) }}
            </p>
        @endif

        @if($post->comments_count > 0)
            <button type="button" class="social-post-view-comments" data-bs-toggle="modal" data-bs-target="#postModal{{ $post->id }}">
                Ver {{ $post->comments_count }} comentario(s)
            </button>
        @endif
    </div>

    <div class="modal fade" id="postModal{{ $post->id }}" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-xl modal-dialog-centered social-post-detail-dialog">
            <div class="modal-content social-post-detail-modal">
                <div class="social-post-detail">
                    <div class="social-post-detail-media is-{{ $post->display_format ?? 'square' }}">
                        <img src="{{ route('community.posts.photo', $post) }}" alt="Post de {{ $post->user->name }}">
                    </div>

                    <div class="social-post-detail-panel">
                        <div class="social-post-detail-header">
                            <a href="{{ route('community.profile', $post->user) }}" class="social-post-author" wire:navigate>
                                <span>
                                    @if($post->user->hasAvatar())
                                        <img src="{{ route('community.users.avatar', $post->user) }}" alt="Avatar de {{ $post->user->name }}">
                                    @else
                                        {{ mb_substr($post->user->name, 0, 1) }}
                                    @endif
                                </span>
                                <div>
                                    <strong>{{ $post->user->name }}</strong>
                                    <small>{{ $post->created_at->diffForHumans() }} - {{ ucfirst($post->category) }}</small>
                                </div>
                            </a>

                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        @if($post->caption)
                            <p class="social-post-caption">{{ $post->caption }}</p>
                        @endif

                        <div class="social-post-actions">
                            <button type="button"
                                    class="social-action-btn {{ $isLiked ? 'active' : '' }}"
                                    wire:click="toggleLike({{ $post->id }})"
                                    wire:loading.attr="disabled">
                                <i class="bi {{ $isLiked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                <span>{{ $post->likes_count }}</span>
                            </button>

                            <button type="button" class="social-action-btn" onclick="document.getElementById('comment-{{ $post->id }}')?.focus()">
                                <i class="bi bi-chat"></i>
                                <span>{{ $post->comments_count }}</span>
                            </button>

                            <button type="button" class="social-action-btn" data-share-url="{{ route('community.index') }}#post-{{ $post->id }}">
                                <i class="bi bi-share"></i>
                                <span>Compartilhar</span>
                            </button>

                            <button type="button"
                                    class="social-action-btn {{ $isSaved ? 'active' : '' }}"
                                    wire:click="toggleSave({{ $post->id }})"
                                    wire:loading.attr="disabled">
                                <i class="bi {{ $isSaved ? 'bi-bookmark-fill' : 'bi-bookmark' }}"></i>
                                <span>{{ $isSaved ? 'Salvo' : 'Salvar' }}</span>
                            </button>
                        </div>

                        <div class="social-comments">
                            @foreach($post->comments->reverse() as $comment)
                                @php
                                    $commentLiked = $comment->likedByUser(auth()->id());
                                @endphp

                                <div class="social-comment" wire:key="comment-{{ $comment->id }}">
                                    @if($editingCommentId === $comment->id)
                                        <form class="social-edit-comment-form" wire:submit.prevent="updateComment">
                                            <input type="text"
                                                   class="body-input"
                                                   maxlength="500"
                                                   wire:model="editingCommentBody">
                                            <button type="submit" wire:loading.attr="disabled">Salvar</button>
                                            <button type="button" class="ghost" wire:click="cancelEditComment">Cancelar</button>
                                        </form>
                                    @else
                                        <div class="social-comment-body">
                                            <strong>{{ $comment->user->name }}</strong>
                                            <span>{{ $comment->body }}</span>
                                        </div>
                                    @endif

                                    <div class="social-comment-actions">
                                        <button type="button"
                                                class="{{ $commentLiked ? 'active' : '' }}"
                                                wire:click="toggleCommentLike({{ $comment->id }})"
                                                wire:loading.attr="disabled">
                                            <i class="bi {{ $commentLiked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                            {{ $comment->likes_count }}
                                        </button>

                                        <button type="button" onclick="document.getElementById('reply-{{ $comment->id }}')?.focus()">
                                            <i class="bi bi-reply"></i>
                                            Responder
                                        </button>

                                        @if($comment->user_id === auth()->id())
                                            <button type="button" wire:click="startEditComment({{ $comment->id }})">
                                                <i class="bi bi-pencil"></i>
                                                Editar
                                            </button>
                                        @endif

                                        @if($comment->user_id === auth()->id() || auth()->user()?->is_admin)
                                            <button type="button" class="danger" wire:click="askDeleteComment({{ $comment->id }})">
                                                <i class="bi bi-trash"></i>
                                                Excluir
                                            </button>
                                        @endif
                                    </div>

                                    @if($comment->replies->isNotEmpty())
                                        <div class="social-comment-replies">
                                            @foreach($comment->replies as $reply)
                                                @php
                                                    $replyLiked = $reply->likedByUser(auth()->id());
                                                @endphp

                                                <div class="social-comment reply" wire:key="reply-{{ $reply->id }}">
                                                    @if($editingCommentId === $reply->id)
                                                        <form class="social-edit-comment-form" wire:submit.prevent="updateComment">
                                                            <input type="text"
                                                                   class="body-input"
                                                                   maxlength="500"
                                                                   wire:model="editingCommentBody">
                                                            <button type="submit" wire:loading.attr="disabled">Salvar</button>
                                                            <button type="button" class="ghost" wire:click="cancelEditComment">Cancelar</button>
                                                        </form>
                                                    @else
                                                        <div class="social-comment-body">
                                                            <strong>{{ $reply->user->name }}</strong>
                                                            <span>{{ $reply->body }}</span>
                                                        </div>
                                                    @endif

                                                    <div class="social-comment-actions">
                                                        <button type="button"
                                                                class="{{ $replyLiked ? 'active' : '' }}"
                                                                wire:click="toggleCommentLike({{ $reply->id }})"
                                                                wire:loading.attr="disabled">
                                                            <i class="bi {{ $replyLiked ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                                                            {{ $reply->likes_count }}
                                                        </button>

                                                        @if($reply->user_id === auth()->id())
                                                            <button type="button" wire:click="startEditComment({{ $reply->id }})">
                                                                <i class="bi bi-pencil"></i>
                                                                Editar
                                                            </button>
                                                        @endif

                                                        @if($reply->user_id === auth()->id() || auth()->user()?->is_admin)
                                                            <button type="button" class="danger" wire:click="askDeleteComment({{ $reply->id }})">
                                                                <i class="bi bi-trash"></i>
                                                                Excluir
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <form class="social-reply-form" wire:submit.prevent="addCommentReply({{ $comment->id }})">
                                        <input type="text"
                                               id="reply-{{ $comment->id }}"
                                               class="body-input"
                                               maxlength="500"
                                               placeholder="Responder {{ $comment->user->name }}..."
                                               wire:model="replyBody.{{ $comment->id }}">
                                        <button type="submit" wire:loading.attr="disabled">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>

                        <form class="social-comment-form" wire:submit.prevent="addComment({{ $post->id }})">
                            <input type="text"
                                   id="comment-{{ $post->id }}"
                                   class="body-input"
                                   maxlength="500"
                                   placeholder="Comentar..."
                                   wire:model="commentBody.{{ $post->id }}">
                            <button type="submit" wire:loading.attr="disabled">
                                <i class="bi bi-send"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>
