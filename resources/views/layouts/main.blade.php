<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Thread Anjay')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- CDN SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Style CSS untuk Smart Sticky Header -->
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

        body {
            padding-top: 70px;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Navbar Global dengan Kelas smart-header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm smart-header" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}"><i class="bi bi-chat-square-text-fill"></i> Thread Anjay</a>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('profile.show', auth()->id()) }}" class="text-white text-decoration-none small">
                    <i class="bi bi-person-circle"></i> Profil Saya
                </a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light rounded-pill px-3">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Tempat Konten Berubah-ubah -->
    <div class="container my-4">
        @yield('content')
    </div>

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
            }
            else if (scrollTop < lastScrollTop) {
                navbar.classList.remove('header-hidden');
            }

            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        });

        // 2. Fungsi untuk Like tanpa reload
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

        // 3. Fungsi Konfirmasi Hapus pakai SweetAlert2
        function confirmDelete(postId) {
            Swal.fire({
                title: 'Yakin mau hapus?',
                text: "Postingan yang dihapus nggak bisa dikembalikan lagi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#212529',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-form-${postId}`).submit();
                }
            });
        }

        // 4. Fungsi Buka/Tutup Form Balas Komentar
        function toggleReplyForm(commentId) {
            let form = document.getElementById(`reply-form-${commentId}`);
            form.classList.toggle('d-none');
        }

        // 5. Fungsi AJAX Kirim Komentar Utama
        function submitComment(event, postId) {
            event.preventDefault();

            let input = document.getElementById(`comment-input-${postId}`);
            let content = input.value.trim();
            let form = document.getElementById(`comment-form-${postId}`);
            let token = form.querySelector('input[name="_token"]').value;

            if (!content) return;

            fetch(`/posts/${postId}/comments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content: content
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // 6. Fungsi AJAX Kirim Balasan Komentar (Reply)
        function submitReply(event, postId, commentId) {
            event.preventDefault();

            let input = document.getElementById(`reply-input-${commentId}`);
            let content = input.value.trim();
            let form = document.getElementById(`reply-form-${commentId}`);
            let token = form.querySelector('input[name="_token"]').value;

            if (!content) return;

            fetch(`/comments/${commentId}/reply`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content: content,
                        post_id: postId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        input.value = '';
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>

    <!-- Notifikasi SweetAlert untuk Session Success -->
    @if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('
            success ') }}',
            showConfirmButton: false,
            timer: 1500
        });
    </script>
    @endif

    <!-- Script Proteksi Back-Button Cache -->
    <script>
        window.addEventListener("pageshow", function(event) {
            if (event.persisted || (performance.getEntriesByType("navigation")[0] && performance.getEntriesByType("navigation")[0].type === "back_forward")) {
                window.location.reload(true);
            }
        });
    </script>
</body>

</html>