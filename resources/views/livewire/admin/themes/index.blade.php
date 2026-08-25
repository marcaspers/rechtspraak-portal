<div class="space-y-6">
    <div class="flex justify-end">
        <x-button wire:click="create">{{ __('Nieuw thema') }}</x-button>
    </div>

    <div class="bg-white shadow sm:rounded-lg overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-2">{{ __('Naam') }}</th>
                    <th class="px-4 py-2">{{ __('Trefwoorden') }}</th>
                    <th class="px-4 py-2">{{ __('Status') }}</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($themes as $theme)
                    <tr class="border-t">
                        <td class="px-4 py-2">
                            <div class="font-medium text-gray-900">{{ $theme->name }}</div>
                            @if ($theme->description)
                                <div class="text-xs text-gray-400">{{ $theme->description }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-gray-600">
                            {{ implode(', ', $theme->keywords ?? []) ?: '—' }}
                        </td>
                        <td class="px-4 py-2">
                            @if ($theme->is_active)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">{{ __('actief') }}</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">{{ __('uit') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right whitespace-nowrap">
                            <button type="button" wire:click="edit({{ $theme->id }})" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">{{ __('Bewerken') }}</button>
                            <button type="button" wire:click="toggleActive({{ $theme->id }})" class="ml-3 text-gray-600 hover:text-gray-800 text-xs font-medium">
                                {{ $theme->is_active ? __('Uitschakelen') : __('Inschakelen') }}
                            </button>
                            <button type="button" wire:click="delete({{ $theme->id }})" wire:confirm="{{ __('Dit thema verwijderen?') }}" class="ml-3 text-red-600 hover:text-red-800 text-xs font-medium">
                                {{ __('Verwijderen') }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-4 text-gray-500" colspan="4">{{ __('Nog geen thema\'s geconfigureerd.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-dialog-modal wire:model="showingModal">
        <x-slot name="title">
            {{ $editingId ? __('Thema bewerken') : __('Nieuw thema') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="name" value="{{ __('Naam') }}" />
                    <x-input id="name" type="text" class="mt-1 block w-full" wire:model="name" />
                    <x-input-error for="name" class="mt-1" />
                </div>
                <div>
                    <x-label for="description" value="{{ __('Omschrijving (optioneel)') }}" />
                    <textarea id="description" wire:model="description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error for="description" class="mt-1" />
                </div>
                <div>
                    <x-label for="keywordsInput" value="{{ __('Trefwoorden (komma-gescheiden)') }}" />
                    <x-input id="keywordsInput" type="text" class="mt-1 block w-full" wire:model="keywordsInput" placeholder="ontslag, arbeidsovereenkomst, cao" />
                    <x-input-error for="keywordsInput" class="mt-1" />
                    <p class="mt-1 text-xs text-gray-400">{{ __('Een uitspraak matcht dit thema als de titel of RSS-samenvatting één van deze woorden bevat (hoofdletterongevoelig).') }}</p>
                </div>
                <div class="flex items-center">
                    <input id="theme_is_active" type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <label for="theme_is_active" class="ml-2 text-sm text-gray-600">{{ __('Actief') }}</label>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showingModal', false)">{{ __('Annuleren') }}</x-secondary-button>
            <x-button class="ml-3" wire:click="save">{{ __('Opslaan') }}</x-button>
        </x-slot>
    </x-dialog-modal>
</div>
