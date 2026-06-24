<?php

namespace App\Http\Controllers;

use App\Models\SocialPost;
use App\Models\SocialPostComment;
use App\Models\SocialPostLike;
use App\Models\SocialStatus;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SocialController extends Controller
{
    public function index(Request $request)
    {
        $scope = $request->get('scope') === 'following' ? 'following' : 'all';
        $category = $request->get('category');

        return view('social.index', compact('scope', 'category'));
    }

    public function profile(User $user)
    {
        return view('social.profile', compact('user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:treino,receita,evolucao,dica,refeicao',
            'display_format' => 'nullable|in:square,portrait',
            'caption' => 'nullable|string|max:1200',
            'photo' => 'required|image|max:800',
        ]);

        $photo = $request->file('photo');

        $post = SocialPost::create([
            'user_id' => Auth::id(),
            'category' => $validated['category'],
            'display_format' => $validated['display_format'] ?? 'square',
            'caption' => $validated['caption'] ?? null,
            'photo_mime' => $photo->getMimeType(),
            'photo_size' => $photo->getSize(),
            'photo_data' => file_get_contents($photo->getRealPath()),
        ]);

        return redirect()
            ->route('community.index')
            ->with('success', 'Post publicado com sucesso!');
    }

    public function photo(SocialPost $post)
    {
        $post = SocialPost::select(['id', 'photo_mime', 'photo_data'])->findOrFail($post->id);

        if (!$post->photo_data) {
            abort(404);
        }

        $photoData = is_resource($post->photo_data)
            ? stream_get_contents($post->photo_data)
            : $post->photo_data;

        return response($photoData)
            ->header('Content-Type', $post->photo_mime ?: 'image/jpeg')
            ->header('Cache-Control', 'private, max-age=86400');
    }

    public function statusPhoto(SocialStatus $status)
    {
        $status = SocialStatus::select(['id', 'photo_mime', 'photo_data', 'expires_at'])->findOrFail($status->id);

        if (!$status->photo_data || $status->expires_at->isPast()) {
            abort(404);
        }

        $photoData = is_resource($status->photo_data)
            ? stream_get_contents($status->photo_data)
            : $status->photo_data;

        return response($photoData)
            ->header('Content-Type', $status->photo_mime ?: 'image/jpeg')
            ->header('Cache-Control', 'private, max-age=3600');
    }

    public function avatar(User $user)
    {
        $user = User::select(['id', 'avatar_mime', 'avatar_data'])->findOrFail($user->id);

        if (!$user->avatar_data) {
            abort(404);
        }

        $avatarData = is_resource($user->avatar_data)
            ? stream_get_contents($user->avatar_data)
            : $user->avatar_data;

        return response($avatarData)
            ->header('Content-Type', $user->avatar_mime ?: 'image/jpeg')
            ->header('Cache-Control', 'private, max-age=86400');
    }

    public function toggleLike(SocialPost $post)
    {
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

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $post->fresh()->likes_count,
        ]);
    }

    public function comment(Request $request, SocialPost $post)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:500',
        ]);

        $comment = DB::transaction(function () use ($post, $validated) {
            $comment = SocialPostComment::create([
                'social_post_id' => $post->id,
                'user_id' => Auth::id(),
                'body' => $validated['body'],
            ]);

            $post->increment('comments_count');

            return $comment->load('user:id,name');
        });

        $this->notifyPostOwner($post, 'Novo comentario', Auth::user()->name . ' comentou no seu post.', 'bi-chat-dots-fill');

        return response()->json([
            'success' => true,
            'comments_count' => $post->fresh()->comments_count,
            'comment' => [
                'author' => $comment->user->name,
                'body' => $comment->body,
                'created_at' => $comment->created_at->diffForHumans(),
            ],
        ]);
    }

    public function toggleFollow(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Voce nao pode seguir a si mesmo.',
            ], 422);
        }

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

        return response()->json([
            'success' => true,
            'following' => $following,
            'followers_count' => $user->followers()->count(),
        ]);
    }

    public function destroy(SocialPost $post)
    {
        if ($post->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }

        $post->delete();

        return redirect()
            ->route('community.index')
            ->with('success', 'Post removido com sucesso!');
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
