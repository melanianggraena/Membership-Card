<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Technolife</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="app-shell" x-data>
    @include('layouts.sidebar')
    <div class="app-main">
        @include('layouts.navbar')
        <main class="page-wrap">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-error">{{ session('error') }}</div>@endif
            @yield('content')
        </main>
        @include('layouts.footer')
    </div>
</div>
<div class="modal-backdrop" id="modalBackdrop"></div>
@stack('modals')
</body>
</html>
