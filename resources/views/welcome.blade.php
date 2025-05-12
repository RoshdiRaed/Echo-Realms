<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Echo Realms - Home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white min-h-screen relative overflow-hidden font-orbitron">
    <!-- Animated Background -->
    <div id="particles-js" class="absolute inset-0 z-0"></div>

    <!-- Main Content -->
    <div class="relative z-10 flex flex-col items-center justify-center min-h-screen p-6">
        <h1 class="text-6xl font-bold mb-4 text-blue-400 animate-pulse drop-shadow-lg">Echo Realms</h1>
        <p class="text-xl mb-8 text-gray-300">A mystical game of memory, strategy, and deception.</p>

        @auth
            <!-- User Profile -->
            <div class="mb-8 bg-gray-800 p-6 rounded-lg shadow-lg flex items-center space-x-4">
                <img src="{{ auth()->user()->profile->avatar ?? './echo.svg' }}" alt="Avatar" class="w-16 h-16 rounded-full">
                <div>
                    <h2 class="text-2xl font-bold">{{ auth()->user()->name }}</h2>
                    <p class="text-gray-400">Points: {{ auth()->user()->profile->points ?? 0 }}</p>
                    <p class="text-gray-400">Wins: {{ auth()->user()->profile->wins ?? 0 }} / Games: {{ auth()->user()->profile->games_played ?? 0 }}</p>
                </div>
            </div>

            <!-- Game Actions -->
            <div class="space-x-4 mb-8">
                <a href="{{ route('game.create') }}" class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg shadow-lg hover:shadow-blue-500/50 transition duration-300 transform hover:scale-105">Create Game</a>
                <a href="{{ route('games.index') }}" class="bg-green-600 hover:bg-green-700 px-6 py-3 rounded-lg shadow-lg hover:shadow-green-500/50 transition duration-300 transform hover:scale-105">Join Game</a>
            </div>
        @else
            <div class="space-x-4 mb-8">
                <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg shadow-lg hover:shadow-blue-500/50 transition duration-300 transform hover:scale-105">Login</a>
                <a href="{{ route('register') }}" class="bg-green-600 hover:bg-green-700 px-6 py-3 rounded-lg shadow-lg hover:shadow-green-500/50 transition duration-300 transform hover:scale-105">Register</a>
            </div>
        @endauth

        <!-- Leaderboard -->
        <div class="w-full max-w-2xl bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-3xl font-bold mb-4 text-blue-400">Leaderboard</h2>
            <div class="space-y-4">
                @foreach (\App\Models\Profile::orderByDesc('points')->take(5)->get() as $profile)
                    <div class="flex items-center space-x-4 bg-gray-700 p-4 rounded-lg">
                        <img src="{{ $profile->avatar ?? 'https://via.placeholder.com/48' }}" alt="Avatar" class="w-12 h-12 rounded-full">
                        <div>
                            <p class="text-lg font-bold">{{ $profile->user->name }}</p>
                            <p class="text-gray-400">Points: {{ $profile->points }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script>
        particlesJS('particles-js', {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: ['#00f7ff', '#ff00ff', '#00ff7f'] },
                shape: { type: 'circle', stroke: { width: 0 } },
                opacity: { value: 0.5, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: '#00f7ff', opacity: 0.4, width: 1 },
                move: { enable: true, speed: 2, direction: 'none', random: false, straight: false, out_mode: 'out' }
            },
            interactivity: {
                detect_on: 'canvas',
                events: { onhover: { enable: true, mode: 'repulse' }, onclick: { enable: true, mode: 'push' }, resize: true },
                modes: { repulse: { distance: 100, duration: 0.4 }, push: { particles_nb: 4 } }
            },
            retina_detect: true
        });
    </script>
</body>
</html>
