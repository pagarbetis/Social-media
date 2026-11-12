<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Thread Anjay')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- CDN SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Style CSS untuk Smart Sticky Header & Mobile Bottom Nav -->
    <style>
        .smart-header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1030;
            transition: top 0.3s ease-in-out;
        }

        .header-hidden {
            top: -100px;
        }

        .comment-image-trigger {
            width: 32px;
            min-width: 32px;
            height: 32px;
            flex: 0 0 32px;
            padding: 0;
            border-radius: 50% !important;
        }

        body {
            background-color: #f8f9fa;
            padding-top: 70px;
            padding-bottom: 60px;
        }

        @media (min-width: 768px) {
            body {
                padding-bottom: 0;
            }
        }

        .user-popover {
            position: relative;
            display: inline-flex;
            flex-shrink: 0;
        }

        .user-popover.is-open {
            z-index: 1085;
        }

        .user-popover-trigger {
            display: inline-flex;
            overflow: hidden;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: transparent;
            cursor: pointer;
        }

        .user-popover-trigger:focus-visible,
        .user-popover-close:focus-visible,
        .user-popover-link:focus-visible {
            outline: 2px solid #212529;
            outline-offset: 3px;
        }

        .user-popover-initial {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #6c757d;
            color: #fff;
            font-weight: 700;
        }

        .user-popover-card {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            display: none;
            z-index: 1085;
            width: 18rem;
            max-width: calc(100vw - 24px);
            padding: 16px;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 6px 20px rgb(33 37 41 / 14%);
            color: #212529;
        }

        .user-popover.is-above .user-popover-card {
            top: auto;
            bottom: calc(100% + 8px);
        }

        .user-popover.is-open .user-popover-card {
            display: block;
        }

        .user-popover-heading {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 10px;
        }

        .user-popover-heading strong {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-popover-card-initial {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            font-size: 15px;
        }

        .user-popover-close {
            display: inline-flex;
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #6c757d;
        }

        .user-popover-bio {
            margin: 12px 0;
            color: #6c757d;
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .user-popover-link {
            display: block;
            padding: 8px 10px;
            border-radius: 6px;
            background: #212529;
            color: #fff;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
        }

        .user-popover-link:hover {
            background: #343a40;
            color: #fff;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Navbar Global (Header Atas) -->
    <nav class="shadow-sm navbar navbar-expand-lg navbar-dark bg-dark smart-header" id="mainNavbar">
        <div class="container d-flex justify-content-between align-items-center position-relative">

            <!-- Spacer kosong di kiri khusus untuk mobile -->
            <div class="d-md-none" style="width: 32px;"></div>

            <!-- Brand / Nama Aplikasi: Center di mobile (mx-auto), Left di desktop (mx-md-0) -->
            <a class="mx-auto text-center navbar-brand fw-bold mx-md-0" href="{{ route('dashboard') }}">
                <i class="bi bi-chat-square-text-fill"></i> Thread Anjay
            </a>

            <!-- Elemen penyeimbang kosong di kanan khusus mobile-->
            <div class="d-md-none" style="width: 32px;"></div>

            <!-- Menu Desktop dengan Dropdown -->
            @auth
            <div class="gap-3 d-none d-md-flex align-items-center">
                <div class="dropdown">
                    <button
                        class="gap-1 px-3 btn btn-outline-light btn-sm rounded-pill dropdown-toggle d-flex align-items-center"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> Akun Saya
                    </button>
                    <ul class="py-2 shadow-sm dropdown-menu dropdown-menu-end rounded-3">
                        <li>
                            <a class="py-2 dropdown-item small" href="{{ route('profile.show', auth()->id()) }}">
                                <i class="bi bi-person me-2"></i> Profil Saya
                            </a>
                        </li>
                        <li>
                            <a class="py-2 dropdown-item small" href="{{ route('password.edit') }}">
                                <i class="bi bi-key me-2"></i> Ganti Password
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form id="logout-form-dropdown" action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="button" onclick="confirmLogout()"
                                    class="py-2 bg-transparent border-0 dropdown-item small text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            @endauth
        </div>
    </nav>

    <!-- 2. Tempat Konten Berubah-ubah -->
    <div class="container my-4">
        @yield('content')
    </div>

    <!-- 3. Bottom Navbar Mobile: Hanya muncul di HP (d-flex d-md-none) -->
    @auth
    <div class="py-2 bg-white shadow-sm fixed-bottom border-top d-flex d-md-none justify-content-around align-items-center"
        style="z-index: 1030;">
        <!-- Tombol Home -->
        <a href="{{ route('dashboard') }}" class="text-dark text-decoration-none d-flex flex-column align-items-center"
            style="font-size: 11px;">
            <i class="bi bi-house-door fs-5"></i>
            <span>Home</span>
        </a>

        <!-- Tombol Profil -->
        <a href="{{ route('profile.show', auth()->id()) }}"
            class="text-dark text-decoration-none d-flex flex-column align-items-center" style="font-size: 11px;">
            <i class="bi bi-person-circle fs-5"></i>
            <span>Profil</span>
        </a>

        <!-- Tombol Logout -->
        <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="m-0 d-inline">
            @csrf
            <button type="button" onclick="confirmLogout()"
                class="p-0 bg-transparent border-0 text-danger d-flex flex-column align-items-center"
                style="font-size: 11px; cursor: pointer;">
                <i class="bi bi-box-arrow-right fs-5"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
    @endauth

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Global JavaScript Logic -->
    <script>
        let lastScrollTop = 0;
        const navbar = document.getElementById('mainNavbar');
        const scrollThreshold = 50;

        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > lastScrollTop && scrollTop > scrollThreshold) {
                navbar.classList.add('header-hidden');
            } else if (scrollTop < lastScrollTop) {
                navbar.classList.remove('header-hidden');
            }

            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        });

        function closeUserPopover(popover) {
            popover.classList.remove('is-open', 'is-above');
            popover.querySelector('[data-user-popover-trigger]').setAttribute('aria-expanded', 'false');
        }

        function openUserPopover(popover) {
            document.querySelectorAll('[data-user-popover].is-open').forEach((openPopover) => {
                if (openPopover !== popover) closeUserPopover(openPopover);
            });

            popover.classList.add('is-open');
            popover.querySelector('[data-user-popover-trigger]').setAttribute('aria-expanded', 'true');

            const trigger = popover.querySelector('[data-user-popover-trigger]');
            const card = popover.querySelector('.user-popover-card');
            const triggerRect = trigger.getBoundingClientRect();
            const popoverRect = popover.getBoundingClientRect();
            const viewportWidth = document.documentElement.clientWidth;
            card.style.maxWidth = `${Math.max(0, viewportWidth - 24)}px`;

            const cardLeft = Math.min(Math.max(12, triggerRect.left), viewportWidth - card.offsetWidth - 12);
            card.style.left = `${cardLeft - popoverRect.left}px`;
            popover.classList.toggle('is-above', triggerRect.bottom + card.offsetHeight > window.innerHeight - 12 &&
                triggerRect.top > card.offsetHeight + 12);
        }

        window.addEventListener('resize', function() {
            document.querySelectorAll('[data-user-popover].is-open').forEach(openUserPopover);
        });

        document.addEventListener('pointerover', function(event) {
            const popover = event.target.closest('[data-user-popover]');
            if (event.pointerType === 'mouse' && popover && !popover.contains(event.relatedTarget)) {
                openUserPopover(popover);
            }
        });

        document.addEventListener('pointerout', function(event) {
            const popover = event.target.closest('[data-user-popover]');
            if (event.pointerType === 'mouse' && popover && !popover.contains(event.relatedTarget) &&
                !popover.contains(document.activeElement)) {
                closeUserPopover(popover);
            }
        });

        let suppressNextUserPopoverFocus = false;
        let userPopoverKeyboardFocus = false;

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Tab') userPopoverKeyboardFocus = true;
        });

        document.addEventListener('pointerdown', function() {
            userPopoverKeyboardFocus = false;
        });

        document.addEventListener('focusin', function(event) {
            if (suppressNextUserPopoverFocus) {
                suppressNextUserPopoverFocus = false;
                return;
            }

            const popover = event.target.closest('[data-user-popover]');
            if (popover && userPopoverKeyboardFocus) openUserPopover(popover);
        });

        document.addEventListener('focusout', function(event) {
            const popover = event.target.closest('[data-user-popover]');
            if (popover && !popover.contains(event.relatedTarget)) closeUserPopover(popover);
        });

        document.addEventListener('click', function(event) {
            const trigger = event.target.closest('[data-user-popover-trigger]');
            if (trigger) {
                const popover = trigger.closest('[data-user-popover]');
                if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                    openUserPopover(popover);
                } else if (event.detail === 0 || !popover.classList.contains('is-open')) {
                    openUserPopover(popover);
                } else {
                    closeUserPopover(popover);
                }
                return;
            }

            const closeButton = event.target.closest('[data-user-popover-close]');
            if (closeButton) {
                closeUserPopover(closeButton.closest('[data-user-popover]'));
                return;
            }

            if (!event.target.closest('[data-user-popover]')) {
                document.querySelectorAll('[data-user-popover].is-open').forEach(closeUserPopover);
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key !== 'Escape') return;

            const popover = document.querySelector('[data-user-popover].is-open');
            if (popover) {
                closeUserPopover(popover);
                const trigger = popover.querySelector('[data-user-popover-trigger]');
                if (!trigger.matches(':focus')) {
                    suppressNextUserPopoverFocus = true;
                    trigger.focus();
                    if (document.activeElement !== trigger) suppressNextUserPopoverFocus = false;
                }
            }
        });

        // 1. Fungsi untuk Like tanpa reload
        function toggleLike(postId) {
            fetch(`/posts/${postId}/like`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    const icon = document.getElementById(`like-icon-${postId}`);
                    const count = document.getElementById(`like-count-${postId}`);

                    count.textContent = data.count;
                    if (data.liked) {
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill', 'text-danger');
                    } else {
                        icon.classList.remove('bi-heart-fill', 'text-danger');
                        icon.classList.add('bi-heart');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // 2. Fungsi Buka/Tutup Form Balas Komentar
        function toggleReplyForm(id) {
            const form = document.getElementById(`reply-form-${id}`);
            if (form) {
                form.classList.toggle('d-none');
            }
        }

        function escapeHtml(value) {
            const element = document.createElement('span');
            element.textContent = value;
            return element.innerHTML;
        }

        function parseCommentResponse(response) {
            return response.json().then(data => {
                if (!response.ok) {
                    const validationMessage = data.errors ? Object.values(data.errors).flat()[0] : null;
                    throw new Error(validationMessage || data.message || 'Komentar gagal disimpan.');
                }

                return data;
            });
        }

        function commentImageHtml(imagePath) {
            if (!imagePath) return '';

            const encodedPath = imagePath.split('/').map(encodeURIComponent).join('/');
            const src = escapeHtml(`/storage/${encodedPath}`);

            return `<img src="${src}" alt="Foto komentar" class="mt-1 mb-2 img-fluid rounded-3" style="max-height: 200px; object-fit: contain;">`;
        }

        function commentImageControlHtml() {
            return `
                <button type="button"
                    class="btn btn-light border p-0 d-inline-flex align-items-center justify-content-center text-secondary comment-image-trigger"
                    title="Tambah foto" aria-label="Tambah foto komentar">
                    <i class="bi bi-image" aria-hidden="true"></i>
                </button>
                <input type="file" name="image" class="d-none comment-image-input"
                    accept="image/jpeg,image/png,image/gif" aria-label="Pilih foto komentar">`;
        }

        function clearCommentImage(form) {
            const input = form.querySelector('.comment-image-input');
            const preview = form.querySelector('.comment-image-preview');
            const previewImage = preview.querySelector('img');

            if (previewImage.dataset.objectUrl) {
                URL.revokeObjectURL(previewImage.dataset.objectUrl);
                delete previewImage.dataset.objectUrl;
            }

            input.value = '';
            previewImage.removeAttribute('src');
            preview.classList.add('d-none');
            preview.classList.remove('d-flex');
        }

        function changeCommentCount(postId, difference) {
            const count = document.getElementById(`comment-count-${postId}`);

            if (count) {
                count.textContent = Math.max(0, Number(count.textContent) + difference);
            }
        }

        function newCommentHtml(comment, postId, token) {
            const initial = escapeHtml((comment.user.name || '?').charAt(0).toUpperCase());
            const userName = escapeHtml(comment.user.name);
            const commentBody = comment.body ? `<p class="mb-1 text-dark">${escapeHtml(comment.body)}</p>` : '';
            const commentImage = commentImageHtml(comment.image_path);
            const replyForm = `
                <form id="reply-form-${comment.id}" class="mt-2 ms-4 d-none"
                    onsubmit="submitReply(event, ${postId}, ${comment.id})">
                    <input type="hidden" name="_token" value="${token}">
                    <div class="input-group input-group-sm">
                        <input type="text" name="content" id="reply-input-${comment.id}"
                            class="px-3 border-0 form-control rounded-pill bg-light"
                            placeholder="Balas ${userName}..." style="font-size: 12px;">
                        ${commentImageControlHtml()}
                        <button type="submit" class="px-3 btn btn-dark btn-sm rounded-pill ms-1"
                            style="font-size: 11px;">Kirim</button>
                    </div>
                    <div class="gap-2 mt-2 d-none align-items-center comment-image-preview">
                        <img src="" alt="Preview foto komentar" class="rounded object-fit-cover"
                            style="width: 64px; height: 64px;">
                        <button type="button"
                            class="p-0 btn btn-link btn-sm text-danger comment-image-remove">Hapus foto</button>
                    </div>
                </form>`;

            return `
                <div class="mb-3" id="comment-container-${comment.id}" data-post-id="${postId}">
                    <div class="gap-2 d-flex align-items-start">
                        <div class="text-white rounded-circle bg-secondary d-flex align-items-center justify-content-center fw-bold"
                            style="width: 28px; height: 28px; font-size: 12px;">${initial}</div>
                        <div class="p-2 bg-light rounded-3 flex-grow-1" style="font-size: 13px;">
                            <span class="fw-bold text-dark">${userName}</span>
                            ${commentBody}
                            ${commentImage}
                            <div class="gap-2 d-flex align-items-center">
                                <span class="text-muted" style="font-size: 10px;">Baru saja</span>
                                <button class="p-0 btn btn-link btn-sm text-muted text-decoration-none"
                                    style="font-size: 11px;" onclick="toggleReplyForm(${comment.id})">Balas</button>
                                <span class="text-muted" style="font-size: 10px;">•</span>
                                <button type="button" class="p-0 border-0 btn btn-link text-danger text-decoration-none"
                                    style="font-size: 11px;" onclick="confirmDeleteComment('/comments/${comment.id}', 'comment-container-${comment.id}')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 ms-4 ps-2 border-start" id="replies-list-${comment.id}"></div>
                    ${replyForm}
                </div>`;
        }

        function removeCommentFromPage(commentContainer) {
            if (!commentContainer) return;

            const postId = commentContainer.dataset.postId;
            const removedCount = 1 + commentContainer.querySelectorAll('[id^="comment-container-"]').length;

            if (postId) {
                changeCommentCount(postId, -removedCount);
            }

            commentContainer.remove();
        }

        document.addEventListener('change', function(event) {
            if (!event.target.matches('.comment-image-input')) return;

            const file = event.target.files[0];
            const form = event.target.closest('form');
            const preview = form.querySelector('.comment-image-preview');
            const previewImage = preview.querySelector('img');

            if (!file) return;

            if (previewImage.dataset.objectUrl) {
                URL.revokeObjectURL(previewImage.dataset.objectUrl);
            }

            previewImage.src = URL.createObjectURL(file);
            previewImage.dataset.objectUrl = previewImage.src;
            preview.classList.remove('d-none');
            preview.classList.add('d-flex');
        });

        document.addEventListener('click', function(event) {
            const button = event.target.closest('.comment-image-trigger');
            if (!button) return;

            button.closest('form').querySelector('.comment-image-input').click();
        });

        document.addEventListener('click', function(event) {
            const button = event.target.closest('.comment-image-remove');
            if (!button) return;

            clearCommentImage(button.closest('form'));
        });

        // 3. Fungsi AJAX Kirim Komentar Utama
        function submitComment(event, postId) {
            event.preventDefault();

            const form = event.currentTarget;
            const input = form.querySelector('input[name="content"]');
            const content = input.value.trim();
            const token = form.querySelector('input[name="_token"]').value;
            const imageSelected = form.querySelector('input[name="image"]').files.length > 0;

            if (!content && !imageSelected) {
                Swal.fire('Komentar kosong', 'Isi teks atau pilih foto.', 'info');
                return;
            }

            input.value = content;

            fetch(`/posts/${postId}/comments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                })
                .then(parseCommentResponse)
                .then(data => {
                    if (data.success) {
                        document.getElementById(`comment-list-${postId}`).insertAdjacentHTML(
                            'beforeend',
                            newCommentHtml(data.comment, postId, token)
                        );
                        form.reset();
                        clearCommentImage(form);
                        changeCommentCount(postId, 1);
                    }
                })
                .catch(error => Swal.fire('Gagal mengirim', error.message, 'error'));
        }

        // 4. Fungsi AJAX Kirim Balasan Komentar (Reply)
        function submitReply(event, postId, parentId) {
            event.preventDefault();

            const form = event.currentTarget;
            const input = form.querySelector('input[name="content"]');
            const content = input.value.trim();
            const token = form.querySelector('input[name="_token"]').value;
            const imageSelected = form.querySelector('input[name="image"]').files.length > 0;

            if (!content && !imageSelected) {
                Swal.fire('Balasan kosong', 'Isi teks atau pilih foto.', 'info');
                return;
            }

            input.value = content;
            const formData = new FormData(form);
            formData.set('parent_id', parentId);

            fetch(`/posts/${postId}/comments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(parseCommentResponse)
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        clearCommentImage(form);
                        form.classList.add('d-none');
                        const userName = escapeHtml(data.comment.user.name);
                        const commentBody = data.comment.body ?
                            `<p class="mb-1 text-dark">${escapeHtml(data.comment.body)}</p>` : '';
                        const commentImage = commentImageHtml(data.comment.image_path);

                        const newReplyHTML = `
                    <div class="gap-2 mb-2 d-flex align-items-start" id="comment-container-${data.comment.id}" data-post-id="${postId}">
                        <div class="text-white rounded-circle bg-secondary d-flex align-items-center justify-content-center fw-bold"
                            style="width: 24px; height: 24px; font-size: 10px;">
                            ${userName.charAt(0).toUpperCase()}
                        </div>
                        <div class="p-2 bg-light rounded-3 flex-grow-1" style="font-size: 12px;">
                            <span class="fw-bold text-dark">${userName}</span>
                            ${commentBody}
                            ${commentImage}
                            <div class="gap-2 d-flex align-items-center">
                                <button class="p-0 btn btn-link btn-sm text-muted text-decoration-none"
                                    style="font-size: 10px;"
                                    onclick="toggleReplyForm(${data.comment.id})">Balas</button>
                                <span class="text-muted" style="font-size: 10px;">•</span>
                                <button type="button" class="p-0 border-0 btn btn-link text-danger text-decoration-none"
                                    style="font-size: 10px; z-index: 10; position: relative;"
                                    onclick="confirmDeleteComment('/comments/${data.comment.id}', 'comment-container-${data.comment.id}')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                        form.insertAdjacentHTML('beforebegin', newReplyHTML);
                        changeCommentCount(postId, 1);
                    }
                })
                .catch(error => Swal.fire('Gagal mengirim', error.message, 'error'));
        }

        // 5. Fungsi Konfirmasi Hapus Komentar / Balasan pakai SweetAlert2 (AJAX)
        function confirmDeleteComment(deleteUrl, containerId) {
            const token = document.querySelector('meta[name="csrf-token"]') ?
                document.querySelector('meta[name="csrf-token"]').content :
                document.querySelector('input[name="_token"]').value;

            const commentContainer = document.getElementById(containerId);

            Swal.fire({
                title: 'Yakin mau hapus?',
                text: "Komentar yang dihapus gak bisa dikembalikan lagi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#212529',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(deleteUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                _method: 'DELETE'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                removeCommentFromPage(commentContainer);
                            } else {
                                Swal.fire('Gagal!', data.message || 'Gagal menghapus komentar.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Terjadi kesalahan pada server.', 'error');
                        });
                }
            });
        }

        // 6. Fungsi Konfirmasi Logout pakai SweetAlert2
        function confirmLogout() {
            Swal.fire({
                title: 'Mau keluar aplikasi?',
                text: "Sesi kamu bakal diakhiri, bro!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#212529',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    let desktopForm = document.getElementById('logout-form');
                    let mobileForm = document.getElementById('logout-form-mobile');

                    if (desktopForm && window.innerWidth >= 768) {
                        desktopForm.submit();
                    } else if (mobileForm) {
                        mobileForm.submit();
                    } else {
                        desktopForm.submit();
                    }
                }
            });
        }

        // Global Event Delegation untuk tombol hapus dengan class 'delete-btn'
        document.addEventListener('click', function(event) {
            const button = event.target.closest('.delete-btn');
            if (!button) return;

            event.stopPropagation();
            confirmDeleteComment(button.dataset.url, button.dataset.container);
        });

        function confirmDelete(postId) {
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: 'Postingan yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-form-${postId}`).submit();
                }
            });
        }
        @if(session('success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
        @endif
    </script>
</body>

</html>