<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Debug Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4">Admin Authentication Debug</h1>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Authentication Status</h3>
            </div>
            <div class="card-body">
                <p><strong>Is Logged In:</strong> {{ $data['is_logged_in'] ? 'Yes' : 'No' }}</p>
                <p><strong>Guard Name:</strong> {{ $data['guard_name'] }}</p>
                
                @if($data['is_logged_in'])
                    <form action="{{ route('admin.logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-danger">Logout</button>
                    </form>
                @endif
            </div>
        </div>

        @if($data['user_data'])
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">User Data</h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><strong>ID:</strong> {{ $data['user_data']['id'] }}</li>
                        <li><strong>Username:</strong> {{ $data['user_data']['username'] }}</li>
                        <li><strong>Email:</strong> {{ $data['user_data']['email'] }}</li>
                        <li><strong>Created At:</strong> {{ $data['user_data']['created_at'] }}</li>
                        <li><strong>Updated At:</strong> {{ $data['user_data']['updated_at'] }}</li>
                    </ul>
                </div>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Session Data</h3>
            </div>
            <div class="card-body">
                <pre class="bg-light p-3">{{ print_r($data['session_data'], true) }}</pre>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Request Information</h3>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li><strong>IP Address:</strong> {{ $data['request_info']['ip'] }}</li>
                    <li><strong>User Agent:</strong> {{ $data['request_info']['user_agent'] }}</li>
                    <li><strong>Request Method:</strong> {{ $data['request_info']['method'] }}</li>
                    <li><strong>URL:</strong> {{ $data['request_info']['url'] }}</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>