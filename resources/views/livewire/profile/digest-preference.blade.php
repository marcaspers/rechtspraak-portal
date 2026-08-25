<x-action-section>
    <x-slot name="title">
        {{ __('E-maildigest') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Ontvang periodiek een e-mail met nieuwe relevante uitspraken per thema.') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600 space-y-4">
            @foreach ($options as $option)
                <label class="flex items-center gap-2">
                    <input type="radio" wire:model="digestFrequency" value="{{ $option->value }}" class="text-indigo-600 focus:ring-indigo-500 border-gray-300">
                    <span>
                        {{ match($option) {
                            \App\Enums\DigestFrequency::Daily => __('Dagelijks'),
                            \App\Enums\DigestFrequency::Weekly => __('Wekelijks'),
                            \App\Enums\DigestFrequency::None => __('Uit'),
                        } }}
                    </span>
                </label>
            @endforeach

            <x-button wire:click="update">
                {{ __('Opslaan') }}
            </x-button>

            <x-action-message on="saved">
                {{ __('Opgeslagen.') }}
            </x-action-message>
        </div>
    </x-slot>
</x-action-section>
