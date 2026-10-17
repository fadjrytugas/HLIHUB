<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan · HLI HUB</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.sidebar')
    <main class="data-page settings-page">
        <header class="data-page__header">
            <div>
                <span class="section-kicker">Preferensi aplikasi</span>
                <h1>Pengaturan</h1>
                <p>Kelola keamanan akun dashboard dan tampilan aplikasi.</p>
            </div>
        </header>

        @if (session('success'))
            <div class="legal-alert legal-alert--success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="legal-alert legal-alert--error" role="alert">
                <strong>Pengaturan belum berhasil disimpan.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="panel settings-section" aria-labelledby="appearanceTitle">
            <div class="settings-section__heading">
                <span class="settings-section__icon" aria-hidden="true">◐</span>
                <div>
                    <span class="section-kicker">Preferensi tampilan</span>
                    <h2 id="appearanceTitle">Mode Tampilan</h2>
                    <p>Pilih tampilan yang nyaman. Preferensi disimpan di browser ini.</p>
                </div>
            </div>
            <label class="settings-theme-toggle" for="themeToggle">
                <span class="settings-theme-toggle__copy">
                    <strong data-theme-label>Mode terang</strong>
                    <span data-theme-description>Tampilan saat ini menggunakan mode terang.</span>
                </span>
                <input id="themeToggle" type="checkbox" role="switch" aria-label="Aktifkan mode gelap">
                <span class="settings-theme-toggle__switch" aria-hidden="true"></span>
            </label>
        </section>

        <section class="panel settings-section" aria-labelledby="passwordTitle">
            <div class="settings-section__heading">
                <span class="settings-section__icon" aria-hidden="true">⌑</span>
                <div>
                    <span class="section-kicker">Keamanan akun</span>
                    <h2 id="passwordTitle">Ubah Password</h2>
                    <p>Password baru minimal 8 karakter. Password saat ini diperlukan untuk mengonfirmasi perubahan.</p>
                </div>
            </div>
            <form class="settings-password-form" method="POST" action="{{ route('settings.password.update') }}">
                @csrf
                @method('PUT')
                <label class="legal-field">Password Saat Ini
                    <input type="password" name="current_password" autocomplete="current-password" required>
                </label>
                <label class="legal-field">Password Baru
                    <input type="password" name="password" autocomplete="new-password" minlength="8" required>
                </label>
                <label class="legal-field">Konfirmasi Password Baru
                    <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
                </label>
                <button class="legal-primary-button" type="submit">Simpan Password Baru</button>
            </form>
        </section>
    </main>
</body>
</html>
