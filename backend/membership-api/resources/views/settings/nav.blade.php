<aside class="card settings-nav">
    <a href="{{ route('settings.index') }}" class="{{ request()->routeIs('settings.index') ? 'active' : '' }}"><i data-lucide="user-round"></i>Profil Saya</a>
    <a href="{{ route('settings.security') }}" class="{{ request()->routeIs('settings.security') ? 'active' : '' }}"><i data-lucide="shield"></i>Keamanan</a>
    <a href="{{ route('settings.notifications') }}" class="{{ request()->routeIs('settings.notifications*') ? 'active' : '' }}"><i data-lucide="bell"></i>Notifikasi</a>
</aside>
