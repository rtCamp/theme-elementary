# Live Reload

This document explains how live reload works in the theme's development workflow and how to configure it for HTTPS local environments.

## Overview

Running `npm run dev` enables live reload via [BrowserSync](https://browsersync.io/) in snippet mode. Your site URL stays unchanged. BrowserSync runs a small server on port 3000 and injects a client script into the page that listens for file change events.

- **CSS changes** inject in-place — no full page reload.
- **PHP, HTML, and JS changes** trigger a full page reload.

---

## Quick Start

```bash
npm run dev
```

Webpack starts watching for file changes and BrowserSync starts on port 3000. Open your local site and edits will reflect automatically.

---

## Requirements

The BrowserSync client script is only enqueued when `WP_ENVIRONMENT_TYPE` is set to `local`. Add this to your `wp-config.php` if it isn't already:

```php
define( 'WP_ENVIRONMENT_TYPE', 'local' );
```

---

## How It Works

1. `npm run dev` runs webpack in watch mode.
2. When a file changes, webpack rebuilds the affected assets in `assets/build/`.
3. BrowserSync detects the change and notifies the browser via the client script.
4. CSS changes are injected in-place. Everything else triggers a full reload.

BrowserSync watches the following:

- `assets/build/**/*`
- `**/*.php` (excluding `vendor/`)
- `**/*.html`

The client script is enqueued by PHP from `{scheme}://{host}:3000/browser-sync/browser-sync-client.js`. The scheme (`http` or `https`) and host are derived automatically from the WordPress site URL using `is_ssl()` and `home_url()`.

BrowserSync is only added to the `scripts` webpack config. Adding it to all three configs (`scripts`, `styles`, `moduleScripts`) would start three BrowserSync instances on the same port.

---

## Configuration

Copy `.env.local.example` to `.env.local` and set your local site hostname:

```
WP_HOST=yoursite.local
```

`WP_HOST` is your local site's hostname (without protocol or port). Set it to match your local hostname exactly.

`.env.local` is gitignored.

### HTTPS

If your local site runs on HTTPS, also add the SSL cert paths:

```
WP_SSL_KEY=/path/to/yoursite.local.key
WP_SSL_CERT=/path/to/yoursite.local.crt
```

This is required to avoid mixed content errors — the BrowserSync client script on port 3000 must also be served over HTTPS. Since SSL certs are domain-based, the same cert your local site uses also covers port 3000.

**Finding cert paths in LocalWP (macOS):**

```
~/Library/Application Support/Local/run/router/nginx/certs/<domain>.key
~/Library/Application Support/Local/run/router/nginx/certs/<domain>.crt
```

---

## Known Limitation

BrowserSync requires its own port (3000) for the client script, separate from the port your local site runs on. Using BrowserSync's proxy mode would avoid this but would change the site URL (e.g. `yoursite.local:3000` instead of `yoursite.local`), causing issues with WordPress redirects and cookie domains. Snippet mode keeps the site URL unchanged.
