---
paths:
  - 'app/Livewire/**'
---

# Livewire

## Class-based Livewire components, mounted as the route
Livewire components are class-based, with their Blade view in `resources/views/livewire/`. Full-page components are registered directly as the route handler: `Route::get('account', AccountComponent::class)`. Volt and single-file components are not used.

## Validate in the component, no Form Requests
Validate inside the Livewire component with `$this->validate([...])`, or a `rules()` method when the whole component shares one rule set. Rules are arrays, using `Rule::` objects where needed. There are no Form Request classes — don't add any.
