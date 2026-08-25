<?php

namespace App\Livewire\Profile;

use App\Enums\DigestFrequency;
use Livewire\Component;

class DigestPreference extends Component
{
    public string $digestFrequency;

    public function mount(): void
    {
        $this->digestFrequency = auth()->user()->digest_frequency->value;
    }

    public function update(): void
    {
        $this->validate([
            'digestFrequency' => 'required|in:'.implode(',', array_column(DigestFrequency::cases(), 'value')),
        ]);

        auth()->user()->update(['digest_frequency' => $this->digestFrequency]);

        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.profile.digest-preference', [
            'options' => DigestFrequency::cases(),
        ]);
    }
}
