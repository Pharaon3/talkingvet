<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\Permission;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
 * Created by PhpStorm.
 * User: 585
 * Date: 2/25/2025
 * Time: 5:40 AM
 */

class OrganizationService{
    public function get_all_organizations(){
        return Organization::all();
    }

    public function get_organizations_for_master($authenticated_user){
        $permissions = Permission::where([
            'user_id' => $authenticated_user->id,
            'role' => MASTER_ACCOUNT_ROLE
        ])->get();

        $organizations = [];

        foreach ($permissions as $permission){
            $organization = Organization::find($permission->organization_id);
            if($organization){
                $organizations[] = $organization;
            }
        }

        return $organizations;
    }

    public function create_organization(array $validated_data){
        $authenticated_user = Auth::guard('sanctum')->user();
        if(Organization::where('name', $validated_data['organization_name'])->exists()){
            throw new Exception('Conflict - duplicated entry', 409);
        }

        $organization = Organization::create([
            'name' => $validated_data['organization_name'],
            'enabled' => $validated_data['enabled'] ?? true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Permission::create([
           'user_id' => $authenticated_user->id,
            'organization_id' => $organization->id,
            'role' => ADMIN_ROLE
        ]);

        return $organization;
    }

    public function update_organization(array $validated_data){
        $organization = Organization::find($validated_data['organization_id']);

        if(!$organization){
            throw new Exception('Not Found - non-existing organization', 404);
        }

        $existing_organization = Organization::where('name', $validated_data['organization_name'])
            ->where('id', '!=', $validated_data['organization_id'])
            ->exists();

        if($existing_organization){
            throw new Exception('Conflict - duplicated organization name', 409);
        }

        $organization->update([
            'name' => $validated_data['organization_name'],
            'enabled' => $validated_data['enabled'] ?? true,
            'updated_at' => now()
        ]);

        return $organization;
    }

    public function get_organization_by_id($organization_id){
        if (!is_numeric($organization_id) || intval($organization_id) != $organization_id) {
            throw new Exception('Bad Request - id value must be integer', 400);
        }
        $organization = Organization::find($organization_id);
        if(!$organization){
            throw new Exception('Not Found - non-existing organization', 404);
        }

        return $organization;
    }
}