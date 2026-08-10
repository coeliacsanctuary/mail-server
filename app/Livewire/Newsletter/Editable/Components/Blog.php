<?php

declare(strict_types=1);

namespace App\Livewire\Newsletter\Editable\Components;

use App\Dto\ApiResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class Blog extends NewsletterComponent
{
    public string $description = '';

    public ?int $blogId = null;

    public string $search = '';

    /** @var Collection<int, ApiResult> */
    public Collection $results;

    public ApiResult $blog;

    public function mount(): void
    {
        $this->blogId = $this->properties['content'] ?? null;
        $this->results = new Collection();

        if ($this->blogId) {
            $this->blog = $this->getBlog();
            $this->description = $this->block === 'single'
                ? $this->blog->description
                : $this->blog->meta_description;
        }

        if (isset($this->properties['description'])) {
            $this->description = $this->properties['description'];
        }
    }

    /**
     * Searching deliberately does not persist anything - it would overwrite
     * the saved block with whatever is half-typed in the search box.
     */
    public function updatedSearch(): void
    {
        $this->results = Http::coeliac()
            ->get('api/blogs', ['search' => $this->search])
            ->collect('data.data')
            ->map(fn (array $blog) => new ApiResult(
                id: $blog['id'],
                title: $blog['title'],
                description: $blog['description'],
                meta_description: $blog['meta_description'],
                created_at: $blog['created_at'],
                main_image: $blog['main_image'],
                link: $blog['link'],
            ));
    }

    public function updatedDescription(): void
    {
        $this->syncProperties();

        $this->skipRender();
    }

    public function selectBlog(int $id): void
    {
        $this->blogId = $id;
        $this->blog = $this->getBlog($id);
        $this->description = $this->block === 'single'
            ? $this->blog->description
            : $this->blog->meta_description;

        $this->clearSearch();
        $this->syncProperties();
    }

    public function remove(): void
    {
        $this->blogId = null;
        $this->description = '';

        $this->clearSearch();
        $this->syncProperties();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.blog');
    }

    protected function getBlog(?int $id = null): ApiResult
    {
        $id ??= $this->blogId;

        $response = Http::coeliac()->get("api/blogs/{$id}")->json();

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
            'content' => $this->blogId,
            'title' => $this->blog->title,
            'image' => $this->blog->main_image,
            'description' => $this->description,
            'created_at' => $this->blog->created_at,
            'link' => $this->blog->link,
        ];
    }
}
