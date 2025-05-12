<?php
namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\EchoSlot;
use Illuminate\Support\Facades\Auth;

use Illuminate\Routing\Controller;

class GameController extends Controller // Extend the base Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['create', 'join']);
    }

    public function index()
    {
        $games = Game::where('status', 'waiting')->get();
        return view('games.index', compact('games'));
    }

    public function create()
    {
        $game = Game::create(['status' => 'waiting']);
        $player = Player::create([
            'game_id' => $game->id,
            'user_id' => Auth::id(),
            'health' => 10
        ]);

        $actions = ['attack', 'shield', 'swap', 'reveal', 'mimic'];
        for ($i = 0; $i < 5; $i++) {
            EchoSlot::create([
                'game_id' => $game->id,
                'player_id' => $player->id,
                'action' => $actions[array_rand($actions)],
                'position' => $i
            ]);
        }

        return redirect()->route('game.show', $game->id);
    }

    public function join($id)
    {
        $game = Game::findOrFail($id);
        if ($game->status !== 'waiting' || $game->players()->count() >= 2) {
            return redirect()->route('games.index')->with('error', 'Game is full or started.');
        }

        $player = Player::create([
            'game_id' => $game->id,
            'user_id' => Auth::id(),
            'health' => 10
        ]);

        $actions = ['attack', 'shield', 'swap', 'reveal', 'mimic'];
        for ($i = 0; $i < 5; $i++) {
            EchoSlot::create([
                'game_id' => $game->id,
                'player_id' => $player->id,
                'action' => $actions[array_rand($actions)],
                'position' => $i
            ]);
        }

        $game->update(['status' => 'active', 'current_player_id' => $game->players()->first()->id]);

        return redirect()->route('game.show', $game->id);
    }
}
