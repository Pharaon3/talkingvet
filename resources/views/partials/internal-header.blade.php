<div class="flex justify-between items-center">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
        {{ __('assist.header') }}
    </h2>
    <div class="flex items-center">
        <a href="{{route('assist.home')}}"
           class="py-2 px-4 border-b-2 border-transparent hover:border-gray-300 transition">
            Visits
        </a>
        @php
        $authenticated_user = Auth::guard('internal-auth-guard')->user();
        $authenticated_user_role = $authenticated_user 
            ? internal_api_get_user_role($authenticated_user->id, $authenticated_user->organization_id) 
            : null;
        @endphp
        @if (internal_api_is_user_master($authenticated_user_role))
            <a href="{{route('prompts.home')}}"
            class="py-2 px-4 border-b-2 border-transparent hover:border-gray-300 transition">
            Summary Type Editor
            </a>
        @endif
        @php
        $authenticated_user = Auth::guard('internal-auth-guard')->user();
        $authenticated_user_role = $authenticated_user 
            ? internal_api_get_user_role($authenticated_user->id, $authenticated_user->organization_id) 
            : null;
        @endphp
        @if (internal_api_is_user_admin($authenticated_user_role))
        <a href="{{route('user.new')}}"
           class="py-2 px-4 border-b-2 border-transparent hover:border-gray-300 transition">
            Add User
        </a>
        @endif
    </div>
</div>