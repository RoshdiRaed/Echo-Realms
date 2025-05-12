<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Player;
use App\Models\EchoSlot;
use App\Models\Profile;
use App\Models\GameStat;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GameBoard extends Component
{
    public $game;
    public $selectedSlots = [];
    public $targetSlot = null;
    public $lastAction = null;
    public $showActions = true;
    public $stats = ['attacks' => 0, 'shields' => 0, 'swaps' => 0, 'reveals' => 0, 'mimics' => 0, 'damage_dealt' => 0];
    public $musicMuted = false;
    public $errorMessage = null;

    public function mount($gameId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        $this->game = Game::with('players', 'echoSlots')->findOrFail($gameId);
        if (!$this->game->players->pluck('user_id')->contains(Auth::id())) {
            $this->errorMessage = 'You are not a participant in this game.';
            return;
        }
        $this->loadStats();
    }

    public function selectSlot($slotId)
    {
        if (count($this->selectedSlots) < 2 && !in_array($slotId, $this->selectedSlots)) {
            $this->selectedSlots[] = $slotId;
        }
    }

    public function setTarget($slotId)
    {
        $this->targetSlot = $slotId;
    }

    public function toggleMusic()
    {
        $this->musicMuted = !$this->musicMuted;
        $this->dispatch('toggleMusic', muted: $this->musicMuted);
    }

    public function submitTurn()
    {
        $this->errorMessage = null;

        if ($this->game->current_player_id != Auth::id()) {
            $this->errorMessage = 'It is not your turn.';
            return;
        }

        if (count($this->selectedSlots) !== 2) {
            $this->errorMessage = 'Please select exactly two slots.';
            return;
        }

        $currentPlayer = $this->game->players()->where('user_id', Auth::id())->first();
        $opponent = $this->game->players()->where('user_id', '!=', Auth::id())->first();

        foreach ($this->selectedSlots as $slotId) {
            $slot = EchoSlot::where('player_id', $currentPlayer->id)->where('id', $slotId)->first();
            if (!$slot) {
                $this->errorMessage = 'Invalid slot selected.';
                return;
            }

            if ($slot->action === 'attack' && $this->targetSlot) {
                $target = EchoSlot::where('player_id', $opponent->id)->where('id', $this->targetSlot)->first();
                if ($target && $target->action !== 'shield') {
                    $opponent->health -= 2;
                    $opponent->save();
                    $this->stats['attacks']++;
                    $this->stats['damage_dealt'] += 2;
                    $this->dispatch('playSound', sound: 'attack');
                } else {
                    $this->stats['shields']++;
                    $this->dispatch('playSound', sound: 'shield');
                }
                $this->lastAction = 'attack';
            } elseif ($slot->action === 'swap' && $this->targetSlot) {
                $target = EchoSlot::where('player_id', $currentPlayer->id)->where('id', $this->targetSlot)->first();
                if ($target) {
                    $temp = $slot->position;
                    $slot->position = $target->position;
                    $target->position = $temp;
                    $slot->save();
                    $target->save();
                    $this->stats['swaps']++;
                    $this->dispatch('playSound', sound: 'swap');
                }
                $this->lastAction = 'swap';
            } elseif ($slot->action === 'reveal' && $this->targetSlot) {
                $target = EchoSlot::where('player_id', $opponent->id)->where('id', $this->targetSlot)->first();
                if ($target) {
                    $this->stats['reveals']++;
                    $this->dispatch('playSound', sound: 'reveal');
                }
                $this->lastAction = 'reveal';
            } elseif ($slot->action === 'mimic' && $this->lastAction) {
                if ($this->lastAction === 'attack' && $this->targetSlot) {
                    $target = EchoSlot::where('player_id', $opponent->id)->where('id', $this->targetSlot)->first();
                    if ($target && $target->action !== 'shield') {
                        $opponent->health -= 2;
                        $opponent->save();
                        $this->stats['mimics']++;
                        $this->stats['damage_dealt'] += 2;
                        $this->dispatch('playSound', sound: 'mimic');
                    }
                }
                $this->lastAction = 'mimic';
            }
        }

        if ($opponent->health <= 0) {
            $this->game->update(['status' => 'finished', 'winner_id' => $currentPlayer->id]);
            $this->awardPoints($currentPlayer, true);
            $this->awardPoints($opponent, false);
        } else {
            $this->game->update(['current_player_id' => $opponent->id]);
        }

        $this->saveStats();
        $this->selectedSlots = [];
        $this->targetSlot = null;
        $this->game->refresh();
    }

    protected function loadStats()
    {
        $stat = GameStat::where('game_id', $this->game->id)->where('user_id', Auth::id())->first();
        if ($stat) {
            $this->stats = json_decode($stat->stats, true);
        }
    }

    protected function saveStats()
    {
        GameStat::updateOrCreate(
            ['game_id' => $this->game->id, 'user_id' => Auth::id()],
            ['stats' => json_encode($this->stats)]
        );
    }

    protected function awardPoints($player, $isWinner)
    {
        $profile = Profile::firstOrCreate(['user_id' => $player->user_id]);
        $points = $isWinner ? 100 : 20;
        $points += $this->stats['attacks'] * 10 + $this->stats['swaps'] * 5 + $this->stats['reveals'] * 15 + $this->stats['mimics'] * 20;
        $profile->points += $points;
        $profile->games_played += 1;
        if ($isWinner) {
            $profile->wins += 1;
        }
        $profile->save();
    }

    public function render()
    {
        return view('livewire.game-board', [
            'stats' => $this->stats,
            'errorMessage' => $this->errorMessage
        ]);
    }
}
