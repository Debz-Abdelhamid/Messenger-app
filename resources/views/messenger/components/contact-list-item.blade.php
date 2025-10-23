


<div class="wsus__user_list_item messenger-list-item" data-id="{{ $user->id }}">
    <div class="img">
        @if (!empty($user->avatar))

            <img src="{{ asset('storage/'.$user->avatar) }}" alt="User" class="img-fluid">
        @else
            <img src="{{ asset('storage/Avatars/avatar.png') }}" alt="User" class="img-fluid">

        @endif
        <span class="inactive"></span>
    </div>
    <div class="text">
        <h5>{{ $user->name }}</h5>

        @if ($LastMessage->from_id == auth()->user()->id)

                @if ($LastMessage->body)

                    <p><span>You:</span>{{ Str::limit($LastMessage->body, 18)}}</p>
                @else
                    <p><span>You:</span>sent a photo</p>
                @endif
        @else

            @if ($LastMessage->body)

                <p class="p">{{ Str::limit($LastMessage->body, 18) }}</p>
            @else
                <p class="p">photo</p>
            @endif
        @endif
    </div>

    @if ($UnseenCounter > 0)

        <span class="badge rounded-pill bg-danger  unseen_count time text-white">{{ $UnseenCounter }}</span>
    @endif


</div>
