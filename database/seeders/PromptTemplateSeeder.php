<?php

namespace Database\Seeders;

use App\Enums\LlmTaskKey;
use App\Models\PromptTemplate;
use Illuminate\Database\Seeder;

class PromptTemplateSeeder extends Seeder
{
    public function run(): void
    {
        PromptTemplate::query()->updateOrCreate(
            ['task_key' => LlmTaskKey::Summarization, 'name' => 'Standaard samenvatting (NL)'],
            [
                'content' => <<<'PROMPT'
                Je bent een juridisch redacteur die beknopte Nederlandstalige samenvattingen schrijft van
                rechterlijke uitspraken voor interne juridische professionals. Gebruik uitsluitend de
                onderstaande, publiek beschikbare uitspraaktekst als bron.

                Uitspraak:
                - Titel: {{title}}
                - ECLI: {{ecli}}
                - Instantie: {{instantie}}
                - Datum: {{published_at}}
                - Gekoppelde thema's: {{themes}}

                Volledige tekst van de uitspraak:
                {{full_text}}

                Schrijf een beknopte samenvatting (3-5 zinnen) van de kernoverweging en de uitkomst van
                deze uitspraak, in het Nederlands, ongeacht de brontaal van de uitspraak. Schrijf
                vervolgens in één zin waarom deze uitspraak relevant is voor de gekoppelde thema's.

                Antwoord exact in dit format, zonder inleidende tekst:

                SAMENVATTING:
                <jouw samenvatting hier>

                RELEVANTIE:
                <één zin over de relevantie>
                PROMPT,
                'version' => 1,
                'is_active' => true,
            ]
        );
    }
}
