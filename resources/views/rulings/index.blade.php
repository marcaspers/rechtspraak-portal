<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Uitspraken') }}
        </h2>
    </x-slot>

    <livewire:rulings.index />
</x-app-layout>
