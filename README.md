# Rechtspraak Portal

Interne portal die RSS/Atom-feeds van rechtspraakbronnen (Rechtspraak.nl, HvJ EU, EHRM) ophaalt,
uitspraken classificeert op configureerbare thema's, en per relevante uitspraak een
Nederlandstalige samenvatting genereert via een instelbare LLM-provider (Anthropic, OpenAI, of
lokaal Ollama).

Dit document beschrijft installatie, configuratie en deployment.

## Tech-stack

| Onderdeel        | Keuze                                   | Waarom |
|-------------------|------------------------------------------|--------|
| Framework          | Laravel 12 (PHP 8.3)                     | Actuele stabiele versie op moment van bouwen. |
| Database           | PostgreSQL 16                            | Betere full-text search (`tsvector`/GIN) en `jsonb`-ondersteuning dan MySQL — expliciete voorkeur uit de briefing. |
| Queue/cache        | Redis                                    | Achtergrondverwerking (feed-ophaal, LLM-taken) mag nooit een request blokkeren. |
| Frontend           | Blade + Livewire (Jetstream, Livewire-stack, teams uit) | Bundelt Fortify + 2FA/OTP-UI kant-en-klaar. |
| RSS/Atom-parsing   | `laminas/laminas-feed`                   | `willvincent/feeds` (uit de briefing) is sinds ~2014 niet meer onderhouden en ondersteunt geen Laravel 10+; laminas-feed wel. |
| Deployment         | Docker (Coolify-compatibel), `serversideup/php` images | Doelplatform is Coolify; deze images zijn specifiek gebouwd voor Laravel-op-Docker/Coolify. |

## Architectuur in het kort

- **Provider-abstractie voor LLM's** — `App\Services\Llm\Contracts\LlmProviderInterface` met
  concrete implementaties per provider (`AnthropicProvider`, `OpenAiProvider`, `OllamaProvider`).
  Welke provider + welk model per taak (classificatie vs. samenvatting) wordt gebruikt, staat in
  de database (`llm_task_configs`) en is dus **runtime aanpasbaar via het dashboard/tinker**,
  zonder herdeploy. API-keys blijven in `.env`. Zie `App\Services\Llm\LlmManager`.
- **Verwerkingspijplijn**: `FetchFeedJob` → dedupliceren op ECLI/GUID → `ClassifyRulingJob`
  (keyword-classificatie; LLM-classificatie is voorbereid maar staat standaard uit) → bij een
  match: `FetchFullTextJob` → `SummarizeRulingJob`. Elke stap is een aparte queue-job met eigen
  retry/backoff, zodat één falende feed of uitspraak de rest niet blokkeert.
  Zie [docs/PRIVACY_NOTES.md](docs/PRIVACY_NOTES.md) voor wat er wel/niet naar een externe
  LLM-provider wordt verstuurd.
- **Full-text ophalen**: alleen voor uitspraken die de thema-filter doorstaan (bespaart kosten).
  Rechtspraak.nl gebruikt de officiële Open Data content-endpoint; Curia/EHRM hebben een
  voorbereide maar nog niet geïmplementeerde fetcher (zie `App\Services\FullText`).
- **Scheduler**: één schedule-entry (`feeds:fetch-due`, elke minuut) leest per feed diens eigen
  `poll_interval_minutes` uit de database — nieuwe feeds of aangepaste intervallen vereisen dus
  geen wijziging aan de scheduler zelf.

## MVP-scope

Deze eerste versie implementeert briefing §6 volledig: databaseschema + kern-architectuur, één
werkende feed (Rechtspraak.nl), alle drie de LLM-providers (interface + implementaties),
keyword-classificatie, en een basisportal (overzicht, detail, login met 2FA, e-maildigest) —
zonder geavanceerde admin-UI. Beheer van feeds/thema's/LLM-config gebeurt voorlopig via seeders of
`php artisan tinker`. Zie de briefing voor wat in latere iteraties volgt (admin-UI,
LLM-classificatie aanzetten, Curia/EHRM full-text).

## Installatie (lokale ontwikkeling)

Vereisten: PHP 8.3+, Composer, Node.js 20+, Docker (voor Postgres/Redis).

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
docker compose up -d postgres redis
php artisan migrate --seed
npm run build
```

Start de applicatie en achtergrondprocessen (elk in een eigen terminal):

```bash
php artisan serve
php artisan queue:work
php artisan schedule:work
npm run dev   # voor Vite hot-reload tijdens front-end ontwikkeling
```

De seeders maken een admin-account aan (`ADMIN_EMAIL`/`ADMIN_PASSWORD` in `.env`, standaard
`admin@example.com` / `password` — wijzig dit wachtwoord direct na de eerste login), een aantal
voorbeeldthema's, de Rechtspraak.nl-feed, en de standaard LLM-taakconfiguratie en prompt-template.

Om de pijplijn handmatig te testen zonder op de scheduler te wachten:

```bash
php artisan tinker
>>> App\Jobs\FetchFeedJob::dispatchSync(App\Models\Feed::first());
```

## Benodigde `.env`-variabelen

Zie `.env.example` voor de volledige lijst met toelichting. De belangrijkste categorieën:

- **Database/Redis**: `DB_*`, `REDIS_*` — standaard PostgreSQL + Redis.
- **Mail**: `MAIL_MAILER` + bijbehorende credentials. Gebruik `smtp` met Mailtrap-credentials in
  development/staging, en `postmark` met `POSTMARK_TOKEN` in productie — dit is een pure
  configuratiewissel via Laravel's Mail-abstractie, geen codewijziging.
- **LLM-providers**: `ANTHROPIC_API_KEY`, `OPENAI_API_KEY`, `OLLAMA_BASE_URL`. Welk van deze drie
  providers (en welk model) daadwerkelijk gebruikt wordt per taak, staat in de
  `llm_task_configs`-tabel, niet in `.env` — dat is bewust, zodat een beheerder dit later via een
  dashboard kan wijzigen zonder herdeploy.
- **Digest**: `DIGEST_SEND_HOUR` — uur waarop de dagelijkse/wekelijkse e-maildigest wordt
  verstuurd (het `digest:send`-commando draait elk uur en doet verder niets buiten dit uur).

## Tests & codekwaliteit

```bash
php artisan test          # Pest
vendor/bin/pint --test    # PSR-12 codestijl
vendor/bin/phpstan analyse # Larastan, level 5
```

## Deployment via Coolify

De applicatie is volledig Docker-gebaseerd (`Dockerfile`, `docker-compose.yml`) met aparte
services voor `app` (web), `worker` (queue), `scheduler`, `postgres` en `redis` — elk als los
Coolify-resource te draaien. Environment-variabelen worden via Coolify's eigen env-management per
service ingesteld (nooit hardcoded in de image).

Belangrijk: het meegeleverde `docker-compose.yml` bevat `DB_HOST=postgres`/`REDIS_HOST=redis`
overrides voor de `app`/`worker`/`scheduler`-services, zodat deze de meegeleverde Postgres/Redis-
containers via het Docker-netwerk bereiken. De root-`.env` zelf gebruikt `127.0.0.1` met
afwijkende poorten, bedoeld voor lokale ontwikkeling via `php artisan serve` (waarbij alleen
`postgres`/`redis` uit docker-compose draaien en via hun published ports bereikbaar zijn). Stel in
Coolify per service de juiste `DB_HOST`/`REDIS_HOST` in via Coolify's eigen env-management.

Migraties worden bewust niet automatisch bij het opstarten van de container gedraaid (zodat een
kapotte migratie de webservice niet blokkeert) — draai ze als losse stap na deployment:

```bash
docker compose exec app php artisan migrate --force
```

## Privacy

Zie [docs/PRIVACY_NOTES.md](docs/PRIVACY_NOTES.md) voor een feitelijk overzicht van welke data
naar externe LLM-providers wordt verstuurd (en welke nooit).
