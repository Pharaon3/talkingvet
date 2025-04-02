<div
    {{ $attributes->merge([
'class' => 'p-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg'
]) }}>
    {{$slot}}
</div>
