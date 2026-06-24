<?php

namespace App\Livewire;

use App\Models\SocialPost;
use App\Models\SocialPostComment;
use App\Models\SocialPostCommentLike;
use App\Models\SocialPostLike;
use App\Models\SocialPostSave;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CommunityProfile extends Component
{
    use WithFileUploads;
    use WithPagination;

    public User $user;
    public string $socialBio = '';
    public $avatar;
    public array $commentBody = [];
    public array $replyBody = [];
    public ?int $postToDeleteId = null;
    public ?int $commentToDeleteId = null;
    public ?int $editingCommentId = null;
    public string $editingCommentBody = '';
    public string $tab = 'posts';
    public string $peopleModal = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->socialBio = (string) ($user->social_bio ?? '');
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

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['posts', 'saved'], true) ? $tab : 'posts';
        $this->resetPage();
    }

    public function openPeopleModal(string $type): void
    {
        $this->peopleModal = in_array($type, ['followers', 'following'], true) ? $type : '';
    }

    public function closePeopleModal(): void
    {
        $this->peopleModal = '';
    }

    public function saveSocialProfile(): void
    {
        if ($this->user->id !== Auth::id()) {
            abort(403);
        }

        $validated = $this->validate([
            'socialBio' => 'nullable|string|max:220',
            'avatar' => 'nullable|image|max:400',
        ]);

        $data = [
            'social_bio' => trim($validated['socialBio'] ?? '') ?: null,
        ];

        if ($this->avatar) {
            $data['avatar_mime'] = $this->avatar->getMimeType();
            $data['avatar_size'] = $this->avatar->getSize();
            $data['avatar_data'] = file_get_contents($this->avatar->getRealPath());
        }

        $this->user->update($data);
        $this->user->refresh();
        $this->reset('avatar');
        $this->dispatch('social-profile-updated');
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

    public function cancelDelete(): void
    {
        $this->postToDeleteId = null;
        $this->commentToDeleteId = null;
    }

    public function render()
    {
        $followingIds = Auth::user()->following()->pluck('users.id');

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
            ->when($this->tab === 'posts', fn ($query) => $query->where('user_id', $this->user->id))
            ->when($this->tab === 'saved', function ($query) {
                $query->whereHas('saves', fn ($saveQuery) => $saveQuery->where('user_id', Auth::id()));
            })
            ->latest()
            ->paginate(8);

        $stats = [
            'posts' => SocialPost::where('user_id', $this->user->id)->count(),
            'followers' => $this->user->followers()->count(),
            'following' => $this->user->following()->count(),
            'saved' => SocialPostSave::where('user_id', Auth::id())->count(),
        ];

        $followers = collect();
        $following = collect();

        if ($this->peopleModal === 'followers') {
            $followers = $this->user->followers()
                ->select(['users.id', 'users.name', 'users.social_bio', 'users.avatar_mime', 'users.avatar_size'])
                ->limit(60)
                ->get();
        }

        if ($this->peopleModal === 'following') {
            $following = $this->user->following()
                ->select(['users.id', 'users.name', 'users.social_bio', 'users.avatar_mime', 'users.avatar_size'])
                ->limit(60)
                ->get();
        }

        return view('livewire.community-profile', [
            'posts' => $posts,
            'stats' => $stats,
            'followingIds' => $followingIds,
            'isFollowing' => Auth::id() !== $this->user->id && $followingIds->contains($this->user->id),
            'followersList' => $followers,
            'followingList' => $following,
        ]);
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
