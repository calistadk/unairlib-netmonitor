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
        <div class="space-y-5">

            <div>
                <label class="text-gray-700 text-sm">Email</label>
                <input type="text"
                    placeholder="Masukkan email"
                    class="w-full mt-1 px-4 py-3 rounded-xl border bg-gray-200 focus:outline-none">
            </div>

            <div>
                <label class="text-gray-700 text-sm">Password</label>
                <input type="password"
                    placeholder="Masukkan password"
                    class="w-full mt-1 px-4 py-3 rounded-xl border bg-gray-200 focus:outline-none">
            </div>

            <div class="text-right">
                <a href="#" class="text-sm text-blue-600">
                    Lupa Password?
                </a>
            </div>

            <button class="w-full bg-[#0F4C8A] text-white py-3 rounded-xl font-semibold hover:bg-blue-800 transition">
                Login
            </button>

        </div>

        <!-- FOOTER -->
        <p class="text-center text-gray-400 text-sm mt-8">
            © 2026 Universitas Airlangga - Network Monitoring System
        </p>

    </div>

</body>
</html>