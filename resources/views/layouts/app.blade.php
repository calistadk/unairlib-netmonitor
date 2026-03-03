<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UNAIR LIB NetMonitor</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100">

    {{-- HEADER --}}
    @include('components.header')

    <div class="flex">

        {{-- SIDEBAR --}}
        @include('components.sidebar')

        {{-- CONTENT --}}
        <main class="flex-1 p-8">
            @yield('content')
        </main>

    </div>

</body>
</html>