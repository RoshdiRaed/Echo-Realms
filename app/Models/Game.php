<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = ['status', 'current_player_id', 'winner_id'];

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function echoSlots()
    {
        return $this->hasMany(EchoSlot::class);
    }
}
