<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Dto\ApiResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

/**
 * Shared behaviour for the components that search coeliacsanctuary.co.uk and
 * pin one result into a newsletter column.
 *
 * Subclasses express their differences as methods rather than a config array,
 * so a missing one is a compile error rather than a request to the API root.
 */
abstract class SearchableApiComponent extends NewsletterComponent
{
    public ?int $selectedId = null;

    public ?ApiResult $selected = null;

    public string $search = '';

    public string $description = '';

    /** @var Collection<int, ApiResult> */
    public Collection $results;

    /** The API path, used for both search and single fetch ("api/blogs"). */
    abstract protected function endpoint(): string;

    /** Plural noun for the search placeholder and empty state ("blogs"). */
    abstract protected function label(): string;

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

    /**
     * Searching deliberately does not persist anything - it would overwrite the
     * saved block with whatever is half-typed in the search box.
     */
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
        $this->description = '';

        $this->clearSearch();
        $this->syncProperties();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.searchable-api', [
            'label' => $this->label(),
            'meta' => $this->selected instanceof ApiResult ? $this->meta($this->selected) : '',
        ]);
    }

    /** Dot path to the results array inside the search response. */
    protected function searchResultsPath(): string
    {
        return 'data.data';
    }

    /** The line shown under the title of the selected item. */
    protected function meta(ApiResult $result): string
    {
        return $result->created_at;
    }

    /**
     * Extra keys persisted alongside the standard set.
     *
     * @return array<string, mixed>
     */
    protected function extraProperties(ApiResult $result): array
    {
        return [];
    }

    /**
     * Non-standard fields lifted off the API payload into ApiResult::$extra.
     *
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

    /**
     * Mapped field by field rather than spread into the constructor. The old
     * `new ApiResult(...$response)` meant any field added to the API upstream
     * became a fatal "Unknown named parameter".
     *
     * @param array<string, mixed> $payload
     */
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

    /** A single column has room for the long description; two or three do not. */
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
