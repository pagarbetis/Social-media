@extends('layouts.main')

@section('title', 'Edit Profil - ' . $user->name)

@section('content')
<!-- Form untuk mengubah data diri dan foto profil user -->
<div class="container d-flex justify-content-center py-5">
    <div style="width: 100%; max-width: 550px;">
        <div class="bg-white p-4 rounded-4 shadow-sm border">
            <h4 class="fw-bold mb-4 text-dark">Edit Profil</h4>
            
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3 text-center">
                    <div class="mb-2">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" class="rounded-circle shadow-sm" style="width: 90px; height: 90px; object-fit: cover;">
                        @else
                            <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center fw-bold fs-2 mx-auto shadow-sm" style="width: 90px; height: 90px;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <label class="form-label small fw-bold text-muted">Ganti Foto Profil</label>
                    <input type="file" name="avatar" class="form-control form-control-sm rounded-pill">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Bio</label>
                    <textarea name="bio" class="form-control rounded-3" rows="3" maxlength="150" placeholder="Tulis bio singkat tentang dirimu...">{{ old('bio', $user->bio) }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('profile.show', $user->id) }}" class="btn btn-light rounded-pill px-4">Batal</a>
                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection