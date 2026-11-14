@props(['comments', 'post'])

@foreach ($comments as $reply)
    <div class="mb-2" id="comment-container-{{ $reply->id }}" data-post-id="{{ $post->id }}">
        <div class="gap-2 d-flex align-items-start">
            <x-user-popover :user="$reply->user" :size="24" />

            <div class="p-2 bg-light rounded-3 flex-grow-1" style="font-size: 12px;">
                <span class="d-block fw-bold text-dark">{{ $reply->user->name }}</span>
                @if ($reply->body)
                    <p class="mb-1 text-dark" id="comment-body-{{ $reply->id }}">{{ $reply->body }}</p>
                @endif
                @if ($reply->image_path)
                    <div id="comment-image-container-{{ $reply->id }}">
                        <img src="{{ asset('storage/' . $reply->image_path) }}" alt="Foto komentar"
                            class="d-block mt-1 mb-2 img-fluid rounded-3" style="max-height: 200px; object-fit: contain;">
                    </div>
                @endif

                <div class="gap-2 d-flex align-items-center flex-wrap">
                    <span class="text-muted" style="font-size: 10px;">
                        {{ $reply->created_at->format('d M Y, H:i') }}
                    </span>
                    @if ($reply->location)
                        <span class="text-muted comment-location-badge" style="font-size: 10px;">
                            <i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $reply->location }}
                        </span>
                    @endif
                    <button type="button" class="p-0 btn btn-link btn-sm text-muted text-decoration-none"
                        style="font-size: 10px;" onclick="toggleReplyForm({{ $reply->id }})">Balas</button>
                    <button type="button" id="comment-like-button-{{ $reply->id }}"
                        class="gap-1 p-0 btn btn-link btn-sm text-muted text-decoration-none d-inline-flex align-items-center ms-auto"
                        style="font-size: 10px;" title="Suka komentar" aria-label="Suka komentar"
                        aria-pressed="{{ $reply->likes->contains('id', auth()->id()) ? 'true' : 'false' }}"
                        onclick="toggleCommentLike({{ $reply->id }})">
                        <i class="bi {{ $reply->likes->contains('id', auth()->id()) ? 'bi-heart-fill text-danger' : 'bi-heart' }}"
                            id="comment-like-icon-{{ $reply->id }}" aria-hidden="true"></i>
                        <span id="comment-like-count-{{ $reply->id }}">{{ $reply->likes->count() }}</span>
                    </button>
                    @if(auth()->id() === $reply->user_id)
                        <button type="button" class="p-0 border-0 btn btn-link text-primary text-decoration-none"
                            style="font-size: 10px;" onclick="toggleEditForm({{ $reply->id }})">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                    @endif
                    @if ($reply->user_id === auth()->id())
                        <button type="button" class="p-0 border-0 btn btn-link text-danger text-decoration-none"
                            style="font-size: 10px; z-index: 10; position: relative;" onclick="confirmDeleteComment(
                                '{{ route('comments.destroy', $reply->id) }}',
                                'comment-container-{{ $reply->id }}'
                            )">
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

        <div class="mt-2 ms-3 ps-2 border-start" id="replies-list-{{ $reply->id }}">
            <x-comment-replies :comments="$reply->replies" :post="$post" />
        </div>

        <form id="reply-form-{{ $reply->id }}" class="mt-2 ms-3 d-none"
            onsubmit="submitReply(event, {{ $post->id }}, {{ $reply->id }})">
            @csrf
            <div class="input-group input-group-sm">
                <input type="text" name="content" id="reply-input-{{ $reply->id }}"
                    class="px-3 border-0 form-control rounded-pill bg-light"
                    placeholder="Balas {{ $reply->user->name }}..." style="font-size: 11px;">
                <button type="button"
                    class="p-0 border btn btn-light d-inline-flex align-items-center justify-content-center text-secondary comment-image-trigger"
                    title="Tambah foto" aria-label="Tambah foto komentar">
                    <i class="bi bi-image" aria-hidden="true"></i>
                </button>
                <input type="file" name="image" class="d-none comment-image-input"
                    accept="image/jpeg,image/png,image/gif" aria-label="Pilih foto komentar">
                <button type="submit" class="px-3 btn btn-dark btn-sm rounded-pill ms-1"
                    style="font-size: 10px;">Kirim</button>
            </div>
            <input type="hidden" name="share_location" value="1" disabled>
            <input type="hidden" name="location" value="">
            <div class="gap-2 mt-2 d-none align-items-center comment-image-preview">
                <img src="" alt="Preview foto komentar" class="rounded object-fit-cover"
                    style="width: 64px; height: 64px;">
                <button type="button"
                    class="p-0 btn btn-link btn-sm text-danger comment-image-remove">Hapus foto</button>
            </div>
        </form>
    </div>
@endforeach
