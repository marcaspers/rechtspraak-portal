<?php

namespace App\Livewire\Admin\Themes;

use App\Models\Theme;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Index extends Component
{
    public bool $showingModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public string $keywordsInput = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'keywordsInput' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    protected $validationAttributes = [
        'name' => 'naam',
    ];

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'description', 'keywordsInput', 'is_active']);
        $this->is_active = true;
        $this->showingModal = true;
    }

    public function edit(int $themeId): void
    {
        $theme = Theme::findOrFail($themeId);

        $this->editingId = $theme->id;
        $this->name = $theme->name;
        $this->description = (string) $theme->description;
        $this->keywordsInput = implode(', ', $theme->keywords ?? []);
        $this->is_active = $theme->is_active;
        $this->showingModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $slug = Str::slug($this->name);

        $slugTaken = Theme::query()
            ->where('slug', $slug)
            ->when($this->editingId, fn ($query, $id) => $query->where('id', '!=', $id))
            ->exists();

        if ($slugTaken) {
            throw ValidationException::withMessages([
                'name' => __('Er bestaat al een thema met (vrijwel) deze naam.'),
            ]);
        }

        $keywords = collect(explode(',', $this->keywordsInput))
            ->map(fn (string $keyword) => trim($keyword))
            ->filter()
            ->values()
            ->all();

        $attributes = [
            'name' => $this->name,
            'slug' => $slug,
            'description' => $this->description ?: null,
            'keywords' => $keywords,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            Theme::findOrFail($this->editingId)->update($attributes);
        } else {
            Theme::create($attributes);
        }

        $this->showingModal = false;
    }

    public function toggleActive(int $themeId): void
    {
        $theme = Theme::findOrFail($themeId);
        $theme->update(['is_active' => ! $theme->is_active]);
    }

    public function delete(int $themeId): void
    {
        Theme::findOrFail($themeId)->delete();
    }

    public function render()
    {
        return view('livewire.admin.themes.index', [
            'themes' => Theme::query()->orderBy('name')->get(),
        ]);
    }
}
