<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Services\OrganizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrganizationController extends Controller
{
    protected $organization_service;

    public function __construct(OrganizationService $organization_service)
    {
        $this->organization_service = $organization_service;
    }

    public function organization_list(Request $request){
        try{
            $authenticated_user = Auth::guard('sanctum')->user();
            $authenticated_user_role = internal_api_get_user_role($authenticated_user->id, $authenticated_user->organization_id);
            if(internal_api_is_user_admin($authenticated_user_role)){
                $organizations = $this->organization_service->get_all_organizations();
            }
            else{
                $organizations = $this->organization_service->get_organizations_for_master($authenticated_user);
            }

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new OrganizationResource($organizations)
            ], 200);
        }
        catch (\Exception $e){
            Log::error('Error - to get organization list: ' . $e->getMessage());
            return response()->json([
                'statusMessage' => 'Internal Server Error - ' . $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        try {
            $validated = $request->validate([
                'organization_name' => 'required|string|max:255',
                'enabled' => 'sometimes|boolean'
            ]);
        } catch (\Exception $e) {
            // Handle validation errors
            return response()->json([
                'statusMessage' => 'Bad Request - ' . $e->getMessage()
            ], 400);
        }

        try{
            $organization = $this->organization_service->create_organization($validated);

            return response()->json([
                'statusMessage' => 'Created - new organization created',
                'organization' => new OrganizationResource($organization)
            ], 201);
        }
        catch (\Exception $e){
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            Log::error('Create Organization error: ' . $e->getMessage());
            return response()->json([
               'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function update(Request $request)
    {
        try {
            // Validate the request parameters
            $validated = $request->validate([
                'organization_id' => 'required|integer',
                'organization_name' => 'required|string|max:255',
                'enabled' => 'boolean'
            ]);


        } catch (\Exception $e) {
            return response()->json([
                'statusMessage' => 'Bad Request - ' . $e->getMessage()
            ], 400);
        }

        try {
            $updated_organization = $this->organization_service->update_organization($validated);

            return response()->json([
                'statusMessage' => 'Success - organization updated',
                'organization' => new OrganizationResource($updated_organization)
            ], 200);

        } catch (\Exception $e) {
            $code = $e->getCode() > 0 ? $e->getCode() : 500;
            Log::error('Update Organization error: ' . $e->getMessage());
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }

    public function view(Request $request, $organization_id){
        try{
            $organization = $this->organization_service->get_organization_by_id($organization_id * 1);

            return response()->json([
                'statusMessage' => 'Success',
                'data' => new OrganizationResource($organization)
            ], 200);
        }
        catch (\Exception $e){
            $code = $e->getCode() > 0 ? $e->getCode() : 404;
            return response()->json([
                'statusMessage' => $e->getMessage()
            ], $code);
        }
    }
}
