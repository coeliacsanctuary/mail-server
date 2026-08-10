<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Dto\ApiResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class Recipe extends NewsletterComponent
{
    public string $description = '';

    public ?int $recipeId = null;

    public string $search = '';

    /** @var Collection<int, ApiResult> */
    public Collection $results;

    public ApiResult $recipe;

    public function mount(): void
    {
        $this->recipeId = $this->properties['content'] ?? null;
        $this->results = new Collection();

        if ($this->recipeId) {
            $this->recipe = $this->getRecipe();
            $this->description = $this->block === 'single'
                ? $this->recipe->description
                : $this->recipe->meta_description;
        }

        if (isset($this->properties['description'])) {
            $this->description = $this->properties['description'];
        }
    }

    public function updatedSearch(): void
    {
        $this->results = Http::coeliac()
            ->get('api/recipes', ['search' => $this->search])
            ->collect('data.data')
            ->map(fn (array $recipe) => new ApiResult(
                id: $recipe['id'],
                title: $recipe['title'],
                description: $recipe['description'],
                meta_description: $recipe['meta_description'],
                created_at: $recipe['created_at'],
                main_image: $recipe['main_image'],
                link: $recipe['link'],
            ));
    }

    public function updatedDescription(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function selectRecipe(int $id): void
    {
        $this->recipeId = $id;
        $this->recipe = $this->getRecipe($id);
        $this->description = $this->block === 'single'
            ? $this->recipe->description
            : $this->recipe->meta_description;

        $this->clearSearch();
        $this->syncProperties();
    }

    public function remove(): void
    {
        $this->recipeId = null;
        $this->description = '';

        $this->clearSearch();
        $this->syncProperties();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.recipe');
    }

    protected function getRecipe(?int $id = null): ApiResult
    {
        $id ??= $this->recipeId;

        $response = Http::coeliac()->get("api/recipes/{$id}")->json();

        return new ApiResult(...$response);
    }

    protected function clearSearch(): void
    {
        $this->search = '';
        $this->results = new Collection();
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        return [
            'content' => $this->recipeId,
            'description' => $this->description,
            'title' => $this->recipe->title,
            'image' => $this->recipe->main_image,
            'created_at' => $this->recipe->created_at,
            'link' => $this->recipe->link,
        ];
    }
}
