<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Echo Realms - Join Game</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    @livewireStyles
</head>
<body class="bg-gray-900 text-white p-4">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-4">Join a Game</h1>
        @if (session('error'))
            <div class="bg-red-600 p-4 rounded mb-4">{{ session('error') }}</div>
        @endif
        @if ($games->isEmpty())
            <p>No games available. <a href="{{ route('game.create') }}" class="text-blue-400">Create one?</a></p>
        @else
            <div class="grid gap-4">
                @foreach ($games as $game)
                    <div class="bg-gray-800 p-4 rounded flex justify-between items-center">
                        <p>Game #{{ $game->id }}</p>
                        <a href="{{ route('game.join', $game->id) }}" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded">Join</a>
                    </div>
                @endforeach
            </div>
        @endif
        <a href="{{ route('home') }}" class="mt-4 inline-block text-blue-400">Back to Home</a>
    </div>
    @livewireScripts
</body>
</html>
