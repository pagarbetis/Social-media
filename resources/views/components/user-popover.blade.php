@props(['user', 'size' => 38])

<div class="user-popover" data-user-popover>
    <button type="button" class="user-popover-trigger" data-user-popover-trigger
        aria-label="Lihat profil {{ $user->name }}" aria-haspopup="dialog" aria-expanded="false"
        style="width: {{ $size }}px; height: {{ $size }}px;">
        @if ($user->avatar)
            <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="rounded-circle"
                style="width: 100%; height: 100%; object-fit: cover;">
        @else
            <span class="user-popover-initial"
                style="width: 100%; height: 100%; font-size: {{ max(10, (int) ($size * 0.3)) }}px;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </span>
        @endif
    </button>

    <div class="user-popover-card" role="dialog" aria-label="Profil {{ $user->name }}">
        <div class="user-popover-heading">
            @if ($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" alt="" class="rounded-circle"
                    style="width: 44px; height: 44px; object-fit: cover;">
            @else
                <span class="user-popover-initial user-popover-card-initial">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
            @endif
            <strong class="text-truncate">{{ $user->name }}</strong>
            <button type="button" class="user-popover-close" data-user-popover-close
                aria-label="Tutup kartu profil">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>

        <p class="user-popover-bio">
            {{ $user->bio ?: 'Belum ada bio.' }}
        </p>

        <a href="{{ route('profile.show', $user->id) }}" class="user-popover-link">
            Lihat profil
        </a>
    </div>
</div>
