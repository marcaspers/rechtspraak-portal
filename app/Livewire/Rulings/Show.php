<?php

namespace App\Livewire\Rulings;

use App\Actions\Rulings\UpdateUserRulingStateAction;
use App\Enums\RulingStatus;
use App\Models\Ruling;
use App\Models\UserRulingState;
use Livewire\Component;

class Show extends Component
{
    public Ruling $ruling;

    public function mount(Ruling $ruling, UpdateUserRulingStateAction $action): void
    {
        abort_unless($ruling->status === RulingStatus::Summarized, 404);

        $this->ruling = $ruling->load(['themes', 'feed', 'currentSummary']);

        $action->execute(auth()->user(), $this->ruling, ['is_read' => true]);
    }

    public function toggleFavorite(UpdateUserRulingStateAction $action): void
    {
        $current = $this->state();

        $action->execute(auth()->user(), $this->ruling, ['is_favorite' => ! ($current !== null && $current->is_favorite)]);
    }

    public function toggleArchived(UpdateUserRulingStateAction $action): void
    {
        $current = $this->state();

        $action->execute(auth()->user(), $this->ruling, ['is_archived' => ! ($current !== null && $current->is_archived)]);
    }

    public function state(): ?UserRulingState
    {
        return UserRulingState::query()
            ->where('user_id', auth()->id())
            ->where('ruling_id', $this->ruling->id)
            ->first();
    }

    public function render()
    {
        return view('livewire.rulings.show', [
            'state' => $this->state(),
        ]);
    }
}
