<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk · HLI HUB</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-landing{{ $errors->any() ? ' login-landing--form-open' : '' }}" aria-labelledby="loginTitle">
            <img class="login-logo" src="{{ asset('images/logo-hli-hub-modern.png') }}" alt="HLI HUB — Strategic Monitoring & Regulatory Information">
            <h1 id="loginTitle" class="visually-hidden">HLI HUB</h1>
            <button class="login-trigger" type="button" id="loginTrigger" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}" aria-controls="loginPanel">
                <span data-login-trigger-label>{{ $errors->any() ? 'Tutup formulir' : 'Login' }}</span><span data-login-trigger-icon aria-hidden="true">{{ $errors->any() ? '×' : '→' }}</span>
            </button>

            <section class="login-panel" id="loginPanel" aria-labelledby="loginPanelTitle" @if (!$errors->any()) hidden @endif>
                <img class="login-panel__logo" src="{{ asset('images/logo-hli-hub-modern.png') }}" alt="HLI HUB">
                <div class="login-intro">
                    <span class="login-eyebrow">PT Kereta Api Indonesia (Persero)</span>
                    <h2 id="loginPanelTitle">Selamat datang</h2>
                    <p>Masuk untuk melanjutkan ke Dashboard Monitoring Isu Strategis.</p>
                </div>

                @if ($errors->any())
                    <div class="login-error" role="alert">
                        @foreach ($errors->all() as $error)
                            <span>{{ $error }}</span>
                        @endforeach
                    </div>
                @endif

                <form class="login-form" method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" required @if ($errors->any()) autofocus @endif>

                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>

                    <button type="submit">Masuk ke dashboard <span aria-hidden="true">→</span></button>
                </form>
                <p class="login-security-note">Akses terbatas untuk pengguna terotorisasi.</p>
            </section>
        </section>
        <footer class="login-footer">© {{ now()->year }} PT Kereta Api Indonesia (Persero)</footer>
    </main>
</body>
</html>
