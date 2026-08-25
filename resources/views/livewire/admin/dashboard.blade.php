<div class="space-y-6">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white shadow sm:rounded-lg p-4">
            <div class="text-sm text-gray-500">{{ __('Actieve feeds') }}</div>
            <div class="text-2xl font-semibold text-gray-900">{{ $feedsActive }}</div>
            @if ($feedsInactive > 0)
                <div class="text-xs text-gray-400">{{ $feedsInactive }} {{ __('gepauzeerd') }}</div>
            @endif
        </div>
        <div class="bg-white shadow sm:rounded-lg p-4">
            <div class="text-sm text-gray-500">{{ __('Samengevat') }}</div>
            <div class="text-2xl font-semibold text-gray-900">{{ $rulingsByStatus['summarized'] ?? 0 }}</div>
        </div>
        <div class="bg-white shadow sm:rounded-lg p-4">
            <div class="text-sm text-gray-500">{{ __('Gefaald') }}</div>
            <div class="text-2xl font-semibold text-red-600">{{ $rulingsByStatus['failed'] ?? 0 }}</div>
        </div>
        <div class="bg-white shadow sm:rounded-lg p-4">
            <div class="text-sm text-gray-500">{{ __('Feed-fetches gefaald (7d)') }}</div>
            <div class="text-2xl font-semibold {{ $failedFetchesLast7Days > 0 ? 'text-red-600' : 'text-gray-900' }}">
                {{ $failedFetchesLast7Days }}
            </div>
        </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('Uitspraken per status') }}</h3>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-sm">
            <div><span class="text-gray-500">{{ __('Wachtend') }}</span> — {{ $rulingsByStatus['pending'] ?? 0 }}</div>
            <div><span class="text-gray-500">{{ __('Niet-relevant') }}</span> — {{ $rulingsByStatus['filtered_out'] ?? 0 }}</div>
            <div><span class="text-gray-500">{{ __('Tekst opgehaald') }}</span> — {{ $rulingsByStatus['text_fetched'] ?? 0 }}</div>
            <div><span class="text-gray-500">{{ __('Samengevat') }}</span> — {{ $rulingsByStatus['summarized'] ?? 0 }}</div>
            <div><span class="text-gray-500">{{ __('Gefaald') }}</span> — {{ $rulingsByStatus['failed'] ?? 0 }}</div>
        </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('LLM-gebruik (laatste 7 dagen)') }}</h3>
        @forelse ($usageLast7Days as $row)
            <div class="text-sm flex gap-4">
                <span class="{{ $row->success ? 'text-green-700' : 'text-red-600' }}">
                    {{ $row->success ? __('Geslaagd') : __('Gefaald') }}
                </span>
                <span class="text-gray-500">{{ $row->calls }} {{ __('calls') }}</span>
                <span class="text-gray-500">{{ number_format($row->tokens) }} {{ __('tokens') }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">{{ __('Nog geen LLM-aanroepen in de afgelopen 7 dagen.') }}</p>
        @endforelse
    </div>

    <div class="bg-white shadow sm:rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('Recente feed-ophaal-runs') }}</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="pr-4 pb-2">{{ __('Feed') }}</th>
                        <th class="pr-4 pb-2">{{ __('Gestart') }}</th>
                        <th class="pr-4 pb-2">{{ __('Status') }}</th>
                        <th class="pr-4 pb-2">{{ __('Nieuw') }}</th>
                        <th class="pr-4 pb-2">{{ __('Fout') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentFetchLogs as $log)
                        <tr class="border-t">
                            <td class="pr-4 py-1">{{ $log->feed->name }}</td>
                            <td class="pr-4 py-1 text-gray-500">{{ $log->started_at->format('d-m-Y H:i') }}</td>
                            <td class="pr-4 py-1">
                                <span @class([
                                    'px-2 py-0.5 rounded text-xs font-medium',
                                    'bg-green-100 text-green-800' => $log->status->value === 'success',
                                    'bg-yellow-100 text-yellow-800' => $log->status->value === 'partial',
                                    'bg-red-100 text-red-800' => $log->status->value === 'failed',
                                ])>{{ $log->status->value }}</span>
                            </td>
                            <td class="pr-4 py-1">{{ $log->items_new }}</td>
                            <td class="pr-4 py-1 text-red-600 text-xs">{{ $log->error_message }}</td>
                        </tr>
                    @empty
                        <tr><td class="py-2 text-gray-500" colspan="5">{{ __('Nog geen feed-ophaal-runs.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-900 mb-3">{{ __('Gefaalde uitspraken') }}</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500">
                        <th class="pr-4 pb-2">{{ __('Titel') }}</th>
                        <th class="pr-4 pb-2">{{ __('ECLI') }}</th>
                        <th class="pr-4 pb-2">{{ __('Foutmelding') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($failedRulings as $ruling)
                        <tr class="border-t">
                            <td class="pr-4 py-1">{{ \Illuminate\Support\Str::limit($ruling->title, 60) }}</td>
                            <td class="pr-4 py-1 text-gray-500">{{ $ruling->ecli }}</td>
                            <td class="pr-4 py-1 text-red-600 text-xs">{{ \Illuminate\Support\Str::limit($ruling->error_message, 100) }}</td>
                        </tr>
                    @empty
                        <tr><td class="py-2 text-gray-500" colspan="3">{{ __('Geen gefaalde uitspraken.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
