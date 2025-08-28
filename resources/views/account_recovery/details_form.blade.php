@extends('layouts.blankLayout')

@section('title', 'Account Recovery - Account Details')

@section('vendor-style')
<style>
.referral-search-container {
    position: relative;
}

.referral-search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 4px 4px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
}

.referral-search-item {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.referral-search-item:hover {
    background-color: #f8f9fa;
}

.referral-search-item:last-child {
    border-bottom: none;
}

.loading-indicator {
    padding: 10px;
    text-align: center;
    color: #6c757d;
}
</style>
@endsection

@section('content')
<div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6">
        <!-- Logo -->
        <div class="card p-7">
            <!-- Title -->
            <div class="app-brand justify-content-center mb-6">
                <a href="{{ url('/') }}" class="app-brand-link gap-2">
                    <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20,"withbg"=>'fill: #ff6f61;'])</span>
                    <span class="app-brand-text demo text-heading fw-bold">Account Recovery</span>
                </a>
            </div>
            <!-- /Title -->

            <h4 class="mb-1">Account Details</h4>
            <p class="mb-6">Please provide your account information for verification</p>

            <form class="mb-6" action="{{ route('account-recovery.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="recovery_email" value="{{ session('recovery_email') }}">

                <div class="mb-4">
                    <label for="email" class="form-label">Email (Confirm)</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                           id="email" name="email" placeholder="Re-enter your email address"
                           value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="deposit" class="form-label">Deposit Amount</label>
                    <input type="number" step="0.01" min="0" class="form-control @error('deposit') is-invalid @enderror"
                           id="deposit" name="deposit" placeholder="Enter your deposit amount"
                           value="{{ old('deposit') }}" required>
                    @error('deposit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="profit" class="form-label">Profit Amount</label>
                    <input type="number" step="0.01" min="0" class="form-control @error('profit') is-invalid @enderror"
                           id="profit" name="profit" placeholder="Enter your profit amount"
                           value="{{ old('profit') }}" required>
                    @error('profit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="referral_search" class="form-label">Referral Username (Optional)</label>
                    <div class="referral-search-container">
                        <input type="text" class="form-control"
                               id="referral_search" placeholder="Search by name, username, or referral code"
                               autocomplete="off">
                        <div id="referral_search_results" class="referral-search-results"></div>
                    </div>
                    <input type="hidden" name="referral_username" id="referral_username" value="{{ old('referral_username') }}">
                    <small class="text-muted">Start typing to search for your referral by name, username, or referral code</small>
                    @error('referral_username')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <button class="btn btn-primary d-grid w-100" type="submit">Submit Recovery Request</button>
            </form>

            <div class="text-center">
                <a href="{{ route('account-recovery.email') }}" class="d-flex align-items-center justify-content-center">
                    <i class="icon-base ti ti-chevron-left"></i>
                    Back to email verification
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('referral_search');
    const resultsContainer = document.getElementById('referral_search_results');
    const hiddenInput = document.getElementById('referral_username');
    let searchTimeout;

    // Set initial value if there's an old value
    if (hiddenInput.value) {
        searchInput.value = hiddenInput.value;
    }

    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        clearTimeout(searchTimeout);

        if (query.length < 2) {
            resultsContainer.style.display = 'none';
            hiddenInput.value = '';
            return;
        }

        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.length >= 2) {
            resultsContainer.style.display = 'block';
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.referral-search-container')) {
            resultsContainer.style.display = 'none';
        }
    });

    function performSearch(query) {
        resultsContainer.innerHTML = '<div class="loading-indicator">Searching...</div>';
        resultsContainer.style.display = 'block';

        fetch(`{{ route('account-recovery.search-referral-users') }}?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                displayResults(data);
            })
            .catch(error => {
                console.error('Search error:', error);
                resultsContainer.innerHTML = '<div class="loading-indicator text-danger">Search failed</div>';
            });
    }

    function displayResults(users) {
        if (users.length === 0) {
            resultsContainer.innerHTML = '<div class="loading-indicator">No users found</div>';
            return;
        }

        let html = '';
        users.forEach(user => {
            html += `
                <div class="referral-search-item" data-username="${user.username}" data-display="${user.display}">
                    <strong>${user.display}</strong>
                    ${user.referral_code ? `<br><small class="text-muted">Referral Code: ${user.referral_code}</small>` : ''}
                </div>
            `;
        });

        resultsContainer.innerHTML = html;

        // Add click handlers to result items
        resultsContainer.querySelectorAll('.referral-search-item').forEach(item => {
            item.addEventListener('click', function() {
                const username = this.getAttribute('data-username');
                const display = this.getAttribute('data-display');

                searchInput.value = display;
                hiddenInput.value = username;
                resultsContainer.style.display = 'none';
            });
        });
    }
});
</script>
@endsection
