<?php

namespace App\Services\Classification;

use App\Models\Ruling;
use App\Models\Theme;
use App\Services\Classification\DTO\ThemeMatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class KeywordClassifier
{
    /**
     * Match a ruling's title + RSS summary against every active theme's keywords. This is the
     * cheap pre-filter (§2.2 step 1) — no LLM call involved.
     *
     * @return Collection<int, ThemeMatch>
     */
    public function classify(Ruling $ruling): Collection
    {
        $haystack = Str::lower($ruling->title.' '.$ruling->rss_summary);

        return Theme::query()
            ->where('is_active', true)
            ->get()
            ->map(function (Theme $theme) use ($haystack) {
                $matched = collect($theme->keywords ?? [])
                    ->filter(fn (string $keyword) => $keyword !== '' && str_contains($haystack, Str::lower($keyword)))
                    ->values()
                    ->all();

                return $matched === [] ? null : new ThemeMatch($theme, $matched);
            })
            ->filter()
            ->values();
    }
}
