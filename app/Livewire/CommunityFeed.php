<?php

namespace App\Livewire;

use App\Models\SocialPost;
use App\Models\SocialPostComment;
use App\Models\SocialPostCommentLike;
use App\Models\SocialPostLike;
use App\Models\SocialPostSave;
use App\Models\SocialStatus;
use App\Models\SocialStatusReaction;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CommunityFeed extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $scope = 'all';
    public ?string $category = null;
    public string $search = '';
    public string $postCategory = 'treino';
    public string $postFormat = 'square';
    public string $caption = '';
    public $photo;
    public string $statusCaption = '';
    public $statusPhoto;
    public array $commentBody = [];
    public array $replyBody = [];
    public ?int $postToDeleteId = null;
    public ?int $statusToDeleteId = null;
    public ?int $commentToDeleteId = null;
    public ?int $editingCommentId = null;
    public string $editingCommentBody = '';

    protected array $categories = [
        'treino' => 'Treino',
        'receita' => 'Receita',
        'evolucao' => 'Evolucao',
        'dica' => 'Dica',
        'refeicao' => 'Refeicao',
    ];

    protected array $statusReactions = [
        'like' => ['icon' => 'bi-heart-fill', 'label' => 'Curti'],
        'fire' => ['icon' => 'bi-fire', 'label' => 'Top'],
        'strong' => ['icon' => 'bi-lightning-charge-fill', 'label' => 'Brabo'],
        'star' => ['icon' => 'bi-star-fill', 'label' => 'Incrivel'],
    ];

    public function mount(?string $scope = 'all', ?string $category = null): void
    {
        $this->scope = $scope === 'following' ? 'following' : 'all';
        $this->category = array_key_exists((string) $category, $this->categories) ? $category : null;
    }

    public function setScope(string $scope): void
    {
        $this->scope = $scope === 'following' ? 'following' : 'all';
        $this->resetPage();
    }

    public function setCategory(?string $category = null): void
    {
        $this->category = array_key_exists((string) $category, $this->categories) ? $category : null;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->search = mb_substr(trim($this->search), 0, 80);
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function createPost(): void
    {
        $validated = $this->validate([
            'postCategory' => 'required|in:treino,receita,evolucao,dica,refeicao',
            'postFormat' => 'required|in:square,portrait',
            'caption' => 'nullable|string|max:1200',
            'photo' => 'required|image|max:800',
        ]);

        SocialPost::create([
            'user_id' => Auth::id(),
            'category' => $validated['postCategory'],
            'display_format' => $validated['postFormat'],
            'caption' => trim($validated['caption'] ?? '') ?: null,
            'photo_mime' => $this->photo->getMimeType(),
            'photo_size' => $this->photo->getSize(),
            'photo_data' => file_get_contents($this->photo->getRealPath()),
        ]);

        $this->reset(['caption', 'photo']);
        $this->postCategory = 'treino';
        $this->postFormat = 'square';
        $this->resetPage();
        $this->dispatch('social-post-created');
    }

    public function createStatus(): void
    {
        $validated = $this->validate([
            'statusCaption' => 'nullable|string|max:300',
            'statusPhoto' => 'required|image|max:750',
        ]);

        SocialStatus::create([
            'user_id' => Auth::id(),
            'caption' => trim($validated['statusCaption'] ?? '') ?: null,
            'photo_mime' => $this->statusPhoto->getMimeType(),
            'photo_size' => $this->statusPhoto->getSize(),
            'photo_data' => file_get_contents($this->statusPhoto->getRealPath()),
            'expires_at' => now()->addDay(),
        ]);

        $this->reset(['statusCaption', 'statusPhoto']);
        $this->dispatch('social-status-created');
    }

    public function toggleLike(int $postId): void
    {
        $post = SocialPost::findOrFail($postId);
        $liked = false;

        DB::transaction(function () use ($post, &$liked) {
            $like = SocialPostLike::where('social_post_id', $post->id)
                ->where('user_id', Auth::id())
                ->first();

            if ($like) {
                $like->delete();
                $post->decrement('likes_count');

                return;
            }

            SocialPostLike::create([
                'social_post_id' => $post->id,
                'user_id' => Auth::id(),
            ]);

            $post->increment('likes_count');
            $liked = true;
        });

        if ($liked) {
            $this->notifyPostOwner($post, 'Nova curtida', Auth::user()->name . ' curtiu seu post.', 'bi-heart-fill');
        }
    }

    public function toggleSave(int $postId): void
    {
        $post = SocialPost::findOrFail($postId);

        $save = SocialPostSave::where('social_post_id', $post->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($save) {
            $save->delete();

            return;
        }

        SocialPostSave::create([
            'social_post_id' => $post->id,
            'user_id' => Auth::id(),
        ]);
    }

    public function addComment(int $postId): void
    {
        $body = trim((string) ($this->commentBody[$postId] ?? ''));

        if ($body === '') {
            return;
        }

        $post = SocialPost::findOrFail($postId);

        DB::transaction(function () use ($post, $body) {
            SocialPostComment::create([
                'social_post_id' => $post->id,
                'user_id' => Auth::id(),
                'body' => $body,
            ]);

            $post->increment('comments_count');
        });

        $this->commentBody[$postId] = '';
        $this->notifyPostOwner($post, 'Novo comentario', Auth::user()->name . ' comentou no seu post.', 'bi-chat-dots-fill');
    }

    public function addCommentReply(int $commentId): void
    {
        $body = trim((string) ($this->replyBody[$commentId] ?? ''));

        if ($body === '') {
            return;
        }

        $comment = SocialPostComment::with('post')->findOrFail($commentId);

        DB::transaction(function () use ($comment, $body) {
            SocialPostComment::create([
                'social_post_id' => $comment->social_post_id,
                'user_id' => Auth::id(),
                'parent_id' => $comment->id,
                'body' => $body,
            ]);

            $comment->post->increment('comments_count');
        });

        $this->replyBody[$commentId] = '';

        if ($comment->user_id !== Auth::id()) {
            UserNotification::create([
                'user_id' => $comment->user_id,
                'title' => 'Nova resposta',
                'message' => Auth::user()->name . ' respondeu seu comentario.',
                'type' => 'social',
                'icon' => 'bi-reply-fill',
                'link_url' => route('community.index') . '#post-' . $comment->social_post_id,
            ]);
        }
    }

    public function toggleCommentLike(int $commentId): void
    {
        $comment = SocialPostComment::findOrFail($commentId);
        $liked = false;

        DB::transaction(function () use ($comment, &$liked) {
            $like = SocialPostCommentLike::where('social_post_comment_id', $comment->id)
                ->where('user_id', Auth::id())
                ->first();

            if ($like) {
                $like->delete();
                $comment->decrement('likes_count');

                return;
            }

            SocialPostCommentLike::create([
                'social_post_comment_id' => $comment->id,
                'user_id' => Auth::id(),
            ]);

            $comment->increment('likes_count');
            $liked = true;
        });

        if ($liked && $comment->user_id !== Auth::id()) {
            UserNotification::create([
                'user_id' => $comment->user_id,
                'title' => 'Curtida no comentario',
                'message' => Auth::user()->name . ' curtiu seu comentario.',
                'type' => 'social',
                'icon' => 'bi-heart-fill',
                'link_url' => route('community.index') . '#post-' . $comment->social_post_id,
            ]);
        }
    }

    public function startEditComment(int $commentId): void
    {
        $comment = SocialPostComment::findOrFail($commentId);

        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $this->editingCommentId = $comment->id;
        $this->editingCommentBody = $comment->body;
    }

    public function cancelEditComment(): void
    {
        $this->editingCommentId = null;
        $this->editingCommentBody = '';
    }

    public function updateComment(): void
    {
        if (!$this->editingCommentId) {
            return;
        }

        $body = trim($this->editingCommentBody);

        if ($body === '') {
            return;
        }

        $comment = SocialPostComment::findOrFail($this->editingCommentId);

        if ($comment->user_id !== Auth::id()) {
            abort(403);
        }

        $comment->update(['body' => $body]);
        $this->cancelEditComment();
    }

    public function askDeleteComment(int $commentId): void
    {
        $comment = SocialPostComment::findOrFail($commentId);

        if ($comment->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        $this->commentToDeleteId = $comment->id;
        $this->postToDeleteId = null;
        $this->statusToDeleteId = null;
    }

    public function confirmDeleteComment(): void
    {
        if (!$this->commentToDeleteId) {
            return;
        }

        $comment = SocialPostComment::with('replies')->findOrFail($this->commentToDeleteId);

        if ($comment->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        DB::transaction(function () use ($comment) {
            $deletedCount = 1 + $comment->replies()->count();
            $post = SocialPost::find($comment->social_post_id);

            if ($post) {
                $post->update([
                    'comments_count' => max(0, $post->comments_count - $deletedCount),
                ]);
            }

            $comment->delete();
        });

        if ($this->editingCommentId === $this->commentToDeleteId) {
            $this->cancelEditComment();
        }

        $this->commentToDeleteId = null;
        $this->dispatch('social-content-deleted');
    }

    public function toggleFollow(int $userId): void
    {
        if ($userId === Auth::id()) {
            return;
        }

        $user = User::findOrFail($userId);
        $following = false;

        DB::transaction(function () use ($user, &$following) {
            $follow = UserFollow::where('follower_id', Auth::id())
                ->where('followed_id', $user->id)
                ->first();

            if ($follow) {
                $follow->delete();

                return;
            }

            UserFollow::create([
                'follower_id' => Auth::id(),
                'followed_id' => $user->id,
            ]);

            $following = true;
        });

        if ($following) {
            UserNotification::create([
                'user_id' => $user->id,
                'title' => 'Novo seguidor',
                'message' => Auth::user()->name . ' comecou a seguir voce.',
                'type' => 'social',
                'icon' => 'bi-person-plus-fill',
                'link_url' => route('community.profile', Auth::id()),
            ]);
        }
    }

    public function reactToStatus(int $statusId, string $reaction): void
    {
        if (!array_key_exists($reaction, $this->statusReactions)) {
            return;
        }

        $status = SocialStatus::where('expires_at', '>', now())->findOrFail($statusId);
        $created = false;

        DB::transaction(function () use ($status, $reaction, &$created) {
            $existing = SocialStatusReaction::where('social_status_id', $status->id)
                ->where('user_id', Auth::id())
                ->first();

            if ($existing && $existing->reaction === $reaction) {
                $existing->delete();

                return;
            }

            SocialStatusReaction::updateOrCreate(
                [
                    'social_status_id' => $status->id,
                    'user_id' => Auth::id(),
                ],
                ['reaction' => $reaction]
            );

            $created = true;
        });

        if ($created && $status->user_id !== Auth::id()) {
            UserNotification::create([
                'user_id' => $status->user_id,
                'title' => 'Reacao no status',
                'message' => Auth::user()->name . ' reagiu ao seu status.',
                'type' => 'social',
                'icon' => $this->statusReactions[$reaction]['icon'],
                'link_url' => route('community.index'),
            ]);
        }
    }

    public function deletePost(int $postId): void
    {
        $post = SocialPost::findOrFail($postId);

        if ($post->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        $post->delete();
    }

    public function askDeletePost(int $postId): void
    {
        $this->postToDeleteId = $postId;
        $this->statusToDeleteId = null;
        $this->commentToDeleteId = null;
    }

    public function confirmDeletePost(): void
    {
        if (!$this->postToDeleteId) {
            return;
        }

        $this->deletePost($this->postToDeleteId);
        $this->postToDeleteId = null;
        $this->dispatch('social-content-deleted');
    }

    public function askDeleteStatus(int $statusId): void
    {
        $this->statusToDeleteId = $statusId;
        $this->postToDeleteId = null;
        $this->commentToDeleteId = null;
    }

    public function confirmDeleteStatus(): void
    {
        if (!$this->statusToDeleteId) {
            return;
        }

        $status = SocialStatus::findOrFail($this->statusToDeleteId);

        if ($status->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        $status->delete();
        $this->statusToDeleteId = null;
        $this->dispatch('social-content-deleted');
    }

    public function cancelDelete(): void
    {
        $this->postToDeleteId = null;
        $this->statusToDeleteId = null;
        $this->commentToDeleteId = null;
    }

    public function render()
    {
        $followingIds = Auth::user()->following()->pluck('users.id');
        $categories = $this->categories;
        $searchTerm = trim($this->search);
        $searchLike = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $searchTerm) . '%';

        $posts = SocialPost::query()
            ->select([
                'id',
                'user_id',
                'category',
                'display_format',
                'caption',
                'photo_mime',
                'photo_size',
                'likes_count',
                'comments_count',
                'created_at',
                'updated_at',
            ])
            ->with([
                'user:id,name,social_bio,avatar_mime,avatar_size',
                'likes' => fn ($query) => $query->where('user_id', Auth::id()),
                'saves' => fn ($query) => $query->where('user_id', Auth::id()),
                'comments' => fn ($query) => $query
                    ->whereNull('parent_id')
                    ->with([
                        'user:id,name',
                        'likes' => fn ($likeQuery) => $likeQuery->where('user_id', Auth::id()),
                        'replies' => fn ($replyQuery) => $replyQuery
                            ->with([
                                'user:id,name',
                                'likes' => fn ($likeQuery) => $likeQuery->where('user_id', Auth::id()),
                            ])
                            ->oldest(),
                    ])
                    ->latest()
                    ->limit(3),
            ])
            ->when($this->scope === 'following', fn ($query) => $query->whereIn('user_id', $followingIds))
            ->when($this->category, fn ($query) => $query->where('category', $this->category))
            ->when($searchTerm !== '', function ($query) use ($searchTerm, $searchLike) {
                $categoryKeys = collect($this->categories)
                    ->filter(fn ($label, $key) => str_contains(mb_strtolower($label), mb_strtolower($searchTerm)) || str_contains(mb_strtolower($key), mb_strtolower($searchTerm)))
                    ->keys()
                    ->all();

                $query->where(function ($innerQuery) use ($searchLike, $categoryKeys) {
                    $innerQuery
                        ->where('caption', 'like', $searchLike)
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $searchLike));

                    if (!empty($categoryKeys)) {
                        $innerQuery->orWhereIn('category', $categoryKeys);
                    }
                });
            })
            ->latest()
            ->paginate(8);

        $peopleResults = collect();

        if ($searchTerm !== '') {
            $peopleResults = User::query()
                ->select(['id', 'name', 'social_bio', 'avatar_mime', 'avatar_size'])
                ->where('id', '!=', Auth::id())
                ->where('name', 'like', $searchLike)
                ->latest()
                ->limit(6)
                ->get();
        }

        $suggestedUsers = User::query()
            ->select(['id', 'name', 'social_bio', 'avatar_mime', 'avatar_size'])
            ->where('id', '!=', Auth::id())
            ->whereNotIn('id', $followingIds)
            ->latest()
            ->limit(6)
            ->get();

        $statuses = SocialStatus::query()
            ->select(['id', 'user_id', 'caption', 'photo_mime', 'photo_size', 'expires_at', 'created_at'])
            ->with([
                'user:id,name',
                'reactions:id,social_status_id,user_id,reaction',
            ])
            ->where('expires_at', '>', now())
            ->latest()
            ->limit(18)
            ->get();

        $stats = [
            'posts' => SocialPost::where('user_id', Auth::id())->count(),
            'followers' => Auth::user()->followers()->count(),
            'following' => $followingIds->count(),
        ];
        $statusReactions = $this->statusReactions;

        return view('livewire.community-feed', compact(
            'posts',
            'categories',
            'suggestedUsers',
            'statuses',
            'statusReactions',
            'stats',
            'followingIds',
            'peopleResults'
        ));
    }

    private function notifyPostOwner(SocialPost $post, string $title, string $message, string $icon): void
    {
        if ($post->user_id === Auth::id()) {
            return;
        }

        UserNotification::create([
            'user_id' => $post->user_id,
            'title' => $title,
            'message' => $message,
            'type' => 'social',
            'icon' => $icon,
            'link_url' => route('community.index') . '#post-' . $post->id,
        ]);
    }
}
