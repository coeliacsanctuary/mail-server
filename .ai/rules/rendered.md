---
paths:
  - 'resources/views/components/newsletter/rendered/**'
---

# Rendered

## fluid-on-mobile alone does not make an mj-image fill its column on iOS
mj-image writes its computed column width onto the image's own <td> (290px in a double, 193px in a triple). `fluid-on-mobile` releases it by setting that cell to `width:auto`, which leaves the image's `width:100%` with no definite containing block. WebKit then falls back to the intrinsic width — the `width` attribute MJML wrote — so on iPhone the image stays at its desktop column width while the column around it is full width. Chrome resolves it against the table, so this never reproduces in a browser.

Blog and recipe carry `css-class="fluid-img"`, and metas.blade.php gives that cell a definite width and pins the image to it. Any new component with an image in a double or triple block needs the same class.

Verify MJML changes by compiling with the mjml Sidecar actually deploys — `node vendor/spatie/mjml-sidecar/lambda/node_modules/mjml/bin/mjml` (4.14.1). It is already vendored; nothing needs installing. A real test send is still the only check on WebKit.
