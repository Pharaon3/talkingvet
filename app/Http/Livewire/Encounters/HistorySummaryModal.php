<?php

namespace App\Http\Livewire\Encounters;

use Livewire\Component;

class HistorySummaryModal extends Component
{
    public $history_summary;
    public function close_modal(){
        $data = [
            'type' => 'close-modal',
            'attributes' => []
        ];
        $this->emit('modal_updated', $data);
    }

    public function mount($history_summary){
        $this->history_summary = process_history_summary($history_summary);
    }

    public function render()
    {
        return view('livewire.encounters.history-summary-modal',[
            'history_summary' => $this->history_summary
        ]);
    }
}
