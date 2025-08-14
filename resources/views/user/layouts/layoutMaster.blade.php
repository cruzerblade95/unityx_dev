<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>@yield('title', 'User Area')</title>
	@vite(['resources/assets/vendor/libs/animate-css/animate.scss'])
</head>
<body>
	<div class="user-layout">
		@include('user.layouts.sections.sidebar')
		<main class="user-content">
			@yield('content')
		</main>
	</div>
</body>
</html>
