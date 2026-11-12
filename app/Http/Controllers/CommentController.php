<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|max:255',
            'parent_id' => ['nullable', Rule::exists('comments', 'id')->where('post_id', $post->id)],
        ]);

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'comment' => $comment->load('user'),
        ]);
    }

    public function storeReply(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'content' => 'required|max:255',
        ]);

        $reply = $comment->replies()->create([
            'post_id' => $comment->post_id,
            'user_id' => $request->user()->id,
            'body' => $validated['content'],
        ]);

        return response()->json([
            'success' => true,
            'comment' => $reply->load('user'),
        ]);
    }

    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== auth()->id()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Lu gak punya hak buat hapus komentar ini!'], 403);
            }

            return back()->with('error', 'Lu gak punya hak buat hapus komentar ini!');
        }

        $comment->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Komentar berhasil dihapus!');
    }
}
