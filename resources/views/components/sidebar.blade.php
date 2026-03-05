<aside class="w-64 bg-[#0F4C8A] text-white min-h-screen p-6">

    <h2 class="text-xl font-bold mb-10">
        UNAIR LIB NetMonitor
    </h2>

    <ul class="space-y-6 pl-4 text-lg">

        <li>
            <a href="/dashboard"
               class="block {{ request()->is('dashboard') ? 'text-[#EAB308] font-semibold' : 'hover:text-[#EAB308]' }}">
                Dashboard
            </a>
        </li>

        <li>
            <a href="/monitoring"
               class="block {{ request()->is('monitoring') ? 'text-[#EAB308] font-semibold' : 'hover:text-[#EAB308]' }}">
                Monitoring
            </a>
        </li>

        <li>
            <a href="/perangkat"
               class="block {{ request()->is('perangkat') ? 'text-[#EAB308] font-semibold' : 'hover:text-[#EAB308]' }}">
                Perangkat
            </a>
        </li>

        <li>
            <a href="/log"
               class="block {{ request()->is('log') ? 'text-[#EAB308] font-semibold' : 'hover:text-[#EAB308]' }}">
                Log Aktivitas
            </a>
        </li>

    </ul>

</aside>