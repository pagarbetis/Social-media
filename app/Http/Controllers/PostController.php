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
            'images', 
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
        $validated = $request->validate([
            'content' => 'nullable|string|max:500',
            'images' => 'nullable|array', // <-- Ubah validasi jadi array
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // <-- Validasi tiap file di dalam array
            'share_location' => ['sometimes', 'accepted'],
            'location' => ['nullable', 'string', 'max:100'],
        ]);

        // Cek jika konten kosong DAN tidak ada file gambar yang diunggah
        if (! $request->filled('content') && ! $request->hasFile('images')) {
            return back()->withErrors(['content' => 'Postingan nggak boleh kosong, minimal isi teks atau foto!']);
        }

        // 1. Buat postingan utamanya terlebih dahulu (kolom 'image' tunggal dihapus/diabaikan)
        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'location' => $request->boolean('share_location') ? ($validated['location'] ?? null) : null,
        ]);

        // 2. Jika ada banyak foto yang diunggah, loop dan simpan ke tabel post_images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('posts', 'public');

                $post->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->back();
    }

    public function destroy(Post $post)
    {
        abort_unless($post->user_id === Auth::id(), 403);

        $post->delete();

        return redirect()->route('dashboard')->with('success', 'Post berhasil dihapus!');
    }
}
