<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function toggle(Request $request, Post $post)
    {
        $user = auth()->user();
        $existingLike = $post->likes()->where('user_id', $user->id)->first();

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            $post->likes()->create(['user_id' => $user->id]);
            $liked = true;

            if ($post->user_id !== $user->id) {
                Notification::create([
                    'user_id' => $post->user_id,
                    'sender_id' => $user->id,
                    'post_id' => $post->id,
                    'message' => $user->name.' menyukai postingan Anda.',
                ]);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'liked' => $liked,
                'count' => $post->likes()->count(),
            ]);
        }

        return back();
    }

    public function toggleComment(Request $request, Comment $comment): JsonResponse|RedirectResponse
    {
        $userId = $request->user()->id;
        $existingLike = $comment->likes()->where('users.id', $userId)->first();

        if ($existingLike) {
            $comment->likes()->detach($userId);
            $liked = false;
        } else {
            $comment->likes()->attach($userId);
            $liked = true;

            if ($comment->user_id !== $userId) {
                Notification::create([
                    'user_id' => $comment->user_id,
                    'sender_id' => $userId,
                    'post_id' => $comment->post_id,
                    'message' => $request->user()->name.' menyukai komentar Anda.',
                ]);
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'liked' => $liked,
                'count' => $comment->likes()->count(),
            ]);
        }

        return back();
    }
}
