@props(['post'])

@php
$images = $post->images;
$count = $images->count();
$gallery = 'post-' . $post->id;
@endphp

@if ($count > 0)
<div class="mb-3">
    @if ($count === 1)
    <a href="{{ asset('storage/' . $images->first()->image_path) }}" class="glightbox" data-gallery="{{ $gallery }}">
        <img src="{{ asset('storage/' . $images->first()->image_path) }}"
            class="shadow-sm img-fluid rounded-4 w-100"
            style="max-height: 450px; object-fit: cover;">
    </a>
    @elseif ($count === 2)
    <div class="d-flex gap-2">
        @foreach ($images as $image)
        <a href="{{ asset('storage/' . $image->image_path) }}" class="glightbox flex-fill" data-gallery="{{ $gallery }}">
            <img src="{{ asset('storage/' . $image->image_path) }}"
                class="shadow-sm img-fluid rounded-4 w-100"
                style="height: 300px; object-fit: cover;">
        </a>
        @endforeach
    </div>
    @elseif ($count === 3)
    <a href="{{ asset('storage/' . $images->first()->image_path) }}" class="glightbox d-block" data-gallery="{{ $gallery }}">
        <img src="{{ asset('storage/' . $images->first()->image_path) }}"
            class="shadow-sm img-fluid rounded-4 w-100 mb-2"
            style="max-height: 350px; object-fit: cover;">
    </a>
    <div class="d-flex gap-2">
        @foreach ($images->skip(1) as $image)
        <a href="{{ asset('storage/' . $image->image_path) }}" class="glightbox flex-fill" data-gallery="{{ $gallery }}">
            <img src="{{ asset('storage/' . $image->image_path) }}"
                class="shadow-sm img-fluid rounded-4 w-100"
                style="height: 220px; object-fit: cover;">
        </a>
        @endforeach
    </div>
    @else
    <div class="row g-2">
        @foreach ($images->take(4) as $image)
        <div class="col-6">
            <a href="{{ asset('storage/' . $image->image_path) }}" class="glightbox d-block position-relative" data-gallery="{{ $gallery }}">
                <img src="{{ asset('storage/' . $image->image_path) }}"
                    class="shadow-sm img-fluid rounded-4 w-100"
                    style="height: 200px; object-fit: cover;">
                @if ($count > 4 && $loop->last)
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center rounded-4 text-white fw-bold"
                    style="background: rgba(0, 0, 0, 0.5); font-size: 24px;">
                    +{{ $count - 4 }}
                </div>
                @endif
            </a>
        </div>
        @endforeach
    </div>
    @foreach ($images->skip(4) as $image)
    <a href="{{ asset('storage/' . $image->image_path) }}" class="glightbox d-none" data-gallery="{{ $gallery }}"></a>
    @endforeach
    @endif
</div>
@elseif (!empty($post->image))
<div class="mb-3">
    <a href="{{ asset('storage/' . $post->image) }}" class="glightbox" data-gallery="{{ $gallery }}">
        <img src="{{ asset('storage/' . $post->image) }}"
            class="shadow-sm img-fluid rounded-4 w-100"
            style="max-height: 400px; object-fit: cover;">
    </a>
</div>
@endif
