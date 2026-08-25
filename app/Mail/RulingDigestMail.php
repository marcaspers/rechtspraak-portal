<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class RulingDigestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  Collection<int, \App\Models\Ruling>  $rulings
     */
    public function __construct(
        public readonly User $user,
        public readonly Collection $rulings,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nieuwe relevante rechtspraak — '.$this->rulings->count().' uitspraken',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.digest',
            with: [
                'rulingsByTheme' => $this->groupRulingsByTheme(),
            ],
        );
    }

    /**
     * @return array<string, Collection<int, \App\Models\Ruling>>
     */
    private function groupRulingsByTheme(): array
    {
        $rulingsByTheme = [];

        foreach ($this->rulings as $ruling) {
            $themeNames = $ruling->themes->isEmpty() ? ['Overig'] : $ruling->themes->pluck('name')->all();

            foreach ($themeNames as $themeName) {
                $rulingsByTheme[$themeName] ??= collect();
                $rulingsByTheme[$themeName]->push($ruling);
            }
        }

        return $rulingsByTheme;
    }
}
