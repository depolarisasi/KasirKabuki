<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'KasirBraga') }}</title>

        <!-- PWA Meta Tags -->
        <meta name="application-name" content="KasirBraga">
        <meta name="description" content="Progressive Web App untuk Point of Sales KasirKabuki">
        <meta name="theme-color" content="#3b82f6">
        <meta name="background-color" content="#ffffff">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="KasirBraga">
        
        <!-- Mobile/Tablet Optimizations -->
        <meta name="format-detection" content="telephone=no">
        <meta name="msapplication-tap-highlight" content="no">
        <meta name="apple-touch-fullscreen" content="yes">
        
        <!-- PWA Manifest -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        
        <!-- PWA Icons -->
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/icon-192x192.png') }}">
        <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/icon-192x192.png') }}">

        <!-- Fonts -->
        <!-- Using system fonts for better performance -->

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Livewire Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-base-100 pb-20 lg:pb-0">
            @include('partials.navigation')

          

            <!-- Page Content -->
            <main class="container mx-auto">
                {{ $slot }}
            </main>
        </div>
        
        <!-- Livewire Scripts -->
        @livewireScripts
         
        
        <!-- SweetAlert -->
        @include('sweetalert::alert')
    </body>
</html> 