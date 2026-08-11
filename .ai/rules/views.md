---
paths:
  - 'resources/views/**'
---

# Views

## Use __mc() for user-facing strings
User-facing strings go through Mailcoach's `__mc()` helper, not Laravel's `__()`. There is no `lang/` directory in this app.

## Anonymous Blade components only
Blade components are anonymous: a file under `resources/views/components/` declaring `@props([...])`, used as `<x-…>`. There are no class-backed view components and no `app/View/Components` directory. Prefer a component over `@include`.
