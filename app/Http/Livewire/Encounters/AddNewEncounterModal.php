<?php

namespace App\Http\Livewire\Encounters;
use App\Services\EncounterService;
use App\Services\PromptService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

use Livewire\Component;

class AddNewEncounterModal extends Component
{
    use WithFileUploads;

    public $identifier;
    public $notes;
    public $encounter_date;
    public $prompt;
    public $pdf_files = [];

    protected $rules = [
        'identifier' => 'required|string|max:255',
        'notes' => 'required|string',
        'prompt' => 'required',
        'encounter_date' => 'required|date',
        'pdf_files.*' => 'nullable|file|mimes:pdf|max:10240', // 10MB max per file
    ];

    protected $messages = [
        'identifier.required' => 'The identifier field is required.',
        'notes.required' => 'The notes field is required.',
        'prompt.required' => 'Please select a prompt template.',
        'encounter_date.required' => 'Please specify the encounter date.',
        'pdf_files.*.mimes' => 'Only PDF files are allowed.',
        'pdf_files.*.max' => 'PDF file size should not exceed 10MB.',
    ];

    protected function get_encounter_service(){
        return app(EncounterService::class);
    }

    protected function get_prompt_service(){
        return app(PromptService::class);
    }

    public function close_modal(){
        $data = [
          'type' => 'close-modal',
          'attributes' => []
        ];
        $this->emit('modal_updated', $data);
    }

    public function save_and_record()
    {
        $this->validate();

        $encounter_service = $this->get_encounter_service();
        $authenticated_user = Auth::guard('internal-auth-guard')->user();

        $validated_data = [
            'identifier' => $this->identifier,
            'notes' => $this->notes,
            'encounter_date' => $this->encounter_date,
            'default_prompt_id' => $this->prompt,
            'pdf_files' => [], // Initialize as an empty array; we'll populate it if there are files
            'organization_id' => $authenticated_user->organization_id,
            'status' => 0
        ];

        if (!empty($this->pdf_files)) {
            $unique_folder = Str::uuid()->toString();
            $upload_path = 'pdf_files/' . $unique_folder; // Using relative path to start with

            // Ensure the directory exists using Storage facade
            Storage::disk('public')->makeDirectory($upload_path);

            $file_paths = [];
            foreach ($this->pdf_files as $file) {
                if ($file->isValid()) {
                    $filename = uniqid() . '_' . Str::snake($file->getClientOriginalName());
                    // Use Storage facade to store the file and get the path
                    $path = $file->storeAs($upload_path, $filename, 'public');
                    $file_paths[] = Storage::disk('public')->path($path); // Store the absolute path of each file
                } else {
                    session()->flash('error', 'File is not valid: ' . $file->getClientOriginalName());
                    return;
                }
            }

            // Store the absolute folder path
            $absolute_upload_path = Storage::disk('public')->path($upload_path);
            $validated_data['upload_path'] = $absolute_upload_path; // Store absolute upload folder path
            // Stores file information as absolute paths
            $validated_data['pdf_files'] = $file_paths;
        }

        $created_encounter = $encounter_service->create_encounter($validated_data, $authenticated_user);

        $data = [
            'type' => 'open-record-modal',
            'attributes' => [
                'record_param' => [
                    "record_identifier" => $this->identifier,
                    "record_notes" => $this->notes,
                    "encounter_id" => $created_encounter->id
                ]
            ]
        ];
        $this->emit('modal_updated', $data);
    }

    public function save_and_clear(){
        
        $this->validate();

        $encounter_service = $this->get_encounter_service();
        $authenticated_user = Auth::guard('internal-auth-guard')->user();

        $validated_data = [
            'identifier' => $this->identifier,
            'notes' => $this->notes,
            'encounter_date' => $this->encounter_date,
            'default_prompt_id' => $this->prompt,
            'pdf_files' => [], // Initialize as an empty array; we'll populate it if there are files
            'organization_id' => $authenticated_user->organization_id,
            'status' => 0
        ];

        if (!empty($this->pdf_files)) {
            $unique_folder = Str::uuid()->toString();
            $upload_path = 'pdf_files/' . $unique_folder; // Using relative path to start with

            // Ensure the directory exists using Storage facade
            Storage::disk('public')->makeDirectory($upload_path);

            $file_paths = [];
            foreach ($this->pdf_files as $file) {
                if ($file->isValid()) {
                    $filename = uniqid() . '_' . Str::snake($file->getClientOriginalName());
                    // Use Storage facade to store the file and get the path
                    $path = $file->storeAs($upload_path, $filename, 'public');
                    $file_paths[] = Storage::disk('public')->path($path); // Store the absolute path of each file
                } else {
                    session()->flash('error', 'File is not valid: ' . $file->getClientOriginalName());
                    return;
                }
            }

            // Store the absolute folder path
            $absolute_upload_path = Storage::disk('public')->path($upload_path);
            $validated_data['upload_path'] = $absolute_upload_path; // Store absolute upload folder path
            // Stores file information as absolute paths
            $validated_data['pdf_files'] = $file_paths;
        }

        $encounter_service->create_encounter($validated_data, $authenticated_user);

        $this->identifier = ''; // Clear identifier
        $this->notes = '';      // Clear notes
        $this->prompt = null;   // Clear prompt (if it's nullable)
        
        $data = [
            'type' => 'save-clear-new-encounter-modal',
            'attributes' => []
        ];
        $this->emit('modal_updated', $data);
    }

    public function render()
    {
        $authenticated_user = Auth::guard('internal-auth-guard')->user();
        $prompt_service = $this->get_prompt_service();

        // $prompts = $prompt_service->get_prompts_by_organization($authenticated_user->organization_id, $authenticated_user);
        $prompts = $prompt_service->get_prompt_by_user($authenticated_user->id, $authenticated_user);

        return view('livewire.encounters.add-new-encounter-modal', [
            'identifier' => $this->identifier,
            'notes' =>$this->notes,
            'encounter_date' => $this->encounter_date,
            'pdf_files' => $this->pdf_files,
            'prompt' => $this->prompt,
            'prompts' => $prompts
        ]);
    }
}
