<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - Technolife</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="login-page">

    <section class="login-visual">
        <div class="login-brand">
            Technolife
            <small>Membership made effortless.</small>
        </div>

        <div>
            <span class="eyebrow">ADMIN SUITE</span>

            <h1>
                Kelola komunitas<br>
                dengan lebih cerdas.
            </h1>

            <p>
                Satu dashboard untuk member, akses outlet, dan seluruh transaksi Technolife.
            </p>
        </div>
    </section>

    <main class="login-panel">
        <form
            method="POST"
            action="{{ route('login.process') }}"
            class="login-card"
        >
            @csrf

            <div class="login-logo" aria-label="Technolife">
                <div class="login-logo-mark"><i data-lucide="building-2"></i></div>
                <div class="login-logo-copy">
                    <strong>Technolife</strong>
                    <small>MEMBERSHIP TECHNOLIFE</small>
                </div>
            </div>

            <span class="eyebrow">SELAMAT DATANG</span>

            <h2>
                Masuk ke akun Anda
            </h2>

            <p class="muted">
                Gunakan akun admin atau cashier yang terdaftar.
            </p>

            @if($errors->any())
                <div class="alert alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <label>
                Email
                <input
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    placeholder="admin@technolife.com"
                    required
                    autofocus
                >
            </label>

            <label>
                Password

                <div class="password-wrap">
                    <input
                        name="password"
                        type="password"
                        id="password"
                        placeholder="••••••••"
                        required
                    >

                    <button type="button" data-toggle-password>
                        <i data-lucide="eye"></i>
                    </button>
                </div>
            </label>

            <label class="check">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                >
                Ingat saya
            </label>

            <button
                class="btn btn-primary btn-block"
                type="submit"
            >
                Masuk
                <i data-lucide="arrow-right"></i>
            </button>

            <div class="login-divider"><span>atau</span></div>

            <a
                class="btn btn-keycloak btn-block"
                href="{{ route('keycloak.redirect') }}"
            >
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 3.5 20 8v8l-8 4.5L4 16V8l8-4.5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="m8 10 4 2.3 4-2.3M12 12.3V17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Masuk dengan SSO
            </a>
        </form>
    </main>

</body>

</html>
