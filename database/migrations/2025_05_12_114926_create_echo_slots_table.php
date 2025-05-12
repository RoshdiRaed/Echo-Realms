<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEchoSlotsTable extends Migration
{
    public function up()
    {
        Schema::create('echo_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->unsignedBigInteger('player_id');
            $table->string('action');
            $table->integer('position');
            $table->timestamps();
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('echo_slots');
    }
}
