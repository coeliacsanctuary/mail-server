@props(['properties' => []])

<mj-divider border-width="2px" border-color="{{ \App\Editor\Support\BrandColour::fromName($properties['colour'] ?? null, \App\Editor\Support\BrandColour::Primary)->background() }}"></mj-divider>
