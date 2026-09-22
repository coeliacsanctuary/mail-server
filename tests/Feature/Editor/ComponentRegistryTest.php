<?php

declare(strict_types=1);

namespace Tests\Feature\Editor;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ComponentRegistryTest extends TestCase
{
    private function registeredComponentNames(): array
    {
        $modal = file_get_contents(
            resource_path('views/components/modals/add-component.blade.php'),
        );

        preg_match_all("/'components' => \[([^\]]+)\]/", (string) $modal, $matches);

        $names = [];

        foreach ($matches[1] as $group) {
            preg_match_all("/'([a-z-]+)'/", $group, $inner);

            $names = [...$names, ...$inner[1]];
        }

        return array_values(array_unique($names));
    }

    public function test_the_modal_offers_every_component(): void
    {
        $this->assertCount(10, $this->registeredComponentNames());
    }

    public function test_the_retired_bundles_are_no_longer_offered(): void
    {
        $offered = $this->registeredComponentNames();

        foreach (['image-with-button', 'text-with-button', 'title-with-text'] as $bundle) {
            $this->assertNotContains($bundle, $offered);
            $this->assertTrue(View::exists("components.newsletter.rendered.components.{$bundle}"));
        }
    }

    public function test_every_offered_component_has_an_editable_livewire_component(): void
    {
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
