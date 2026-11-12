@extends('layouts.main')

@section('title', 'Ganti Password - ' . $user->name)

@section('content')
    <div class="container py-3 d-flex justify-content-center">
        <div style="width: 100%; max-width: 500px;">
            <div class="p-4 overflow-hidden bg-white border shadow-sm rounded-4">
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

                    <div class="gap-2 pt-2 d-flex justify-content-end border-top">
                        <a href="{{ route('profile.show', auth()->id()) }}"
                            class="px-3 py-1 btn btn-light rounded-pill btn-sm fw-semibold">Batal</a>
                        <button type="submit" class="px-3 py-1 btn btn-dark rounded-pill fw-semibold btn-sm">
                            Simpan Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
