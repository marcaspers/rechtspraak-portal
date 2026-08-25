<?php

namespace App\Services\FullText;

use App\Models\Ruling;
use App\Services\FullText\Contracts\FullTextFetcherInterface;
use App\Services\FullText\Exceptions\FullTextFetchException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Fetches full ruling text from Rechtspraak.nl's Open Data content endpoint, which returns
 * structured XML (metadata + inline XHTML body) keyed by ECLI. This is preferred over scraping
 * the public search-result HTML page, which is not designed for machine consumption.
 *
 * @see https://www.rechtspraak.nl/Uitspraken/paginas/open-data.aspx
 */
class RechtspraakFetcher implements FullTextFetcherInterface
{
    private const CONTENT_ENDPOINT = 'https://data.rechtspraak.nl/uitspraken/content';

    public function fetch(Ruling $ruling): string
    {
        if (! $ruling->ecli) {
            throw new FullTextFetchException(
                "Cannot fetch full text for ruling [{$ruling->id}]: no ECLI number available."
            );
        }

        $response = Http::retry(3, 1000, throw: false)
            ->timeout(60)
            ->get(self::CONTENT_ENDPOINT, ['id' => $ruling->ecli]);

        if ($response->failed()) {
            throw new FullTextFetchException(
                "Rechtspraak content endpoint returned status {$response->status()} for ECLI {$ruling->ecli}."
            );
        }

        $body = trim($response->body());

        if ($body === '') {
            throw new FullTextFetchException("Rechtspraak content endpoint returned an empty body for ECLI {$ruling->ecli}.");
        }

        $crawler = new Crawler();
        $crawler->addXmlContent($body, 'UTF-8', LIBXML_NOERROR | LIBXML_NOWARNING);

        $node = $crawler->filterXPath('//*[local-name()="uitspraak" or local-name()="conclusie"]');

        if ($node->count() === 0) {
            throw new FullTextFetchException("No <uitspraak> element found in Rechtspraak XML for ECLI {$ruling->ecli}.");
        }

        $text = trim(preg_replace('/\s+/u', ' ', $node->text()));

        if ($text === '') {
            throw new FullTextFetchException("Rechtspraak XML for ECLI {$ruling->ecli} contained no text content.");
        }

        return $text;
    }
}
