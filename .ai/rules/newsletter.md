---
paths:
  - 'app/Livewire/Newsletter/**'
---

# Newsletter

## Abstract base component, subclasses override protected methods
Newsletter components share behaviour through an abstract base class. Subclasses express their differences as overridden `protected` methods, not config arrays or properties — a missing one is then a compile error rather than a bad request at runtime.
