<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = ['game_id', 'user_id', 'health'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function echoSlots()
    {
        return $this->hasMany(EchoSlot::class);
    }
}
