@if ($errors->has('global'))
{{--    @dd($errors, $errors->first('global'))--}}
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition
        x-init="setTimeout(() => show = false, 3000)"
        class="mb-4 rounded-lg bg-danger-100 px-6 py-5 text-base text-danger-700"
        role="alert"
    >
        {{ $errors->first('global') }}
    </div>
@endif

@if (session('status'))
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition
        x-init="setTimeout(() => show = false, 3000)"
        class="mb-4 rounded-lg bg-success-100 px-6 py-5 text-base text-success-700"
        role="alert"
    >
        {{ session('status') }}
    </div>
@endif

{{-- test --}}
{{--
<div

    x-data="{ show: true }"
    x-show="show"
    x-transition
    x-init="setTimeout(() => show = false, 6000)"
    class="mb-4 rounded-lg bg-danger-100 px-6 py-5 text-base text-danger-700"
    role="alert"
>
    Hello from error
</div>

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition
    x-init="setTimeout(() => show = false, 6000)"
    class="mb-4 rounded-lg bg-success-100 px-6 py-5 text-base text-success-700"
    role="alert"
>
    Hello success status
</div>

--}}
