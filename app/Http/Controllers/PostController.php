<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with([
            'user',
            'likes',
            'comments' => function ($query) {
                $query->whereNull('parent_id')
                    ->oldest()
                    ->with([
                        'user',
                        'replies' => function ($q) {
                            $q->oldest();
                        },
                        'replies.user',
                    ]);
            },
        ])->latest()->get();

        return view('dashboard', compact('posts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'image' => $imagePath,
        ]);

        return redirect()->back()->with('success', 'Postingan berhasil di-upload!');
    }

    public function destroy(Post $post)
    {
        abort_unless($post->user_id === Auth::id(), 403);

        $post->delete();

        return redirect()->route('dashboard')->with('success', 'Post berhasil dihapus!');
    }
}
