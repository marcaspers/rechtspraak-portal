<?php

namespace Database\Seeders;

use App\Enums\FeedSource;
use App\Models\Feed;
use Illuminate\Database\Seeder;

class FeedSeeder extends Seeder
{
    /**
     * Rechtspraak.nl's Open Data "zoeken" endpoint is a documented, live Atom feed of recent
     * ECLI rulings (verified against https://data.rechtspraak.nl/uitspraken/zoeken at build
     * time) — this is the one feed that must work end-to-end for the MVP. Add a `subject=`
     * query parameter (see the Open Data technical documentation) to scope this to a specific
     * rechtsgebied; briefing §7 leaves the exact initial feed selection as a non-blocking,
     * admin-configurable choice.
     *
     * The Curia (HvJ EU) and EHRM feeds below are placeholders — their exact feed URLs are left
     * as a TODO for an admin to verify and activate; full-text fetching for those sources is
     * not yet implemented either (see App\Services\FullText\CuriaFetcher / EchrFetcher).
     */
    public function run(): void
    {
        Feed::query()->updateOrCreate(
            ['name' => 'Rechtspraak.nl — recente uitspraken'],
            [
                'url' => 'https://data.rechtspraak.nl/uitspraken/zoeken?max=50&sort=DESC',
                'source' => FeedSource::Rechtspraak,
                'category_tag' => null,
                'poll_interval_minutes' => 60,
                'is_active' => true,
            ]
        );

        Feed::query()->updateOrCreate(
            ['name' => 'HvJ EU / Curia (TE VERIFIËREN)'],
            [
                'url' => 'https://curia.europa.eu/jcms/jcms/Jo2_7052/',
                'source' => FeedSource::Curia,
                'category_tag' => null,
                'poll_interval_minutes' => 240,
                'is_active' => false,
            ]
        );

        Feed::query()->updateOrCreate(
            ['name' => 'EHRM / HUDOC (TE VERIFIËREN)'],
            [
                'url' => 'https://hudoc.echr.coe.int/',
                'source' => FeedSource::Echr,
                'category_tag' => null,
                'poll_interval_minutes' => 240,
                'is_active' => false,
            ]
        );
    }
}
