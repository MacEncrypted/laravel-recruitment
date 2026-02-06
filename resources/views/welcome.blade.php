<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="bg-light">
<!-- Navigation -->
@if (Route::has('login'))
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                {{ config('app.name', 'Laravel') }}
            </a>

            <div class="ms-auto">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-dark btn-sm">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-dark btn-sm me-2">
                        Login
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-dark btn-sm">
                            Register
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>
@endif

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h1 class="display-3 fw-bold mb-4">
                    Projekt Rekrutacyjny
                </h1>

                <p class="lead text-muted mb-4">
                    Poszukujemy <strong>Junior/Mid PHP Laravel Developer</strong><br>
                    do rozwijającego się zespołu
                </p>

                <div class="mt-5">
                    <p class="text-muted small mb-3">Stack technologiczny:</p>
                    <div class="d-flex justify-content-center flex-wrap gap-2">
                        <span class="badge bg-dark">Laravel</span>
                        <span class="badge bg-dark">PHP 8+</span>
                        <span class="badge bg-dark">MySQL</span>
                        <span class="badge bg-dark">Bootstrap 5</span>
                        <span class="badge bg-dark">Git</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
