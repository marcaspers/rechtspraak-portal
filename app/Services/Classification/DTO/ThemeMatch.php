<?php

namespace App\Services\Classification\DTO;

use App\Models\Theme;

final class ThemeMatch
{
    /**
     * @param  array<int, string>  $matchedKeywords
     */
    public function __construct(
        public readonly Theme $theme,
        public readonly array $matchedKeywords,
    ) {
    }
}
