<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Uitspraak') }}
            </h2>
            <a href="{{ route('rulings.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                &larr; {{ __('Terug naar overzicht') }}
            </a>
        </div>
    </x-slot>

    <livewire:rulings.show :ruling="$ruling" />
</x-app-layout>
