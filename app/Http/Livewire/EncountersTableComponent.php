<?php

namespace App\Http\Livewire;

use Livewire\Component;

class EncountersTableComponent extends Component
{
    public function mount($accounts)
    {
        // store allowed accounts
        $this->accounts = $accounts;
//        $this->sortBy($this->sortColumn); // sort at start
    }
    public function render()
    {
        return view('livewire.encounters-table-component');
    }
}
