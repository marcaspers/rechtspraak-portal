<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow sm:rounded-lg p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="lg:col-span-2">
                        <x-label for="search" value="{{ __('Zoeken') }}" />
                        <x-input id="search" type="text" class="mt-1 block w-full" wire:model.live.debounce.400ms="search" placeholder="Titel, ECLI, samenvatting..." />
                    </div>

                    <div>
                        <x-label for="theme" value="{{ __('Thema') }}" />
                        <select id="theme" wire:model.live="theme" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('Alle thema\'s') }}</option>
                            @foreach ($themes as $themeOption)
                                <option value="{{ $themeOption->id }}">{{ $themeOption->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-label for="instantie" value="{{ __('Instantie') }}" />
                        <select id="instantie" wire:model.live="instantie" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('Alle instanties') }}</option>
                            @foreach ($instanties as $instantieOption)
                                <option value="{{ $instantieOption }}">{{ $instantieOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <x-label for="dateFrom" value="{{ __('Vanaf') }}" />
                            <x-input id="dateFrom" type="date" class="mt-1 block w-full" wire:model.live="dateFrom" />
                        </div>
                        <div class="flex-1">
                            <x-label for="dateTo" value="{{ __('Tot') }}" />
                            <x-input id="dateTo" type="date" class="mt-1 block w-full" wire:model.live="dateTo" />
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex items-center">
                    <input id="onlyFavorites" type="checkbox" wire:model.live="onlyFavorites" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <label for="onlyFavorites" class="ml-2 text-sm text-gray-600">{{ __('Alleen favorieten') }}</label>
                </div>
            </div>

            <div class="space-y-4">
                @forelse ($rulings as $ruling)
                    @php($state = $ruling->userStates->first())
                    <div class="bg-white shadow sm:rounded-lg p-5 {{ $state?->is_read ? 'opacity-70' : '' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap gap-2 mb-1">
                                    @foreach ($ruling->themes as $ruligTheme)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $ruligTheme->name }}
                                        </span>
                                    @endforeach
                                </div>

                                <a href="{{ route('rulings.show', $ruling) }}" wire:click="markRead({{ $ruling->id }})" class="text-lg font-semibold text-gray-900 hover:text-indigo-600">
                                    {{ $ruling->title }}
                                </a>

                                <div class="text-sm text-gray-500 mt-1">
                                    {{ $ruling->ecli }} &middot; {{ $ruling->instantie }} &middot; {{ $ruling->published_at->format('d-m-Y') }}
                                </div>

                                <p class="mt-2 text-gray-700 text-sm">
                                    {{ \Illuminate\Support\Str::limit($ruling->currentSummary?->content, 240) }}
                                </p>
                            </div>

                            <button wire:click="toggleFavorite({{ $ruling->id }})" class="shrink-0 text-2xl {{ $state?->is_favorite ? 'text-yellow-500' : 'text-gray-300' }}" title="{{ __('Favoriet') }}">
                                &#9733;
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="bg-white shadow sm:rounded-lg p-6 text-center text-gray-500">
                        {{ __('Geen uitspraken gevonden.') }}
                    </div>
                @endforelse
            </div>

            <div>
                {{ $rulings->links() }}
            </div>
        </div>
    </div>
</div>
