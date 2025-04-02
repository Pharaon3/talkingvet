<?php
/**
 * Created by PhpStorm.
 * User: 585
 * Date: 2/25/2025
 * Time: 7:13 AM
 */

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Organization;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class UserService
{
    public function get_all_users()
    {
        $users = User::all();

        return $this->add_role_to_user($users);
    }

    public function get_users_for_master($authenticated_user)
    {
        $user_list = [];
        $permissions = Permission::where([
            'user_id' => $authenticated_user->id,
            'role' => MASTER_ACCOUNT_ROLE
        ])->get();

        foreach ($permissions as $permission) {
            $users = User::where(['organization_id' => $permission->organization_id])->get();


            if ($users) {
                $user_list = array_merge($user_list, $users->toArray());
            }
        }

        return $user_list;
    }

    public function get_users_by_organization($organization_id, $authenticated_user)
    {
        if (!is_numeric($organization_id) || intval($organization_id) != $organization_id) {
            throw new Exception('Bad Request - id value must be integer', 400);
        }
        $authenticated_user_role = $this->get_user_role($authenticated_user->id, $organization_id);

        $organization = Organization::find($organization_id);
        if (!$organization) {
            throw new Exception('Not Found - non-existing organization', 404);
        }

        if ($authenticated_user_role == null or !$this->is_user_master($authenticated_user_role)) {
            throw new Exception('Forbidden - You don\'t have permission to access the users in this organization', 403);
        }

        $users = User::where('organization_id', $organization_id)->get();

        return $this->add_role_to_user($users);
    }

    public function create_user(array $validated_data, $authenticated_user)
    {
        $organization = Organization::find($validated_data['organization_id']);
        if (!$organization) {
            throw new Exception('Not Found - non-existing organization.', 404);
        }

        $authenticated_user_role = $this->get_user_role($authenticated_user->id, $validated_data['organization_id']);

        if ($authenticated_user_role == null or !$this->is_user_master($authenticated_user_role)) {
            throw new Exception('Forbidden - not allowed to create user for this organization.', 403);
        }

        $account_type = $validated_data['account_type'] ?? SUB_USER_ACCOUNT_ROLE;

        if ($account_type == ADMIN_ROLE or (!$this->is_user_admin($authenticated_user_role) and $account_type == MASTER_ACCOUNT_ROLE)) {
            throw new Exception('Forbidden - not allowed to create this type of user.', 403);
        }

        if (User::where('username', $validated_data['username'])->exists()) {
            throw new Exception('Conflict - username already exists', 409);
        }

        $user_data = [
            'username' => $validated_data['username'],
            'password' => Hash::make($validated_data['password']),
            'firstname' => $validated_data['firstname'],
            'lastname' => $validated_data['lastname'],
            'organization_id' => $validated_data['organization_id'],
            'login_server' => $validated_data['login_server'],
            'default_language' => $validated_data['default_language'] ?? 'en-us',
            'sync_key' => $validated_data['sync_key'],
            'sync_needed' => $validated_data['sync_needed'] ?? 0,
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now()
        ];

        $new_user = User::create($user_data);

        Permission::create([
            'user_id' => $new_user->id,
            'organization_id' => $validated_data['organization_id'],
            'role' => $account_type
        ]);

        return $new_user;
    }

    /// User can update their own details. Master can update any account in org and admin can update anything
    /// Only admin account can change a user organization
    /// Master account can update any user (besides organization) within their organization
    public function update_user(array $validated_data, $authenticated_user)
    {
        $authenticated_user_role = internal_api_get_user_role($authenticated_user->id, $authenticated_user->organization_id);

        $user_to_update = User::find($validated_data['user_id']);

        if (!$user_to_update) {
            throw new Exception('Not Found - non-existing user', 404);
        }

        $user_to_update_role = $this->get_user_role($user_to_update->id, $user_to_update->organization_id);

       
        if (!$this->is_user_self($authenticated_user->id, $user_to_update->id) && 
        !$this->is_user_admin($authenticated_user_role) && 
        !$this->is_user_master($authenticated_user_role)) {
            throw new Exception('Forbidden - TRACK - you don\'t have permission to update this user.', 403);
        }

        if (isset($validated_data['organization_id'])) {

            $target_organization = Organization::find($validated_data['organization_id']);

            if(!$target_organization){
                throw new Exception('Not Found - non-existing organization', 404);
            }

            if (!$this->is_user_admin($authenticated_user_role)) {
                Log::error("User attempted to change the organization when not an Admin user");
                throw new Exception('Forbidden - You don\'t have permission to update user organizations.', 403);
            }
        } else {
            $is_user_to_update_same_org = internal_api_is_user_to_update_same_org($authenticated_user->organization_id, $user_to_update->organization_id);
            if (!$this->is_user_admin($authenticated_user_role) && !$is_user_to_update_same_org) {
                Log::error("User attempted to update a user not withing their organization");
                throw new Exception('Forbidden - You don\'t have permission to modify this user.', 403);
            }
        }
        if ($validated_data['username'] and User::where('username', $validated_data['username'])->exists()) {
            throw new Exception('Conflict - username already exists.', 409);
        }

        $update_data = $this->prepare_update_data($validated_data, $user_to_update);

        if (isset($validated_data['organization_id'])) {
            $this->handle_organization_change($authenticated_user, $user_to_update, $validated_data['organization_id']);
        }

        $user_to_update->update($update_data);

        return $user_to_update;
    }

    public function view_user($user_id, $authenticated_user)
    {
        if (!is_numeric($user_id) || intval($user_id) != $user_id) {
            throw new Exception('Bad Request - id value must be integer', 400);
        }

        $user = User::find($user_id);

        if (!$user) {
            throw new Exception('Not Found - non-existing user.', 404);
        }

        $authenticated_user_role = $this->get_user_role($authenticated_user->id, $user->organization_id);

        if($authenticated_user->id != $user_id){
            if ($authenticated_user_role == null or !$this->is_user_master($authenticated_user_role) ) {
                throw new Exception('Forbidden - you don\'t have permission to access this user.', 403);
            }
        }


        return $this->add_role_to_user($user);
    }

    private function prepare_update_data($validated_data, $user)
    {
        return [
            'username' => $validated_data['username'] ?? $user->username,
            'firstname' => $validated_data['firstname'] ?? $user->firstname,
            'lastname' => $validated_data['lastname'] ?? $user->lastname,
            'organization_id' => $validated_data['organization_id'] ?? $user->organization_id,
            'login_server' => $validated_data['login_server'] ?? $user->login_server,
            'default_language' => $validated_data['default_language'] ?? $user->default_language,
            'password' => isset($validated_data['password']) ? Hash::make($validated_data['password']) : $user->password,
            'sync_key' => $validated_data['sync_key'] ?? $user->syncKey,
            'sync_needed' => $validated_data['sync_needed'] ?? $user->syncNeeded,
            'updated_at' => now()
        ];
    }

    private function handle_organization_change($authenticated_user, $user, $new_organization_id)
    {
        $before_organization = Organization::find($user->organization_id);
        $after_organization = Organization::find($new_organization_id);

        if (!$after_organization) {
            throw new Exception('Not Found - organization not found.', 404);
        }

        if (!$this->is_user_both_master($authenticated_user->id, $before_organization->id, $after_organization->id)) {
            throw new Exception('Forbidden - no permission to change organization');
        }

        $current_permission = Permission::where([
            'user_id' => $user->id,
            'organization_id' => $user->organization_id
        ])->first();

        Permission::create([
            'user_id' => $user->id,
            'organization_id' => $new_organization_id,
            'role' => $current_permission->role
        ]);

        $current_permission->delete();
    }

    private function add_role_to_user($users){
        if($users instanceof Collection){
            foreach ($users as $user){
                $this->add_role_to_single_user($user);
            }
        } elseif ($users instanceof User){
            $this->add_role_to_single_user($users);
        }

        return $users;
    }

    private function add_role_to_single_user(&$user){
        $permission = Permission::where([
            'user_id' => $user->id,
            'organization_id' => $user->organization_id
        ])->first();
        $user->role = $permission->role;
    }

    private function get_user_role($user_id, $organization_id)
    {
        return internal_api_get_user_role($user_id, $organization_id);
    }

    private function is_user_master($role)
    {
        return internal_api_is_user_master($role);
    }

    private function is_user_admin($role)
    {
        return internal_api_is_user_admin($role);
    }

    private function is_user_both_master($user_id, $before_organization_id, $after_organization_id)
    {
        return internal_api_is_user_both_master($user_id, $before_organization_id, $after_organization_id);
    }
    private function is_user_self($authenticated_user, $user_id) {
        return internal_api_is_user_self($authenticated_user, $user_id);
    }
}