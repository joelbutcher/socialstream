@php use Illuminate\Support\Facades\Route; @endphp

@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge([
        'class' => Route::has('filament.admin.auth.login') && Route::current() === route('filament.admin.auth.login')
            ? 'text-sm text-danger-600 dark:text-danger-400 space-y-1'
            : 'text-sm text-red-600 dark:text-red-400 space-y-1',
    ])}}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
