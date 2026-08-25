<div>
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach ($ruling->themes as $theme)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ $theme->name }}
                        </span>
                    @endforeach
                </div>

                <h1 class="text-2xl font-bold text-gray-900">{{ $ruling->title }}</h1>

                <dl class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-2 text-sm text-gray-500">
                    <div>
                        <dt class="font-medium text-gray-400">{{ __('ECLI') }}</dt>
                        <dd>{{ $ruling->ecli ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-400">{{ __('Instantie') }}</dt>
                        <dd>{{ $ruling->instantie ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-400">{{ __('Datum') }}</dt>
                        <dd>{{ $ruling->published_at->format('d-m-Y') }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-400">{{ __('Bron') }}</dt>
                        <dd>{{ $ruling->feed->name }}</dd>
                    </div>
                </dl>

                <div class="mt-6 flex gap-3">
                    <x-secondary-button wire:click="toggleFavorite">
                        {{ $state?->is_favorite ? __('★ Favoriet') : __('☆ Markeer als favoriet') }}
                    </x-secondary-button>

                    <x-secondary-button wire:click="toggleArchived">
                        {{ $state?->is_archived ? __('Gearchiveerd') : __('Archiveren') }}
                    </x-secondary-button>

                    <a href="{{ $ruling->source_url }}" target="_blank" rel="noopener" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500">
                        {{ __('Bekijk origineel') }}
                    </a>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('Samenvatting') }}</h3>
                <p class="text-gray-700 whitespace-pre-line">{{ $ruling->currentSummary?->content }}</p>

                @if ($ruling->currentSummary?->relevance_note)
                    <div class="mt-4 border-t pt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-1">{{ __('Waarom relevant') }}</h4>
                        <p class="text-gray-600 text-sm">{{ $ruling->currentSummary->relevance_note }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
