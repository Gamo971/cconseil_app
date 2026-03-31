<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>
            @hasSection('title')
                @yield('title') — {{ config('app.name', 'Laravel') }}
            @else
                {{ config('app.name', 'Laravel') }}
            @endif
        </title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @elseif(view()->hasSection('title'))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h1 class="text-2xl font-semibold text-gray-900">@yield('title')</h1>
                                @hasSection('subtitle')
                                    <p class="mt-1 text-sm text-gray-500">@yield('subtitle')</p>
                                @endif
                            </div>
                            @hasSection('header-actions')
                                <div class="flex flex-wrap items-center gap-2">
                                    @yield('header-actions')
                                </div>
                            @endif
                        </div>
                    </div>
                </header>
            @endif

            <main>
                @hasSection('content')
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        @yield('content')
                    </div>
                @else
                    {{ $slot }}
                @endif
            </main>
        </div>
    </body>
</html>
