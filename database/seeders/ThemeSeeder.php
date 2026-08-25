<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ThemeSeeder extends Seeder
{
    /**
     * Example themes for local development / demonstration. The exact list is
     * organization-specific and fully admin-configurable (briefing §2.2, §7) — these are
     * starting points, not fixed categories.
     */
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Arbeidsrecht',
                'description' => 'Uitspraken over arbeidsovereenkomsten, ontslag, en aanverwante arbeidsrechtelijke geschillen.',
                'keywords' => [
                    'arbeidsovereenkomst', 'ontslag', 'arbeidsrecht', 'concurrentiebeding',
                    'cao', 'loonvordering', 'ziekte en re-integratie', 'transitievergoeding',
                ],
            ],
            [
                'name' => 'AVG / Privacy',
                'description' => 'Uitspraken over gegevensbescherming, de AVG/GDPR, en privacyrecht.',
                'keywords' => [
                    'AVG', 'GDPR', 'persoonsgegevens', 'privacy', 'gegevensbescherming',
                    'datalek', 'verwerkingsverantwoordelijke', 'verwerker',
                ],
            ],
            [
                'name' => 'Aanbestedingsrecht',
                'description' => 'Uitspraken over aanbestedingsprocedures en aanbestedingsrecht.',
                'keywords' => [
                    'aanbesteding', 'aanbestedingsrecht', 'aanbestedende dienst',
                    'gunning', 'inschrijving', 'aanbestedingswet',
                ],
            ],
        ];

        foreach ($themes as $theme) {
            Theme::query()->updateOrCreate(
                ['slug' => Str::slug($theme['name'])],
                [
                    'name' => $theme['name'],
                    'description' => $theme['description'],
                    'keywords' => $theme['keywords'],
                    'is_active' => true,
                ]
            );
        }
    }
}
