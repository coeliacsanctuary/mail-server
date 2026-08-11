# Newsletter Editor

`App\Editor\Editor` is a custom Mailcoach content editor. It stores a block tree as JSON in `content_items.structured_html`, and `App\Editor\Support\NewsletterCompiler` renders that tree to MJML and compiles it via Sidecar.

## Stored data

- A component `name` is used as **both** a Livewire component path (`resources/views/components/newsletter/editable/block.blade.php`) and a Blade view path (`resources/views/editor/rendered.blade.php`). Renaming one is a migration of every saved campaign, not a refactor.
- The same is true of the property keys inside a component's `properties` bag. Key **order** is also stored data — append new keys, don't reorder.
- Public Livewire property names are free to change; anything reaching `savedProperties()` is not.
- `tests/Support/ComponentData.php` is the reference for what each component persists.
- Newsletter-level metadata (currently the preheader) lives as a sibling key alongside `blocks` in the same document. `App\Editor\Support\BlockCollection` reads and writes back unknown top-level keys verbatim, which is also how Mailcoach's own `templateValues` survives. Adding more metadata needs no schema change.
- There is an archive of years of campaigns. Every change to the format must be additive and read with a `??` fallback.

## Block manipulation

- All of it goes through `BlockCollection`, `Block` and `BlockComponent` in `app/Editor/Support/`. Don't reach into the decoded array directly.
- House convention: an unknown block id throws `BlockNotFound`; an out-of-range column index or position is a **silent no-op**, not a clamp.

## Rendered views

- Changes under `resources/views/components/newsletter/rendered/` decide what ~6,000 subscribers see. They need a real test send before deploying, not just a green suite — the automated tests assert MJML, not rendered email.
- Every component must render without throwing when its properties are empty. `Editor::addComponent()` creates components with `properties: []`, and a component chosen but never filled in used to 500 the save.

## Tests

- `tests/Feature/Editor` and `tests/Feature/Newsletter/Components`, with fixtures in `tests/Support`.
- MJML is always faked. No test compiles for real: that would need either Lambda or a node `mjml` install, and the suite deliberately depends on neither. The tests assert the MJML we generate, never the HTML it turns into — a real test send is the only check on that.
- Property arrays are compared with `assertEquals`, not `assertSame` — key order across components is deliberately not locked.
