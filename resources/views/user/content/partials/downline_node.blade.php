<li>
    {{ $user->username }} ({{ $user->referral_code }})
    @if($user->downlines && $user->downlines->count())
        <ul>
            @foreach($user->downlines as $child)
                @include('user.content.partials.downline_node', ['user' => $child])
            @endforeach
        </ul>
    @endif
</li>
