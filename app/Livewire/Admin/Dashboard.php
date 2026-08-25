<?php

namespace App\Livewire\Admin;

use App\Enums\FeedFetchStatus;
use App\Enums\RulingStatus;
use App\Models\Feed;
use App\Models\FeedFetchLog;
use App\Models\LlmUsageLog;
use App\Models\Ruling;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $rulingsByStatus = DB::table('rulings')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $usageLast7Days = LlmUsageLog::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('success, count(*) as calls, coalesce(sum(tokens_total), 0) as tokens')
            ->groupBy('success')
            ->get();

        return view('livewire.admin.dashboard', [
            'feedsActive' => Feed::query()->where('is_active', true)->count(),
            'feedsInactive' => Feed::query()->where('is_active', false)->count(),
            'rulingsByStatus' => $rulingsByStatus,
            'recentFetchLogs' => FeedFetchLog::query()
                ->with('feed')
                ->latest('started_at')
                ->limit(15)
                ->get(),
            'failedRulings' => Ruling::query()
                ->where('status', RulingStatus::Failed)
                ->latest('updated_at')
                ->limit(15)
                ->get(),
            'usageLast7Days' => $usageLast7Days,
            'failedFetchesLast7Days' => FeedFetchLog::query()
                ->where('status', FeedFetchStatus::Failed)
                ->where('started_at', '>=', now()->subDays(7))
                ->count(),
        ]);
    }
}
