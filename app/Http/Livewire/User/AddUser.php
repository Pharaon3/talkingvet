<?php

namespace App\Http\Livewire\User;

use Livewire\Component;
use App\Services\UserService;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AddUser extends Component
{
    public $username;
    public $firstName;
    public $lastName;
    public $role;
    public $organization;
    public $password;
    public $confirmPassword;
    public $enabled = true;
    public $syncNeeded = true;
    public $syncKey;
    public $roles = ['Master Account', 'Sub Account', 'Clerical'];
    public $organizations = [];
    public $saveAction = null;

    protected $userService;

    protected $rules = [
        'organization_id' => 'required|int',
        'firstname' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
        'username' => 'required|string|max:255|unique:users,username',
        'account_type' => 'int|in:0,1,2',
        'default_language' => 'string',
        'enabled' => 'boolean',
        'login_server' => 'int|in:0,1,2'
    ];

    protected function get_user_service(){
        return app(UserService::class);
    }

    public function setSaveAction($action)
    {
        $this->saveAction = $action;
        $this->save();
    }
    
    public function mount(UserService $userService)
    {
        $this->userService = $userService;
        $this->organizations = Organization::where('enabled', 1)->pluck('name', 'id');
    }

    public function generateSyncKey()
    {
        $this->syncKey = bin2hex(random_bytes(16));
    }

    public function save()
    {
        $user_service = $this->get_user_service();
        
        $validated = [
            'organization_id' => $this->organization,
            'password' => $this->password,
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'username' => $this->username,
            'account_type' => $this->role,
            'default_language' => 'en-ca',
            'enabled' => $this->enabled,
            'login_server' => 0 
        ];

        try {
            $authenticated_user = Auth::guard('internal-auth-guard')->user();
            $newUser = $user_service->create_user($validated, $authenticated_user);

            session()->flash('message', 'User added successfully');

            if ($this->saveAction === 'close') {
                return redirect('/assist/home');
            } else {
                $this->resetForm(); // Reset fields but stay on the page
            }
        } catch (\Exception $e) {
            Log::error('Create user error: ' . $e->getMessage());
            session()->flash('error', 'Error creating user: ' . $e->getMessage());
        }
    }

    public function resetForm()
    {
        $this->reset([
            'username', 'firstName', 'lastName', 'role', 'organization', 'enabled', 'syncNeeded', 'syncKey'
        ]);
    }


    public function render()
    {
        return view('livewire.user.add-user', [
            'organizations' => $this->organizations
        ]);
    }
}
