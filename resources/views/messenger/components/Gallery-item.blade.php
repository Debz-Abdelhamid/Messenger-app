@php
    $image = json_decode($photo->attachment)
@endphp
<li>

    <a class="venobox" data-gall="gallery01" href="{{ asset('storage/'.$image) }}">
        <img src="{{ asset('storage/'.$image) }}" alt="" class="img-fluid w-100" loading="lazy">
    </a>
</li>
