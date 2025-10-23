<div class="col-xl-3 messenger-list-item" data-id="{{ $user->id }}">
    <a href="#" class="wsus__favourite_item">
        <div class="img">


                <img src="{{ asset('storage/'.$user->avatar) }}" alt="User" class="img-fluid">

                <img src="{{ asset('storage/Avatars/avatar.png') }}" alt="User" class="img-fluid">

            <span class="inactive"></span>
        </div>
        <p>{{ $user->name }}</p>
    </a>
</div>
