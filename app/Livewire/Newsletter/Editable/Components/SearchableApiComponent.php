<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Dto\ApiResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

abstract class SearchableApiComponent extends NewsletterComponent
{
    public ?int $selectedId = null;

    public ?ApiResult $selected = null;

    public string $search = '';

    public string $description = '';

    /** @var Collection<int, ApiResult> */
    public Collection $results;

    abstract protected function endpoint(): string;

    abstract protected function label(): string;

    protected function heading(): string
    {
        return Str::headline(class_basename(static::class));
    }

    public function mount(): void
    {
        $this->results = new Collection();
        $this->selectedId = $this->properties['content'] ?? null;

        if ($this->selectedId) {
            $this->selected = $this->fetch($this->selectedId);
            $this->description = $this->defaultDescription($this->selected);
        }

        if (isset($this->properties['description'])) {
            $this->description = $this->properties['description'];
        }
    }

    public function updatedSearch(): void
    {
        $this->results = Http::coeliac()
            ->get($this->endpoint(), ['search' => $this->search])
            ->collect($this->searchResultsPath())
            ->map(fn (array $result) => $this->toApiResult($result));
    }

    public function updatedDescription(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function select(int $id): void
    {
        $this->selectedId = $id;
        $this->selected = $this->fetch($id);
        $this->description = $this->defaultDescription($this->selected);

        $this->clearSearch();
        $this->syncProperties();
    }

    public function remove(): void
    {
        $this->selectedId = null;
        $this->selected = null;
        $this->description = '';

        $this->clearSearch();
        $this->syncProperties();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.searchable-api', [
            'label' => $this->label(),
            'heading' => $this->heading(),
            'meta' => $this->selected instanceof ApiResult ? $this->meta($this->selected) : '',
        ]);
    }

    protected function searchResultsPath(): string
    {
        return 'data.data';
    }

    protected function meta(ApiResult $result): string
    {
        return $result->created_at;
    }

    /** @return array<string, mixed> */
    protected function extraProperties(ApiResult $result): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function extra(array $payload): array
    {
        return [];
    }

    protected function fetch(int $id): ApiResult
    {
        return $this->toApiResult(
            Http::coeliac()->get($this->endpoint() . "/{$id}")->json(),
        );
    }

    /** @param array<string, mixed> $payload */
    protected function toApiResult(array $payload): ApiResult
    {
        return new ApiResult(
            id: (int) $payload['id'],
            title: $payload['title'],
            description: $payload['description'],
            meta_description: $payload['meta_description'],
            created_at: $payload['created_at'],
            main_image: $payload['main_image'],
            link: $payload['link'],
            extra: $this->extra($payload),
        );
    }

    protected function defaultDescription(ApiResult $result): string
    {
        return $this->block === 'single'
            ? $result->description
            : $result->meta_description;
    }

    protected function clearSearch(): void
    {
        $this->search = '';
        $this->results = new Collection();
    }

    /** @return array<string, mixed> */
    protected function savedProperties(): array
    {
        if ( ! $this->selected instanceof ApiResult) {
            return ['content' => null];
        }

        return [
            'content' => $this->selectedId,
            'title' => $this->selected->title,
            'image' => $this->selected->main_image,
            'description' => $this->description,
            'created_at' => $this->selected->created_at,
            'link' => $this->selected->link,
            ...$this->extraProperties($this->selected),
        ];
    }
}
