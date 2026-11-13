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
                        'likes',
                        'replies' => function ($q) {
                            $q->oldest()->with(['user', 'likes']);
                        },
                    ]);
            },
        ])->latest()->get();

        return view('dashboard', compact('posts'));
    }

    public function store(Request $request)
    {
        /**
         * destroy
         *
         * @param  Post  $post
         * @return void
         */
        $validated = $request->validate([
            'content' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'share_location' => ['sometimes', 'accepted'],
            'location' => ['nullable', 'string', 'max:100'],
        ]);

        if (! $request->filled('content') && ! $request->hasFile('image')) {
            return back()->withErrors(['content' => 'Postingan nggak boleh kosong, minimal isi teks atau foto!']);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'image' => $imagePath,
            'location' => $request->boolean('share_location') ? ($validated['location'] ?? null) : null,
        ]);

        return redirect()->back();
    }

    public function destroy(Post $post)
    {
        abort_unless($post->user_id === Auth::id(), 403);

        $post->delete();

        return redirect()->route('dashboard')->with('success', 'Post berhasil dihapus!');
    }
}
