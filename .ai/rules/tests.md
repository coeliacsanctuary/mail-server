---
paths:
  - 'tests/**'
---

# Tests

## PHPUnit classes with test_ snake_case methods
Tests are PHPUnit classes extending `Tests\TestCase`. Name test methods in snake_case with a `test_` prefix: `test_it_adds_a_single_column_block()`. The `#[Test]` attribute is not used.

## Build fixtures with the builders in tests/Support
Editor and newsletter fixtures come from the fluent builders in `tests/Support` — `NewsletterBuilder` and `ComponentData` — not hand-rolled arrays. Extend those when a test needs a new shape. Model factories are only used for the Mailcoach models that ship them; this app defines none of its own.

## Fake at the I/O boundary, never mock app classes
Doubles go at the I/O boundary only: `Http::fake()` via the `FakesCoeliacApi` trait, and a fake MJML action bound into the container by `FakesMjml`. Application classes are exercised for real — Mockery is not used. `Http::preventStrayRequests()` is on globally in `TestCase::setUp()`, so a missing fake fails the test rather than reaching the network.

## Assert with assertSame
Use `assertSame`, not `assertEquals`. The one exception is component property arrays, whose key order is deliberately not locked — those stay on `assertEquals`.

## Docblocks record why, not what
Where a decision is non-obvious or load-bearing, the docblock says why the code is that way and what breaks if it changes — not what it does. Keep writing them, and update the reason when it changes rather than deleting the block.

## Tests never reach outside the process — MJML included
No test may reach an external service or shell out to node. Everything is faked at the I/O boundary. `php artisan test` is the whole suite — there are no excluded groups and nothing to run separately.

MJML is always faked. Do not add a test that compiles it for real: Sidecar means AWS credentials and a Lambda call, and local compilation means an npm `mjml` install, which this project deliberately dropped. Do not reimplement MJML's nesting rules in PHP to work around that either — it is a second implementation that drifts from the real one, and you end up debugging the test.

MJML validity is already covered by the editor preview, which compiles for real through Sidecar on real content every time a newsletter is built. A component cannot be sent without being previewed, so a broken one surfaces there, not in a subscriber's inbox. Rendering across mail clients is checked by a real test send before deploying.

An earlier version of this note claimed a `--group mjml` test needed AWS credentials. It never did — `TestCase::setUp()` empties `sidecar.functions`, so `InitializeMjmlAction` always returned the local-node path. The test has been removed; ignore any lingering reference to that group.
