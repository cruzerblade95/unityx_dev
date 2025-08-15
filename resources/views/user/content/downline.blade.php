@extends('user.layouts.layoutMaster')

@section('title', 'My Downline')

@section('content')
<div class="container py-4">
    <h2>My Downline Tree</h2>
    <div id="downline-tree">
        @if($downlines->isEmpty())
            <p>No downlines found.</p>
        @else
            <ul>
                @foreach($downlines as $downline)
                    @include('user.content.partials.downline_node', ['user' => $downline])
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
