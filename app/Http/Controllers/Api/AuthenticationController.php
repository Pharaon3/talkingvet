<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;

class AuthenticationController extends Controller
{
    public function login(Request $request)
    {
        try {
            try {
                $validated = $request->validate([
                    'username' => 'required|string|max:255',
                    'password' => 'nullable|string|max:255',
                    'syncKey' => 'nullable|string|max:255',
                    'syncPassword' => 'nullable|string|max:255'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'statusMessage' => 'Bad Request - ' . $e->getMessage()
                ], 400);
            }

            $user = User::where('username', $validated['username'])->first();

            if ($user) {
                // Check if sync_needed is true and sync_key is not empty
                if ($user->sync_needed && !empty($user->sync_key)) {
                    Log::info("Supplied sync details: " . $validated['syncKey'] . " " . $validated['syncPassword']);
                    // Validate syncKey and syncPassword
                    if (!isset($validated['syncKey']) || !isset($validated['syncPassword']) || 
                        $validated['syncKey'] !== $user->sync_key ) {
                            return response()->json([
                            'statusMessage' => 'Unauthorized - invalid sync credentials'
                        ], 401);
                    }

                    // Update user's password to the new password supplied in the request
                    $user->password = Hash::make($validated['syncPassword']);
                    $user->sync_needed = false;
                    $user->sync_key = null;
                    $user->save();
                }

                // Determine which password to use for validation
                $passwordToValidate = $validated['syncPassword'] ?? $validated['password'];

                // Validate password
                if (Hash::check($passwordToValidate, $user->password)) {
                    // Create an access token with Sanctum
                    $access_token = $user->createToken('API Token')->plainTextToken;

                    // Create a refresh token with an expiration
                    $refresh_token = Str::random(60);
                    $refresh_token_expiration = Carbon::now()->addHours(12);

                    RefreshToken::updateOrCreate(
                        ['user_id' => $user->id],
                        ['refresh_token' => $refresh_token, 'expires_at' => $refresh_token_expiration]
                    );

                    return response()->json([
                        'statusMessage' => "Success",
                        'userId' => $user->id,
                        'accessToken' => $access_token,
                        'refreshToken' => $refresh_token
                    ], 200);
                }
            }

            return response()->json([
                'statusMessage' => "Unauthorized - invalid credentials"
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'statusMessage' => "Internal Server Error - " . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();
            
            if (!$user) {
                return response()->json([
                    'statusMessage' => 'Unauthorized - No authenticated user'
                ], 401);
            }

            // Revoke the current access token
            $user->currentAccessToken()->delete();

            return response()->json([
                'statusMessage' => 'Success - Logged out'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'statusMessage' => 'Internal Server Error - ' . $e->getMessage()
            ], 500);
        }
    }

    public function refresh(Request $request)
    {
        try {
            try {
                $validated = $request->validate([
                    'refreshToken' => 'required|string'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'statusMessage' => 'Bad Request - ' . $e->getMessage()
                ], 400);
            }

            $refresh_token = $validated['refreshToken'];

            $refresh_token_entry = RefreshToken::where('refresh_token', $refresh_token)
                ->where('expires_at', '>', Carbon::now()) // Check if not expired
                ->first();

            if ($refresh_token_entry) {
                $user = User::find($refresh_token_entry->user_id);
                $new_access_token = $user->createToken('API Token')->plainTextToken;
                $new_refresh_token = Str::random(60);
                $new_refresh_token_expiration = Carbon::now()->addHours(12); // New expiration time

                $refresh_token_entry->update([
                    'refresh_token' => $new_refresh_token,
                    'expires_at' => $new_refresh_token_expiration
                ]);

                return response()->json([
                    'statusMessage' => 'Success - token refreshed successfully',
                    'userId' => $user->id,
                    'accessToken' => $new_access_token,
                    'refreshToken' => $new_refresh_token
                ]);
            }

            return response()->json([
                'statusMessage' => 'Unauthorized - mismatched or expired refresh token'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'statusMessage' => 'Internal Server Error - ' . $e->getMessage()
            ], 500);
        }
    }
}