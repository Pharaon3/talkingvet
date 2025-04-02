<?php

namespace App\Http\Livewire\Encounters;

use Livewire\Component;

class MicrophoneTestModal extends Component
{
    public $mic_available;
    public function close_modal(){
        $data = [
            'type' => 'close-modal',
            'attributes' => []
        ];
        $this->emit('modal_updated', $data);
    }

    public function test_mic(){
        $this->dispatchBrowserEvent('test-mic-requested'); // Dispatch the event
    }

    public function request_mic(){
        $this->dispatchBrowserEvent('enable-mic-requested'); // Dispatch the event
    }

    public function mount($mic_available){
        $this->mic_available = $mic_available;
    }

    public function render()
    {
        return view('livewire.encounters.microphone-test-modal');
    }
}
