---
paths:
  - 'app/**'
---

# App

## Customise Mailcoach by subclassing and rebinding
This app extends Mailcoach rather than reimplementing it. To change package behaviour: subclass the package class, call `parent::` where the original work still applies, and bind the subclass over the original in a service provider. Before writing a new class, look for a package class to extend.

## No service, action or repository layer
Business logic lives in the Livewire component that owns the screen, and queries Eloquent directly. There is no service, action, query-object or repository layer — don't introduce one. `app/Actions` holds only a Mailcoach override, not an app-level action pattern.

## Reach the coeliac API through Http::coeliac()
Every call to coeliacsanctuary.co.uk goes through the `Http::coeliac()` macro, which applies the base URL and JSON accept header. Never build the base URL by hand or read `services.coeliac.url` at the call site.

## Use __mc() for user-facing strings
User-facing strings go through Mailcoach's `__mc()` helper, not Laravel's `__()`. There is no `lang/` directory in this app.

## Auth facade, not the auth() helper
Reach the authenticated user through the `Auth` facade — `Auth::user()` — not the `auth()` helper.

## No comments — the code says it
Code is self-documenting through naming and structure. Do not add explanatory comments or docblocks, and do not reintroduce ones that have been removed: a comment that feels necessary is a signal to rename or restructure, not to write prose. If the reasoning genuinely cannot live in the code, it belongs in these rule files or in the commit message, where it stays out of the reader's way.

The sole exception is a PHPDoc type declaration PHP's own syntax cannot express — generics, array shapes, `@param list<Foo>`. Those are types, not prose, and `composer stan` runs at level 5 over `app/` with `checkModelProperties`, so removing them fails the build.
