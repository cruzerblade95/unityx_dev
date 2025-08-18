@extends('user.layouts.layoutMaster')

@section('title', 'My Downline')

<!-- Vendor Styles -->
@section('vendor-style')
  @vite('resources/assets/vendor/libs/jstree/jstree.scss')
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
  @vite('resources/assets/vendor/libs/jstree/jstree.js')
@endsection

<!-- Page Scripts -->
@section('page-script')
  @vite('resources/assets/js/extended-ui-treeview.js')

  <script>
    function buildDownlineTree(user) {
        let node = {
            text: `${user.username} (${user.referral_code})`,
            icon: 'icon-base ti tabler-users',
            children: []
        };
        if (user.downlines && user.downlines.length > 0) {
            node.children = user.downlines.map(buildDownlineTree);
        }
        return node;
        }

        @php
        $downlineTreeData = [];
        foreach ($downlines as $downline) {
            $downlineTreeData[] = [
            'username' => $downline->username,
            'referral_code' => $downline->referral_code,
            'downlines' => $downline->downlines->map(function($child) {
                return [
                'username' => $child->username,
                'referral_code' => $child->referral_code,
                'downlines' => $child->downlines
                ];
            })->toArray()
            ];
        }
        @endphp

        document.addEventListener('DOMContentLoaded', function() {
        let data = @json($downlineTreeData);
        let treeData = data.map(buildDownlineTree);
        $('#jstree-downline').jstree({
        'core': {
        'data': treeData
        },
        'themes': {
        'icons': true
        }
        });
        $('#jstree-downline').on('ready.jstree', function() {
        $('#jstree-downline').jstree('open_all');
        });
        });
    </script>
@endsection

@section('content')
<div class="container py-4">
    {{-- <h2>My Downline Tree</h2> --}}
  <div id="downline-tree" class="d-none">
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

<!-- JSTree -->
  <div class="row gy-6">
    <!-- View my Referral Code -->
    <div class="col-md-6 col-12">
      {{-- <div class="card">
        <h5 class="card-header">My Referral Code</h5>
        <div class="card-body">
          <p class="mb-0">{{ auth()->user()->referral_code }}</p>
        </div>
      </div> --}}
      <div class="card">
        <div class="d-flex align-items-end row card-body">
          <div class="col-7">
            <h5 class=" mb-0">Referral Code for {{ Illuminate\Support\Facades\Auth::user()->firstname }} {{ Illuminate\Support\Facades\Auth::user()->lastname }}!</h5>
            <p class="mb-2">Send this code to your friends:</p>
            <h4 class="text-primary mb-1">{{ auth()->user()->referral_code }}</h4>
          </div>
          <div class="col-5 text-center text-sm-left">
            <img src="{{ asset('assets/svg/icons/undraw_referral_j2rw.svg')}}" class="img-fluid" alt="refer a friend" />
          </div>
        </div>
      </div>
    </div>
    <!-- Basic -->
    <div class="col-md-6 col-12">
      <div class="card">
        @if(isset($upline) && $upline)
          <div class="alert alert-info mb-0">
            <strong>My Upline:</strong> {{ $upline->username }} ({{ $upline->referral_code }})
          </div>
        @endif
        <h5 class="card-header">My Downline</h5>
        <div class="card-body">
          <div id="jstree-downline"></div>
        </div>
      </div>
    </div>
    <!-- /Basic -->
  </div>
@endsection
