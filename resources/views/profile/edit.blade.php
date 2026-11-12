@extends('layouts.main')

@section('title', 'Edit Profil & Password - ' . $user->name)

@section('content')
    <div class="container py-3 d-flex justify-content-center">
        <div style="width: 100%; max-width: 550px;">

            <!-- CARD 1: EDIT PROFIL UTAMA (Foto, Nama, Bio) -->
            <div class="p-4 mb-4 overflow-hidden bg-white border shadow-sm rounded-4">
                <h4 class="mb-4 fw-bold text-dark">Edit Profil</h4>

                <form id="edit-profile-form" action="{{ route('profile.update') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="mb-3 text-center">
                        <div class="mb-2">
                            <div class="position-relative d-inline-block">
                                <img id="avatar-preview" src="{{ $user->avatar ? asset('storage/' . $user->avatar) : '#' }}"
                                    class="rounded-circle shadow-sm {{ $user->avatar ? '' : 'd-none' }}"
                                    style="width: 90px; height: 90px; object-fit: cover;">

                                <div id="avatar-placeholder"
                                    class="mx-auto text-white shadow-sm rounded-circle bg-dark d-flex align-items-center justify-content-center fw-bold fs-2 {{ $user->avatar ? 'd-none' : '' }}"
                                    style="width: 90px; height: 90px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            </div>
                        </div>
                        <label class="form-label small fw-bold text-muted">Ganti Foto Profil</label>
                        <input type="file" id="avatar-input" name="avatar"
                            class="form-control form-control-sm rounded-pill" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control rounded-3"
                            value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Bio</label>
                        <textarea name="bio" class="form-control rounded-3" rows="3" maxlength="150"
                            placeholder="Tulis bio singkat tentang dirimu...">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <div class="gap-2 pt-2 d-flex justify-content-end align-items-center border-top">
                        <a href="{{ route('profile.show', $user->id) }}"
                            class="px-3 py-1 btn btn-light rounded-pill btn-sm fw-semibold">Kembali</a>
                        <button type="submit" id="submit-btn"
                            class="px-3 py-1 btn btn-dark rounded-pill fw-semibold btn-sm">
                            <span id="btn-text">Simpan Profil</span>
                            <span id="btn-spinner" class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- CARD 2: GANTI PASSWORD -->
            <div class="p-4 overflow-hidden bg-white border shadow-sm rounded-4 d-block d-md-none">
                <h4 class="mb-4 fw-bold text-dark">Ganti Password</h4>

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control rounded-3" required
                            placeholder="Masukkan password lama">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Password Baru</label>
                        <input type="password" name="password" class="form-control rounded-3" required
                            placeholder="Minimal 8 karakter">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-control rounded-3" required
                            placeholder="Ulangi password baru">
                    </div>

                    <div class="pt-2 d-flex justify-content-end border-top">
                        <button type="submit" class="px-3 py-1 btn btn-dark rounded-pill fw-semibold btn-sm">
                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

    <!-- Script Live Preview Avatar -->
    <script>
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreview = document.getElementById('avatar-preview');
        const avatarPlaceholder = document.getElementById('avatar-placeholder');

        if (avatarInput) {
            avatarInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const objectURL = URL.createObjectURL(file);
                    avatarPreview.src = objectURL;
                    avatarPreview.classList.remove('d-none');
                    if (avatarPlaceholder) {
                        avatarPlaceholder.classList.add('d-none');
                    }
                }
            });
        }

        const editForm = document.getElementById('edit-profile-form');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const btnSpinner = document.getElementById('btn-spinner');

        if (editForm) {
            editForm.addEventListener('submit', function() {
                submitBtn.setAttribute('disabled', 'true');
                btnText.textContent = ' Menyimpan...';
                btnSpinner.classList.remove('d-none');
            });
        }
    </script>
@endsection
