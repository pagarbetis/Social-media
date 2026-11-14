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

        .notification-menu {
            width: min(22rem, calc(100vw - 24px));
            max-height: 70vh;
            overflow-y: auto;
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
            @php
            $recentNotifications = auth()->user()->notifications()->with('sender')->limit(8)->get();
            $unreadNotificationCount = auth()->user()->notifications()->where('is_read', false)->count();
            @endphp
            <div class="gap-2 d-flex align-items-center">
                <div class="dropdown">
                    <button type="button"
                        class="rounded-circle position-relative btn btn-outline-light btn-sm d-inline-flex align-items-center justify-content-center"
                        aria-label="Notifikasi{{ $unreadNotificationCount ? ', '.$unreadNotificationCount.' belum dibaca' : '' }}"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill" aria-hidden="true"></i>
                        @if ($unreadNotificationCount > 0)
                        <span class="top-0 position-absolute start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                        </span>
                        @endif
                    </button>
                    <div class="p-0 shadow dropdown-menu dropdown-menu-end rounded-3 notification-menu">
                        <div class="px-3 py-2 border-bottom fw-semibold">Notifikasi</div>
                        @forelse ($recentNotifications as $notification)
                        <form action="{{ route('notifications.read', $notification) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="px-3 py-2 text-start dropdown-item text-wrap {{ $notification->is_read ? '' : 'bg-light' }}">
                                <span class="d-block">{{ $notification->message }}</span>
                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                            </button>
                        </form>
                        @empty
                        <p class="px-3 py-3 mb-0 small text-muted">Belum ada notifikasi.</p>
                        @endforelse
                    </div>
                </div>
                <div class="dropdown d-none d-md-block">
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

        function toggleCommentLike(commentId) {
            const button = document.getElementById(`comment-like-button-${commentId}`);
            if (!button || button.disabled) return;

            button.disabled = true;

            fetch(`/comments/${commentId}/like`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Like komentar gagal disimpan.');
                    }

                    return response.json();
                })
                .then(data => {
                    const icon = document.getElementById(`comment-like-icon-${commentId}`);
                    const count = document.getElementById(`comment-like-count-${commentId}`);

                    count.textContent = data.count;
                    button.setAttribute('aria-pressed', String(data.liked));
                    button.setAttribute('aria-label', data.liked ? 'Batal suka komentar' : 'Suka komentar');
                    icon.classList.toggle('bi-heart-fill', data.liked);
                    icon.classList.toggle('text-danger', data.liked);
                    icon.classList.toggle('bi-heart', !data.liked);
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Gagal memperbarui like', error.message, 'error');
                })
                .finally(() => {
                    button.disabled = false;
                });
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

        function getDeviceCity() {
            if (!navigator.geolocation) {
                return Promise.reject(new Error('Browser ini tidak mendukung lokasi perangkat.'));
            }

            return new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(async position => {
                    try {
                        const params = new URLSearchParams({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            localityLanguage: 'id'
                        });
                        const response = await fetch(
                            `https://api.bigdatacloud.net/data/reverse-geocode-client?${params}`);

                        if (!response.ok) {
                            throw new Error('Layanan lokasi gagal mengambil nama kota.');
                        }

                        const data = await response.json();
                        const city = [data.city, data.locality].find(value =>
                            typeof value === 'string' && value.trim());

                        if (!city) {
                            throw new Error('Nama kota tidak tersedia untuk lokasi ini.');
                        }

                        resolve(city.trim());
                    } catch (error) {
                        reject(error);
                    }
                }, error => {
                    const messages = {
                        1: 'Izin lokasi ditolak. Izinkan akses lokasi lalu coba lagi.',
                        2: 'Lokasi perangkat tidak tersedia.',
                        3: 'Permintaan lokasi terlalu lama. Coba lagi.'
                    };

                    reject(new Error(messages[error.code] || 'Lokasi perangkat gagal diambil.'));
                }, {
                    enableHighAccuracy: false,
                    timeout: 10000,
                    maximumAge: 300000
                });
            });
        }

        let deviceCity = null;
        const hasPostForms = document.querySelector('#post-form, form[id^="comment-form-"]');
        const deviceLocationReady = hasPostForms ? getDeviceCity().then(city => {
            deviceCity = city;
            const status = document.getElementById('location-sharing-status');
            // if (status) status.textContent = `Lokasi aktif: ${city}. Kota akan tampil publik pada post dan komentar.`;

            return city;
        }).catch(() => {
            const status = document.getElementById('location-sharing-status');
            // if (status) status.textContent = 'Lokasi tidak dibagikan. Post dan komentar tetap bisa dikirim tanpa lokasi.';

            return null;
        }) : Promise.resolve(null);

        function setFormLocation(form, city) {
            const locationInput = form.querySelector('input[name="location"]');
            const shareLocationInput = form.querySelector('input[name="share_location"]');

            locationInput.value = city || '';
            shareLocationInput.disabled = !city;
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

            return `<img src="${src}" alt="Foto komentar" class="d-block mt-1 mb-2 img-fluid rounded-3" style="max-height: 200px; object-fit: contain;">`;
        }

        function commentImageControlHtml() {
            return `
                <button type="button"
                    class="p-0 border btn btn-light d-inline-flex align-items-center justify-content-center text-secondary comment-image-trigger"
                    title="Tambah foto" aria-label="Tambah foto komentar">
                    <i class="bi bi-image" aria-hidden="true"></i>
                </button>
                <input type="file" name="image" class="d-none comment-image-input"
                    accept="image/jpeg,image/png,image/gif" aria-label="Pilih foto komentar">`;
        }

        function commentLocationControlHtml() {
            return `
                <input type="hidden" name="share_location" value="1" disabled>
                <input type="hidden" name="location" value="${escapeHtml(deviceCity || '')}">`;
        }

        function commentLocationHtml(location) {
            if (!location) return '';

            return `<span class="text-muted comment-location-badge" style="font-size: 10px;">
                <i class="bi bi-geo-alt" aria-hidden="true"></i> ${escapeHtml(location)}
            </span>`;
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

        function commentLikeHtml(commentId, liked = false, count = 0, fontSize = 11) {
            const iconClass = liked ? 'bi-heart-fill text-danger' : 'bi-heart';
            const label = liked ? 'Batal suka komentar' : 'Suka komentar';

            return `<button type="button" id="comment-like-button-${commentId}"
                class="gap-1 p-0 btn btn-link btn-sm text-muted text-decoration-none d-inline-flex align-items-center ms-auto"
                style="font-size: ${fontSize}px;" title="${label}" aria-label="${label}" aria-pressed="${liked}"
                onclick="toggleCommentLike(${commentId})">
                <i class="bi ${iconClass}" id="comment-like-icon-${commentId}" aria-hidden="true"></i>
                <span id="comment-like-count-${commentId}">${count}</span>
            </button>`;
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
                    ${commentLocationControlHtml()}
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
                            <span class="d-block fw-bold text-dark">${userName}</span>
                            ${commentBody}
                            ${commentImage}
                            <div class="gap-2 d-flex align-items-center flex-wrap">
                                ${commentLocationHtml(comment.location)}
                                <span class="text-muted" style="font-size: 10px;">Baru saja</span>
                                <button class="p-0 btn btn-link btn-sm text-muted text-decoration-none"
                                    style="font-size: 11px;" onclick="toggleReplyForm(${comment.id})">Balas</button>
                                ${commentLikeHtml(comment.id)}
                                <button type="button" class="p-0 border-0 btn btn-link text-danger text-decoration-none"
                                    style="font-size: 11px; z-index: 10; position: relative;" onclick="confirmDeleteComment('/comments/${comment.id}', 'comment-container-${comment.id}')">
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
        async function submitComment(event, postId) {
            event.preventDefault();

            const form = event.currentTarget;
            if (form.dataset.submitting) return;

            const input = form.querySelector('input[name="content"]');
            const content = input.value.trim();
            const token = form.querySelector('input[name="_token"]').value;
            const imageSelected = form.querySelector('input[name="image"]').files.length > 0;

            if (!content && !imageSelected) {
                Swal.fire('Komentar kosong', 'Isi teks atau pilih foto.', 'info');
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            const submitLabel = submitButton.textContent;
            form.dataset.submitting = 'true';
            submitButton.disabled = true;
            submitButton.textContent = 'Mengirim...';
            input.value = content;

            try {
                const city = await deviceLocationReady;
                setFormLocation(form, city);
                const response = await fetch(`/posts/${postId}/comments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                });
                const data = await parseCommentResponse(response);

                if (!data.success) {
                    throw new Error('Komentar gagal disimpan.');
                }

                document.getElementById(`comment-list-${postId}`).insertAdjacentHTML(
                    'beforeend',
                    newCommentHtml(data.comment, postId, token)
                );
                form.reset();
                clearCommentImage(form);
                changeCommentCount(postId, 1);
            } catch (error) {
                Swal.fire('Gagal mengirim', error.message, 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = submitLabel;
                delete form.dataset.submitting;
            }
        }

        // 4. Fungsi AJAX Kirim Balasan Komentar (Reply)
        async function submitReply(event, postId, parentId) {
            event.preventDefault();

            const form = event.currentTarget;
            if (form.dataset.submitting) return;

            const input = form.querySelector('input[name="content"]');
            const content = input.value.trim();
            const token = form.querySelector('input[name="_token"]').value;
            const imageSelected = form.querySelector('input[name="image"]').files.length > 0;

            if (!content && !imageSelected) {
                Swal.fire('Balasan kosong', 'Isi teks atau pilih foto.', 'info');
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            const submitLabel = submitButton.textContent;
            form.dataset.submitting = 'true';
            submitButton.disabled = true;
            submitButton.textContent = 'Mengirim...';
            input.value = content;

            try {
                const city = await deviceLocationReady;
                setFormLocation(form, city);
                const formData = new FormData(form);
                formData.set('parent_id', parentId);

                const response = await fetch(`/posts/${postId}/comments`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await parseCommentResponse(response);

                if (!data.success) {
                    throw new Error('Balasan gagal disimpan.');
                }

                form.reset();
                clearCommentImage(form);
                form.classList.add('d-none');
                const userName = escapeHtml(data.comment.user.name);
                const commentBody = data.comment.body ?
                    `<p class="mb-1 text-dark">${escapeHtml(data.comment.body)}</p>` : '';
                const commentImage = commentImageHtml(data.comment.image_path);
                const commentLocation = commentLocationHtml(data.comment.location);
                const replyForm = `
                    <form id="reply-form-${data.comment.id}" class="mt-2 ms-3 d-none"
                        onsubmit="submitReply(event, ${postId}, ${data.comment.id})">
                        <input type="hidden" name="_token" value="${token}">
                        <div class="input-group input-group-sm">
                            <input type="text" name="content" id="reply-input-${data.comment.id}"
                                class="px-3 border-0 form-control rounded-pill bg-light"
                                placeholder="Balas ${userName}..." style="font-size: 11px;">
                            ${commentImageControlHtml()}
                            <button type="submit" class="px-3 btn btn-dark btn-sm rounded-pill ms-1"
                                style="font-size: 10px;">Kirim</button>
                        </div>
                        ${commentLocationControlHtml()}
                        <div class="gap-2 mt-2 d-none align-items-center comment-image-preview">
                            <img src="" alt="Preview foto komentar" class="rounded object-fit-cover"
                                style="width: 64px; height: 64px;">
                            <button type="button"
                                class="p-0 btn btn-link btn-sm text-danger comment-image-remove">Hapus foto</button>
                        </div>
                    </form>`;

                const newReplyHTML = `
                    <div class="mb-2" id="comment-container-${data.comment.id}" data-post-id="${postId}">
                        <div class="gap-2 d-flex align-items-start">
                            <div class="text-white rounded-circle bg-secondary d-flex align-items-center justify-content-center fw-bold"
                                style="width: 24px; height: 24px; font-size: 10px;">
                                ${userName.charAt(0).toUpperCase()}
                            </div>
                            <div class="p-2 bg-light rounded-3 flex-grow-1" style="font-size: 12px;">
                                <span class="d-block fw-bold text-dark">${userName}</span>
                                ${commentBody}
                                ${commentImage}
                                <div class="gap-2 d-flex align-items-center flex-wrap">
                                    <span class="text-muted" style="font-size: 10px;">Baru saja</span>
                                    ${commentLocation}
                                    <button type="button" class="p-0 btn btn-link btn-sm text-muted text-decoration-none"
                                        style="font-size: 10px;" onclick="toggleReplyForm(${data.comment.id})">Balas</button>
                                    ${commentLikeHtml(data.comment.id, false, 0, 10)}
                                    <button type="button" class="p-0 border-0 btn btn-link text-danger text-decoration-none"
                                        style="font-size: 10px; z-index: 10; position: relative;"
                                        onclick="confirmDeleteComment('/comments/${data.comment.id}', 'comment-container-${data.comment.id}')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 ms-3 ps-2 border-start" id="replies-list-${data.comment.id}"></div>
                        ${replyForm}
                    </div>
                `;

                const repliesList = document.getElementById(`replies-list-${parentId}`);
                if (!repliesList) {
                    throw new Error('Daftar balasan tidak ditemukan.');
                }

                repliesList.insertAdjacentHTML('beforeend', newReplyHTML);
                changeCommentCount(postId, 1);
            } catch (error) {
                Swal.fire('Gagal mengirim', error.message, 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = submitLabel;
                delete form.dataset.submitting;
            }
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

        function updateCount(textarea) {
            const maxLength = 500;
            const currentLength = textarea.value.length;

            document.getElementById('char-count').innerText = `${currentLength} / ${maxLength}`;

            const warning = document.getElementById('warning-text');
            if (currentLength >= maxLength) {
                warning.style.display = 'inline';
            } else {
                warning.style.display = 'none';
            }
        }

        // Fungsi untuk memunculkan / menyembunyikan form edit
        function toggleEditForm(commentId) {
            const form = document.getElementById(`edit-form-${commentId}`);
            if (form) {
                form.classList.toggle('d-none');
            }
        }

        // Fungsi AJAX untuk submit update komentar/balasan
        function submitEditComment(event, commentId) {
            event.preventDefault();
            const form = document.getElementById(`edit-form-${commentId}`);
            const formData = new FormData(form);

            fetch(`/comments/${commentId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 1. Update teks komentar
                        const bodyElement = document.getElementById(`comment-body-${commentId}`);
                        if (bodyElement) {
                            bodyElement.innerText = data.comment.body;
                        }

                        // 2. Update gambar komentar
                        let imageContainer = document.getElementById(`comment-image-container-${commentId}`);
                        if (data.comment.image_path) {
                            const imageUrl = `/storage/${data.comment.image_path}`;
                            if (imageContainer) {
                                imageContainer.innerHTML = `<img src="${imageUrl}" alt="Foto komentar" class="mt-1 mb-2 img-fluid rounded-3" style="max-height: 200px; object-fit: contain;">`;
                            } else {
                                const textEl = document.getElementById(`comment-body-${commentId}`);
                                const newImgDiv = document.createElement('div');
                                newImgDiv.id = `comment-image-container-${commentId}`;
                                newImgDiv.innerHTML = `<img src="${imageUrl}" alt="Foto komentar" class="mt-1 mb-2 img-fluid rounded-3" style="max-height: 200px; object-fit: contain;">`;
                                textEl.insertAdjacentElement('afterend', newImgDiv);
                            }
                        } else {
                            if (imageContainer) {
                                imageContainer.remove();
                            }
                        }

                        // 3. TAMPILKAN KEMBALI LOKASI JIKA ADA
                        if (data.comment.location) {
                            const commentContainer = document.getElementById(`comment-container-${commentId}`);
                            if (commentContainer) {
                                // Cari apakah elemen lokasi sudah ada sebelumnya
                                let locationSpan = commentContainer.querySelector('.comment-location-badge');

                                if (!locationSpan) {
                                    // Jika belum ada, cari elemen tanggal/waktu di dalam container ini untuk dijadikan patokan posisi
                                    // Asumsi elemen waktu dibungkus text-muted dengan format tanggal
                                    const timeElements = commentContainer.querySelectorAll('.text-muted');
                                    let targetElement = null;

                                    timeElements.forEach(el => {
                                        // Cari elemen yang keliatannya nampilin tanggal (bukan tombol balas/like)
                                        if (el.textContent.includes('2026') || el.textContent.includes('AM') || el.textContent.includes('PM') || el.textContent.includes(':')) {
                                            targetElement = el;
                                        }
                                    });

                                    if (targetElement) {
                                        const newLoc = document.createElement('span');
                                        newLoc.className = 'text-muted comment-location-badge me-2';
                                        newLoc.style.fontSize = '10px';
                                        newLoc.innerHTML = `<i class="bi bi-geo-alt" aria-hidden="true"></i> ${data.comment.location}`;
                                        targetElement.before(newLoc);
                                    }
                                } else {
                                    locationSpan.innerHTML = `<i class="bi bi-geo-alt" aria-hidden="true"></i> ${data.comment.location}`;
                                }
                            }
                        }

                        // Tutup form edit
                        toggleEditForm(commentId);

                        // Reset checkbox hapus gambar
                        const removeCheckbox = form.querySelector('input[name="remove_image"]');
                        if (removeCheckbox) removeCheckbox.checked = false;
                    } else {
                        alert('Gagal memperbarui komentar.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan pada server.');
                });
        }
    </script>

    <!-- Script buat Preview Foto & Loading State Tombol Kirim -->
    <script>
        // 1. Preview Gambar
        const postForm = document.getElementById('post-form');

        if (postForm) {
            const imageInput = document.getElementById('post-image');
            const previewContainer = document.getElementById('image-preview-container');
            const previewImage = document.getElementById('image-preview');
            const removeButton = document.getElementById('remove-image');

            imageInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    previewImage.src = URL.createObjectURL(file);
                    previewContainer.classList.remove('d-none');
                }
            });

            removeButton.addEventListener('click', function() {
                imageInput.value = '';
                previewImage.src = '#';
                previewContainer.classList.add('d-none');
            });

            // 2. Loading State saat Form Postingan Disubmit
            const postContent = document.getElementById('postContent');
            const charCounter = document.getElementById('charCounter');
            const warningMessage = document.getElementById('warningMessage');
            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');
            const btnSpinner = document.getElementById('btn-spinner');

            function checkLength(textarea) {
                const length = textarea.value.length;
                charCounter.textContent = `${length}/500`;
                warningMessage.style.display = length > 500 ? 'inline' : 'none';
            }

            checkLength(postContent);

            postForm.addEventListener('submit', async function(event) {
                if (postContent.value.length > 500) {
                    event.preventDefault();
                    checkLength(postContent);
                    return;
                }

                event.preventDefault();
                if (postForm.dataset.submitting) return;

                postForm.dataset.submitting = 'true';
                submitBtn.disabled = true;
                btnText.textContent = 'Menyiapkan lokasi...';
                btnSpinner.classList.remove('d-none');

                try {
                    setFormLocation(postForm, await deviceLocationReady);
                    btnText.textContent = 'Mengirim...';
                    postForm.submit();
                } catch (error) {
                    delete postForm.dataset.submitting;
                    submitBtn.disabled = false;
                    btnText.textContent = 'Kirim';
                    btnSpinner.classList.add('d-none');
                    Swal.fire('Gagal mengirim', error.message, 'error');
                }
            });
        }
    </script>
</body>

</html>