# Privacy-aandachtspunten (LLM-verwerking)

Deze notitie beschrijft feitelijk welke data naar externe LLM-providers wordt gestuurd. Het is
input voor een formele beoordeling (bijv. door een privacyjurist), geen juridisch advies op zich.

## Wat wordt verstuurd

- **Classificatie** (indien LLM-classificatie is ingeschakeld, zie `llm_task_configs`): de titel
  en RSS-samenvatting van een uitspraak (`rulings.title`, `rulings.rss_summary`), plus de namen
  en omschrijvingen van de geconfigureerde thema's.
- **Samenvatting**: de volledige, publiek beschikbare uitspraaktekst (`rulings.full_text`,
  opgehaald via de open databank van de bron — bijv. Rechtspraak.nl Open Data), metadata (ECLI,
  instantie, datum) en de gekoppelde thema-namen.

In beide gevallen betreft dit uitsluitend **publiek gepubliceerde rechtspraak** (reeds
gepseudonimiseerd door de bron, bijv. Rechtspraak.nl). Er wordt geen interne bedrijfsinformatie,
geen data over gebruikers van dit portal, en geen data van derden buiten de uitspraaktekst zelf
meegestuurd naar een LLM-provider.

## Wat nooit wordt verstuurd

- Gebruikersgegevens (namen, e-mailadressen, leesgedrag, favorieten) — deze blijven in de eigen
  database en worden nooit als onderdeel van een LLM-prompt meegestuurd.
- API-keys of andere secrets — deze worden alleen gebruikt voor authenticatie van de aanroep
  zelf (`.env`), nooit in de prompt-inhoud.

## Provider-keuze en lokale verwerking

Voor gevoeligere toepassingen (of als externe verwerking onwenselijk is) ondersteunt de
provider-abstractielaag een lokale Ollama-instantie (`App\Services\Llm\Providers\OllamaProvider`)
als drop-in vervanging voor Anthropic/OpenAI — zonder dat data het eigen netwerk verlaat. Welke
provider per taak (classificatie/samenvatting) wordt gebruikt is runtime instelbaar via de
`llm_task_configs`-tabel.

## Bewaring van brontekst

De volledige uitspraaktekst (`rulings.full_text`) wordt na summarization niet automatisch
verwijderd (zie briefing §2.3) — dit is een bewuste afweging tussen opslagkosten en de
mogelijkheid om een samenvatting later te regenereren zonder opnieuw te hoeven scrapen. Indien
gewenst kan een periodieke opschoontaak worden toegevoegd die `full_text` leegmaakt na
succesvolle summarization, met behoud van de samenvatting, metadata en bron-URL.
