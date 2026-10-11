# Leaflet 1.9.4

Vendored browser assets for the operators GIS pages. This is the same version
previously loaded from unpkg; no map behavior or database schema is changed.

## Upstream

- Project: https://github.com/Leaflet/Leaflet
- Release: https://github.com/Leaflet/Leaflet/releases/tag/v1.9.4
- Distribution: https://github.com/Leaflet/Leaflet/releases/download/v1.9.4/leaflet.zip
- License: BSD-2-Clause; see `LICENSE` (from the upstream `v1.9.4` tag).

`leaflet.css`, `leaflet.js`, and `images/` are copied unmodified from the
release archive's `dist/` directory. Keep the relative image paths intact:
Leaflet uses them for default markers and layer controls.

`leaflet.js.map` comes from the same distribution, with `sourcesContent` added
from the upstream `v1.9.4` tag's `src/` files. The original map contains only
relative source paths; embedding the corresponding sources keeps developer
tools usable without shipping a separate source tree or requesting missing
files. All original mapping fields are unchanged. Source archive:
https://github.com/Leaflet/Leaflet/archive/refs/tags/v1.9.4.tar.gz
Unminified and ESM builds are not used by daloRADIUS and are not included.

The operators `static` symlink exposes these shared assets at
`static/leaflet/`. GIS pages use `?v=1.9.4` for cache versioning; update it
alongside the distribution when upgrading. Review the upstream release notes,
replace the distribution assets and license, and run
`python3 -m unittest discover -s tests -p 'test_leaflet_assets.py'`.
Also smoke-test marker icons and layer controls in a browser.

Only the library is local: OpenStreetMap tiles still require network access.
