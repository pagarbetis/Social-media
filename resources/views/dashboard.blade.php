@extends('layouts.main')

@section('title', 'Dashboard - Thread Anjay')

@section('content')
<!-- Menampilkan halaman utama dashboard -->
<div class="container d-flex justify-content-center py-4">
    <div style="width: 100%; max-width: 600px;">

        <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
            <h5 class="fw-bold mb-3 text-dark">Buat Postingan Baru</h5>

            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <textarea name="content" class="form-control rounded-3 border-0 bg-light p-3" rows="3" placeholder="Ada apa hari ini?" required style="resize: none;"></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <label for="post-image" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-muted" style="cursor: pointer;">
                            <i class="bi bi-image"></i> Foto
                        </label>
                        <input type="file" id="post-image" name="image" class="d-none">
                    </div>
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold btn-sm">Kirim</button>
                </div>
            </form>
        </div>

        <h5 class="fw-bold mb-3 text-dark px-1">Linimasa</h5>
        <div class="d-flex flex-column gap-3">
            @isset($posts)
            @forelse($posts as $post)
            <div class="bg-white p-4 rounded-4 shadow-sm border">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        @if($post->user->avatar)
                        <img src="{{ asset('storage/' . $post->user->avatar) }}" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover;">
                        @else
                        <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold small" style="width: 38px; height: 38px;">
                            {{ strtoupper(substr($post->user->name, 0, 1)) }}
                        </div>
                        @endif
                        <div>
                            <a href="{{ route('profile.show', $post->user->id) }}" class="fw-bold text-dark text-decoration-none">{{ $post->user->name }}</a>
                            <span class="text-muted d-block" style="font-size: 11px;">{{ $post->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    @if(Auth::id() === $post->user_id)
                    <!-- Form Hapus dengan SweetAlert -->
                    <form id="delete-form-{{ $post->id }}" action="{{ route('posts.destroy', $post->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="confirmDelete({{ $post->id }})" class="btn btn-sm text-danger border-0 bg-transparent p-0" title="Hapus Postingan">
                            <i class="bi bi-trash fs-6"></i>
                        </button>
                    </form>
                    @endif
                </div>

                <p class="mb-3 text-dark" style="font-size: 15px;">{{ $post->content }}</p>

                @if($post->image)
                <div class="mb-3">
                    <img src="{{ asset('storage/' . $post->image) }}" class="img-fluid rounded-4 w-100 shadow-sm" style="max-height: 400px; object-fit: cover;">
                </div>
                @endif

                <div class="d-flex align-items-center gap-3 border-top pt-3 text-muted small">
                    <button type="button" onclick="toggleLike({{ $post->id }})" class="btn btn-sm p-0 border-0 bg-transparent text-muted d-flex align-items-center gap-1 shadow-none">
                        @if($post->likes->contains('user_id', auth()->id()))
                        <i class="bi bi-heart-fill text-danger fs-6" id="like-icon-{{ $post->id }}"></i>
                        @else
                        <i class="bi bi-heart fs-6" id="like-icon-{{ $post->id }}"></i>
                        @endif
                        <span id="like-count-{{ $post->id }}">{{ $post->likes->count() }}</span>
                    </button>

                    <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-chat fs-6"></i>
                        <span>{{ $post->comments->count() }}</span>
                    </div>
                </div>

                <!-- Wadah Daftar Komentar -->
                <div class="mt-3 pt-3 border-top" id="comment-section-{{ $post->id }}">
                    <div id="comment-list-{{ $post->id }}">
                        @foreach($post->comments as $comment)
                        <div class="mb-3" id="comment-container-{{ $comment->id }}">
                            <!-- Komentar Utama -->
                            <div class="d-flex align-items-start gap-2">
                                @if($comment->user->avatar)
                                <img src="{{ asset('storage/' . $comment->user->avatar) }}" class="rounded-circle shadow-sm" style="width: 28px; height: 28px; object-fit: cover;">
                                @else
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 11px;">
                                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                </div>
                                @endif

                                <div class="bg-light p-2 rounded-3 flex-grow-1" style="font-size: 13px;">
                                    <span class="fw-bold text-dark">{{ $comment->user->name }}</span>
                                    <p class="mb-1 text-dark">{{ $comment->body }}</p>

                                    <!-- Tombol untuk memunculkan form balas -->
                                    <button class="btn btn-link btn-sm p-0 text-muted text-decoration-none" style="font-size: 11px;" onclick="toggleReplyForm({{ $comment->id }})">Balas</button>
                                </div>
                            </div>

                            <!-- Daftar Balasan (Replies) -->
                            <div class="ms-4 ps-2 border-start mt-2" id="replies-list-{{ $comment->id }}">
                                @if($comment->replies && $comment->replies->count() > 0)
                                @foreach($comment->replies as $reply)
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    @if($reply->user->avatar)
                                    <img src="{{ asset('storage/' . $reply->user->avatar) }}" class="rounded-circle shadow-sm" style="width: 24px; height: 24px; object-fit: cover;">
                                    @else
                                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 24px; height: 24px; font-size: 10px;">
                                        {{ strtoupper(substr($reply->user->name, 0, 1)) }}
                                    </div>
                                    @endif

                                    <div class="bg-light p-2 rounded-3 flex-grow-1" style="font-size: 12px;">
                                        <span class="fw-bold text-dark">{{ $reply->user->name }}</span>
                                        <p class="mb-0 text-dark">{{ $reply->body }}</p>
                                    </div>
                                </div>
                                @endforeach
                                @endif
                            </div>

                            <!-- Form Balas Komentar (Hidden secara default) -->
                            <form id="reply-form-{{ $comment->id }}" class="ms-4 mt-2 d-none" onsubmit="submitReply(event, {{ $post->id }}, {{ $comment->id }})">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <input type="text" name="content" id="reply-input-{{ $comment->id }}" class="form-control rounded-pill bg-light border-0 px-3" placeholder="Balas {{ $comment->user->name }}..." required style="font-size: 12px;">
                                    <button type="submit" class="btn btn-dark btn-sm rounded-pill px-3 ms-1" style="font-size: 11px;">Kirim</button>
                                </div>
                            </form>
                        </div>
                        @endforeach
                    </div>

                    <!-- Form Komentar Utama AJAX -->
                    <form onsubmit="submitComment(event, {{ $post->id }})" class="mt-2 d-flex gap-2" id="comment-form-{{ $post->id }}">
                        @csrf
                        <input type="text" name="content" id="comment-input-{{ $post->id }}" class="form-control form-control-sm rounded-pill bg-light border-0 px-3" placeholder="Balas postingan..." required>
                        <button type="submit" class="btn btn-dark btn-sm rounded-pill px-3" style="font-size: 12px;">Kirim</button>
                    </form>
                </div>

            </div>
            @empty
            <div class="text-center text-muted py-5 bg-white rounded-4 shadow-sm border">
                <i class="bi bi-chat-square-text fs-1 text-secondary opacity-50"></i>
                <p class="mt-2 mb-0">Belum ada postingan dari siapa pun.</p>
            </div>
            @endforelse
            @endisset
        </div>

    </div>
</div>
@endsection