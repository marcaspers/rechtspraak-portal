<div class="space-y-8">
    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">{{ __('Provider per taak') }}</h3>
        <p class="text-sm text-gray-500 mb-4">
            {{ __('Kies per taak welke LLM-provider en welk model gebruikt wordt. Classificatie kan hier uitgeschakeld blijven — het systeem valt dan terug op de kosteloze trefwoord-classificatie.') }}
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach ($taskKeys as $taskKey)
                <div class="bg-white shadow sm:rounded-lg p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-medium text-gray-900">
                            {{ $taskKey === App\Enums\LlmTaskKey::Summarization ? __('Samenvatting') : __('Classificatie (LLM)') }}
                        </h4>
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" wire:model="taskConfigs.{{ $taskKey->value }}.is_active" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            {{ __('Actief') }}
                        </label>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <x-label value="{{ __('Provider') }}" />
                            <select wire:model.live="taskConfigs.{{ $taskKey->value }}.provider" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach ($providers as $providerOption)
                                    <option value="{{ $providerOption->value }}">{{ $providerOption->value }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="taskConfigs.{{ $taskKey->value }}.provider" class="mt-1" />
                        </div>

                        <div>
                            <x-label value="{{ __('Model') }}" />
                            <x-input type="text" class="mt-1 block w-full" wire:model="taskConfigs.{{ $taskKey->value }}.model" placeholder="bv. claude-sonnet-4-5-20250929" />
                            <x-input-error for="taskConfigs.{{ $taskKey->value }}.model" class="mt-1" />

                            @if ($taskConfigs[$taskKey->value]['provider'] === App\Enums\LlmProvider::Ollama->value)
                                <div class="mt-2">
                                    <button type="button"
                                        wire:click="loadOllamaModels('{{ $taskKey->value }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="loadOllamaModels('{{ $taskKey->value }}')"
                                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800 disabled:opacity-50">
                                        {{ __('Beschikbare modellen ophalen uit Ollama') }}
                                    </button>
                                    <span wire:loading wire:target="loadOllamaModels('{{ $taskKey->value }}')" class="ml-2 text-xs text-gray-400">
                                        {{ __('Laden…') }}
                                    </span>

                                    @if ($ollamaModelsError[$taskKey->value] ?? null)
                                        <p class="mt-1 text-xs text-red-600">{{ $ollamaModelsError[$taskKey->value] }}</p>
                                    @elseif (isset($ollamaModels[$taskKey->value]))
                                        @if (empty($ollamaModels[$taskKey->value]))
                                            <p class="mt-1 text-xs text-gray-500">{{ __('Geen modellen gevonden op deze Ollama-server.') }}</p>
                                        @else
                                            <div class="mt-2 flex flex-wrap gap-1">
                                                @foreach ($ollamaModels[$taskKey->value] as $modelName)
                                                    <button type="button"
                                                        wire:click="$set('taskConfigs.{{ $taskKey->value }}.model', '{{ $modelName }}')"
                                                        class="px-2 py-0.5 rounded text-xs {{ $taskConfigs[$taskKey->value]['model'] === $modelName ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                                        {{ $modelName }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div>
                            <x-label value="{{ __('Endpoint (optioneel, vooral voor Ollama)') }}" />
                            <x-input type="text" class="mt-1 block w-full" wire:model="taskConfigs.{{ $taskKey->value }}.endpoint" placeholder="http://localhost:11434" />
                            <x-input-error for="taskConfigs.{{ $taskKey->value }}.endpoint" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <x-label value="{{ __('Temperature') }}" />
                                <x-input type="number" step="0.1" min="0" max="2" class="mt-1 block w-full" wire:model="taskConfigs.{{ $taskKey->value }}.temperature" />
                                <x-input-error for="taskConfigs.{{ $taskKey->value }}.temperature" class="mt-1" />
                            </div>
                            <div>
                                <x-label value="{{ __('Max tokens') }}" />
                                <x-input type="number" min="1" class="mt-1 block w-full" wire:model="taskConfigs.{{ $taskKey->value }}.max_tokens" />
                                <x-input-error for="taskConfigs.{{ $taskKey->value }}.max_tokens" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-right">
                        <x-button wire:click="saveTaskConfig('{{ $taskKey->value }}')">{{ __('Opslaan') }}</x-button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">{{ __('Prompt-templates') }}</h3>
        <p class="text-sm text-gray-500 mb-4">
            {{ __('Precies één template per taak kan actief zijn. Nieuwe versies starten inactief, zodat je kunt controleren voordat je ze activeert.') }}
        </p>

        <div class="space-y-6">
            @foreach ($taskKeys as $taskKey)
                <div class="bg-white shadow sm:rounded-lg p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-medium text-gray-900">
                            {{ $taskKey === App\Enums\LlmTaskKey::Summarization ? __('Samenvatting') : __('Classificatie (LLM)') }}
                        </h4>
                        <x-secondary-button wire:click="createTemplate('{{ $taskKey->value }}')">{{ __('Nieuwe versie') }}</x-secondary-button>
                    </div>

                    <div class="divide-y">
                        @forelse ($templatesByTask[$taskKey->value] ?? [] as $template)
                            <div class="py-3 flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $template->name }}
                                        <span class="text-gray-400 font-normal">v{{ $template->version }}</span>
                                        @if ($template->is_active)
                                            <span class="ml-2 px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">{{ __('actief') }}</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($template->content, 160) }}</p>
                                </div>
                                <div class="shrink-0 whitespace-nowrap">
                                    <button type="button" wire:click="editTemplate({{ $template->id }})" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">{{ __('Bewerken') }}</button>
                                    @unless ($template->is_active)
                                        <button type="button" wire:click="activateTemplate({{ $template->id }})" class="ml-3 text-gray-600 hover:text-gray-800 text-xs font-medium">{{ __('Activeren') }}</button>
                                        <button type="button" wire:click="deleteTemplate({{ $template->id }})" wire:confirm="{{ __('Deze template verwijderen?') }}" class="ml-3 text-red-600 hover:text-red-800 text-xs font-medium">{{ __('Verwijderen') }}</button>
                                    @endunless
                                </div>
                            </div>
                        @empty
                            <p class="py-3 text-sm text-gray-500">{{ __('Nog geen template voor deze taak.') }}</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <x-dialog-modal wire:model="showingTemplateModal" maxWidth="2xl">
        <x-slot name="title">
            {{ $editingTemplateId ? __('Template bewerken') : __('Nieuwe template') }}
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="templateName" value="{{ __('Naam') }}" />
                    <x-input id="templateName" type="text" class="mt-1 block w-full" wire:model="templateName" />
                    <x-input-error for="templateName" class="mt-1" />
                </div>
                <div>
                    <x-label for="templateContent" value="{{ __('Inhoud') }}" />
                    <textarea id="templateContent" wire:model="templateContent" rows="14" class="mt-1 block w-full font-mono text-xs border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                    <x-input-error for="templateContent" class="mt-1" />
                    <p class="mt-1 text-xs text-gray-400">
                        {{ __('Plaatshouders zoals') }}
                        <code>@{{title}}</code>, <code>@{{ecli}}</code>, <code>@{{instantie}}</code>,
                        <code>@{{published_at}}</code>, <code>@{{themes}}</code> {{ __('en') }} <code>@{{full_text}}</code>
                        {{ __('worden als platte tekst vervangen (geen Blade).') }}
                    </p>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showingTemplateModal', false)">{{ __('Annuleren') }}</x-secondary-button>
            <x-button class="ml-3" wire:click="saveTemplate">{{ __('Opslaan') }}</x-button>
        </x-slot>
    </x-dialog-modal>
</div>
