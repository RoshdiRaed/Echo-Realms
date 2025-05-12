<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EchoSlot extends Model
{
    protected $fillable = ['game_id', 'player_id', 'action', 'position'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
