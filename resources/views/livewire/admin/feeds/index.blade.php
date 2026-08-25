<div class="space-y-6">
    <div class="flex justify-end">
        <x-button wire:click="create">{{ __('Nieuwe feed') }}</x-button>
    </div>

    <div class="bg-white shadow sm:rounded-lg overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-2">{{ __('Naam') }}</th>
                    <th class="px-4 py-2">{{ __('Bron') }}</th>
                    <th class="px-4 py-2">{{ __('Rechtsgebied') }}</th>
                    <th class="px-4 py-2">{{ __('Interval') }}</th>
                    <th class="px-4 py-2">{{ __('Laatst opgehaald') }}</th>
                    <th class="px-4 py-2">{{ __('Status') }}</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($feeds as $feed)
                    <tr class="border-t">
                        <td class="px-4 py-2">
                            <div class="font-medium text-gray-900">{{ $feed->name }}</div>
                            <div class="text-xs text-gray-400 break-all">{{ $feed->url }}</div>
                        </td>
                        <td class="px-4 py-2">{{ $feed->source->value }}</td>
                        <td class="px-4 py-2">{{ $feed->category_tag ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $feed->poll_interval_minutes }} min</td>
                        <td class="px-4 py-2 text-gray-500">
                            {{ $feed->last_fetched_at?->format('d-m-Y H:i') ?? __('nog niet') }}
                        </td>
                        <td class="px-4 py-2">
                            @if ($feed->is_active)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">{{ __('actief') }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">{{ __('gepauzeerd') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            <button type="button" wire:click="edit({{ $feed->id }})" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">{{ __('Bewerken') }}</button>
                            <button type="button" wire:click="togglePause({{ $feed->id }})" class="ml-3 text-gray-600 hover:text-gray-800 text-xs font-medium">
                                {{ $feed->is_active ? __('Pauzeren') : __('Activeren') }}
                            </button>
                            <button type="button" wire:click="delete({{ $feed->id }})" wire:confirm="{{ __('Deze feed verwijderen?') }}" class="ml-3 text-red-600 hover:text-red-800 text-xs font-medium">
                                {{ __('Verwijderen') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-4 text-gray-500" colspan="7">{{ __('Nog geen feeds geconfigureerd.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-dialog-modal wire:model="showingModal">
        <x-slot name="title">
            {{ $editingId ? __('Feed bewerken') : __('Nieuwe feed') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="name" value="{{ __('Naam') }}" />
                    <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                    <x-input-error for="name" class="mt-1" />
                </div>
                <div>
                    <x-label for="url" value="{{ __('Feed-URL') }}" />
                    <x-input id="url" type="text" class="mt-1 block w-full" wire:model="url" />
                    <x-input-error for="url" class="mt-1" />
                </div>
                <div>
                    <x-label for="source" value="{{ __('Bron') }}" />
                    <select id="source" wire:model="source" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">{{ __('Kies een bron') }}</option>
                        @foreach ($sources as $sourceOption)
                            <option value="{{ $sourceOption->value }}">{{ $sourceOption->value }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="source" class="mt-1" />
                </div>
                <div>
                    <x-label for="category_tag" value="{{ __('Rechtsgebied / categorie (optioneel)') }}" />
                    <x-input id="category_tag" type="text" class="mt-1 block w-full" wire:model="category_tag" />
                    <x-input-error for="category_tag" class="mt-1" />
                </div>
                <div>
                    <x-label for="poll_interval_minutes" value="{{ __('Ophaalfrequentie (minuten)') }}" />
                    <x-input id="poll_interval_minutes" type="number" min="1" class="mt-1 block w-full" wire:model="poll_interval_minutes" />
                    <x-input-error for="poll_interval_minutes" class="mt-1" />
                </div>
                <div class="flex items-center">
                    <input id="is_active" type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <label for="is_active" class="ml-2 text-sm text-gray-600">{{ __('Actief') }}</label>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showingModal', false)">{{ __('Annuleren') }}</x-secondary-button>
            <x-button class="ml-3" wire:click="save">{{ __('Opslaan') }}</x-button>
        </x-slot>
    </x-dialog-modal>
</div>
