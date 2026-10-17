@php
    $legalModules = config('dasar_hukum');
    $isLegalModule = request()->routeIs('dasar-hukum.show');
    $isLegalProductModule = request()->routeIs('legal-products.*');
    $isSettingsModule = request()->routeIs('settings.*') || request()->routeIs('tracking.*');
@endphp
<aside class="app-sidebar">
    <div class="sidebar-brand"><span><img src="{{ asset('images/logo-hli-hub-sidebar.png') }}" alt="Logo HLI HUB"></span><button type="button" class="sidebar-toggle" aria-label="Tutup sidebar" aria-expanded="true">‹</button></div>
    <nav class="sidebar-nav" aria-label="Navigasi utama">
        <a href="{{ url('/') }}" class="sidebar-link {{ request()->routeIs('dashboard') || request()->is('/') ? 'sidebar-link--active' : '' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3.5 10 8.5-7 8.5 7v10.5h-6v-6h-5v6h-6z"/></svg>Dashboard</a>
        <a href="{{ route('isu-strategis.data') }}" class="sidebar-link {{ request()->routeIs('isu-strategis.data') || request()->routeIs('isu-strategis.search') ? 'sidebar-link--active' : '' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="3.5" width="16" height="17" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>Tracking Kegiatan/Isu Strategis</a>
        <a href="{{ route('stakeholders.index') }}" class="sidebar-link {{ request()->routeIs('stakeholders.*') ? 'sidebar-link--active' : '' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.8 20v-1.5a6.2 6.2 0 0 1 12.4 0V20zM16 5.5a3.5 3.5 0 0 1 0 6.8M18.2 14.5a5.5 5.5 0 0 1 3 5.5"/></svg>Stakeholder</a>
        <div class="sidebar-group sidebar-group--legal {{ $isLegalModule ? 'sidebar-group--open' : '' }}">
            <button type="button" class="sidebar-link sidebar-link--parent {{ $isLegalModule ? 'sidebar-link--active' : '' }}" data-sidebar-submenu aria-expanded="{{ $isLegalModule ? 'true' : 'false' }}" aria-controls="legalBasisSubmenu">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3.5h9l5 5v12H5z"/><path d="M14 3.5v5h5M8.5 13h7M8.5 16.5h7"/></svg>
                <span>Dasar Hukum/Peraturan</span>
                <svg class="sidebar-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
            </button>
            <div class="sidebar-subnav sidebar-subnav--scrollable" id="legalBasisSubmenu" @if (!$isLegalModule) hidden @endif>
                @foreach ($legalModules as $moduleSlug => $moduleTitle)
                    <a href="{{ route('dasar-hukum.show', $moduleSlug) }}" class="sidebar-sublink {{ $isLegalModule && $slug === $moduleSlug ? 'sidebar-sublink--active' : '' }}">{{ $moduleTitle }}</a>
                @endforeach
            </div>
            <div class="sidebar-group {{ $isLegalProductModule ? 'sidebar-group--open' : '' }}">
                <button type="button" class="sidebar-link sidebar-link--parent {{ $isLegalProductModule ? 'sidebar-link--active' : '' }}" data-sidebar-submenu aria-expanded="{{ $isLegalProductModule ? 'true' : 'false' }}" aria-controls="legalProductsSubmenu">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3.5h9l5 5v12H5z"/><path d="M14 3.5v5h5M8.5 13h7M8.5 16.5h7"/></svg>
                    <span>Arsip Produk Hukum</span>
                    <svg class="sidebar-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
                </button>
                <div class="sidebar-subnav" id="legalProductsSubmenu" @if (!$isLegalProductModule) hidden @endif>
                    <a href="{{ route('legal-products.index', 'pks') }}" class="sidebar-sublink {{ request()->route('type') === 'pks' ? 'sidebar-sublink--active' : '' }}">PKS</a>
                    <a href="{{ route('legal-products.index', 'mou') }}" class="sidebar-sublink {{ request()->route('type') === 'mou' ? 'sidebar-sublink--active' : '' }}">MoU</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="sidebar-settings-area">
        <div class="sidebar-group sidebar-settings-group {{ $isSettingsModule ? 'sidebar-group--open' : '' }}">
            <button type="button" class="sidebar-link sidebar-link--parent sidebar-settings-trigger {{ $isSettingsModule ? 'sidebar-link--active' : '' }}" data-sidebar-submenu aria-label="Pengaturan" title="Pengaturan" aria-expanded="{{ $isSettingsModule ? 'true' : 'false' }}" aria-controls="settingsSubmenu">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8.2a3.8 3.8 0 1 0 0 7.6 3.8 3.8 0 0 0 0-7.6Z"/><path d="m19.4 15 .1.1a1.8 1.8 0 1 1-2.5 2.5l-.1-.1a1.8 1.8 0 0 0-3 .9v.2a1.8 1.8 0 1 1-3.6 0v-.2a1.8 1.8 0 0 0-3-.9l-.1.1a1.8 1.8 0 1 1-2.5-2.5l.1-.1a1.8 1.8 0 0 0-.9-3H3.7a1.8 1.8 0 1 1 0-3.6h.2a1.8 1.8 0 0 0 .9-3l-.1-.1a1.8 1.8 0 1 1 2.5-2.5l.1.1a1.8 1.8 0 0 0 3-.9v-.2a1.8 1.8 0 1 1 3.6 0v.2a1.8 1.8 0 0 0 3 .9l.1-.1a1.8 1.8 0 1 1 2.5 2.5l-.1.1a1.8 1.8 0 0 0 .9 3h.2a1.8 1.8 0 1 1 0 3.6h-.2a1.8 1.8 0 0 0-.9 3Z"/></svg>
            </button>
            <div class="sidebar-subnav sidebar-settings-subnav" id="settingsSubmenu" @if (!$isSettingsModule) hidden @endif>
                <a href="{{ route('settings.index') }}" class="sidebar-sublink {{ request()->routeIs('settings.*') ? 'sidebar-sublink--active' : '' }}">Akun &amp; Tampilan</a>
                <a href="{{ route('tracking.index') }}" class="sidebar-sublink {{ request()->routeIs('tracking.*') ? 'sidebar-sublink--active' : '' }}">Pengaturan SLA</a>
            </div>
        </div>
    </div>
    <form class="sidebar-logout" method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>Keluar</button>
    </form>
</aside>
