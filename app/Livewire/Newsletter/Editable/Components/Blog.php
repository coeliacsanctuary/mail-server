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

    public Collection $results;

    public ApiResult $blog;

    public function mount(): void
    {
        $this->blogId = $this->properties['content'] ?? null;
        $this->results = new Collection();

        if ($this->blogId) {
            $this->blog = $this->getBlog();
            $this->description = $this->block === 'single' ? $this->blog->description : $this->blog->meta_description;
        }

        if (isset($this->properties['description'])) {
            $this->description = $this->properties['description'];
        }
    }

    public function updated($property = null): void
    {
        if ($property === 'search') {
            $this->handleSearch();

            return;
        }

        $this->properties = [
            'content' => $this->blogId,
            'title' => $this->blog->title,
            'image' => $this->blog->main_image,
            'description' => $this->description,
            'created_at' => $this->blog->created_at,
            'link' => $this->blog->link,
        ];

        $this->dispatch('component-updated', $this->blockId, $this->properties, $this->index);
    }

    protected function handleSearch(): void
    {
        $this->results = Http::coeliac()
            ->get('api/blogs', ['search' => $this->search])
            ->collect('data')
            ->map(fn ($blog) => new ApiResult(
                id: $blog['id'],
                title: $blog['title'],
                description: $blog['description'],
                meta_description: $blog['meta_description'],
                created_at: $blog['created_at'],
                main_image: $blog['main_image'],
                link: $blog['link']
            ));
    }

    protected function getBlog(?int $id = null): ApiResult
    {
        $id ??= $this->blogId;

        $response = Http::coeliac()->get("api/blogs/{$id}")->json();

        return new ApiResult(...$response);
    }

    public function selectBlog($id): void
    {
        $this->blogId = $id;
        $this->blog = $this->getBlog($id);
        $this->description = $this->block === 'single' ? $this->blog->description : $this->blog->meta_description;
        $this->results = collect();
        $this->search = '';
        $this->updated();
    }

    public function remove(): void
    {
        $this->blogId = null;
        $this->description = '';
        $this->results = collect();
        $this->search = '';
        $this->updated();
    }

    public function render(): View
    {
        return view('livewire.newsletter.editable.components.blog');
    }
}
