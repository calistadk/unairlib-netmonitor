<aside class="w-64 bg-[#0F4C8A] text-white min-h-screen p-6 sticky top-0 self-start h-screen overflow-y-auto">

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
               class="block {{ request()->is('monitoring*') ? 'text-[#EAB308] font-semibold' : 'hover:text-[#EAB308]' }}">
                Monitoring
            </a>
        </li>

        <li>
            <a href="/perangkat"
               class="block {{ request()->is('perangkat*') ? 'text-[#EAB308] font-semibold' : 'hover:text-[#EAB308]' }}">
                Devices
            </a>
        </li>

        <li>
            <a href="/maintenance"
               class="block {{ request()->is('maintenance*') ? 'text-[#EAB308] font-semibold' : 'hover:text-[#EAB308]' }}">
                Maintenance
            </a>
        </li>

        <li>
            <a href="/log"
               class="block {{ request()->is('log') ? 'text-[#EAB308] font-semibold' : 'hover:text-[#EAB308]' }}">
                History & Activity Log
            </a>
        </li>

    </ul>

</aside>