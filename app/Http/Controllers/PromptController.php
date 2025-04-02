<?php

namespace App\Http\Controllers;

use App\Models\Prompt;
use App\Services\PromptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class PromptController extends Controller
{
    protected $prompt_service;

    public function __construct(PromptService $prompt_service)
    {
        $this->prompt_service = $prompt_service;
    }

    //This returns all of the prompts for the current user. Used by the Summary Type Editor
    public function index(){
        $authenticated_user = Auth::guard('internal-auth-guard')->user();
        $prompts = $this->prompt_service->get_prompt_by_user($authenticated_user->id, $authenticated_user);
        $promptCount = $prompts->count();
        if ($promptCount >= 1) {
            if ($prompts[0]['system_default'] == 1) {
                return view('prompts.home', ['prompts' => $prompts, 'selected_prompt_id' => 0, 'selected_prompt' => null]);
            } else {
                return view('prompts.home', ['prompts' => $prompts, 'selected_prompt_id' => isset($prompts[0]) ? $prompts[0]['id'] : 0, 'selected_prompt' => isset($prompts[0]) ? $prompts[0] : null]);
            }
        } else {
            return view('prompts.home', ['prompts' => $prompts, 'selected_prompt_id' => 0, 'selected_prompt' => null]);
        }
    }

    //This returns a spcific prompt loaded into the editor
    public function view(Request $request, $prompt_id){
        $authenticated_user = Auth::guard('internal-auth-guard')->user();
        $prompts = $this->prompt_service->get_prompt_by_user($authenticated_user->id, $authenticated_user);
        $promptCount = $prompts->count();
        if ($promptCount >= 1) {
            if ($prompts[0]['system_default'] == 1) {
                return view('prompts.home', ['prompts' => $prompts, 'selected_prompt_id' => 0, 'selected_prompt' => null]);
            } else {
                $selected_prompt = Prompt::find($prompt_id);
                if(!$selected_prompt){
                    abort(404);
                }
                return view('prompts.home', ['prompts' => $prompts, 'selected_prompt_id' => $prompt_id, 'selected_prompt' => $selected_prompt]);
            }
        } else {
            return view('prompts.home', ['prompts' => $prompts, 'selected_prompt_id' => 0, 'selected_prompt' => null]);
        }
    }

    public function create(){
        return view('prompts.create');
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string',
            'prompt' => 'required|string',
            'description' => 'required|string',
            'is_default' => 'boolean',
        ]);
        $authenticated_user = Auth::guard('internal-auth-guard')->user();
        $validated['user_id'] = $authenticated_user->id;
        try{
            $this->prompt_service->create_prompt($validated, $authenticated_user);
            // return redirect()->route('prompts.create')->with('success', 'Successfully Created!');
            return redirect()->route('prompts.home')->with('success', 'Prompt created successfully!');
        }catch (\Exception $e){
            return redirect()->route('prompts.create')
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request){
//        dd($request);
        $validated = $request->validate([
            'prompt_id' => 'required|int',
            'name' => 'nullable|string',
            'prompt' => 'nullable|string',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
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

        $authenticated_user = Auth::guard('internal-auth-guard')->user();
        $validated['user_id'] = $authenticated_user->id;
        try{

            $this->prompt_service->update_prompt($validated, $authenticated_user);

            return redirect()->route('prompts.home')->with('success', 'Successfully Updated!');
        }catch (\Exception $e){
            return redirect()->route('prompts.home')
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function delete(Request $request, $prompt_id)
    {
        try {
            $prompt = Prompt::findOrFail($prompt_id);
            $prompt->delete();

            return response()->json([
                'success' => true,
                'message' => 'Prompt deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete prompt'
            ], 500);
        }
    }

}
