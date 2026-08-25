<x-mail::message>
# Nieuwe relevante rechtspraak

Hallo {{ $user->name }},

Hieronder vind je een overzicht van nieuwe uitspraken die aansluiten bij jouw thema's.

@foreach ($rulingsByTheme as $themeName => $rulings)
## {{ $themeName }}

@foreach ($rulings as $ruling)
**{{ $ruling->title }}**
{{ $ruling->ecli }} — {{ $ruling->instantie }} — {{ $ruling->published_at->format('d-m-Y') }}

{{ $ruling->currentSummary?->content }}

<x-mail::button :url="route('rulings.show', $ruling)">
Bekijk uitspraak
</x-mail::button>

---
@endforeach
@endforeach

Je ontvangt deze e-mail omdat je een {{ $user->digest_frequency->value }} digest hebt ingesteld in het portal. Je kunt dit aanpassen in je profielinstellingen.

Met vriendelijke groet,<br>
{{ config('app.name') }}
</x-mail::message>
