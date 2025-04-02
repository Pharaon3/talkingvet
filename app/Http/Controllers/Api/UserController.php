<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\Permission;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    protected $user_service;

    public function __construct(UserService $user_service)
    {
        $this->user_service = $user_service;
    }

    public function user_list(Request $request){
        try{
            $authenticated_user = Auth::guard('sanctum')->user();
            $authenticated_user_role = internal_api_get_user_role($authenticated_user->id, $authenticated_user->organization_id);
            if(internal_api_is_user_admin($authenticated_user_role)){
                $users = $this->user_service->get_all_users();
            }
            else{
                $users = $this->user_service->get_users_for_master($authenticated_user);
            }

            return response()->json([
               'statusMessage' => 'Success',
               'data' => new UserResource($users)
            ], 200);
        }
        catch (\Exception $e){
            Log::error('Error to get user list: ' . $e->getMessage());
            return response()->json([
                'statusMessage' => 'Internal Server Error - ' . $e->getMessage()
            ], 500);
        }

    }

    public function user_list_by_organization(Request $request, $organization_id){

        try {
            $authenticated_user = Auth::guard('sanctum')->user();
            $users = $this->user_service->get_users_by_organization($organization_id, $authenticated_user);

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new UserResource($users)
            ], 200);
        }catch(\Exception $e){
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
                'organization_id' => 'required|int',
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'username' => 'required|string|max:255',
                'password' => 'required|string|min:6|max:255',
                'enabled' => 'required|int|in:0,1',
                'account_type' => 'int|in:0,1,2,3', //0-Admin, 1-Master, 2-Sub-Account, 3-Clerical
                'default_language' => 'string',
                'login_server' => 'required|int|in:0,1,2',  
                'sync_key' => 'sometimes|string|max:255',
                'sync_needed' => 'sometimes|int|in:0,1'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'statusMessage' => 'Bad Request - ' . $e->getMessage()
            ], 400);
        }

        try {

            $authenticated_user = Auth::guard('sanctum')->user();
            $new_user = $this->user_service->create_user($validated, $authenticated_user);

            $new_user_role = internal_api_get_user_role($new_user->id, $new_user->organization_id);
            $user_data = [
              'id' => $new_user->id,
              'username' => $new_user->username,
              'firstname' => $new_user->firstname,
              'lastname' => $new_user->lastname,
              'role' => internal_api_get_role_string($new_user_role),
              'enabled' => $new_user->enabled,
              'default_language' => $new_user->default_language,
              'login_server' => internal_api_get_login_server_string($new_user->login_server),
              'sync_key' => $new_user->sync_key,
              'sync_needed' => $new_user->sync_needed
            ];
            return response()->json([
                'statusMessage' => 'Created',
                'data' => new UserResource($user_data)
            ], 201);

        } catch (\Exception $e) {
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            Log::error('Create user error: ' . $e->getMessage());
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }

    }

    public function update(Request $request){
        try{
            $validated = $request->validate([
                'user_id' => 'required|int',
                'organization_id' => 'sometimes|int',
                'firstname' => 'sometimes|string|max:255',
                'lastname' => 'sometimes|string|max:255',
                'username' => 'sometimes|string|max:255',
                'password' => 'sometimes|string|min:6|max:255',
                'default_language' => 'sometimes|string',
                'enabled' => 'sometimes|boolean',
                'login_server' => 'sometimes|int|in:0,1,2',
                'sync_key' => 'sometimes|string|max:255',
                'sync_needed' => 'sometimes|int|in:0,1'
            ]);

            $validated = [
                'user_id' => $validated['user_id'],
                'organization_id' => $validated['organization_id'] ?? null,
                'firstname' => $validated['firstname'] ?? null,
                'lastname' => $validated['lastname'] ?? null,
                'username' => $validated['username'] ?? null,
                'password' => $validated['password'] ?? null,
                'default_language' => $validated['default_language'] ?? null,
                'enabled' => $validated['enabled'] ?? null,
                'login_server' => $validated['login_server'] ?? null,
                'sync_key' => $validated['sync_key'] ?? null,
                'sync_needed' => $validated['sync_needed'] ?? null
            ];

        }catch (\Exception $e){
            return response()->json([
                'statusMessage' => 'Bad Request - ' . $e->getMessage()
            ], 400);
        }

        try{

            $authenticated_user = Auth::guard('sanctum')->user();
            $updated_user = $this->user_service->update_user($validated, $authenticated_user);

            $updated_user_role = internal_api_get_user_role($updated_user->id, $updated_user->organization_id);

            $user_data = [
                'id' => $updated_user->id,
                'username' => $updated_user->username,
                'firstname' => $updated_user->firstname,
                'lastname' => $updated_user->lastname,
                'role' => internal_api_get_role_string($updated_user_role),
                'enabled' => $updated_user->enabled,
                'default_language' => $updated_user->default_language,
                'login_server' => internal_api_get_login_server_string($updated_user->login_server),
                'sync_key' => $updated_user->sync_key,
                'sync_needed' => $updated_user->sync_needed
            ];

            return response()->json([
                'statusMessage' => 'Success - user updated successfully',
                'data' => new UserResource($user_data)
            ], 200);

        }catch (\Exception $e){
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function view(Request $request, $user_id){
        try{
            $authenticated_user = Auth::guard('sanctum')->user();
            $user = $this->user_service->view_user($user_id, $authenticated_user);

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new UserResource($user)
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
