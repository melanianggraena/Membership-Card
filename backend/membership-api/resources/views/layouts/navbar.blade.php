<header class="navbar">
    <button class="icon-btn mobile-menu" type="button" data-sidebar-toggle><i data-lucide="menu"></i></button>
    <div class="search-box"><i data-lucide="search"></i><input type="search" placeholder="Cari di Technolife..." data-global-search></div>
    <strong class="navbar-title">Membership Technolife</strong>
    <div class="navbar-actions"><button class="icon-btn"><i data-lucide="bell"></i><span class="dot"></span></button><a href="{{ route('settings.index') }}" class="profile"><span class="avatar">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span><span>{{ auth()->user()->name }}</span><i data-lucide="chevron-down"></i></a></div>
</header>
