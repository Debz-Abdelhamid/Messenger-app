<div class="wsus__user_list_item messenger-list-item" data-id="{{ $record->id }}">
    <div class="img">
        @if (empty($record->avatar))

            <img src="{{ asset('storage/Avatars/avatar.png') }}" alt="User" class="img-fluid">


        @else

            <img src="{{ asset('storage/'.$record->avatar) }}" alt="User" class="img-fluid">
        @endif
        {{-- <span class="active"></span> --}}
    </div>
    <div class="text">
        <h5>{{ $record->name }}</h5>
        <p>{{ $record->user_name }}</p>
    </div>
   {{-- <span class="time">10m ago</span>--}}
</div>
