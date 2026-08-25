<?php

namespace App\Livewire\Rulings;

use App\Actions\Rulings\UpdateUserRulingStateAction;
use App\Models\Ruling;
use App\Models\Theme;
use App\Models\UserRulingState;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $theme = null;

    #[Url]
    public ?string $instantie = null;

    #[Url]
    public ?string $dateFrom = null;

    #[Url]
    public ?string $dateTo = null;

    public bool $onlyFavorites = false;

    public function updating($property): void
    {
        if (in_array($property, ['search', 'theme', 'instantie', 'dateFrom', 'dateTo', 'onlyFavorites'], true)) {
            $this->resetPage();
        }
    }

    public function toggleFavorite(int $rulingId, UpdateUserRulingStateAction $action): void
    {
        $ruling = Ruling::findOrFail($rulingId);
        $current = UserRulingState::query()
            ->where('user_id', auth()->id())
            ->where('ruling_id', $ruling->id)
            ->first();

        $action->execute(auth()->user(), $ruling, ['is_favorite' => ! ($current !== null && $current->is_favorite)]);
    }

    public function markRead(int $rulingId, UpdateUserRulingStateAction $action): void
    {
        $action->execute(auth()->user(), Ruling::findOrFail($rulingId), ['is_read' => true]);
    }

    public function render()
    {
        $rulings = Ruling::query()
            ->visible()
            ->with(['themes', 'feed', 'userStates' => fn ($query) => $query->where('user_id', auth()->id())])
            ->when($this->search !== '', fn ($query) => $query->search($this->search))
            ->when($this->theme, fn ($query) => $query->whereHas(
                'themes',
                fn ($query) => $query->where('themes.id', $this->theme)
            ))
            ->when($this->instantie, fn ($query) => $query->where('instantie', $this->instantie))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('published_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('published_at', '<=', $this->dateTo))
            ->when($this->onlyFavorites, fn ($query) => $query->whereHas(
                'userStates',
                fn ($query) => $query->where('user_id', auth()->id())->where('is_favorite', true)
            ))
            ->orderByDesc('published_at')
            ->paginate(20);

        return view('livewire.rulings.index', [
            'rulings' => $rulings,
            'themes' => Theme::query()->where('is_active', true)->orderBy('name')->get(),
            'instanties' => Ruling::query()->visible()->distinct()->pluck('instantie')->filter()->values(),
        ]);
    }
}
