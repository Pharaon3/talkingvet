<?php

namespace App\Http\Livewire;

use Livewire\Component;

class AblePlayer extends Component
{

    public $audio;
    public $playerId;

    public function mount($audio)
    {
        // Generate a unique ID for the player
        $this->playerId = 'audioPlayer_' . uniqid();

        // save starting audio data
        $this->audio = $audio;
    }

    public function render()
    {
        return view('livewire.able-player', ['audio', $this->audio]);
    }
}
