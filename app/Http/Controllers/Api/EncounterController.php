<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EncounterResource;
use App\Http\Resources\RecordingResource;
use App\Models\Prompt;
use App\Services\EncounterService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EncounterController extends Controller
{
    protected $encounter_service;

    public function __construct(EncounterService $encounter_service)
    {
        $this->encounter_service = $encounter_service;
    }

    public function encounter_list(Request $request){
        try {
            $authenticated_user = Auth::guard('sanctum')->user();
            $authenticated_user_role = internal_api_get_user_role($authenticated_user->id, $authenticated_user->organization_id);

            if (internal_api_is_user_admin($authenticated_user_role)) {
                $encounters = $this->encounter_service->get_all_encounters();
            } else {
                $encounters = $this->encounter_service->get_encounters_for_master($authenticated_user);
            }

            // Convert $encounters to a collection and add the number_of_recordings field
            $encounters = collect($encounters)->map(function ($encounter) {
                $encounter['number_of_recordings'] = internal_api_get_recording_count($encounter['id']);
                return $encounter;
            });

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new EncounterResource($encounters)
            ], 200);
        } catch (\Exception $e) {
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function encounter_list_by_organization(Request $request, $organization_id){
        try{
            $authenticated_user = Auth::guard('sanctum')->user();

            $encounters = $this->encounter_service->get_encounters_by_organization($organization_id, $authenticated_user);

            // Convert $encounters to a collection and add the number_of_recordings field
            $encounters = collect($encounters)->map(function ($encounter) {
                $encounter['number_of_recordings'] = internal_api_get_recording_count($encounter['id']);
                return $encounter;
            });
            return response()->json([
                'statusMessage' => 'Success',
                'data' => new EncounterResource($encounters)
            ], 200);
        }
        catch (\Exception $e){
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function encounter_list_by_user(Request $request, $user_id){
        try{
            $authenticated_user = Auth::guard('sanctum')->user();
            $encounters = $this->encounter_service->get_encounters_by_user($user_id, $authenticated_user);

            // Convert $encounters to a collection and add the number_of_recordings field
            $encounters = collect($encounters)->map(function ($encounter) {
                $encounter['number_of_recordings'] = internal_api_get_recording_count($encounter['id']);
                return $encounter;
            });
            
            return response()->json([
                'statusMessage' => 'Success',
                'data' => new EncounterResource($encounters)
            ], 200);
        }
        catch (\Exception $e){
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function create(Request $request)
    {
        try {
            $rules = [
                'organization_id' => 'sometimes|int|exists:organizations,id',
                'default_prompt_id' => 'sometimes|int|exists:prompts,id',
                'identifier' => 'required|string|max:255',
                'notes' => 'required|string',
                'encounter_date' => 'required|date',
                'status' => 'sometimes|int|in:0,1,2',
                'pdf_files' => 'sometimes|array',
                'pdf_files.*' => 'file|mimes:pdf|max:10240'
            ];

            //$messages = [
            //    'organization_id.exists' => 'The specified organization does not exist.',
            //    'default_prompt_id.exists' => 'The specified prompt does not exist.',
            //];

            $validated = $request->validate($rules);

            $validated['organization_id'] = $validated['organization_id'] ?? null;
            $validated['default_prompt_id'] = $validated['default_prompt_id'] ?? null;
            $validated['status'] = $validated['status'] ?? null;

        } catch (Exception $e) {
            return response()->json([
                'statusMessage' => 'Bad Request - ' . $e->getMessage()
            ], 400);
        }
        try {
            $authenticated_user = Auth::guard('sanctum')->user();

            if($request->has('pdf_files')){
                $unique_folder = Str::uuid()->toString();
                $upload_path = 'pdf_files/' . $unique_folder; // Using relative path to start with

                Storage::disk('public')->makeDirectory($upload_path);

                $uploaded_files = $request->file('pdf_files');
                $file_paths = [];

                foreach ($uploaded_files as $file){
                    if ($file->isValid()) {
                        $filename = uniqid() . '_' . Str::snake($file->getClientOriginalName());
                        // Use Storage facade to store the file and get the path
                        $path = $file->storeAs($upload_path, $filename, 'public');
                        $file_paths[] = Storage::disk('public')->path($path); // Store the absolute path of each file
                    } else {
                        session()->flash('error', 'File is not valid: ' . $file->getClientOriginalName());
                    }
                }
                $validated['upload_path'] = $upload_path;
            }
            $new_encounter = $this->encounter_service->create_encounter($validated, $authenticated_user);

            return response()->json([
                'statusMessage' => 'Created - new encounter successfully added.',
                'data' => new EncounterResource($new_encounter)
            ], 201);

        } catch (Exception $e) {
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function update(Request $request){
        try {
            $rules = [
                'id' => 'required|int|exists:encounters,id',
                'organization_id' => 'sometimes|int|exists:organizations,id',
                'default_prompt_id' => 'sometimes|int|exists:prompts,id',
                'identifier' => 'sometimes|string|max:255',
                'notes' => 'sometimes|string',
                'encounter_date' => 'sometimes|date',
                'status' => 'sometimes|int|in:0,1,2',
                'pdf_files' => 'sometimes|array',
                'pdf_files.*' => 'file|mimes:pdf|max:10240'
            ];

            //$messages = [
            //    'id.exists' => 'The specified encounter does not exist.',
            //    'organization_id.exists' => 'The specified organization does not exist.',
            //    'default_prompt_id.exists' => 'The specified prompt does not exist.',
            //];

            $validated = $request->validate($rules);

        } catch (Exception $e) {
            return response()->json([
                'statusMessage' => 'Bad Request - ' . $e->getMessage()
            ], 400);
        }

        try {
            $authenticated_user = Auth::guard('sanctum')->user();
            $updated_encounter = $this->encounter_service->update_encounter($validated, $authenticated_user);

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new EncounterResource($updated_encounter)
            ], 200);
        } catch (Exception $e) {
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function update_status(Request $request){
        try {
            $rules = [
                'id' => 'required|int|exists:encounters,id',
                'organization_id' => 'required|int|exists:organizations,id',
                'status' => 'required|int|in:0,1,2,3'
            ];

            $validated = $request->validate($rules);

        } catch (Exception $e) {
            return response()->json([
                'statusMessage' => 'Bad Request - ' . $e->getMessage()
            ], 400);
        }

        try {
            $authenticated_user = Auth::guard('sanctum')->user();
            $updated_encounter = $this->encounter_service->update_encounter_status($validated, $authenticated_user);

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new EncounterResource($updated_encounter)
            ], 200);
        } catch (Exception $e) {
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function add_recording(Request $request){
        try{
            $validated = $request->validate([
                'id' => 'required|int|exists:encounters,id',
                'recording' => 'required|file|mimetypes:audio/wav,audio/x-wav,audio/x-m4a|max:10240',
                'seconds' => 'required|int'
            ]);
        }
        catch (Exception $e){
            return response()->json([
                'statusMessage' => 'Bad Request - ' . $e->getMessage()
            ],400);
        }
        try{
            $authenticated_user = Auth::guard('sanctum')->user();
            $new_recording = $this->encounter_service->add_recording_to_encounter($validated, $authenticated_user);

            return response()->json([
                'statusMessage' => 'Created - recording added successfully.',
                'data' => new RecordingResource($new_recording)
            ], 201);
        }
        catch (Exception $e){
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function view(Request $request, $id){
        try{
            $authenticated_user = Auth::guard('sanctum')->user();
            $encounter = $this->encounter_service->view_encounter($id, $authenticated_user);

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new EncounterResource($encounter)
            ], 200);
        }
        catch (\Exception $e){
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }
}
