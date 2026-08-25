<?php

namespace App\Livewire\Admin\Feeds;

use App\Enums\FeedSource;
use App\Models\Feed;
use Livewire\Component;

class Index extends Component
{
    public bool $showingModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $url = '';

    public string $source = '';

    public ?string $category_tag = null;

    public int $poll_interval_minutes = 60;

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'source' => 'required|in:'.implode(',', array_column(FeedSource::cases(), 'value')),
            'category_tag' => 'nullable|string|max:255',
            'poll_interval_minutes' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ];
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'url', 'source', 'category_tag', 'poll_interval_minutes', 'is_active']);
        $this->poll_interval_minutes = 60;
        $this->is_active = true;
        $this->showingModal = true;
    }

    public function edit(int $feedId): void
    {
        $feed = Feed::findOrFail($feedId);

        $this->editingId = $feed->id;
        $this->name = $feed->name;
        $this->url = $feed->url;
        $this->source = $feed->source->value;
        $this->category_tag = $feed->category_tag;
        $this->poll_interval_minutes = $feed->poll_interval_minutes;
        $this->is_active = $feed->is_active;
        $this->showingModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingId) {
            Feed::findOrFail($this->editingId)->update($validated);
        } else {
            Feed::create($validated);
        }

        $this->showingModal = false;
    }

    public function togglePause(int $feedId): void
    {
        $feed = Feed::findOrFail($feedId);
        $feed->update(['is_active' => ! $feed->is_active]);
    }

    public function delete(int $feedId): void
    {
        Feed::findOrFail($feedId)->delete();
    }

    public function render()
    {
        return view('livewire.admin.feeds.index', [
            'feeds' => Feed::query()->orderBy('name')->get(),
            'sources' => FeedSource::cases(),
        ]);
    }
}
