<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function store(Request $request, Post $post): JsonResponse
    {
        return $this->createComment($request, $post);
    }

    public function storeReply(Request $request, Comment $comment): JsonResponse
    {
        return $this->createComment($request, $comment->post, $comment);
    }

    private function createComment(Request $request, Post $post, ?Comment $parent = null): JsonResponse
    {
        $rules = [
            'content' => ['nullable', 'string', 'max:255', 'required_without:image'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048', 'required_without:content'],
            'share_location' => ['sometimes', 'accepted'],
            'location' => ['nullable', 'string', 'max:100'],
        ];

        if ($parent === null) {
            $rules['parent_id'] = ['nullable', Rule::exists('comments', 'id')->where('post_id', $post->id)];
        }

        $validated = $request->validate($rules);
        $parent ??= isset($validated['parent_id'])
            ? Comment::where('post_id', $post->id)->findOrFail($validated['parent_id'])
            : null;

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['content'] ?? null,
            'parent_id' => $parent?->id,
            'image_path' => $request->file('image')?->store('comments', 'public'),
            'location' => $request->boolean('share_location') ? $validated['location'] ?? null : null,
        ]);

        $recipientId = $parent?->user_id ?? $post->user_id;

        if ($recipientId !== $request->user()->id) {
            Notification::create([
                'user_id' => $recipientId,
                'sender_id' => $request->user()->id,
                'post_id' => $post->id,
                'message' => $parent
                    ? $request->user()->name.' membalas komentar Anda.'
                    : $request->user()->name.' mengomentari postingan Anda.',
            ]);
        }

        return response()->json([
            'success' => true,
            'comment' => $comment->load('user'),
        ]);
    }

    public function destroy(Comment $comment): JsonResponse|RedirectResponse
    {
        if ($comment->user_id !== auth()->id()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Lu gak punya hak buat hapus komentar ini!'], 403);
            }

            return back()->with('error', 'Lu gak punya hak buat hapus komentar ini!');
        }

        $this->deleteCommentImages($comment->load('replies'));
        $comment->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Komentar berhasil dihapus!');
    }

    private function deleteCommentImages(Comment $comment): void
    {
        if ($comment->image_path) {
            Storage::disk('public')->delete($comment->image_path);
        }

        foreach ($comment->replies as $reply) {
            $this->deleteCommentImages($reply);
        }
    }
}
