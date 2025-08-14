@extends('layouts.blankLayout')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body text-center">
                    <h3 class="mb-4">Connect Your Telegram</h3>
                    <p class="mb-4">To receive notifications and complete your profile, connect your Telegram account.</p>
                    <a href="https://t.me/unityx_notification_bot" target="_blank" class="btn btn-primary btn-lg">
                        <i class="fab fa-telegram-plane"></i> Connect with Telegram
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
