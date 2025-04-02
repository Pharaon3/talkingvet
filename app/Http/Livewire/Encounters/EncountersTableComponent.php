<?php

namespace App\Http\Livewire\Encounters;

use App\Models\Encounter;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\EncounterService;

class EncountersTableComponent extends Component
{
    const MODAL_NONE = 0;
    const MODAL_NEW_ENCOUNTER = 1;
    const MODAL_RECORD_ENCOUNTER = 2;
    const MODAL_HISTORY_SUMMARY = 3;
    const MODAL_MICROPHONE_TEST = 4;

    protected $listeners = ['modal_updated' => 'handle_modal_update', 'microphone_test' => 'handle_microphone_test'];
    protected $encounter_service;

    public $current_modal_status = self::MODAL_NONE;
    public $mic_available;

    public $search_term;
    public $search_status;
    public $encounter_list;
    public $selected_encounters = [];
    public $selected_status;

    public $current_page = 1;
    public $per_page = 10;
    public $total_records = 0;

    public $record_param;

    public $history_summary;

    protected function get_encounter_service(){
        return app(EncounterService::class);
    }

    public function refresh(){
        $this->encounter_list = $this->filter_encounters();
    }

    private function filter_encounters()
    {
        $authenticated_user = Auth::guard('internal-auth-guard')->user();
        $authenticated_user_role = internal_api_get_user_role($authenticated_user->id, $authenticated_user->organization_id);

        $encounter_service = $this->get_encounter_service();
        $all_encounters = internal_api_is_user_admin($authenticated_user_role)
            ? $encounter_service->get_all_encounters()
            : $encounter_service->get_encounters_for_master($authenticated_user);

        $filtered_encounters = collect($all_encounters)->filter(function ($encounter) {
            $matches_search = true;
            $matches_status = true;

            // Search filter
            if (!empty($this->search_term)) {
                $matches_search =
                    stripos($encounter['identifier'], $this->search_term) !== false ||
                    stripos($encounter['notes'], $this->search_term) !== false ||
                    stripos($encounter['encounter_id'], $this->search_term) !== false;
            }

            // Status filter
            if ($this->search_status !== null && $this->search_status !== "") {
                $matches_status = $encounter['status'] == $this->search_status;
            }

            return $matches_search && $matches_status;
        });

        // Count filtered items
        $this->total_records = $filtered_encounters->count();

        // Pagination logic (using Collection's `slice` method)
        $offset = ($this->current_page - 1) * $this->per_page;
        $paginated_encounters = $filtered_encounters->slice($offset, $this->per_page)->values();

        // Convert to array for use in the view
        return $paginated_encounters->all();
    }


    public function updating($property_name){
        if(in_array($property_name, ['search_term', 'search_status'])){
            $this->reset_page();
        }
    }

    public function reset_page(){
        $this->current_page = 1;
    }

    public function go_to_previous_page(){
        if($this->current_page > 1){
            $this->current_page--;
        }
    }

    public function go_to_next_page(){
        if($this->current_page < ceil($this->total_records / $this->per_page)){
            $this->current_page++;
        }
    }

    public function show_modal($modal_type){
        $this->dispatchBrowserEvent('modal-loaded'); // Dispatch the event
        $this->current_modal_status = $modal_type;
    }

    public function hide_modal(){
        $this->current_modal_status = self::MODAL_NONE;
    }

    public function handle_modal_update($data){
        $type = $data['type'];
        switch ($type){
            case 'close-modal':
                $this->hide_modal();
                break;
            case 'open-record-modal':
                $this->show_modal(self::MODAL_RECORD_ENCOUNTER);
                break;
            case 'save-clear-new-encounter-modal':
                $this->show_modal(self::MODAL_NEW_ENCOUNTER);
                break;
            case 'save-record-close-modal':
                $this->hide_modal();
                break;
            default:
                $this->hide_modal();
                break;
        }
    }

    public function handle_microphone_test($data){
        $type = $data['type'];

        if($type == 'microphone_disabled'){
            $this->show_modal(self::MODAL_MICROPHONE_TEST);
        }
        $this->mic_available = $data['status'];
    }

    public function handle_tr_db_click($encounter_id){
        $selected_encounter = Encounter::find($encounter_id);
        $this->record_param = [
            "record_identifier" =>  $selected_encounter->identifier,
            "record_notes" =>  $selected_encounter->notes,
            "encounter_id" =>  $encounter_id,
        ];

        $this->current_modal_status = self::MODAL_RECORD_ENCOUNTER;
    }

    public function handle_history_summary_click($modal_type, $encounter_id){
        $selected_encounter = Encounter::find($encounter_id);
        $this->history_summary = $selected_encounter->history_summary;

        $this->current_modal_status = self::MODAL_HISTORY_SUMMARY;
    }

    public function update_encounters_status(){
        foreach ($this->selected_encounters as $selected_encounter_id){
            $selected_encounter = Encounter::find($selected_encounter_id);
            if($selected_encounter){
                $selected_encounter->status = (int) $this->selected_status;
                $selected_encounter->save();
            }
        }
    }

    public function mount(EncounterService $encounter_service)
    {
        $this->encounter_service = $encounter_service;
        $this->mic_available = true;
//        $this->sortBy($this->sortColumn); // sort at start
    }

    public function render()
    {
        return view('livewire.encounters.encounters-table-component', [
            'MODAL_NONE' => self::MODAL_NONE,
            'MODAL_NEW_ENCOUNTER' => self::MODAL_NEW_ENCOUNTER,
            'MODAL_RECORD_ENCOUNTER' => self::MODAL_RECORD_ENCOUNTER,
            'MODAL_HISTORY_SUMMARY' => self::MODAL_HISTORY_SUMMARY,
            'MODAL_MICROPHONE_TEST' => self::MODAL_MICROPHONE_TEST,
            'current_modal_status' => $this->current_modal_status,
            'mic_available' => $this->mic_available,
            'encounters' => $this->filter_encounters(),
            'record_param' => $this->record_param,
            'history_summary' => $this->history_summary
        ]);
    }
}
