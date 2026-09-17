---
paths:
  - composer.json
---

# General

## Keep laravel/horizon installed even though nothing uses it
Horizon is unwired — no provider, no config, no scheduled snapshot — because queues run on Laravel Cloud managed queues, which are SQS-backed and cannot be driven by Horizon.

The composer dependency must stay anyway. Mailcoach's debug screen calls `InstalledVersions::getVersion('laravel/horizon')` without a guard (`DebugComponent::render()`), and Composer throws `OutOfBoundsException` for an absent package, so `composer remove laravel/horizon` turns /debug into a 500. Every other version lookup on that screen uses the safe `isInstalled()`, so this is an upstream slip, not something we are meant to satisfy.

`tests/Feature/Queue/HorizonDependencyTest.php` guards it. Drop the dependency and the test together, once Mailcoach guards that call.
