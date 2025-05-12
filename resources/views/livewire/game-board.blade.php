<div class="min-h-screen bg-gray-900 text-white p-6 relative font-orbitron" x-data="gameBoard" x-init="initAudio(); showActions = true"
    wire:poll.1000ms>

    <!-- Animated Background -->
    <div id="particles-js" class="absolute inset-0 z-0"></div>

    <div class="relative z-10 max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-4xl font-bold text-blue-400 animate-pulse drop-shadow-lg">Echo Realms</h1>
            <div class="flex space-x-4">
                <button @click="showTutorial = true; $nextTick(() => console.log('Tutorial opened'))"
                    class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg shadow-lg transition duration-300">Tutorial</button>
                <button @click="toggleMusic; $nextTick(() => console.log('Music toggled'))"
                    class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg shadow-lg transition duration-300">
                    <span x-text="musicMuted ? 'Unmute Music' : 'Mute Music'"></span>
                </button>
            </div>
        </div>

        <!-- Tutorial Modal -->
        <div x-show="showTutorial" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
            x-transition.opacity>
            <div class="bg-gray-800 p-8 rounded-lg max-w-lg">
                <h2 class="text-2xl font-bold text-blue-400 mb-4">How to Play Echo Realms</h2>
                <p class="text-gray-300 mb-4">Echo Realms is a strategic game of memory and deception. Each player has 5
                    Echo Slots with actions:</p>
                <ul class="list-disc list-inside text-gray-300 mb-4">
                    <li><strong>Attack</strong>: Deal 2 damage to an opponent’s slot if not shielded.</li>
                    <li><strong>Shield</strong>: Block an attack on your slot.</li>
                    <li><strong>Swap</strong>: Swap two of your slots’ positions.</li>
                    <li><strong>Reveal</strong>: See an opponent’s slot action.</li>
                    <li><strong>Mimic</strong>: Copy your last action.</li>
                </ul>
                <p class="text-gray-300 mb-4">Select 2 slots, target an opponent’s slot, and submit your turn. Reduce
                    your opponent’s health to 0 to win!</p>
                <button @click="showTutorial = false"
                    class="bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg shadow-lg transition duration-300">Close</button>
            </div>
        </div>

        <!-- Error Messages -->
        @if ($errorMessage)
            <div class="bg-red-600 p-4 rounded-lg mb-6 shadow-lg animate-pulse">
                <p class="text-lg font-bold">{{ $errorMessage }}</p>
            </div>
        @endif

        <!-- Debug Output -->
        <div class="mb-4 text-gray-300">
            Debug: Current Player ID: {{ $game->current_player_id }} | Auth ID: {{ Auth::id() }}
        </div>

        @if ($game->status === 'finished')
            <div class="bg-green-600 p-6 rounded-lg mb-6 shadow-lg animate-bounce">
                <p class="text-2xl font-bold">Game Over! Winner:
                    {{ $game->winner_id == Auth::id() ? 'You' : 'Opponent' }}</p>
                <div class="mt-4 space-x-4">
                    <a href="{{ route('game.create') }}"
                        class="inline-block bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg shadow-lg hover:shadow-blue-500/50 transition duration-300 transform hover:scale-105">New
                        Game</a>
                    <a href="{{ route('home') }}"
                        class="inline-block bg-gray-600 hover:bg-gray-700 px-6 py-3 rounded-lg shadow-lg hover:shadow-gray-500/50 transition duration-300 transform hover:scale-105">Home</a>
                </div>
            </div>
        @else
            <div class="mb-6">
                <p class="text-xl text-gray-300">Current Turn:
                    {{ $game->current_player_id == Auth::id() ? 'Your Turn' : 'Opponent\'s Turn' }}</p>
            </div>

            <!-- Player Health and Stats -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                @foreach ($game->players as $player)
                    <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
                        <p class="text-lg font-bold">{{ $player->user_id == Auth::id() ? 'You' : 'Opponent' }}</p>
                        <div class="w-full bg-gray-700 rounded-full h-4 mt-2 overflow-hidden">
                            <div class="bg-blue-600 h-4 rounded-full transition-all duration-500"
                                style="width: {{ $player->health * 10 }}%" x-data x-init="$el.classList.add('animate-pulse')"></div>
                        </div>
                        <p class="mt-2 text-gray-300">Health: {{ $player->health }}</p>
                        @if ($player->user_id == Auth::id())
                            <div class="mt-4 text-sm text-gray-400">
                                <p>Attacks: {{ $stats['attacks'] }}</p>
                                <p>Shields: {{ $stats['shields'] }}</p>
                                <p>Swaps: {{ $stats['swaps'] }}</p>
                                <p>Reveals: {{ $stats['reveals'] }}</p>
                                <p>Mimics: {{ $stats['mimics'] }}</p>
                                <p>Damage Dealt: {{ $stats['damage_dealt'] }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Echo Slots -->
            <div class="grid grid-cols-2 gap-6">
                @foreach ($game->players as $player)
                    <div>
                        <h2
                            class="text-2xl font-bold mb-4 {{ $player->user_id == Auth::id() ? 'text-blue-400' : 'text-red-400' }}">
                            {{ $player->user_id == Auth::id() ? 'Your Echoes' : 'Opponent\'s Echoes' }}
                        </h2>
                        <div class="grid grid-cols-5 gap-3">
                            @foreach ($game->echoSlots->where('player_id', $player->id)->sortBy('position') as $slot)
                                <div wire:click="{{ $player->user_id == Auth::id() ? 'selectSlot(' . $slot->id . ')' : 'setTarget(' . $slot->id . ')' }}"
                                    class="p-4 bg-gray-700 rounded-lg cursor-pointer {{ in_array($slot->id, $selectedSlots) ? 'border-2 border-blue-500 shadow-blue-500/50' : '' }} {{ $targetSlot == $slot->id ? 'border-2 border-red-500 shadow-red-500/50' : '' }} hover:shadow-lg transition duration-200 transform hover:scale-105 relative"
                                    x-show="{{ $player->user_id == Auth::id() ? 'showActions' : 'true' }}"
                                    x-transition.scale x-data="{ flipped: false }" @click="flipped = !flipped"
                                    :class="flipped ? 'rotate-y-180' : ''"
                                    style="transition: transform 0.5s; transform-style: preserve-3d;">
                                    <!-- Action Animation -->
                                    @if ($lastAction && in_array($slot->id, $selectedSlots))
                                        <div
                                            class="absolute inset-0 z-20 animate-ping bg-{{ ((($lastAction == 'attack' ? 'red' : $lastAction == 'shield') ? 'blue' : $lastAction == 'swap') ? 'green' : $lastAction == 'reveal') ? 'yellow' : 'purple' }}-500 opacity-50 rounded-lg">
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 backface-hidden" :class="flipped ? 'hidden' : ''">
                                        {{ $player->user_id == Auth::id() ? $slot->action : '???' }}
                                    </div>
                                    <div class="absolute inset-0 backface-hidden bg-gray-600 rounded-lg flex items-center justify-center"
                                        :class="flipped ? '' : 'hidden'">
                                        <div
                                            class="w-12 h-12 {{ ((($slot->action == 'attack' ? 'bg-red-500' : $slot->action == 'shield') ? 'bg-blue-500' : $slot->action == 'swap') ? 'bg-green-500' : $slot->action == 'reveal') ? 'bg-yellow-500' : 'bg-purple-500' }} rounded-full flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($slot->action, 0, 1)) }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($game->current_player_id == Auth::id())
                <button wire:click="submitTurn"
                    class="mt-6 bg-blue-600 hover:bg-blue-700 px-6 py-3 rounded-lg shadow-lg hover:shadow-blue-500/50 transition duration-300 transform hover:scale-105">
                    Submit Turn
                </button>
            @endif
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/howler@2.2.3/dist/howler.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('gameBoard', () => ({
                showActions: false,
                musicMuted: false,
                showTutorial: false,
                sounds: {
                    attack: new Howl({
                        src: ['/sounds/attack.mp3'],
                        volume: 0.5
                    }),
                    shield: new Howl({
                        src: ['/sounds/shield.mp3'],
                        volume: 0.5
                    }),
                    swap: new Howl({
                        src: ['/sounds/swap.mp3'],
                        volume: 0.5
                    }),
                    reveal: new Howl({
                        src: ['/sounds/reveal.mp3'],
                        volume: 0.5
                    }),
                    mimic: new Howl({
                        src: ['/sounds/mimic.mp3'],
                        volume: 0.5
                    })
                },
                initAudio() {
                    console.log("Audio initialized.");
                },
                toggleMusic() {
                    this.musicMuted = !this.musicMuted;
                    Howler.mute(this.musicMuted);
                }
            }));

        });

        particlesJS('particles-js', {
            particles: {
                number: {
                    value: 80,
                    density: {
                        enable: true,
                        value_area: 800
                    }
                },
                color: {
                    value: ['#00f7ff', '#ff00ff', '#00ff7f']
                },
                shape: {
                    type: 'circle',
                    stroke: {
                        width: 0
                    }
                },
                opacity: {
                    value: 0.5,
                    random: true
                },
                size: {
                    value: 3,
                    random: true
                },
                line_linked: {
                    enable: true,
                    distance: 150,
                    color: '#00f7ff',
                    opacity: 0.4,
                    width: 1
                },
                move: {
                    enable: true,
                    speed: 2,
                    direction: 'none',
                    random: false,
                    straight: false,
                    out_mode: 'out'
                }
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: {
                        enable: true,
                        mode: 'repulse'
                    },
                    onclick: {
                        enable: true,
                        mode: 'push'
                    },
                    resize: true
                },
                modes: {
                    repulse: {
                        distance: 100,
                        duration: 0.4
                    },
                    push: {
                        particles_nb: 4
                    }
                }
            },
            retina_detect: true
        });
    </script>

    <style>
        .backface-hidden {
            backface-visibility: hidden;
        }

        .rotate-y-180 {
            transform: rotateY(180deg);
        }
    </style>
</div>
