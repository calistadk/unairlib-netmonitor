<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UNAIR LIB NetMonitor</title>

    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 h-screen flex flex-col overflow-hidden">

    {{-- HEADER --}}
    @include('components.header')

    <div class="flex flex-1 overflow-hidden">

        {{-- SIDEBAR --}}
        <div id="sidebar" class="transition-all duration-300 w-64 shrink-0">
            @include('components.sidebar')
        </div>

        {{-- CONTENT --}}
        <main class="flex-1 p-8 overflow-y-auto relative">

            {{-- TOGGLE BUTTON --}}
            <button onclick="toggleSidebar()"
                class="fixed bottom-6 left-6 z-50 bg-[#0F4C8A] text-white rounded-full w-9 h-9 flex items-center justify-center shadow-lg hover:bg-blue-800 transition"
                title="Toggle Sidebar">
                <svg id="toggleIcon" xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/>
                </svg>
            </button>

            @yield('content')
        </main>

    </div>

    <script>
        let sidebarOpen = true;

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const icon    = document.getElementById('toggleIcon');

            sidebarOpen = !sidebarOpen;

            if (sidebarOpen) {
                sidebar.style.width = '16rem'; // w-64
                sidebar.style.overflow = 'visible';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/>`;
            } else {
                sidebar.style.width = '0';
                sidebar.style.overflow = 'hidden';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 5l7 7-7 7M6 5l7 7-7 7"/>`;
            }
        }
    </script>

</body>
</html>