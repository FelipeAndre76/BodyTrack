<div class="social-page">
    <section class="social-profile-hero">
        <div class="social-profile-avatar">
            @if($user->hasAvatar())
                <img src="{{ route('community.users.avatar', $user) }}" alt="Avatar de {{ $user->name }}">
            @else
                {{ mb_substr($user->name, 0, 1) }}
            @endif
        </div>

        <div class="social-profile-info">
            <span>Perfil social</span>
            <h1>{{ $user->name }}</h1>

            <div class="social-profile-stats">
                <strong>{{ $stats['posts'] }} <span>posts</span></strong>
                <button type="button" wire:click="openPeopleModal('followers')">
                    <strong>{{ $stats['followers'] }}</strong>
                    <span>seguidores</span>
                </button>
                <button type="button" wire:click="openPeopleModal('following')">
                    <strong>{{ $stats['following'] }}</strong>
                    <span>seguindo</span>
                </button>
                @if($user->id === auth()->id())
                    <strong>{{ $stats['saved'] }} <span>salvos</span></strong>
                @endif
            </div>

            <p class="social-profile-bio">
                {{ $user->social_bio ?: 'Compartilhando treinos, refeições e evolução no BodyTrack.' }}
            </p>
        </div>

        <div class="social-profile-actions">
            <a href="{{ route('community.index') }}" class="social-profile-link" wire:navigate>
                <i class="bi bi-arrow-left"></i>
                Feed
            </a>

            @if($user->id !== auth()->id())
                <button type="button"
                        class="social-follow-btn {{ $isFollowing ? 'active' : '' }}"
                        wire:click="toggleFollow({{ $user->id }})"
                        wire:loading.attr="disabled">
                    {{ $isFollowing ? 'Seguindo' : 'Seguir' }}
                </button>
            @else
                <button type="button" class="social-follow-btn active" data-bs-toggle="modal" data-bs-target="#editSocialProfileModal">
                    <i class="bi bi-pencil"></i>
                    Editar perfil
                </button>
            @endif
        </div>
    </section>

    @if($user->id === auth()->id())
        <div class="modal fade" id="editSocialProfileModal" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered">
                <form wire:submit.prevent="saveSocialProfile" class="modal-content social-modal">
                    <div class="modal-header">
                        <div>
                            <span>Perfil social</span>
                            <h5 class="modal-title">Editar apresentação</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="social-avatar-editor">
                            <div class="social-profile-avatar is-edit">
                                @if($avatar)
                                    <img src="{{ $avatar->temporaryUrl() }}" alt="Preview do avatar">
                                @elseif($user->hasAvatar())
                                    <img src="{{ route('community.users.avatar', $user) }}" alt="Avatar atual">
                                @else
                                    {{ mb_substr($user->name, 0, 1) }}
                                @endif
                            </div>

                            <div>
                                <label class="label mb-2">Foto de perfil</label>
                                <input type="file"
                                       wire:model="avatar"
                                       class="form-control body-input"
                                       accept="image/*"
                                       data-image-preset="avatar"
                                       data-image-status="avatarOptimizeStatus">
                                <small class="image-compress-hint" id="avatarOptimizeStatus">
                                    A foto será otimizada em formato quadrado antes do envio.
                                </small>
                                @error('avatar') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="label mb-2">Bio</label>
                            <textarea wire:model="socialBio"
                                      class="form-control body-input"
                                      rows="4"
                                      maxlength="220"
                                      placeholder="Conte um pouco sobre seu treino, objetivo ou rotina."></textarea>
                            @error('socialBio') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn-bodytrack" wire:loading.attr="disabled">
                            <i class="bi bi-check2-circle"></i>
                            <span wire:loading.remove wire:target="saveSocialProfile,avatar">Salvar perfil</span>
                            <span wire:loading wire:target="saveSocialProfile,avatar">Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <section class="social-profile-tabs">
        <button type="button"
                class="{{ $tab === 'posts' ? 'active' : '' }}"
                wire:click="setTab('posts')">
            <i class="bi bi-grid-3x3"></i>
            Posts
        </button>

        @if($user->id === auth()->id())
            <button type="button"
                    class="{{ $tab === 'saved' ? 'active' : '' }}"
                    wire:click="setTab('saved')">
                <i class="bi bi-bookmark"></i>
                Salvos
            </button>
        @endif
    </section>

    <main class="social-profile-grid">
        @forelse($posts as $post)
            @include('livewire.social-post-grid-item', ['post' => $post, 'followingIds' => $followingIds])
        @empty
            <div class="social-empty">
                <i class="bi {{ $tab === 'saved' ? 'bi-bookmark' : 'bi-camera' }}"></i>
                <strong>{{ $tab === 'saved' ? 'Nenhum post salvo.' : 'Nenhum post publicado.' }}</strong>
                <span>{{ $tab === 'saved' ? 'Os posts que voce salvar aparecem aqui.' : 'Quando houver publicacoes, elas aparecem aqui.' }}</span>
            </div>
        @endforelse

        <div class="social-pagination">
            {{ $posts->links() }}
        </div>
    </main>

    @if($peopleModal)
        @php
            $peopleList = $peopleModal === 'followers' ? $followersList : $followingList;
            $peopleTitle = $peopleModal === 'followers' ? 'Seguidores' : 'Seguindo';
        @endphp

        <div class="social-confirm-backdrop">
            <div class="social-people-modal">
                <div class="social-people-modal-header">
                    <div>
                        <span>Perfil social</span>
                        <h3>{{ $peopleTitle }}</h3>
                    </div>

                    <button type="button" wire:click="closePeopleModal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="social-people-list">
                    @forelse($peopleList as $person)
                        @php
                            $isFollowingPerson = $followingIds->contains($person->id);
                        @endphp

                        <article wire:key="profile-person-{{ $peopleModal }}-{{ $person->id }}">
                            <a href="{{ route('community.profile', $person) }}" wire:navigate>
                                <span>
                                    @if($person->hasAvatar())
                                        <img src="{{ route('community.users.avatar', $person) }}" alt="Avatar de {{ $person->name }}">
                                    @else
                                        {{ mb_substr($person->name, 0, 1) }}
                                    @endif
                                </span>

                                <div>
                                    <strong>{{ $person->name }}</strong>
                                    <small>{{ $person->social_bio ?: 'BodyTrack' }}</small>
                                </div>
                            </a>

                            @if($person->id !== auth()->id())
                                <button type="button"
                                        class="social-follow-btn {{ $isFollowingPerson ? 'is-following' : '' }}"
                                        wire:click="toggleFollow({{ $person->id }})"
                                        wire:loading.attr="disabled">
                                    {{ $isFollowingPerson ? 'Seguindo' : 'Seguir' }}
                                </button>
                            @endif
                        </article>
                    @empty
                        <div class="social-empty compact">
                            <i class="bi bi-people"></i>
                            <strong>Nenhuma pessoa por aqui.</strong>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    @if($postToDeleteId || $commentToDeleteId)
        <div class="social-confirm-backdrop">
            <div class="social-confirm-modal">
                <div class="social-confirm-icon">
                    <i class="bi bi-trash"></i>
                </div>

                <h3>Excluir {{ $commentToDeleteId ? 'comentario' : 'post' }}?</h3>
                <p>Essa acao remove o conteudo e nao pode ser desfeita.</p>

                <div class="social-confirm-actions">
                    <button type="button" class="social-confirm-secondary" wire:click="cancelDelete">
                        Cancelar
                    </button>

                    @if($commentToDeleteId)
                        <button type="button" class="social-confirm-danger" wire:click="confirmDeleteComment" wire:loading.attr="disabled">
                            Excluir comentario
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

    <script>
        document.addEventListener('livewire:init', function () {
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

            Livewire.on('social-profile-updated', function () {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editSocialProfileModal'));
                modal?.hide();
            });
        });
    </script>
</div>
