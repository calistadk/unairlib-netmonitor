<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UNAIR LIB NetMonitor</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-[#0F4C8A] flex items-center justify-center min-h-screen">

    <div class="bg-gray-100 rounded-3xl shadow-xl w-[480px] p-10">

        <!-- HEADER -->
        <div class="flex items-center gap-4 mb-8">
            <img src="{{ asset('Assets/Logo Unair.png') }}"
                 class="w-14 h-14">

            <div>
                <h1 class="text-2xl font-bold text-[#0F4C8A]">
                    UNAIR LIB NetMonitor
                </h1>
                <p class="text-gray-500 text-sm">
                    Sistem Monitoring Inventori Jaringan
                </p>
            </div>
        </div>

        <!-- FORM -->
        <form action="/login" method="POST" class="space-y-5">
            @csrf

            @if ($errors->any())
            <div class="px-4 py-3 bg-red-100 text-red-700 rounded-xl text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            @if (session('error'))
            <div class="px-4 py-3 bg-red-100 text-red-700 rounded-xl text-sm">
                {{ session('error') }}
            </div>
            @endif

            <div>
                <label class="text-gray-700 text-sm">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    placeholder="Masukkan email"
                    class="w-full mt-1 px-4 py-3 rounded-xl border bg-gray-200 focus:outline-none">
            </div>

            <div>
                <label class="text-gray-700 text-sm">Password</label>
                <div class="relative">
                    <input type="password" name="password" id="password"
                        placeholder="Masukkan password"
                        class="w-full mt-1 px-4 py-3 pr-12 rounded-xl border bg-gray-200 focus:outline-none">

                    <button type="button" id="togglePassword"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">

                        {{-- Ikon mata (password tersembunyi) --}}
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>

                        {{-- Ikon mata dicoret (password terlihat) --}}
                        <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-[#0F4C8A] text-white py-3 rounded-xl font-semibold hover:bg-blue-800 transition">
                Login
            </button>
        </form>

        <!-- FOOTER -->
        <p class="text-center text-gray-400 text-sm mt-8">
            © 2026 Universitas Airlangga - Network Monitoring System
        </p>

    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput  = document.getElementById('password');
        const eyeIcon        = document.getElementById('eyeIcon');
        const eyeOffIcon     = document.getElementById('eyeOffIcon');

        togglePassword.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            eyeIcon.classList.toggle('hidden', isHidden);
            eyeOffIcon.classList.toggle('hidden', !isHidden);
        });
    </script>

</body>
</html>