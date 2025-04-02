<?php
/**
 * Created by PhpStorm.
 * User: 585
 * Date: 2/25/2025
 * Time: 2:29 PM
 */

namespace App\Services;


use App\Http\Resources\EncounterResource;
use App\Jobs\ProcessEncounterTranscription;
use App\Jobs\ProcessMultispeakerDictation;
use App\Jobs\SummarizePdfJob;
use App\Models\Encounter;
use App\Models\GenAiInternalRequest;
use App\Models\GenAIRequest;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Prompt;
use App\Models\Recording;
use App\Models\SummaryPdfRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use App\Models\Enums\GenAIRequestState;
use App\Models\Enums\SummaryPdfRequestState;
use Illuminate\Support\Facades\Log;

class EncounterService
{
    public function get_all_encounters(){
        return Encounter::all();
    }

    public function get_encounters_for_master($authenticated_user){
        $permissions = Permission::where([
            'user_id' => $authenticated_user->id,
            'role' => MASTER_ACCOUNT_ROLE
        ])->get();

        $encounter_list = [];

        foreach ($permissions as $permission) {
            $encounters = Encounter::where([
                'organization_id' => $permission->organization_id
            ])->get();
            $encounter_list = array_merge($encounter_list, $encounters->toArray());
        }

        return $encounter_list;
    }

    public function get_encounters_by_organization($organization_id, $authenticated_user){
        if (!is_numeric($organization_id) || intval($organization_id) != $organization_id) {
            throw new Exception('Bad Request - id value must be integer', 400);
        }

        $organization = Organization::find($organization_id);

        if (!$organization) {
            throw new Exception('Not Found - non-existing organization.', 404);
        }


        $authenticated_user_role = internal_api_get_user_role($authenticated_user->id, $organization_id);
        if (!internal_api_is_user_request_own_org($authenticated_user->organization_id, $organization_id) && (!internal_api_is_user_admin($authenticated_user_role))) {
            throw new Exception('Forbidden - you don\'t have permission to access this organization.', 403);
        }

        return Encounter::where(['organization_id' => $organization_id])->get();
    }

    // Retrieves all encounters for user in their current organization
    public function get_encounters_by_user($user_id, $authenticated_user){
        if (!is_numeric($user_id) || intval($user_id) != $user_id) {
            throw new Exception('Bad Request - id value must be integer', 400);
        }
        $user = User::find($user_id);

        if (!$user) {
            throw new Exception('Not Found - non-existing user', 404);
        }
        if ($user->organization_id == $authenticated_user->organization_id) {
            return Encounter::where(['created_by' => $user_id])
            ->where('organization_id', $authenticated_user->organization_id)
            ->get();
        } else{
            throw new Exception('Forbidden - you don\'t have permission to access this user\'s encounters.', 403);  
        }
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////// encounter_id is not id of encounter ///////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function create_encounter($validated_data, $authenticated_user){

        if(!isset($validated_data['default_prompt_id'])){
            $system_default_prompt = Prompt::where(['system_default' => true])->first();

            if(!$system_default_prompt){
                throw new Exception('Bad Request - you have to set default prompt id or set system default prompt', 400);
            }
            $validated_data['default_prompt_id'] = $validated_data['default_prompt_id'] ?? $system_default_prompt->id;
        }

        $validated_data['organization_id'] = $validated_data['organization_id'] == null ? $authenticated_user->organization_id : $validated_data['organization_id'];
        $validated_data['status'] = $validated_data['status'] == null ? ENCOUNTER_STATUS_OPEN : $validated_data['status'];



        $authenticated_user_role = internal_api_get_user_role($authenticated_user->id, $validated_data['organization_id']);

        $target_organization = Organization::find($validated_data['organization_id']);

        $today = date('Ymd');

        if(!$target_organization){
            throw new Exception('Not Found - non-existing organization', 404);
        }
        if(!internal_api_is_user_master($authenticated_user_role)){
            throw  new Exception('Forbidden - you don\'t have permission to add encounter for this organization.', 403);
        }

        $last_encounter = Encounter::where('encounter_id', 'like', $today . '%')
            ->where('organization_id', $validated_data['organization_id'])
            ->orderBy('encounter_id', 'desc')
            ->first();

        if($last_encounter){
            $last_number = intval(substr($last_encounter->encounter_id, -4));
            $new_number = str_pad($last_number + 1, 4, '0', STR_PAD_LEFT);
            $new_encounter_id = $today . $new_number;
        }
        else{
            $new_encounter_id = $today . '0001';
        }

        $validated_data['encounter_id'] = $new_encounter_id;
        $validated_data['created_by'] = $authenticated_user->id;
        $validated_data['transcripts'] = "";
        $validated_data['summary'] = "";
        $validated_data['history_summary'] = "";

        $new_encounter = Encounter::create($validated_data);

        if(!empty($validated_data['upload_path'])){
            $summary_pdf_request_data = [
                'encounter_id' =>  $new_encounter->id,
                'pdf_location' => $validated_data['upload_path'],
                'state' => SummaryPdfRequestState::SUMMARY_PDF_REQUEST_STATE_0_INIT
            ];
            ;
            $summary_pdf_request = SummaryPdfRequest::create($summary_pdf_request_data);
            SummarizePdfJob::dispatch($summary_pdf_request);
        }

        return $new_encounter;
    }

    public function update_encounter($validated_data, $authenticated_user){
        $authenticated_user_role = internal_api_get_user_role($authenticated_user->id, $authenticated_user->organization_id);
        $current_encounter = Encounter::find($validated_data['id']);

        if(!$current_encounter){
            throw new Exception('Not Found - non-existing encounter', 404);
        }

        if(isset($validated_data['organization_id'])){
            $target_organization = Organization::find($validated_data['organization_id']);
            if(!$target_organization){
                throw new Exception('Not Found - non-existing organization', 404);
            }
            if($authenticated_user->id != $current_encounter->created_by){
                if(!internal_api_is_user_admin($authenticated_user_role) and internal_api_is_user_both_master($authenticated_user->id, $current_encounter->organization_id, $validated_data['organization_id'])){
                    throw new Exception('Forbidden - you don\'t have permission to modify this organization', 403);
                }
            }
        }
        else{
            $validated_data['organization_id'] = $authenticated_user->organization_id;
        }

        $updated_data = [
            'organization_id' => $validated_data['organization_id'] ?? $current_encounter->organization_id,
            'default_prompt_id' => $validated_data['default_prompt_id'] ?? $current_encounter->default_prompt_id,
            'identifier' => $validated_data['identifier'] ?? $current_encounter->identifier,
            'notes' => $validated_data['notes'] ?? $current_encounter->notes,
            'encounter_date' => $validated_data['encounter_date'] ?? $current_encounter->encounter_date,
            'status' => $validated_data['status'] ?? $current_encounter->status
        ];

        $validated_data['created_by'] = $authenticated_user->id;
        $current_encounter->update($updated_data);

        return $current_encounter;
    }

    public function update_encounter_status($validated_data, $authenticated_user){
        $authenticated_user_role = internal_api_get_user_role($authenticated_user->id, $authenticated_user->organization_id);
        $current_encounter = Encounter::find($validated_data['id']);

        if(!$current_encounter){
            throw new Exception('Not Found - non-existing encounter', 404);
        }

        if(isset($validated_data['organization_id'])){
            $target_organization = Organization::find($validated_data['organization_id']);
            if(!$target_organization){
                throw new Exception('Not Found - non-existing organization', 404);
            }
            if($authenticated_user->id != $current_encounter->created_by){
                if(!internal_api_is_user_admin($authenticated_user_role) and internal_api_is_user_both_master($authenticated_user->id, $current_encounter->organization_id, $validated_data['organization_id'])){
                    throw new Exception('Forbidden - you don\'t have permission to modify this organization', 403);
                }
            }
        }
        else{
            $validated_data['organization_id'] = $authenticated_user->organization_id;
        }

        $updated_data = [
            'status' => $validated_data['status'] ?? $current_encounter->status
        ];

        $validated_data['created_by'] = $authenticated_user->id;
        $current_encounter->update($updated_data);

        return $current_encounter;
    }

    public function add_recording_to_encounter($validated_data, $authenticated_user){
        $current_encounter = Encounter::find($validated_data['id']);
        if(!$current_encounter){
            throw new Exception('Not Found - non-existing encounter', 404);
        }

        if($this->check_maximum_recording($validated_data['id'])){
            $this->handle_failed_recording($validated_data['id'], $validated_data['recording']);
            throw new Exception('Bad Request - maximum length exceed', 400);
        }
        $recording_path = "";
        if(isset($validated_data['recording'])) {
            $recording_path = $this->rename_and_store_recording($current_encounter->organization_id, $validated_data['id'], $validated_data['recording']);
        } else if (isset($validated_data['recorded_audio'])) {
            $recording_path = $validated_data['recorded_audio'];
        }
        $new_recording = Recording::create([
            'encounter_id' => $validated_data['id'],
            'path' => $recording_path,
            'seconds' => $validated_data['seconds']
        ]);

        $current_encounter->status = ENCOUNTER_STATUS_IN_PROGRESS;
        $current_encounter->save();

        $gen_ai_request_data = [
            'encounter_id' =>  $validated_data['id'],
            'local_audio_file' => $recording_path,
            'audio_location' => '',
            'state' => GenAIRequestState::INTERNAL_GEN_AI_REQ_STATE_1_TRX_ID_PARSED
        ];
        ;
        $gen_ai_request = GenAiInternalRequest::create($gen_ai_request_data);


        ProcessEncounterTranscription::dispatch($gen_ai_request);

        return $new_recording;
    }

    public function view_encounter($id_of_encounter, $authenticated_user)
    {
        if (!is_numeric($id_of_encounter) || intval($id_of_encounter) != $id_of_encounter) {
            throw new Exception('Bad Request - id value must be integer', 400);
        }
        $encounter = Encounter::find($id_of_encounter);

        if (!$encounter) {
            throw new Exception('Not Found - non-existing encounter.', 404);
        }

        $authenticated_user_role = internal_api_get_user_role($authenticated_user->id, $encounter->organization_id);

        if (!internal_api_is_user_master($authenticated_user_role) and $authenticated_user->id != $encounter->user_id) {
            throw new Exception('Forbidden - you don\'t have permission to access this encounter.', 403);
        }

        return $encounter;
    }

    public function rename_and_store_recording($organization_id, $id_of_encounter, $recording){
        $random_string = Str::random(8);
        $extension = $recording->getClientOriginalExtension();
        $new_file_name = $organization_id . '_' . $id_of_encounter . '_' . $random_string . '.' . $extension;
        return $recording->storeAs('recordings', $new_file_name);
    }

    private function check_maximum_recording($id_of_encounter){
        $recordings = Recording::where('encounter_id', $id_of_encounter)->get();
        $total_recording = $recordings->count();
        $total_length = $recordings->sum('seconds');

        return $total_recording < MAX_RECORDING_COUNT && $total_length <= MAX_RECORDING_LENGTH;
    }

    private function handle_failed_recording($id_of_encounter, $recording){
        $failed_recording_name = 'FAILED_' . $id_of_encounter . '_' . $recording->getClientOriginalName();
        $recording->storeAs('failed_recordings', $failed_recording_name);
    }
}