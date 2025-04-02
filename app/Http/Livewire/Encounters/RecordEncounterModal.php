<?php

namespace App\Http\Livewire\Encounters;

use App\Models\User;
use App\Services\EncounterService;
use App\Models\Encounter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Recording;
use App\Models\Enums\GenAIRequestState;
use App\Models\GenAiInternalRequest;
use App\Jobs\ProcessEncounterTranscription;

class RecordEncounterModal extends Component
{
    use WithFileUploads;

    public $practitioner;
    public $identifier;
    public $notes;
    public $prompts;
    public $consent;
    public $upload_audio;
    public $id_of_encounter;
    public $activeMicrophone;
    public $recorded_audio;

    public function uploadAudio($base64Audio)
    {
        try {
            Log::debug("[Record Visit] - Should be uploading audio from here");
            $audioData = base64_decode($base64Audio);
            $fileName = 'audio-recordings/' . uniqid() . '.wav';
            Storage::disk('local')->put($fileName, $audioData);
            $this->recorded_audio = $fileName;
            
            // Flash success message
            session()->flash('message', 'Audio uploaded successfully!');
        } catch (\Exception $e) {
            Log::error("[Record Visit] - Failed to upload audio: " . $e->getMessage());
            
            // Flash error message
            session()->flash('error', 'Failed to upload audio. Please try again.');
        }
    }    
    
    public function updatedUploadAudio()
{
    Log::debug('updatedUploadAudio triggered with file: ' . ($this->upload_audio ? $this->upload_audio->getClientOriginalName() : 'No file'));

    // Validate the uploaded file
    $this->validate([
        'upload_audio' => 'file|max:20480', // Max size is 20MB
    ]);

    Log::debug('Validation passed for file: ' . $this->upload_audio->getClientOriginalName());

}
    protected function get_encounter_service(){
        return app(EncounterService::class);
    }

    public function save_and_close()
    {
        $data = [
            'type' => 'save-record-close-modal',
            'attributes' => []
        ];

        $encounter_service = $this->get_encounter_service();
        $current_encounter = Encounter::find($this->id_of_encounter);
        if(!$current_encounter){
            throw new Exception('Not Found - non-existing encounter', 404);
        }
        if (isset($this->upload_audio)) {
            $encounter_service->add_recording_to_encounter([
                "id"=>$current_encounter->id,
                "recording"=>$this->upload_audio,
                "seconds"=>10,
            ], Auth::guard('internal-auth-guard')->user()->id);
        } else if (isset($this->recorded_audio)) {
            $encounter_service->add_recording_to_encounter([
                "id"=>$current_encounter->id,
                "recorded_audio"=>$this->recorded_audio,
                "seconds"=>10,
            ], Auth::guard('internal-auth-guard')->user()->id);
        }
        $this->emit('modal_updated', $data);
    }

    public function close_modal()
    {
        $data = [
            'type' => 'close-modal',
            'attributes' => []
        ];
        $this->emit('modal_updated', $data);
    }

    public function updateMicrophone($microphone)
    {
        $this->activeMicrophone = $microphone;
        session(['selectedMicrophone' => $microphone]);
    }

    public function mount($record_param)
    {
        $this->identifier = $record_param['record_identifier'];
        $this->notes = $record_param['record_notes'];
        $this->id_of_encounter = $record_param['encounter_id'];
        $this->activeMicrophone = session('selectedMicrophone', 'Default Microphone');

        // Get the authenticated user using Sanctum
        $authenticated_user = Auth::guard('sanctum')->user();

        if ($authenticated_user) {
            // Set the default practitioner to the logged-in user's ID
            $this->practitioner = $authenticated_user->id;
        }
    }

    public function render()
    {
        $authenticated_user = Auth::guard('internal-auth-guard')->user();
        $practitioners = User::where('organization_id', $authenticated_user->organization_id)
            ->whereHas('permissions', function ($query) {
                $query->whereNotIn('role', [2,9]); // Exclude users with role 2 or 9 which is ADMIN_ROLE IR CLERICAL
            })
            ->get();
        return view('livewire.encounters.record-encounter-modal', [
            'practitioners' => $practitioners,
            'practitioner' => $this->practitioner,
            'identifier' => $this->identifier,
            'notes' => $this->notes,
            'prompts' => $this->prompts,
            'consent' => $this->consent,
            'upload_audio' => $this->upload_audio,
            'authenticated_user' => $authenticated_user->id
        ]);
    }

    public function removeFile($index)
    {
        if (is_array($this->upload_audio)) {
            unset($this->upload_audio[$index]);
            $this->upload_audio = array_values($this->upload_audio); // Reindex the array
        } else {
            $this->upload_audio = null; // Clear the single file
        }
    }
}
