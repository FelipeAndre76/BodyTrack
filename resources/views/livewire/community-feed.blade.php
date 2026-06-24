<div class="social-page">
    <section class="social-hero social-hero-premium">
        <div class="social-hero-copy">
            <span class="social-kicker">
                <i class="bi bi-people"></i>
                Comunidade BodyTrack
            </span>
            <h1>Feed Social</h1>
            <p>Compartilhe treino, receita, evolução e rotina com a comunidade BodyTrack.</p>
        </div>

        <div class="social-hero-actions">
            <button type="button" class="btn-bodytrack social-new-post-btn" data-bs-toggle="modal" data-bs-target="#newPostModal">
                <i class="bi bi-camera"></i>
                Novo post
            </button>

            <button type="button" class="social-hero-secondary" data-bs-toggle="modal" data-bs-target="#newStatusModal">
                <i class="bi bi-plus-circle"></i>
                Novo status
            </button>
        </div>
    </section>

    <section class="social-stats">
        <article>
            <span>Posts</span>
            <strong>{{ $stats['posts'] }}</strong>
        </article>
        <article>
            <span>Seguidores</span>
            <strong>{{ $stats['followers'] }}</strong>
        </article>
        <article>
            <span>Seguindo</span>
            <strong>{{ $stats['following'] }}</strong>
        </article>
    </section>

    <section class="social-composer-card">
        <div class="social-composer-avatar">
            {{ mb_substr(auth()->user()->name, 0, 1) }}
        </div>

        <button type="button" class="social-composer-trigger" data-bs-toggle="modal" data-bs-target="#newPostModal">
            Compartilhe seu treino, receita ou evolução...
        </button>

        <button type="button" class="social-composer-action" data-bs-toggle="modal" data-bs-target="#newPostModal">
            <i class="bi bi-image"></i>
            Foto
        </button>
    </section>

    <section class="social-status-shell">
        <div class="social-section-title">
            <div>
                <span>Status</span>
                <strong>Atualizações rápidas</strong>
            </div>
            <small>{{ $statuses->count() }} ativo(s)</small>
        </div>

        <div class="social-status-strip">
            <button type="button" class="social-status-create" data-bs-toggle="modal" data-bs-target="#newStatusModal">
                <span>
                    <i class="bi bi-plus-lg"></i>
                </span>
                <strong>Seu status</strong>
            </button>

            @forelse($statuses as $status)
                @php
                    $myStatusReaction = $status->reactions->firstWhere('user_id', auth()->id())?->reaction;
                    $reactionCounts = $status->reactions->groupBy('reaction')->map->count();
                @endphp

                <button type="button"
                        class="social-status-item"
                        data-bs-toggle="modal"
                        data-bs-target="#statusModal{{ $status->id }}"
                        wire:key="status-button-{{ $status->id }}">
                    <span>
                        <img src="{{ route('community.statuses.photo', $status) }}" alt="Status de {{ $status->user->name }}" loading="lazy">
                    </span>
                    <strong>{{ \Illuminate\Support\Str::limit($status->user->name, 12) }}</strong>
                </button>

                <div class="modal fade" id="statusModal{{ $status->id }}" tabindex="-1" wire:ignore.self>
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content social-status-modal">
                        <div class="social-status-viewer">
                            <img src="{{ route('community.statuses.photo', $status) }}" alt="Status de {{ $status->user->name }}">

                            <div class="social-status-overlay">
                                <div class="social-status-owner">
                                    <span>{{ mb_substr($status->user->name, 0, 1) }}</span>
                                    <div>
                                        <strong>{{ $status->user->name }}</strong>
                                        <small>{{ $status->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>

                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>

                            @if($status->user_id === auth()->id() || auth()->user()?->is_admin)
                                <button type="button"
                                        class="social-status-delete"
                                        wire:click="askDeleteStatus({{ $status->id }})"
                                        aria-label="Excluir status">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endif

                            @if($status->caption)
                                <p>{{ $status->caption }}</p>
                            @endif

                            <div class="social-status-reactions">
                                @foreach($statusReactions as $reactionKey => $reaction)
                                    <button type="button"
                                            class="{{ $myStatusReaction === $reactionKey ? 'active' : '' }}"
                                            wire:click="reactToStatus({{ $status->id }}, '{{ $reactionKey }}')"
                                            wire:loading.attr="disabled">
                                        <i class="bi {{ $reaction['icon'] }}"></i>
                                        <span>{{ $reactionCounts[$reactionKey] ?? 0 }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
                <div class="social-status-empty">Nenhum status ativo ainda.</div>
            @endforelse
        </div>
    </section>

    <div class="social-layout">
        <main class="social-feed">
            <section class="social-discovery-panel">
                <div class="social-section-title">
                    <div>
                        <span>Descobrir</span>
                        <strong>Encontre pessoas e conteúdos</strong>
                    </div>
                </div>

                <section class="social-search-panel">
                    <div class="social-search-box">
                        <i class="bi bi-search"></i>
                        <input type="search"
                               wire:model.live.debounce.350ms="search"
                               placeholder="Buscar pessoas, receitas, treinos ou legendas...">

                        @if($search)
                            <button type="button" wire:click="clearSearch" aria-label="Limpar busca">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        @endif
                    </div>

                    @if($search)
                        <div class="social-search-meta">
                            <span>Resultados para "{{ $search }}"</span>
                        </div>
                    @endif

                    @if($search && $peopleResults->isNotEmpty())
                        <div class="social-people-results">
                            @foreach($peopleResults as $person)
                                @php
                                    $isFollowingPerson = $followingIds->contains($person->id);
                                @endphp

                                <article wire:key="search-person-{{ $person->id }}">
                                    <a href="{{ route('community.profile', $person) }}" wire:navigate>
                                        <span>
                                            @if($person->hasAvatar())
                                                <img src="{{ route('community.users.avatar', $person) }}" alt="Avatar de {{ $person->name }}">
                                            @else
                                                {{ mb_substr($person->name, 0, 1) }}
                                            @endif
                                        </span>
                                        <strong>{{ $person->name }}</strong>
                                    </a>

                                    <button type="button"
                                            class="social-follow-btn {{ $isFollowingPerson ? 'is-following' : '' }}"
                                            wire:click="toggleFollow({{ $person->id }})"
                                            wire:loading.attr="disabled">
                                        {{ $isFollowingPerson ? 'Seguindo' : 'Seguir' }}
                                    </button>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <div class="social-filter-stack">
                    <div class="social-filter-bar">
                        <button type="button" wire:click="setScope('all')" class="{{ $scope === 'all' ? 'active' : '' }}">
                            Para você
                        </button>
                        <button type="button" wire:click="setScope('following')" class="{{ $scope === 'following' ? 'active' : '' }}">
                            Seguindo
                        </button>
                    </div>

                    <div class="social-category-bar">
                        <button type="button" wire:click="setCategory(null)" class="{{ !$category ? 'active' : '' }}">
                            Todos
                        </button>

                        @foreach($categories as $key => $label)
                            <button type="button" wire:click="setCategory('{{ $key }}')" class="{{ $category === $key ? 'active' : '' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </section>

            @forelse($posts as $post)
                @include('livewire.social-post-card', ['post' => $post, 'followingIds' => $followingIds])
            @empty
                <div class="social-empty">
                    <i class="bi {{ $search ? 'bi-search' : 'bi-camera' }}"></i>
                    <strong>{{ $search ? 'Nenhum post encontrado.' : 'Nenhum post por aqui ainda.' }}</strong>
                    <span>{{ $search ? 'Tente buscar por outro nome, categoria ou palavra da legenda.' : 'Publique a primeira foto ou siga pessoas para montar seu feed.' }}</span>
                </div>
            @endforelse

            <div class="social-pagination">
                {{ $posts->links() }}
            </div>
        </main>

        <aside class="social-side-panel">
            <div class="social-side-card social-profile-side-card">
                <div class="social-profile-mini">
                    <span>
                        @if(auth()->user()->hasAvatar())
                            <img src="{{ route('community.users.avatar', auth()->user()) }}" alt="Seu avatar">
                        @else
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        @endif
                    </span>
                    <div>
                        <strong>{{ auth()->user()->name }}</strong>
                        <small>Seu perfil social</small>
                    </div>
                </div>

                <div class="social-side-mini-stats">
                    <span><strong>{{ $stats['posts'] }}</strong> posts</span>
                    <span><strong>{{ $stats['followers'] }}</strong> seguidores</span>
                    <span><strong>{{ $stats['following'] }}</strong> seguindo</span>
                </div>

                <a href="{{ route('community.profile', auth()->user()) }}" class="social-profile-link" wire:navigate>
                    Ver meu perfil
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="social-side-card social-suggestions">
                <div class="social-panel-title">
                    <strong>Quem seguir</strong>
                    <small>{{ $suggestedUsers->count() }} sugestoes</small>
                </div>

                @forelse($suggestedUsers as $suggestedUser)
                    <div class="social-suggestion-item" wire:key="suggestion-{{ $suggestedUser->id }}">
                        <a href="{{ route('community.profile', $suggestedUser) }}" wire:navigate>
                            <span>
                                @if($suggestedUser->hasAvatar())
                                    <img src="{{ route('community.users.avatar', $suggestedUser) }}" alt="Avatar de {{ $suggestedUser->name }}">
                                @else
                                    {{ mb_substr($suggestedUser->name, 0, 1) }}
                                @endif
                            </span>
                            <strong>{{ $suggestedUser->name }}</strong>
                        </a>

                        <button type="button"
                                class="social-follow-btn"
                                wire:click="toggleFollow({{ $suggestedUser->id }})"
                                wire:loading.attr="disabled">
                            Seguir
                        </button>
                    </div>
                @empty
                    <p class="social-muted-text">Voce ja segue as sugestoes disponiveis.</p>
                @endforelse
            </div>
        </aside>
    </div>

    <div class="modal fade" id="newPostModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <form wire:submit.prevent="createPost" class="modal-content social-modal">
                <div class="modal-header">
                    <div>
                        <span>Novo post</span>
                        <h5 class="modal-title">Compartilhar na comunidade</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="social-upload-preview">
                        @if($photo)
                            <img src="{{ $photo->temporaryUrl() }}" alt="Preview do post">
                        @else
                            <div>
                                <i class="bi bi-image"></i>
                                <span>Selecione uma foto leve para publicar</span>
                            </div>
                        @endif
                    </div>

                    <input type="file"
                           wire:model="photo"
                           class="form-control body-input"
                           accept="image/*"
                           data-image-preset="socialPost"
                           data-image-status="postImageOptimizeStatus"
                           required>
                    <small class="image-compress-hint" id="postImageOptimizeStatus">
                        A imagem sera ajustada para 1080x1080 ou 1080x1920 antes do envio.
                    </small>
                    @error('photo') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror

                    <div class="mt-3">
                        <label class="label mb-2">Formato</label>
                        <div class="social-format-options">
                            <label>
                                <input type="radio" wire:model="postFormat" value="square">
                                <span>
                                    <i class="bi bi-square"></i>
                                    1080x1080
                                </span>
                            </label>

                            <label>
                                <input type="radio" wire:model="postFormat" value="portrait">
                                <span>
                                    <i class="bi bi-phone"></i>
                                    1080x1920
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="label mb-2">Categoria</label>
                        <select wire:model="postCategory" class="form-control body-input" required>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-3">
                        <label class="label mb-2">Legenda</label>
                        <textarea wire:model="caption" class="form-control body-input" rows="4" maxlength="1200" placeholder="Conte sobre o treino, receita ou evolucao..."></textarea>
                        @error('caption') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-bodytrack" wire:loading.attr="disabled">
                        <i class="bi bi-send"></i>
                        <span wire:loading.remove wire:target="createPost,photo">Publicar</span>
                        <span wire:loading wire:target="createPost,photo">Enviando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="newStatusModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <form wire:submit.prevent="createStatus" class="modal-content social-modal">
                <div class="modal-header">
                    <div>
                        <span>Status</span>
                        <h5 class="modal-title">Publicar por 24 horas</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="social-upload-preview is-status">
                        @if($statusPhoto)
                            <img src="{{ $statusPhoto->temporaryUrl() }}" alt="Preview do status">
                        @else
                            <div>
                                <i class="bi bi-phone"></i>
                                <span>Use uma foto vertical para o melhor resultado</span>
                            </div>
                        @endif
                    </div>

                    <input type="file"
                           wire:model="statusPhoto"
                           class="form-control body-input"
                           accept="image/*"
                           data-image-preset="socialStatus"
                           data-image-status="statusImageOptimizeStatus"
                           required>
                    <small class="image-compress-hint" id="statusImageOptimizeStatus">
                        A imagem sera ajustada para 1080x1920 antes do envio.
                    </small>
                    @error('statusPhoto') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror

                    <div class="mt-3">
                        <label class="label mb-2">Legenda curta</label>
                        <input type="text"
                               wire:model="statusCaption"
                               class="form-control body-input"
                               maxlength="300"
                               placeholder="Ex: Treino concluido">
                        @error('statusCaption') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-bodytrack" wire:loading.attr="disabled">
                        <i class="bi bi-lightning-charge"></i>
                        <span wire:loading.remove wire:target="createStatus,statusPhoto">Publicar status</span>
                        <span wire:loading wire:target="createStatus,statusPhoto">Enviando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('social-post-created', function () {
                const modal = bootstrap.Modal.getInstance(document.getElementById('newPostModal'));
                modal?.hide();
            });

            Livewire.on('social-status-created', function () {
                const modal = bootstrap.Modal.getInstance(document.getElementById('newStatusModal'));
                modal?.hide();
            });

            Livewire.on('social-content-deleted', function () {
                document.querySelectorAll('.modal.show').forEach(function (modalElement) {
                    bootstrap.Modal.getInstance(modalElement)?.hide();
                });

                document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
                    backdrop.remove();
                });

                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            });
        });
    </script>

    @if($postToDeleteId || $statusToDeleteId || $commentToDeleteId)
        <div class="social-confirm-backdrop">
            <div class="social-confirm-modal">
                <div class="social-confirm-icon">
                    <i class="bi bi-trash"></i>
                </div>

                <h3>Excluir {{ $commentToDeleteId ? 'comentario' : ($statusToDeleteId ? 'status' : 'post') }}?</h3>
                <p>Essa acao remove o conteudo e nao pode ser desfeita.</p>

                <div class="social-confirm-actions">
                    <button type="button" class="social-confirm-secondary" wire:click="cancelDelete">
                        Cancelar
                    </button>

                    @if($commentToDeleteId)
                        <button type="button" class="social-confirm-danger" wire:click="confirmDeleteComment" wire:loading.attr="disabled">
                            Excluir comentario
                        </button>
                    @elseif($statusToDeleteId)
                        <button type="button" class="social-confirm-danger" wire:click="confirmDeleteStatus" wire:loading.attr="disabled">
                            Excluir status
                        </button>
                    @else
                        <button type="button" class="social-confirm-danger" wire:click="confirmDeletePost" wire:loading.attr="disabled">
                            Excluir post
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
