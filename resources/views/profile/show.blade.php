@extends('layouts.main')

@section('title', 'Profil - ' . $user->name)

@section('content')
<!-- Menampilkan informasi profil dan daftar postingan user -->
<div class="container d-flex justify-content-center py-3">
    <div style="width: 100%; max-width: 600px;">

        <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-3">
                    @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                    @else
                    <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold fs-3 shadow-sm" style="width: 80px; height: 80px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    @endif

                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $user->name }}</h4>
                        <p class="text-muted small mb-0">{{ $user->email }}</p>
                    </div>
                </div>

                @if(Auth::id() === $user->id)
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-dark btn-sm rounded-pill px-4 fw-semibold">Edit Profil</a>
                @endif
            </div>

            <p class="text-secondary mb-3" style="font-size: 14.5px;">
                {{ $user->bio ?? 'Belum ada bio. Tambahkan bio agar profilmu makin keren!' }}
            </p>

            <div class="d-flex gap-3 text-muted small border-top pt-3">
                <div><strong class="text-dark">{{ $user->posts->count() }}</strong> Postingan</div>
            </div>
        </div>

        <h5 class="fw-bold mb-3 text-dark px-1">Postingan</h5>

        <div class="d-flex flex-column gap-3">
            @forelse($user->posts as $post)
            <div class="bg-white p-4 rounded-4 shadow-sm border">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover;">
                        @else
                        <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold small" style="width: 38px; height: 38px;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        @endif
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">{{ $user->name }}</h6>
                            <span class="text-muted" style="font-size: 11px;">{{ $post->created_at->diffForHumans() }}</span>
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
                    <!-- Tombol Like AJAX Murni Tanpa Form & Tanpa Refresh -->
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
                <p class="mt-2 mb-0">Belum ada postingan yang dibuat.</p>
            </div>
            @endforelse
        </div>

    </div>
</div>
@endsection