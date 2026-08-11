---
paths:
  - 'app/Editor/**'
---

# Editor

## Final value objects with static named constructors
The editor's value objects are `final` classes built through static named constructors — `make()`, `fromArray()`, `fromJson()` — not `new` from outside. Their internals use plain arrays and `array_*` functions, not Collections.
