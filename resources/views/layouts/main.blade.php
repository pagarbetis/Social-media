<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    <title>@yield('title', 'Thread Anjay')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <!-- CDN SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/js/app.js'])

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

        .my-compact-toast {
            width: 260px !important;
            min-height: 50px !important;
            padding: 8px 12px !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            display: flex !important;
            align-items: center !important;
        }

        /* Sembunyikan footer/timer bar bawaan kalau bikin berantakan */
        .my-compact-toast .swal2-timer-progress-bar {
            background: rgba(0, 0, 0, 0.2) !important;
        }

        /* Mengatur container isi toast */
        .my-compact-toast .swal2-html-container {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Styling Title / Teks Pesan */
        .my-compact-toast .swal2-title {
            margin: 0 !important;
            font-size: 0.8rem !important;
            text-align: left !important;
            color: #333 !important;
            line-height: 1.3 !important;
            display: block !important;
        }

        /* Styling Ikon agar pas di sebelah kiri */
        .my-compact-toast .swal2-icon {
            width: 22px !important;
            height: 22px !important;
            min-width: 22px !important;
            margin: 0 10px 0 0 !important;
            border-width: 2px !important;
        }

        .my-compact-toast .swal2-icon .swal2-icon-content {
            font-size: 14px !important;
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
                    <button type="button" id="notification-bell"
                        class="rounded-circle position-relative btn btn-outline-light btn-sm d-inline-flex align-items-center justify-content-center"
                        aria-label="Notifikasi{{ $unreadNotificationCount ? ', '.$unreadNotificationCount.' belum dibaca' : '' }}"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill" aria-hidden="true"></i>
                        @if ($unreadNotificationCount > 0)
                        <span id="notification-badge" class="top-0 position-absolute start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                        </span>
                        @endif
                    </button>
                    <div id="notification-dropdown" class="p-0 shadow dropdown-menu dropdown-menu-end rounded-3 notification-menu">
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
                        <p id="notification-empty" class="px-3 py-3 mb-0 small text-muted">Belum ada notifikasi.</p>
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

            if (navbar) {
                if (scrollTop > lastScrollTop && scrollTop > scrollThreshold) {
                    navbar.classList.add('header-hidden');
                } else if (scrollTop < lastScrollTop) {
                    navbar.classList.remove('header-hidden');
                }
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

        // Lokasi diambil secara lazy (saat pertama kali dibutuhkan)
        let deviceCity = null;
        let deviceLocationPromise = null;

        function getDeviceLocationReady() {
            if (!deviceLocationPromise) {
                deviceLocationPromise = getDeviceCity()
                    .then(city => {
                        deviceCity = city;
                        return city;
                    })
                    .catch(error => {
                        console.warn('Lokasi gagal diambil:', error.message);
                        return null;
                    });
            }
            return deviceLocationPromise;
        }

        // Minta izin lebih awal setelah halaman siap
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('#post-form, form[id^="comment-form-"]')) {
                getDeviceLocationReady();
            }
        });

        // Membuat input location/share_location otomatis kalau belum ada di form
        function setFormLocation(form, city) {
            let locationInput = form.querySelector('input[name="location"]');
            let shareLocationInput = form.querySelector('input[name="share_location"]');

            if (!locationInput) {
                locationInput = document.createElement('input');
                locationInput.type = 'hidden';
                locationInput.name = 'location';
                form.appendChild(locationInput);
            }

            if (!shareLocationInput) {
                shareLocationInput = document.createElement('input');
                shareLocationInput.type = 'hidden';
                shareLocationInput.name = 'share_location';
                shareLocationInput.value = '1';
                form.appendChild(shareLocationInput);
            }

            locationInput.value = city || '';
            shareLocationInput.disabled = !city;
        }

        // ==========================================
        // LOGIKA UTAMA: MULTI-IMAGE PREVIEW + SUBMIT FORM
        // ==========================================
        document.addEventListener('DOMContentLoaded', function() {
            const postForm = document.getElementById('post-form');

            if (postForm) {
                const imageInput = document.getElementById('post-image');
                const previewContainer = document.getElementById('image-preview-container');
                let dt = new DataTransfer();

                // 1. Penanganan Multi-Image Akumulatif
                if (imageInput) {
                    imageInput.addEventListener('change', function(event) {
                        for (let i = 0; i < event.target.files.length; i++) {
                            dt.items.add(event.target.files[i]);
                        }
                        imageInput.files = dt.files;
                        previewContainer.innerHTML = '';

                        if (dt.files.length > 0) {
                            previewContainer.classList.remove('d-none');
                            previewContainer.classList.add('d-flex', 'flex-wrap', 'gap-2');

                            Array.from(dt.files).forEach((file, index) => {
                                const reader = new FileReader();

                                reader.onload = function(e) {
                                    const wrapper = document.createElement('div');
                                    wrapper.className = 'position-relative';
                                    wrapper.style.width = '80px';
                                    wrapper.style.height = '80px';

                                    const img = document.createElement('img');
                                    img.src = e.target.result;
                                    img.className = 'border w-100 h-100 object-fit-cover rounded-3 shadow-sm';

                                    // Tombol hapus satuan (X) di tiap thumbnail
                                    const deleteBtn = document.createElement('button');
                                    deleteBtn.type = 'button';
                                    deleteBtn.innerHTML = '&times;';
                                    deleteBtn.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border-0';
                                    deleteBtn.style.cursor = 'pointer';
                                    deleteBtn.style.width = '20px';
                                    deleteBtn.style.height = '20px';
                                    deleteBtn.style.fontSize = '12px';
                                    deleteBtn.style.display = 'flex';
                                    deleteBtn.style.alignItems = 'center';
                                    deleteBtn.style.justifyContent = 'center';

                                    deleteBtn.onclick = function() {
                                        dt.items.remove(index);
                                        imageInput.files = dt.files;
                                        wrapper.remove();

                                        if (dt.files.length === 0) {
                                            previewContainer.classList.add('d-none');
                                        }
                                    };

                                    wrapper.appendChild(img);
                                    wrapper.appendChild(deleteBtn);
                                    previewContainer.appendChild(wrapper);
                                }

                                reader.readAsDataURL(file);
                            });
                        } else {
                            previewContainer.classList.add('d-none');
                        }
                    });
                }

                // 2. Validasi & Submit Form Utama (Beserta Lokasi & Loading State)
                const postContent = document.getElementById('postContent');
                const charCounter = document.getElementById('charCounter');
                const warningMessage = document.getElementById('warningMessage');
                const submitBtn = document.getElementById('submit-btn');
                const btnText = document.getElementById('btn-text');
                const btnSpinner = document.getElementById('btn-spinner');

                function checkLength(textarea) {
                    const length = textarea.value.length;
                    if (charCounter) charCounter.textContent = `${length}/500`;
                    if (warningMessage) warningMessage.style.display = length > 500 ? 'inline' : 'none';
                }

                if (postContent) {
                    checkLength(postContent);
                    postContent.addEventListener('input', function() {
                        checkLength(this);
                    });
                }

                postForm.addEventListener('submit', async function(event) {
                    if (postContent && postContent.value.length > 500) {
                        event.preventDefault();
                        checkLength(postContent);
                        return;
                    }

                    event.preventDefault();
                    if (postForm.dataset.submitting) return;

                    postForm.dataset.submitting = 'true';
                    if (submitBtn) submitBtn.disabled = true;
                    if (btnText) btnText.textContent = 'Menyiapkan lokasi...';
                    if (btnSpinner) btnSpinner.classList.remove('d-none');

                    try {
                        // Ambil dan set lokasi perangkat ke input tersembunyi sebelum dikirim
                        const city = await getDeviceLocationReady();
                        setFormLocation(postForm, city);

                        if (btnText) btnText.textContent = 'Mengirim...';
                        postForm.submit();
                    } catch (error) {
                        console.error(error);
                        delete postForm.dataset.submitting;
                        if (submitBtn) submitBtn.disabled = false;
                        if (btnText) btnText.textContent = 'Kirim';
                        if (btnSpinner) btnSpinner.classList.add('d-none');

                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Gagal mengirim', error.message, 'error');
                        } else {
                            alert('Gagal mengirim: ' + error.message);
                        }
                    }
                });
            }
        });

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

        // Aman kalau form tidak punya elemen preview / input
        function clearCommentImage(form) {
            const input = form.querySelector('.comment-image-input');
            const preview = form.querySelector('.comment-image-preview');
            const previewImage = preview ? preview.querySelector('img') : null;

            if (previewImage && previewImage.dataset.objectUrl) {
                URL.revokeObjectURL(previewImage.dataset.objectUrl);
                delete previewImage.dataset.objectUrl;
            }

            if (input) input.value = '';
            if (previewImage) previewImage.removeAttribute('src');
            if (preview) {
                preview.classList.add('d-none');
                preview.classList.remove('d-flex');
            }
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

        // Form edit untuk komentar buatan JS
        function commentEditFormHtml(comment, token, inputFont, btnFont) {
            const removeImage = comment.image_path ? `
                <div class="mt-1 form-check ms-2">
                    <input class="form-check-input" type="checkbox" name="remove_image" value="1"
                        id="remove-image-${comment.id}">
                    <label class="form-check-label text-danger" for="remove-image-${comment.id}"
                        style="font-size: 10px;">Hapus gambar lama</label>
                </div>` : '';

            return `
                <form id="edit-form-${comment.id}" class="mt-2 edit-comment-form d-none" enctype="multipart/form-data"
                    onsubmit="submitEditComment(event, ${comment.id})">
                    <input type="hidden" name="_token" value="${token}">
                    <div class="input-group input-group-sm">
                        <input type="text" name="body" id="edit-input-${comment.id}"
                            class="px-3 border-0 form-control rounded-pill bg-light"
                            value="${escapeHtml(comment.body || '')}" placeholder="Edit komentar..."
                            style="font-size: ${inputFont}px;">
                        ${commentImageControlHtml()}
                        <button type="submit" class="px-3 btn btn-dark btn-sm rounded-pill ms-1"
                            style="font-size: ${btnFont}px;">Simpan</button>
                        <button type="button" class="px-3 btn btn-light btn-sm rounded-pill ms-1 text-danger"
                            style="font-size: ${btnFont}px;" onclick="toggleEditForm(${comment.id})">Batal</button>
                    </div>
                    ${removeImage}
                    <div class="gap-2 mt-2 d-none align-items-center comment-image-preview">
                        <img src="" alt="Preview foto komentar" class="rounded object-fit-cover"
                            style="width: 64px; height: 64px;">
                        <button type="button"
                            class="p-0 btn btn-link btn-sm text-danger comment-image-remove">Hapus foto</button>
                    </div>
                </form>`;
        }

        // Template komentar utama (isReply=false) dan balasan (isReply=true)
        function buildCommentHtml(comment, postId, token, isReply = false) {
            const userName = escapeHtml(comment.user.name);
            const initial = escapeHtml((comment.user.name || '?').charAt(0).toUpperCase());
            const s = isReply ? {
                wrap: 'mb-2',
                avatar: 24,
                avatarFont: 10,
                bodyFont: 12,
                action: 10,
                margin: 'ms-3',
                formInput: 11,
                formBtn: 10
            } : {
                wrap: 'mb-3',
                avatar: 28,
                avatarFont: 12,
                bodyFont: 13,
                action: 11,
                margin: 'ms-4',
                formInput: 12,
                formBtn: 11
            };

            const commentBody = `<p class="mb-1 text-dark" id="comment-body-${comment.id}">${escapeHtml(comment.body || '')}</p>`;
            const commentImage = comment.image_path ?
                `<div id="comment-image-container-${comment.id}">${commentImageHtml(comment.image_path)}</div>` : '';

            const replyForm = `
                <form id="reply-form-${comment.id}" class="mt-2 ${s.margin} d-none"
                    onsubmit="submitReply(event, ${postId}, ${comment.id})">
                    <input type="hidden" name="_token" value="${token}">
                    <div class="input-group input-group-sm">
                        <input type="text" name="content" id="reply-input-${comment.id}"
                            class="px-3 border-0 form-control rounded-pill bg-light"
                            placeholder="Balas ${userName}..." style="font-size: ${s.formInput}px;">
                        ${commentImageControlHtml()}
                        <button type="submit" class="px-3 btn btn-dark btn-sm rounded-pill ms-1"
                            style="font-size: ${s.formBtn}px;">Kirim</button>
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
                <div class="${s.wrap}" id="comment-container-${comment.id}" data-post-id="${postId}">
                    <div class="gap-2 d-flex align-items-start">
                        <div class="text-white rounded-circle bg-secondary d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                            style="width: ${s.avatar}px; height: ${s.avatar}px; font-size: ${s.avatarFont}px;">${initial}</div>
                        <div class="p-2 bg-light rounded-3 flex-grow-1" style="font-size: ${s.bodyFont}px;">
                            <span class="d-block fw-bold text-dark">${userName}</span>
                            ${commentBody}
                            ${commentImage}
                            <div class="gap-2 d-flex align-items-center flex-wrap">
                                ${commentLocationHtml(comment.location)}
                                <span class="text-muted" style="font-size: 10px;">Baru saja</span>
                                <button type="button" class="p-0 btn btn-link btn-sm text-muted text-decoration-none"
                                    style="font-size: ${s.action}px;" onclick="toggleReplyForm(${comment.id})">Balas</button>
                                ${commentLikeHtml(comment.id, false, 0, s.action)}
                                <button type="button" class="p-0 border-0 btn btn-link text-primary text-decoration-none"
                                    style="font-size: ${s.action}px;" onclick="toggleEditForm(${comment.id})">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button type="button" class="p-0 border-0 btn btn-link text-danger text-decoration-none"
                                    style="font-size: ${s.action}px; z-index: 10; position: relative;"
                                    onclick="confirmDeleteComment('/comments/${comment.id}', 'comment-container-${comment.id}')">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                            ${commentEditFormHtml(comment, token, s.formInput, s.formBtn)}
                        </div>
                    </div>
                    <div class="mt-2 ${s.margin} ps-2 border-start" id="replies-list-${comment.id}"></div>
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

        // Preview foto komentar
        document.addEventListener('change', function(event) {
            if (!event.target.matches('.comment-image-input')) return;
            const file = event.target.files[0];
            const form = event.target.closest('form');
            const preview = form ? form.querySelector('.comment-image-preview') : null;

            if (!file || !preview) return;

            const previewImage = preview.querySelector('img');
            if (!previewImage) return;

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
            const input = button.closest('form').querySelector('.comment-image-input');
            if (input) input.click();
        });

        document.addEventListener('click', function(event) {
            const button = event.target.closest('.comment-image-remove');
            if (!button) return;
            clearCommentImage(button.closest('form'));
        });

        async function submitComment(event, postId) {
            event.preventDefault();
            const form = event.currentTarget;
            if (form.dataset.submitting) return;

            const input = form.querySelector('input[name="content"]');
            const content = input.value.trim();
            const token = form.querySelector('input[name="_token"]').value;
            const imageInput = form.querySelector('input[name="image"]');
            const imageSelected = imageInput && imageInput.files.length > 0;

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
                const city = await getDeviceLocationReady();
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

                const commentList = document.getElementById(`comment-list-${postId}`);
                if (commentList) {
                    commentList.insertAdjacentHTML(
                        'beforeend',
                        buildCommentHtml(data.comment, postId, token, false)
                    );
                } else {
                    console.warn(`Elemen #comment-list-${postId} tidak ditemukan di halaman ini.`);
                }

                form.reset();
                clearCommentImage(form);
                changeCommentCount(postId, 1);
            } catch (error) {
                console.error(error);
                Swal.fire('Gagal mengirim', error.message, 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = submitLabel;
                delete form.dataset.submitting;
            }
        }

        async function submitReply(event, postId, parentId) {
            event.preventDefault();
            const form = event.currentTarget;
            if (form.dataset.submitting) return;

            const input = form.querySelector('input[name="content"]');
            const content = input.value.trim();
            const token = form.querySelector('input[name="_token"]').value;
            const imageInput = form.querySelector('input[name="image"]');
            const imageSelected = imageInput && imageInput.files.length > 0;

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
                const city = await getDeviceLocationReady();
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

                const repliesList = document.getElementById(`replies-list-${parentId}`);
                if (!repliesList) {
                    throw new Error('Daftar balasan tidak ditemukan.');
                }

                repliesList.insertAdjacentHTML(
                    'beforeend',
                    buildCommentHtml(data.comment, postId, token, true)
                );

                form.reset();
                clearCommentImage(form);
                form.classList.add('d-none');
                changeCommentCount(postId, 1);
            } catch (error) {
                console.error(error);
                Swal.fire('Gagal mengirim', error.message, 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = submitLabel;
                delete form.dataset.submitting;
            }
        }

        // ==========================================
        // EDIT KOMENTAR
        // ==========================================
        function toggleEditForm(id) {
            const form = document.getElementById(`edit-form-${id}`);
            if (!form) return;

            const opening = form.classList.contains('d-none');
            form.classList.toggle('d-none');

            if (opening) {
                const input = document.getElementById(`edit-input-${id}`);
                if (input) input.focus();
            } else {
                clearCommentImage(form);
            }
        }

        async function submitEditComment(event, commentId) {
            event.preventDefault();
            const form = event.currentTarget;
            if (form.dataset.submitting) return;

            const input = form.querySelector('input[name="body"]');
            const body = input.value.trim();
            const token = form.querySelector('input[name="_token"]').value;

            if (!body) {
                Swal.fire('Komentar kosong', 'Isi teks komentar.', 'info');
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            const submitLabel = submitButton.textContent;
            form.dataset.submitting = 'true';
            submitButton.disabled = true;
            submitButton.textContent = 'Menyimpan...';

            try {
                const formData = new FormData(form);
                formData.set('body', body);
                formData.set('_method', 'PUT');

                const response = await fetch(`/comments/${commentId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await parseCommentResponse(response);

                if (!data.success) {
                    throw new Error('Komentar gagal diupdate.');
                }

                // Update teks
                const bodyEl = document.getElementById(`comment-body-${commentId}`);
                if (bodyEl) bodyEl.textContent = data.comment.body || '';

                // Update gambar
                const oldImage = document.getElementById(`comment-image-container-${commentId}`);
                if (oldImage) oldImage.remove();

                if (data.comment.image_path && bodyEl) {
                    const wrapper = document.createElement('div');
                    wrapper.id = `comment-image-container-${commentId}`;
                    wrapper.innerHTML = commentImageHtml(data.comment.image_path);
                    bodyEl.insertAdjacentElement('afterend', wrapper);
                }

                // Sinkronkan checkbox "hapus gambar lama"
                const removeCheckbox = form.querySelector('input[name="remove_image"]');
                if (removeCheckbox) {
                    if (data.comment.image_path) {
                        removeCheckbox.checked = false;
                    } else {
                        const holder = removeCheckbox.closest('.form-check');
                        if (holder) holder.remove();
                    }
                }

                clearCommentImage(form);
                form.classList.add('d-none');
            } catch (error) {
                console.error(error);
                Swal.fire('Gagal menyimpan', error.message, 'error');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = submitLabel;
                delete form.dataset.submitting;
            }
        }

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
    </script>

        <!-- Realtime Notifications via Laravel Reverb -->
    <script>
        function initRealtimeNotifications() {
            if (typeof window.Echo === 'undefined') {
                setTimeout(initRealtimeNotifications, 100);
                return;
            }

            var userIdMeta = document.querySelector('meta[name="user-id"]');
            if (!userIdMeta) return;

            var userId = userIdMeta.content;

            window.Echo.private('notifications.' + userId)
                .listen('.notification.created', function(e) {
                    var emptyState = document.getElementById('notification-empty');
                    if (emptyState) emptyState.remove();

                    var dropdown = document.getElementById('notification-dropdown');
                    if (dropdown) {
                        var header = dropdown.querySelector('.fw-semibold');
                        var token = document.querySelector('meta[name="csrf-token"]').content;
                        var formHtml = '<form action="' + e.read_url + '" method="POST">' +
                            '<input type="hidden" name="_token" value="' + token + '">' +
                            '<button type="submit" class="px-3 py-2 text-start dropdown-item text-wrap bg-light">' +
                            '<span class="d-block">' + escapeHtml(e.message) + '</span>' +
                            '<small class="text-muted">Baru saja</small>' +
                            '</button></form>';
                        if (header) {
                            header.insertAdjacentHTML('afterend', formHtml);
                        }
                    }

                    var bell = document.getElementById('notification-bell');
                    if (bell) {
                        var badge = document.getElementById('notification-badge');
                        if (badge) {
                            var current = parseInt(badge.textContent) || 0;
                            current++;
                            badge.textContent = current > 9 ? '9+' : current;
                        } else {
                            var newBadge = document.createElement('span');
                            newBadge.id = 'notification-badge';
                            newBadge.className = 'top-0 position-absolute start-100 translate-middle badge rounded-pill bg-danger';
                            newBadge.textContent = '1';
                            bell.appendChild(newBadge);
                        }
                    }

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: e.message,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'my-compact-toast'
                        }
                    });
                });
        }

        initRealtimeNotifications();

        document.addEventListener('DOMContentLoaded', function() {
            var bellButton = document.getElementById('notification-bell');
            if (!bellButton) return;

            bellButton.addEventListener('click', function() {
                var badge = document.getElementById('notification-badge') ||
                    bellButton.querySelector('.badge');

                if (!badge) return;
                badge.remove();

                // Tandai terbaca di database (butuh route notifications.readAll)
                var tokenMeta = document.querySelector('meta[name="csrf-token"]');
                fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': tokenMeta ? tokenMeta.content : '',
                        'Accept': 'application/json'
                    }
                }).catch(function(error) {
                    console.error('Gagal menandai notifikasi terbaca:', error);
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/gh/mcstudios/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        const lightbox = GLightbox({
            touchNavigation: true,
            loop: true,
        });
    </script>
</body>

</html>