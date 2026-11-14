@extends('layouts.main')

@section('title', 'Dashboard - Thread Anjay')

@section('content')
<!-- Menampilkan halaman utama dashboard -->
<div class="container py-4 d-flex justify-content-center">
    <div style="width: 100%; max-width: 600px;">

        <div class="p-4 mb-4 bg-white border shadow-sm rounded-4">
            <h5 class="mb-3 fw-bold text-dark">Buat Postingan Baru</h5>

            <form id="post-form" action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <textarea name="content" id="postContent" class="p-3 border-0 form-control rounded-3 bg-light" rows="3"
                        placeholder="Ada apa hari ini?" style="resize: none;" maxlength="501" oninput="checkLength(this)">{{ old('content') }}</textarea>

                    <div class="px-1 mt-2 d-flex justify-content-between align-items-center">
                        <small id="warningMessage" class="text-danger fw-semibold" style="display: none;">
                            Text terlalu panjang! Maksimal 500 karakter.
                        </small>
                        @error('content')
                        <small class="text-danger fw-semibold">{{ $message }}</small>
                        @enderror
                        <small id="charCounter" class="text-muted ms-auto">0/500</small>
                    </div>
                </div>
                <input type="hidden" name="share_location" value="1" disabled>
                <input type="hidden" name="location" value="">

                <!-- Area Preview Gambar -->
                <div id="image-preview-container" class="mb-3 d-none position-relative"
                    style="width: 120px; height: 120px;">
                    <img id="image-preview" src="#" alt="Preview"
                        class="border w-100 h-100 object-fit-cover rounded-3">
                    <button type="button" id="remove-image"
                        class="top-0 p-0 border-0 shadow-sm position-absolute start-100 translate-middle badge rounded-pill bg-danger d-flex align-items-center justify-content-center"
                        style="width: 24px; height: 24px; cursor: pointer;">X</button>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <label for="post-image"
                            class="px-3 py-1 btn btn-sm btn-outline-secondary rounded-pill text-muted"
                            style="cursor: pointer;">
                            <i class="bi bi-image"></i> Foto
                        </label>
                        <input type="file" id="post-image" name="image" class="d-none" accept="image/*">
                    </div>



                    <!-- Tombol Kirim dengan State Loading -->
                    <button type="submit" id="submit-btn" class="px-4 btn btn-dark rounded-pill fw-semibold btn-sm">
                        <span id="btn-text">Kirim</span>
                        <span id="btn-spinner" class="spinner-border spinner-border-sm d-none" role="status"
                            aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>

        <h5 class="px-1 mb-3 fw-bold text-dark">Linimasa</h5>
        <div class="gap-3 d-flex flex-column">
            @isset($posts)
            @forelse($posts as $post)
            <div class="p-4 bg-white border shadow-sm rounded-4" id="post-{{ $post->id }}">
                <div class="mb-3 d-flex align-items-center justify-content-between">
                    <div class="gap-2 d-flex align-items-center">
                        <x-user-popover :user="$post->user" :size="38" />
                        <div>
                            <a href="{{ route('profile.show', $post->user->id) }}"
                                class="fw-bold text-dark text-decoration-none">{{ $post->user->name }}</a>
                            <span class="text-muted d-block"
                                style="font-size: 11px;">{{ $post->created_at->diffForHumans() }}</span>
                            @if ($post->location)
                            <span class="text-muted" style="font-size: 11px;">
                                <i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $post->location }}
                            </span>
                            @endif
                        </div>
                    </div>

                    @if (Auth::id() === $post->user_id)
                    <!-- Form Hapus dengan SweetAlert -->
                    <form id="delete-form-{{ $post->id }}" action="{{ route('posts.destroy', $post->id) }}"
                        method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" onclick="confirmDelete({{ $post->id }})"
                            class="p-0 bg-transparent border-0 btn btn-sm text-danger" title="Hapus Postingan">
                            <i class="bi bi-trash fs-6"></i>
                        </button>
                    </form>
                    @endif
                </div>

                <p class="mb-3 text-dark" style="font-size: 15px;">{{ $post->content }}</p>

                @if ($post->image)
                <div class="mb-3">
                    <img src="{{ asset('storage/' . $post->image) }}"
                        class="shadow-sm img-fluid rounded-4 w-100"
                        style="max-height: 400px; object-fit: cover;">
                </div>
                @endif

                <div class="gap-3 pt-3 d-flex align-items-center border-top text-muted small">
                    <button type="button" onclick="toggleLike({{ $post->id }})"
                        class="gap-1 p-0 bg-transparent border-0 shadow-none btn btn-sm text-muted d-flex align-items-center">
                        @if ($post->likes->contains('user_id', auth()->id()))
                        <i class="bi bi-heart-fill text-danger fs-6" id="like-icon-{{ $post->id }}"></i>
                        @else
                        <i class="bi bi-heart fs-6" id="like-icon-{{ $post->id }}"></i>
                        @endif
                        <span id="like-count-{{ $post->id }}">{{ $post->likes->count() }}</span>
                    </button>

                    <div class="gap-1 d-flex align-items-center">
                        <i class="bi bi-chat fs-6"></i>
                        <span id="comment-count-{{ $post->id }}">{{ $post->comments->count() }}</span>
                    </div>
                </div>

                <!-- Wadah Daftar Komentar -->
                <div class="pt-3 mt-3 border-top" id="comment-section-{{ $post->id }}">

                    <!-- 1. LIST KOMENTAR UTAMA -->
                    <div id="comment-list-{{ $post->id }}">
                        @foreach ($post->comments->whereNull('parent_id') as $comment)
                        <div class="mb-3" id="comment-container-{{ $comment->id }}"
                            data-post-id="{{ $post->id }}">

                            <!-- Komentar Utama -->
                            <div class="gap-2 d-flex align-items-start">
                                <x-user-popover :user="$comment->user" :size="28" />

                                <div class="p-2 bg-light rounded-3 flex-grow-1" style="font-size: 13px;">
                                    <span class="d-block fw-bold text-dark">{{ $comment->user->name }}</span>

                                    <!-- Teks Komentar (Diberi ID agar bisa diupdate AJAX) -->
                                    <p class="mb-1 text-dark" id="comment-body-{{ $comment->id }}">{{ $comment->body }}</p>

                                    <!-- Gambar Komentar -->
                                    @if ($comment->image_path)
                                    <div id="comment-image-container-{{ $comment->id }}">
                                        <img src="{{ asset('storage/' . $comment->image_path) }}"
                                            alt="Foto komentar" class="mt-1 mb-2 img-fluid rounded-3"
                                            style="max-height: 200px; object-fit: contain;">
                                    </div>
                                    @endif

                                    <!-- Informasi Lokasi, Waktu & Tombol Aksi -->
                                    <div class="gap-2 d-flex align-items-center flex-wrap">
                                        @if ($comment->location)
                                        <span class="text-muted comment-location-badge" style="font-size: 10px;">
                                            <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                            {{ $comment->location }}
                                        </span>
                                        @endif
                                        <span class="text-muted"
                                            style="font-size: 10px;">{{ $comment->created_at->format('d M Y, H:i') }}</span>

                                        <button class="p-0 btn btn-link btn-sm text-muted text-decoration-none"
                                            style="font-size: 11px;"
                                            onclick="toggleReplyForm({{ $comment->id }})">Balas</button>

                                        <button type="button" id="comment-like-button-{{ $comment->id }}"
                                            class="gap-1 p-0 btn btn-link btn-sm text-muted text-decoration-none ms-auto"
                                            style="font-size: 11px;" title="Suka komentar"
                                            aria-label="Suka komentar"
                                            aria-pressed="{{ $comment->likes->contains('id', auth()->id()) ? 'true' : 'false' }}"
                                            onclick="toggleCommentLike({{ $comment->id }})">
                                            <i class="bi {{ $comment->likes->contains('id', auth()->id()) ? 'bi-heart-fill text-danger' : 'bi-heart' }}"
                                                id="comment-like-icon-{{ $comment->id }}"
                                                aria-hidden="true"></i>
                                            <span
                                                id="comment-like-count-{{ $comment->id }}">{{ $comment->likes->count() }}</span>
                                        </button>

                                        <!-- Tombol Edit (Hanya pemilik) -->
                                        @if(auth()->id() === $comment->user_id)
                                        <button type="button" class="p-0 border-0 btn btn-link text-primary text-decoration-none"
                                            style="font-size: 11px;"
                                            onclick="toggleEditForm({{ $comment->id }})">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        @endif

                                        <!-- Tombol Hapus -->
                                        @if ($comment->user_id === auth()->id())
                                        <button type="button"
                                            class="p-0 border-0 btn btn-link text-danger text-decoration-none"
                                            style="font-size: 11px; z-index: 10; position: relative;"
                                            onclick="confirmDeleteComment('{{ route('comments.destroy', $comment->id) }}', 'comment-container-{{ $comment->id }}')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                        @endif
                                    </div>

                                    <!-- FORM EDIT KOMENTAR -->
                                    @if(auth()->id() === $comment->user_id)
                                    <form id="edit-form-{{ $comment->id }}" class="edit-comment-form d-none mt-2" enctype="multipart/form-data" onsubmit="submitEditComment(event, {{ $comment->id }})">
                                        @csrf

                                        <div class="input-group input-group-sm">
                                            <input type="text" name="body" id="edit-input-{{ $comment->id }}"
                                                class="px-3 border-0 form-control rounded-pill bg-light"
                                                value="{{ $comment->body }}"
                                                placeholder="Edit komentar..."
                                                style="font-size: 12px;">

                                            <button type="button"
                                                class="p-0 border btn btn-light d-inline-flex align-items-center justify-content-center text-secondary comment-image-trigger"
                                                title="Tambah foto" aria-label="Tambah foto komentar">
                                                <i class="bi bi-image" aria-hidden="true"></i>
                                            </button>

                                            <input type="file" name="image" class="d-none comment-image-input"
                                                accept="image/jpeg,image/png,image/gif"
                                                aria-label="Pilih foto komentar">

                                            <button type="submit"
                                                class="px-3 btn btn-dark btn-sm rounded-pill ms-1"
                                                style="font-size: 11px;">Simpan</button>

                                            <button type="button"
                                                class="px-3 btn btn-light btn-sm rounded-pill ms-1 text-danger"
                                                style="font-size: 11px;"
                                                onclick="toggleEditForm({{ $comment->id }})">Batal</button>
                                        </div>

                                        @if($comment->image_path)
                                        <div class="form-check mt-1 ms-2">
                                            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove-image-{{ $comment->id }}">
                                            <label class="form-check-label text-danger" for="remove-image-{{ $comment->id }}" style="font-size: 10px;">
                                                Hapus gambar lama
                                            </label>
                                        </div>
                                        @endif

                                        <div class="gap-2 mt-2 d-none align-items-center comment-image-preview">
                                            <img src="" alt="Preview foto komentar" class="rounded object-fit-cover"
                                                style="width: 64px; height: 64px;">
                                            <button type="button"
                                                class="p-0 btn btn-link btn-sm text-danger comment-image-remove">Hapus foto</button>
                                        </div>
                                    </form>
                                    @endif
                                </div>
                            </div>

                            <!-- Daftar Balasan (Replies) -->
                            <div class="mt-2 ms-4 ps-2 border-start" id="replies-list-{{ $comment->id }}">
                                @if ($comment->replies->isNotEmpty())
                                @foreach ($comment->replies as $reply)
                                <div class="gap-2 mb-2 d-flex align-items-start"
                                    id="comment-container-{{ $reply->id }}"
                                    data-post-id="{{ $post->id }}">
                                    <x-user-popover :user="$reply->user" :size="24" />

                                    <div class="p-2 bg-light rounded-3 flex-grow-1"
                                        style="font-size: 12px;">
                                        <span
                                            class="d-block fw-bold text-dark">{{ $reply->user->name }}</span>

                                        <!-- Teks Reply -->
                                        <p class="mb-1 text-dark" id="comment-body-{{ $reply->id }}">{{ $reply->body }}</p>

                                        <!-- Gambar Reply -->
                                        @if ($reply->image_path)
                                        <div id="comment-image-container-{{ $reply->id }}">
                                            <img src="{{ asset('storage/' . $reply->image_path) }}"
                                                alt="Foto komentar"
                                                class="d-block mt-1 mb-2 img-fluid rounded-3"
                                                style="max-height: 200px; object-fit: contain;">
                                        </div>
                                        @endif

                                        <div class="gap-2 d-flex align-items-center flex-wrap">
                                            <span class="text-muted"
                                                style="font-size: 10px;">{{ $reply->created_at->format('d M Y, H:i') }}</span>
                                            @if ($reply->location)
                                            <span class="text-muted comment-location-badge" style="font-size: 10px;">
                                                <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                                {{ $reply->location }}
                                            </span>
                                            @endif

                                            <button
                                                class="p-0 btn btn-link btn-sm text-muted text-decoration-none"
                                                style="font-size: 10px;"
                                                onclick="toggleReplyForm({{ $reply->id }})">Balas</button>

                                            <button type="button"
                                                id="comment-like-button-{{ $reply->id }}"
                                                class="gap-1 p-0 btn btn-link btn-sm text-muted text-decoration-none d-inline-flex align-items-center ms-auto"
                                                style="font-size: 10px;" title="Suka komentar"
                                                aria-label="Suka komentar"
                                                aria-pressed="{{ $reply->likes->contains('id', auth()->id()) ? 'true' : 'false' }}"
                                                onclick="toggleCommentLike({{ $reply->id }})">
                                                <i class="bi {{ $reply->likes->contains('id', auth()->id()) ? 'bi-heart-fill text-danger' : 'bi-heart' }}"
                                                    id="comment-like-icon-{{ $reply->id }}"
                                                    aria-hidden="true"></i>
                                                <span
                                                    id="comment-like-count-{{ $reply->id }}">{{ $reply->likes->count() }}</span>
                                            </button>

                                            <!-- Tombol Edit Reply -->
                                            @if(auth()->id() === $reply->user_id)
                                            <button type="button" class="p-0 border-0 btn btn-link text-primary text-decoration-none"
                                                style="font-size: 10px;"
                                                onclick="toggleEditForm({{ $reply->id }})">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            @endif

                                            <!-- TOMBOL HAPUS REPLY -->
                                            @if ($reply->user_id === auth()->id())
                                            <button type="button"
                                                class="p-0 border-0 btn btn-link text-danger text-decoration-none"
                                                style="font-size: 10px; z-index: 10; position: relative;"
                                                onclick="confirmDeleteComment('{{ route('comments.destroy', $reply->id) }}', 'comment-container-{{ $reply->id }}')">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                            @endif
                                        </div>

                                        <!-- FORM EDIT REPLY -->
                                        @if(auth()->id() === $reply->user_id)
                                        <form id="edit-form-{{ $reply->id }}" class="edit-comment-form d-none mt-2" enctype="multipart/form-data" onsubmit="submitEditComment(event, {{ $reply->id }})">
                                            @csrf

                                            <div class="input-group input-group-sm">
                                                <input type="text" name="body" id="edit-input-{{ $reply->id }}"
                                                    class="px-3 border-0 form-control rounded-pill bg-light"
                                                    value="{{ $reply->body }}"
                                                    placeholder="Edit komentar..."
                                                    style="font-size: 11px;">

                                                <button type="button"
                                                    class="p-0 border btn btn-light d-inline-flex align-items-center justify-content-center text-secondary comment-image-trigger"
                                                    title="Tambah foto" aria-label="Tambah foto komentar">
                                                    <i class="bi bi-image" aria-hidden="true"></i>
                                                </button>

                                                <input type="file" name="image" class="d-none comment-image-input"
                                                    accept="image/jpeg,image/png,image/gif"
                                                    aria-label="Pilih foto komentar">

                                                <button type="submit"
                                                    class="px-3 btn btn-dark btn-sm rounded-pill ms-1"
                                                    style="font-size: 10px;">Simpan</button>

                                                <button type="button"
                                                    class="px-3 btn btn-light btn-sm rounded-pill ms-1 text-danger"
                                                    style="font-size: 10px;"
                                                    onclick="toggleEditForm({{ $reply->id }})">Batal</button>
                                            </div>

                                            @if($reply->image_path)
                                            <div class="form-check mt-1 ms-2">
                                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove-image-{{ $reply->id }}">
                                                <label class="form-check-label text-danger" for="remove-image-{{ $reply->id }}" style="font-size: 10px;">
                                                    Hapus gambar lama
                                                </label>
                                            </div>
                                            @endif

                                            <div class="gap-2 mt-2 d-none align-items-center comment-image-preview">
                                                <img src="" alt="Preview foto komentar" class="rounded object-fit-cover"
                                                    style="width: 64px; height: 64px;">
                                                <button type="button"
                                                    class="p-0 btn btn-link btn-sm text-danger comment-image-remove">Hapus foto</button>
                                            </div>
                                        </form>
                                        @endif

                                    </div>
                                </div>

                                <!-- Form Balas Khusus untuk Reply ini -->
                                <form id="reply-form-{{ $reply->id }}" class="mt-2 ms-3 d-none"
                                    onsubmit="submitReply(event, {{ $post->id }}, {{ $reply->id }})">
                                    @csrf
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="content"
                                            id="reply-input-{{ $reply->id }}"
                                            class="px-3 border-0 form-control rounded-pill bg-light"
                                            placeholder="Balas {{ $reply->user->name }}..."
                                            style="font-size: 11px;">
                                        <button type="button"
                                            class="p-0 border btn btn-light d-inline-flex align-items-center justify-content-center text-secondary comment-image-trigger"
                                            title="Tambah foto" aria-label="Tambah foto komentar">
                                            <i class="bi bi-image" aria-hidden="true"></i>
                                        </button>
                                        <input type="file" name="image"
                                            class="d-none comment-image-input"
                                            accept="image/jpeg,image/png,image/gif"
                                            aria-label="Pilih foto komentar">
                                        <button type="submit"
                                            class="px-3 btn btn-dark btn-sm rounded-pill ms-1"
                                            style="font-size: 10px;">Kirim</button>
                                    </div>
                                    <input type="hidden" name="share_location" value="1"
                                        disabled>
                                    <input type="hidden" name="location" value="">
                                    <div
                                        class="gap-2 mt-2 d-none align-items-center comment-image-preview">
                                        <img src="" alt="Preview foto komentar"
                                            class="rounded object-fit-cover"
                                            style="width: 64px; height: 64px;">
                                        <button type="button"
                                            class="p-0 btn btn-link btn-sm text-danger comment-image-remove">Hapus
                                            foto</button>
                                    </div>
                                </form>

                                <div class="mt-2 ms-3 ps-2 border-start"
                                    id="replies-list-{{ $reply->id }}">
                                    <x-comment-replies :comments="$reply->replies" :post="$post" />
                                </div>
                                @endforeach
                                @endif
                            </div>

                            <!-- Form Balas Komentar Utama -->
                            <form id="reply-form-{{ $comment->id }}" class="mt-2 ms-4 d-none"
                                onsubmit="submitReply(event, {{ $post->id }}, {{ $comment->id }})">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                        name="content" id="reply-input-{{ $comment->id }}"
                                        class="px-3 border-0 form-control rounded-pill bg-light"
                                        placeholder="Balas {{ $comment->user->name }}..."
                                        style="font-size: 12px;">
                                    <button type="button"
                                        class="p-0 border btn btn-light d-inline-flex align-items-center justify-content-center text-secondary comment-image-trigger"
                                        title="Tambah foto" aria-label="Tambah foto komentar">
                                        <i class="bi bi-image" aria-hidden="true"></i>
                                    </button>
                                    <input type="file" name="image" class="d-none comment-image-input"
                                        accept="image/jpeg,image/png,image/gif"
                                        aria-label="Pilih foto komentar">
                                    <button type="submit" class="px-3 btn btn-dark btn-sm rounded-pill ms-1 "
                                        style="font-size: 11px;">Kirim</button>
                                </div>
                                <input type="hidden" name="share_location" value="1" disabled>
                                <input type="hidden" name="location" value="">
                                <div class="gap-2 mt-2 d-none align-items-center comment-image-preview">
                                    <img src="" alt="Preview foto komentar" class="rounded object-fit-cover"
                                        style="width: 64px; height: 64px;">
                                    <button type="button"
                                        class="p-0 btn btn-link btn-sm text-danger comment-image-remove">Hapus
                                        foto</button>
                                </div>
                            </form>

                        </div>
                        @endforeach
                    </div>

                    <!-- 2. FORM KOMENTAR UTAMA -->
                    <form onsubmit="submitComment(event, {{ $post->id }})" class="mt-3"
                        id="comment-form-{{ $post->id }}">
                        @csrf
                        <div class="gap-2 d-flex">
                            <input type="text" name="content" id="comment-input-{{ $post->id }}"
                                class="px-3 border-0 form-control form-control-sm rounded-pill bg-light"
                                placeholder="Balas postingan...">
                            <button type="button"
                                class="p-0 border btn btn-light d-inline-flex align-items-center justify-content-center text-secondary comment-image-trigger"
                                title="Tambah foto" aria-label="Tambah foto komentar">
                                <i class="bi bi-image" aria-hidden="true"></i>
                            </button>
                            <input type="file" name="image" class="d-none comment-image-input"
                                accept="image/jpeg,image/png,image/gif" aria-label="Pilih foto komentar">
                            <button type="submit" class="px-3 btn btn-dark btn-sm rounded-pill"
                                style="font-size: 12px;">Kirim</button>
                        </div>
                        <input type="hidden" name="share_location" value="1" disabled>
                        <input type="hidden" name="location" value="">
                        <div class="gap-2 mt-2 d-none align-items-center comment-image-preview">
                            <img src="" alt="Preview foto komentar" class="rounded object-fit-cover"
                                style="width: 64px; height: 64px;">
                            <button type="button"
                                class="p-0 btn btn-link btn-sm text-danger comment-image-remove">Hapus
                                foto</button>
                        </div>
                    </form>

                </div>

            </div>
            @empty
            <div class="py-5 text-center bg-white border shadow-sm text-muted rounded-4">
                <i class="opacity-50 bi bi-chat-square-text fs-1 text-secondary"></i>
                <p class="mt-2 mb-0">Belum ada postingan dari siapa pun.</p>
            </div>
            @endforelse
            @endisset
        </div>

    </div>
</div>


@endsection