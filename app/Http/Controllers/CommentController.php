<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // Simpan Komentar Utama
    public function store(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|max:255'
        ]);

        $comment = Comment::create([
            'post_id' => $postId,
            'user_id' => Auth::id(),
            'body' => $request->content,
            'parent_id' => null
        ]);

        return response()->json([
            'success' => true,
            'comment' => $comment->load('user')
        ]);
    }

    // Simpan Balasan Komentar (Reply)
    public function storeReply(Request $request, $commentId)
    {
        $request->validate([
            'content' => 'required|max:255',
            'post_id' => 'required|exists:posts,id'
        ]);

        $reply = Comment::create([
            'post_id' => $request->post_id,
            'user_id' => Auth::id(),
            'body' => $request->content,
            'parent_id' => $commentId
        ]);

        return response()->json([
            'success' => true,
            'reply' => $reply->load('user')
        ]);
    }
}
