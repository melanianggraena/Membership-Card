<header class="navbar">

    <button class="icon-btn mobile-menu" type="button" data-sidebar-toggle>
        <i data-lucide="menu"></i>
    </button>

    <div class="search-box">
        <i data-lucide="search"></i>
        <input
            type="search"
            placeholder="Cari di Technolife..."
            data-global-search
        >
    </div>

    <strong class="navbar-title">
        Membership Technolife
    </strong>

    @php($recentNotifications = auth()->user()->notifications()->latest()->limit(5)->get())
    @php($unreadCount = auth()->user()->unreadNotifications()->count())

    <div class="navbar-actions">

        <div class="notification-menu">

            <button
                class="icon-btn"
                type="button"
                data-notification-toggle
                aria-label="Notifikasi"
            >
                <i data-lucide="bell"></i>

                @if($unreadCount)
                    <span
                        class="notification-count"
                        data-unread-count
                    >
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </button>

            <section
                class="notification-dropdown"
                data-notification-dropdown
            >
                <div class="card-head">
                    <h2>Notifikasi</h2>

                    @if($unreadCount)
                        <button
                            type="button"
                            class="text-button"
                            data-read-all
                            data-url="{{ route('notifications.read-all') }}"
                        >
                            Baca semua
                        </button>
                    @endif
                </div>

                <div class="notification-list">

                    @forelse($recentNotifications as $notification)

                        <a
                            href="{{ $notification->data['url'] ?? route('notifications.index') }}"
                            class="notification-item {{ $notification->read_at ? '' : 'unread' }}"
                            data-notification-id="{{ $notification->id }}"
                            data-read-url="{{ route('notifications.read', $notification->id) }}"
                        >
                            <span class="activity-icon">
                                <i
                                    data-lucide="{{ ($notification->data['category'] ?? '') === 'nfc_access' ? 'scan-line' : (($notification->data['category'] ?? '') === 'top_up' ? 'wallet-cards' : 'receipt-text') }}"
                                ></i>
                            </span>

                            <span>
                                <b>
                                    {{ $notification->data['title'] ?? 'Notifikasi' }}
                                </b>

                                <small>
                                    {{ $notification->data['description'] ?? '' }}
                                </small>

                                <time>
                                    {{ $notification->created_at->diffForHumans() }}
                                </time>
                            </span>
                        </a>

                    @empty

                        <div class="empty">
                            <i data-lucide="bell-off"></i>
                            <p>Belum ada notifikasi.</p>
                        </div>

                    @endforelse

                </div>

                <a
                    class="notification-footer"
                    href="{{ route('notifications.index') }}"
                >
                    Lihat Semua Notifikasi
                </a>
            </section>

        </div>

        <a
            href="{{ route('settings.index') }}"
            class="profile"
        >
            <span class="avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>

            <span>
                {{ auth()->user()->name }}
            </span>

            <i data-lucide="chevron-down"></i>
        </a>

    </div>

</header>
