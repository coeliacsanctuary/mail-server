<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Dto\ApiResult;
use ArgumentCountError;
use PHPUnit\Framework\TestCase;

class ApiResultTest extends TestCase
{
    private function apiResult(): ApiResult
    {
        return new ApiResult(
            id: 1,
            title: 'A Gluten Free Blog',
            description: 'Long.',
            meta_description: 'Short.',
            created_at: '1st January 2026',
            main_image: 'https://coeliac.invalid/images/blog.jpg',
            link: 'https://coeliac.invalid/blog',
            extra: ['price' => '£4.99'],
        );
    }

    public function test_it_serialises_every_property_for_livewire(): void
    {
        $this->assertSame([
            'id' => 1,
            'title' => 'A Gluten Free Blog',
            'description' => 'Long.',
            'meta_description' => 'Short.',
            'created_at' => '1st January 2026',
            'main_image' => 'https://coeliac.invalid/images/blog.jpg',
            'link' => 'https://coeliac.invalid/blog',
            'extra' => ['price' => '£4.99'],
        ], $this->apiResult()->toLivewire());
    }

    public function test_it_round_trips_through_livewire(): void
    {
        $original = $this->apiResult();

        $this->assertEquals($original, ApiResult::fromLivewire($original->toLivewire()));
    }

    /**
     * Pins the brittleness that Blog and Recipe rely on: they spread the raw
     * API response straight into this constructor, so a missing field is fatal
     * - and an added one would be too ("Unknown named parameter"). Product and
     * Eatery hand-roll their own mapping to work around it.
     */
    public function test_it_throws_when_a_field_is_missing(): void
    {
        $this->expectException(ArgumentCountError::class);

        ApiResult::fromLivewire(['id' => 1, 'title' => 'Incomplete']);
    }
}
