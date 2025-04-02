<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromptResource;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Prompt;
use App\Models\User;
use App\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromptController extends Controller
{

    protected $prompt_service;

    public function __construct(PromptService $prompt_service)
    {
        $this->prompt_service = $prompt_service;
    }

    public function prompt_list(Request $request)
    {
        try {
            $authenticated_user = Auth::guard('sanctum')->user();
            $authenticated_user_role = internal_api_get_user_role($authenticated_user->id, $authenticated_user->organization_id);

            if (internal_api_is_user_admin($authenticated_user_role)) {
                $prompts = $this->prompt_service->get_all_prompts();
            } else {
                $prompts = $this->prompt_service->get_prompts_for_master($authenticated_user);
            }

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new PromptResource($prompts)
            ], 200);
        } catch (\Exception $e) {
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function prompt_list_by_organization(Request $request, $organization_id)
    {
        try{
            $authenticated_user = Auth::guard('sanctum')->user();

            $prompts = $this->prompt_service->get_prompts_by_organization($organization_id, $authenticated_user);

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new PromptResource($prompts)
            ], 200);
        }
        catch (\Exception $e){
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function prompt_list_by_user(Request $request, $user_id)
    {
        try{
            $authenticated_user = Auth::guard('sanctum')->user();
            $prompts = $this->prompt_service->get_prompt_by_user($user_id, $authenticated_user);
            return response()->json([
                'statusMessage' => 'Success',
                'data' => new PromptResource($prompts)
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
            $validated = $request->validate([
                'user_id' => 'required|int',
                'name' => 'required|string',
                'prompt' => 'required|string',
                'description' => 'required|string',
                'position' => 'int',
                'is_default' => 'boolean',
                'system_default' => 'boolean'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'statusMessage' => 'Bad Request - ' . $e->getMessage()
            ], 400);
        }

        try {
            $authenticated_user = Auth::guard('sanctum')->user();
            $validated['is_default'] = $validated['is_default'] ?? false;
            $validated['system_default'] = $validated['system_default'] ?? false;

            $new_prompt = $this->prompt_service->create_prompt($validated, $authenticated_user);

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new PromptResource($new_prompt)
            ], 200);

        } catch (\Exception $e) {
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'prompt_id' => 'required|int',
                'user_id' => 'nullable|int',
                'name' => 'nullable|string',
                'prompt' => 'nullable|string',
                'description' => 'nullable|string',
                'position' => 'nullable|string',
                'is_default' => 'nullable|boolean',
                'system_default' => 'nullable|boolean'
            ]);

            $validated = [
                'prompt_id' => $validated['prompt_id'],
                'user_id' => $validated['user_id'] ?? null,
                'name' => $validated['name'] ?? null,
                'prompt' => $validated['prompt'] ?? null,
                'description' => $validated['description'] ?? null,
                'position' => $validated['position'] ?? null,
                'is_default' => $validated['is_default'] ?? null,
                'system_default' => $validated['system_default'] ?? null,
            ];
        } catch (\Exception $e) {
            return response()->json([
                'statusMessage' => 'Bad Request - ' . $e->getMessage()
            ], 400);
        }

        try {
            $authenticated_user = Auth::guard('sanctum')->user();

            $updated_prompt = $this->prompt_service->update_prompt($validated, $authenticated_user);

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new PromptResource($updated_prompt)
            ], 200);
        } catch (\Exception $e) {
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function view(Request $request, $prompt_id){
        try{
            $authenticated_user = Auth::guard('sanctum')->user();
            $prompt = $this->prompt_service->view_prompt($prompt_id, $authenticated_user);

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new PromptResource($prompt)
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
