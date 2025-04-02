<?php
/**
 * Created by PhpStorm.
 * User: 585
 * Date: 2/25/2025
 * Time: 9:45 AM
 */

namespace App\Services;

use App\Http\Resources\PromptResource;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\Prompt;
use App\Models\User;
use Illuminate\Support\Facades\Log;

use Exception;

class PromptService
{
    public function get_all_prompts()
    {
        return Prompt::all();
    }

    public function get_prompts_for_master($authenticated_user)
    {
        $permissions = Permission::where([
            'user_id' => $authenticated_user->id,
            'role' => MASTER_ACCOUNT_ROLE
        ])->get();

        $prompt_list = [];

        foreach ($permissions as $permission) {
            $prompts = Prompt::where([
                'organization_id' => $permission->organization_id
            ])->get();
//            $prompt_list[] = PromptResource::collection($prompts);
            $prompt_list = array_merge($prompt_list, $prompts->toArray());
        }

        return $prompt_list;
    }

    public function get_prompts_by_organization($organization_id, $authenticated_user)
    {
        if (!is_numeric($organization_id) || intval($organization_id) != $organization_id) {
            throw new Exception('Bad Request - id value must be integer', 400);
        }

        $organization = Organization::find($organization_id);

        if (!$organization) {
            throw new Exception('Not Found - non-existing organization.', 404);
        }

        if (!internal_api_user_has_master_role($authenticated_user->id, $organization_id)) {
            throw new Exception('Forbidden - you don\'t have permission to access this organization.', 403);
        }
        $userPrompts = Prompt::where(['organization_id' => $organization_id])->get();
        $promptCount = $userPrompts->count();
        if ($promptCount >= 0) {
            return Prompt::where(['organization_id' => $organization_id])->get();
        } else {
            $defaultPrompts = Prompt::where(['system_default' => true])->get();
            return $defaultPrompts;
        }
    }

    public function get_prompt_by_user($user_id, $authenticated_user)
    {
        if (!is_numeric($user_id) || intval($user_id) != $user_id) {
            throw new Exception('Bad Request - id value must be integer', 400);
        }
        $user = User::find($user_id);

        if (!$user) {
            throw new Exception('Not Found - non-existing user', 404);
        }

        if (internal_api_user_has_master_role($authenticated_user->id, $user->organization_id) or $user_id == $authenticated_user->id) {
            $userPrompts = Prompt::where(['user_id' => $user_id])->get();
            $promptCount = $userPrompts->count();
            if ($promptCount >= 1) {
                return Prompt::where(['user_id' => $user_id])->get();
            } else {
                $defaultPrompts = Prompt::where(['system_default' => true])->get();
                return $defaultPrompts;
            }
        } else {
            throw new Exception('Forbidden - you don\'t have permission to access this user.', 403);
        }
    }

    public function create_prompt($validated_data, $authenticated_user)
    {
        $validated_data['is_default'] = $validated_data['is_default'] ?? false;
        $validated_data['system_default'] = $validated_data['system_default'] ?? false;

        $prompt_owner = User::find($validated_data['user_id']);

        if (!$prompt_owner) {
            throw new Exception('Not Found - non-existing user', 404);
        }

        if (!internal_api_user_has_master_role($authenticated_user->id, $prompt_owner->organization_id)) {
            throw new Exception('Forbidden - you don\'t have permission to add prompt for this organization.', 403);
        }

        if (Prompt::where([
            'user_id' => $validated_data['user_id'],
            'name' => $validated_data['name']])->exists()) {
            throw new Exception('Conflict - prompt name you tried to create is already existed', 409);
        }

        if(!isset($validated_data['position'])){
            $max_position = Prompt::where(['user_id' => $validated_data['user_id']])->max('position');
            if(!$max_position){
                $position = 0;
            }
            else{
                $position = $max_position + 1;
            }
        }
        $validated_data['position'] = $position;
        $validated_data['organization_id'] = $prompt_owner->organization_id;
        $new_prompt = Prompt::create($validated_data);

        return $new_prompt;
    }

    public function update_prompt($validated_data, $authenticated_user)
    {
        $prompt = Prompt::find($validated_data['prompt_id']);

        if (!$prompt) {
            throw new Exception('Not Found - non-existing prompt.', 404);
        }

        if (isset($validated_data['user_id'])) {

            $target_user = User::find($validated_data['user_id']);

            if(!$target_user){
                throw new Exception('Not Found - non-existing user', 404);
            }
            $is_authenticated_user_both_master = internal_api_is_user_both_master($authenticated_user->id, $prompt->organization_id, User::find($validated_data['user_id'])->organization_id);
            if (!$is_authenticated_user_both_master and $authenticated_user->id != $validated_data['user_id']) {
                throw new Exception('Forbidden - you don\'t have permission to access modify this user.', 403);
            }
        }

        if (isset($validated_data['position']) and $validated_data['position'] != $prompt->position) {
            $target_prompt = Prompt::where([
                'user_id' => $prompt->user_id,
                'position' => $validated_data['position']
            ])->first();

            if ($target_prompt) {
                $target_prompt->update(['position' => $prompt->position]);
            }
        }
        $same_name_prompt = Prompt::where([
            'user_id' => $prompt->user_id,
            'name' => $validated_data['name']])->first();
        if (isset($validated_data['name']) and $same_name_prompt and $same_name_prompt->id != $validated_data['prompt_id']) {
            throw new Exception('Conflict - prompt name you tried to update is already existed', 409);
        }

        $update_data = [
            'user_id' => $validated_data['user_id'] ?? $prompt->user_id,
            'name' => $validated_data['name'] ?? $prompt->name,
            'prompt' => $validated_data['prompt'] ?? $prompt->prompt,
            'description' => $validated_data['description'] ?? $prompt->description,
            'position' => $validated_data['position'] ?? $prompt->position,
            'is_default' => $validated_data['is_default'] ?? $prompt->is_default,
            'system_default' => $validated_data['system_default'] ?? $prompt->system_default,
        ];

        $prompt->update($update_data);

        return $prompt;
    }

    public function view_prompt($prompt_id, $authenticated_user)
    {
        if (!is_numeric($prompt_id) || intval($prompt_id) != $prompt_id) {
            throw new Exception('Bad Request - id value must be integer', 400);
        }
        $prompt = Prompt::find($prompt_id);

        if (!$prompt) {
            throw new Exception('Not Found - non-existing prompt.', 404);
        }

        $authenticated_user_role = internal_api_get_user_role($authenticated_user->id, $prompt->organization_id);

        if (!internal_api_is_user_master($authenticated_user_role) and $authenticated_user->id != $prompt->user_id) {
            throw new Exception('Forbidden - you don\'t have permission to access this prompt.', 403);
        }

        return $prompt;
    }
}