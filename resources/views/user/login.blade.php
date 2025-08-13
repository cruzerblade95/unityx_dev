<!-- User Login View -->
<h1>User Login</h1>
<form method="POST" action="{{ route('user.login') }}">
    @csrf
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
