<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use Illuminate\Support\Facades\View;
use Livewire\Factory\Factory;
use Tests\TestCase;

/**
 * The component "name" stored in structured_html is used as BOTH a Livewire
 * component path and a Blade view path, and nothing checks that the two agree.
 * Static analysis sees all of these as unused, because both lookups are by
 * dynamic string.
 *
 * The add-component modal is the canonical registry: if a name is offered
 * there, both halves must exist.
 */
class ComponentRegistryTest extends TestCase
{
    /** @return array<int, string> */
    private function registeredComponentNames(): array
    {
        $modal = file_get_contents(
            resource_path('views/components/modals/add-component.blade.php'),
        );

        preg_match_all("/'component' => '([a-z-]+)'/", (string) $modal, $matches);

        return $matches[1];
    }

    public function test_the_modal_offers_every_component(): void
    {
        // A guard on the guard: if the regex stops matching, the two tests
        // below would pass vacuously.
        $this->assertCount(13, $this->registeredComponentNames());
    }

    public function test_every_offered_component_has_an_editable_livewire_component(): void
    {
        /** @var Factory $factory Registered under an alias, not the class name. */
        $factory = app('livewire.factory');

        foreach ($this->registeredComponentNames() as $name) {
            $this->assertTrue(
                $factory->exists("newsletter.editable.components.{$name}"),
                "No Livewire component for [{$name}]. The add-component modal offers it, "
                . 'and editable/block.blade.php resolves it with no existence check.',
            );
        }
    }

    public function test_every_offered_component_has_a_rendered_view(): void
    {
        foreach ($this->registeredComponentNames() as $name) {
            $this->assertTrue(
                View::exists("components.newsletter.rendered.components.{$name}"),
                "No rendered view for [{$name}]. editor/rendered.blade.php guards with "
                . 'View::exists(), so this component would silently vanish from the email.',
            );
        }
    }
}
