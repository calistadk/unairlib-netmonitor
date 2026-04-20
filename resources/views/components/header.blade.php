<header class="bg-white shadow flex justify-between items-center px-6 py-3 sticky top-0 z-50">

    <!-- Logo -->
    <div class="flex items-center gap-3">
        <img src="{{ asset('Assets/Logo Header.png') }}" class="h-10">
    </div>

    <!-- Right Menu -->
    <div class="flex items-center gap-6 text-blue-700">

        <!-- Profile -->
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M20 21a8 8 0 10-16 0"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span class="text-sm font-semibold">{{ auth()->user()->name }}</span>
        </div>
        
        <!-- Logout -->
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="cursor-pointer text-blue-700 hover:text-red-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round"
                     stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>
        </form>

    </div>

</header>