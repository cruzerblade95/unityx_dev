@extends('user.layouts.layoutMaster')

@section('title', 'Notifications')

@section('page-style')
    <style>
        .notification-item {
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            background-color: var(--bs-body-bg, #ffffff);
            color: var(--bs-body-color, #212529);
            padding: 1rem !important;
            /* Increased padding for better readability */
            font-size: 15px;
            /* Slightly larger base font size */
            line-height: 1.6;
            /* Improved line height for readability */
        }

        .notification-item.unread {
            border-left-color: var(--bs-primary, #7367f0);
            background-color: var(--bs-primary-bg-subtle, #f8f7ff);
            border-left-width: 5px;
            /* Thicker border for better visibility */
        }

        /* Dark theme unread notifications - enhanced visibility */
        [data-bs-theme="dark"] .notification-item.unread {
            background-color: rgba(115, 103, 240, 0.2);
            /* More visible background */
            border-left-color: #a29bfe;
            /* Brighter primary color */
        }

        .notification-item:hover {
            background-color: var(--bs-gray-100, #f8f9fa);
            transform: translateY(-1px);
            /* Subtle lift effect */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        [data-bs-theme="dark"] .notification-item:hover {
            background-color: var(--bs-gray-700, #495057);
            /* More contrast in dark mode */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .notification-icon {
            width: 48px;
            /* Larger icon size */
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 20px;
            /* Larger icon font */
            color: var(--bs-body-color, #212529);
            margin-right: 1rem;
            /* More spacing */
        }

        /* Enhanced icon backgrounds for better visibility */
        .bg-light-primary {
            background-color: var(--bs-primary-bg-subtle, rgba(115, 103, 240, 0.15));
            color: var(--bs-primary, #7367f0);
        }

        .bg-light-success {
            background-color: var(--bs-success-bg-subtle, rgba(25, 135, 84, 0.15));
            color: var(--bs-success, #198754);
        }

        .bg-light-warning {
            background-color: var(--bs-warning-bg-subtle, rgba(255, 193, 7, 0.15));
            color: var(--bs-warning, #ffc107);
        }

        .bg-light-danger {
            background-color: var(--bs-danger-bg-subtle, rgba(220, 53, 69, 0.15));
            color: var(--bs-danger, #dc3545);
        }

        .bg-light-info {
            background-color: var(--bs-info-bg-subtle, rgba(13, 202, 240, 0.15));
            color: var(--bs-info, #0dcaf0);
        }

        .bg-light-secondary {
            background-color: var(--bs-secondary-bg-subtle, rgba(108, 117, 125, 0.15));
            color: var(--bs-secondary, #6c757d);
        }

        /* Dark theme icon adjustments - much more visible */
        [data-bs-theme="dark"] .bg-light-primary {
            background-color: rgba(115, 103, 240, 0.25);
            color: #b8b0ff;
            /* Much brighter */
        }

        [data-bs-theme="dark"] .bg-light-success {
            background-color: rgba(25, 135, 84, 0.25);
            color: #85d1aa;
            /* Much brighter green */
        }

        [data-bs-theme="dark"] .bg-light-warning {
            background-color: rgba(255, 193, 7, 0.25);
            color: #ffe066;
            /* Much brighter yellow */
        }

        [data-bs-theme="dark"] .bg-light-danger {
            background-color: rgba(220, 53, 69, 0.25);
            color: #ff7a85;
            /* Much brighter red */
        }

        [data-bs-theme="dark"] .bg-light-info {
            background-color: rgba(13, 202, 240, 0.25);
            color: #7ee8ff;
            /* Much brighter cyan */
        }

        [data-bs-theme="dark"] .bg-light-secondary {
            background-color: rgba(108, 117, 125, 0.25);
            color: #c5c9d0;
            /* Much brighter gray */
        }

        .notification-actions {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .notification-item:hover .notification-actions {
            opacity: 1;
        }

        /* Enhanced button visibility */
        .notification-actions .btn {
            font-size: 14px;
            /* Larger button text */
            padding: 0.375rem 0.75rem;
            /* Better button padding */
            font-weight: 500;
            /* Medium weight for readability */
        }

        /* Card theme adjustments */
        .card {
            background-color: var(--bs-body-bg, #ffffff);
            border-color: var(--bs-border-color, #dee2e6);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Subtle shadow */
        }

        [data-bs-theme="dark"] .card {
            background-color: #2a2d3a;
            /* Lighter dark background */
            border-color: #4a5568;
            /* Better contrast border */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            background-color: var(--bs-light, #f8f9fa);
            border-bottom-color: var(--bs-border-color, #dee2e6);
            color: var(--bs-body-color, #212529);
            padding: 1.25rem;
            /* More padding for header */
        }

        [data-bs-theme="dark"] .card-header {
            background-color: #363c4a;
            /* Lighter dark header */
            border-bottom-color: #4a5568;
            /* Better contrast */
            color: #f1f3f4;
            /* High contrast text */
        }

        .card-header h5 {
            font-size: 1.35rem;
            /* Larger header text */
            font-weight: 600;
            margin-bottom: 0;
        }

        /* Enhanced text readability */
        .text-muted {
            color: #6c757d !important;
            /* Standard muted color */
        }

        [data-bs-theme="dark"] .text-muted {
            color: #b8bcc8 !important;
            /* Much lighter muted text for dark mode */
        }

        /* Button theme consistency with better visibility */
        .btn-outline-primary {
            border-color: var(--bs-primary, #7367f0);
            color: var(--bs-primary, #7367f0);
            font-weight: 500;
            /* Medium weight */
        }

        .btn-outline-primary:hover {
            background-color: var(--bs-primary, #7367f0);
            border-color: var(--bs-primary, #7367f0);
            color: #ffffff;
        }

        [data-bs-theme="dark"] .btn-outline-primary {
            border-color: #a29bfe;
            /* Brighter primary in dark mode */
            color: #a29bfe;
        }

        .btn-outline-danger {
            border-color: var(--bs-danger, #dc3545);
            color: var(--bs-danger, #dc3545);
            font-weight: 500;
        }

        .btn-outline-danger:hover {
            background-color: var(--bs-danger, #dc3545);
            border-color: var(--bs-danger, #dc3545);
            color: #ffffff;
        }

        [data-bs-theme="dark"] .btn-outline-danger {
            border-color: #ff7a85;
            /* Brighter danger in dark mode */
            color: #ff7a85;
        }

        /* Border colors with better contrast */
        .border-bottom {
            border-bottom-color: var(--bs-border-color, #dee2e6) !important;
        }

        [data-bs-theme="dark"] .border-bottom {
            border-bottom-color: #4a5568 !important;
            /* More visible border */
        }

        /* Empty state avatar */
        .avatar-initial {
            background-color: var(--bs-secondary-bg-subtle, rgba(108, 117, 125, 0.15));
            color: var(--bs-secondary, #6c757d);
        }

        [data-bs-theme="dark"] .avatar-initial {
            background-color: rgba(108, 117, 125, 0.25);
            color: #b8bcc8;
            /* Much lighter */
        }

        /* Loading spinner */
        .spinner-border {
            color: var(--bs-primary, #7367f0);
        }

        [data-bs-theme="dark"] .spinner-border {
            color: #a29bfe;
            /* Brighter spinner */
        }

        /* Enhanced notification content typography */
        .notification-item h6 {
            color: var(--bs-body-color, #212529);
            font-size: 1.1rem;
            /* Larger title text */
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 0.5rem;
        }

        [data-bs-theme="dark"] .notification-item h6 {
            color: #f1f3f4;
            /* High contrast title in dark mode */
        }

        .notification-item p {
            color: var(--bs-secondary, #6c757d);
            font-size: 15px;
            /* Larger message text */
            line-height: 1.5;
            margin-bottom: 0.75rem;
        }

        [data-bs-theme="dark"] .notification-item p {
            color: #b8bcc8;
            /* Much lighter message text */
        }

        /* Timestamp styling */
        .notification-item small {
            color: var(--bs-secondary, #6c757d);
            font-size: 13px;
            /* Slightly larger timestamp */
        }

        [data-bs-theme="dark"] .notification-item small {
            color: #9ca3af;
            /* Lighter timestamp */
        }

        /* Enhanced empty state */
        #emptyState h5 {
            font-size: 1.4rem;
            /* Larger empty state title */
            color: var(--bs-body-color, #212529);
        }

        [data-bs-theme="dark"] #emptyState h5 {
            color: #f1f3f4;
        }

        #emptyState p {
            font-size: 15px;
            /* Larger empty state text */
        }

        /* Accessibility improvements */
        .notification-item:focus-within {
            outline: 2px solid var(--bs-primary, #7367f0);
            outline-offset: 2px;
        }

        /* Better spacing for content */
        .notification-item .d-flex {
            gap: 1rem;
            /* Consistent spacing */
        }

        /* Enhanced visual hierarchy */
        .fw-semibold {
            font-weight: 600 !important;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Notifications</h5>
                        <div>
                            <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="markAllAsRead()">
                                <i class="bx bx-check-double me-1"></i>
                                Mark All as Read
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearAllNotifications()">
                                <i class="bx bx-trash me-1"></i>
                                Clear All
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="notificationsContainer">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading notifications...</p>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div id="emptyState" class="text-center py-5" style="display: none;">
                            <div class="avatar avatar-xl mx-auto mb-3">
                                <span class="avatar-initial rounded-circle bg-label-secondary">
                                    <i class="bx bx-bell-off bx-lg text-muted"></i>
                                </span>
                            </div>
                            <h5 class="mb-2">No notifications yet</h5>
                            <p class="text-muted">When you have notifications, they'll appear here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadAllNotifications();
        });

        function loadAllNotifications() {
            fetch('/user/notifications/api?all=true')
                .then(response => response.json())
                .then(data => {
                    displayNotifications(data.notifications);
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    showError('Failed to load notifications');
                });
        }

        function displayNotifications(notifications) {
            const container = document.getElementById('notificationsContainer');
            const emptyState = document.getElementById('emptyState');

            if (notifications.length === 0) {
                container.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            container.style.display = 'block';
            emptyState.style.display = 'none';

            const html = notifications.map(notification => {
                const iconClass = getNotificationIcon(notification.type);
                const timeAgo = formatTimeAgo(notification.created_at);
                const isUnread = !notification.read_at;

                return `
            <div class="notification-item ${isUnread ? 'unread' : ''} p-3 border-bottom">
                <div class="d-flex align-items-start">
                    <div class="notification-icon bg-light-${getColorFromType(notification.type)} me-3">
                        <i class="${iconClass}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1 fw-semibold">${notification.title}</h6>
                                <p class="mb-2 text-muted">${notification.message}</p>
                                <small class="text-muted">
                                    <i class="bx bx-time-five me-1"></i>
                                    ${timeAgo}
                                </small>
                            </div>
                            <div class="notification-actions">
                                ${isUnread ? `<button type="button" class="btn btn-sm btn-outline-primary me-2" onclick="markAsRead(${notification.id})">Mark as Read</button>` : ''}
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteNotification(${notification.id})">Delete</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
            }).join('');

            container.innerHTML = html;
        }

        function getNotificationIcon(type) {
            const icons = {
                'kyc_verified': 'bx bx-check-circle',
                'kyc_rejected': 'bx bx-x-circle',
                'withdrawal_success': 'bx bx-money',
                'withdrawal_failed': 'bx bx-x-circle',
                'deposit_success': 'bx bx-plus-circle',
                'deposit_failed': 'bx bx-x-circle',
                'marketplace_verification_pending': 'bx bx-time',
                'marketplace_listing_approved': 'bx bx-check-circle',
                'marketplace_listing_rejected': 'bx bx-x-circle',
                'marketplace_topup_success': 'bx bx-wallet'
            };

            return icons[type] || 'bx bx-bell';
        }

        function getColorFromType(type) {
            const colors = {
                'kyc_verified': 'success',
                'kyc_rejected': 'danger',
                'withdrawal_success': 'success',
                'withdrawal_failed': 'danger',
                'deposit_success': 'success',
                'deposit_failed': 'danger',
                'marketplace_verification_pending': 'warning',
                'marketplace_listing_approved': 'success',
                'marketplace_listing_rejected': 'danger',
                'marketplace_topup_success': 'success'
            };

            return colors[type] || 'primary';
        }

        function formatTimeAgo(datetime) {
            const now = new Date();
            const past = new Date(datetime);
            const diffInSeconds = Math.floor((now - past) / 1000);

            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' minutes ago';
            if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hours ago';
            if (diffInSeconds < 2592000) return Math.floor(diffInSeconds / 86400) + ' days ago';

            return past.toLocaleDateString();
        }

        function markAsRead(notificationId) {
            fetch(`/user/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadAllNotifications(); // Reload notifications
                        showSuccess('Notification marked as read');
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                    showError('Failed to mark notification as read');
                });
        }

        function markAllAsRead() {
            if (!confirm('Are you sure you want to mark all notifications as read?')) {
                return;
            }

            fetch('/user/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadAllNotifications(); // Reload notifications
                        showSuccess('All notifications marked as read');
                    }
                })
                .catch(error => {
                    console.error('Error marking all notifications as read:', error);
                    showError('Failed to mark all notifications as read');
                });
        }

        function deleteNotification(notificationId) {
            if (!confirm('Are you sure you want to delete this notification?')) {
                return;
            }

            fetch(`/user/notifications/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadAllNotifications(); // Reload notifications
                        showSuccess('Notification deleted');
                    }
                })
                .catch(error => {
                    console.error('Error deleting notification:', error);
                    showError('Failed to delete notification');
                });
        }

        function clearAllNotifications() {
            if (!confirm('Are you sure you want to delete all notifications? This action cannot be undone.')) {
                return;
            }

            fetch('/user/notifications/clear-all', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        loadAllNotifications(); // Reload notifications
                        showSuccess('All notifications cleared');
                    } else {
                        showError(data.message || 'Failed to clear all notifications');
                    }
                })
                .catch(error => {
                    console.error('Error clearing all notifications:', error);
                    showError('Failed to clear all notifications: ' + error.message);
                });
        }

        function showSuccess(message) {
            // You can implement a toast notification system here
            alert('Success: ' + message);
        }

        function showError(message) {
            // You can implement a toast notification system here
            alert('Error: ' + message);
        }
    </script>
@endsection
