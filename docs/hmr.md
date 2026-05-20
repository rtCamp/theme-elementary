# Live Reload & Block Editor HMR

This document explains how live reload and hot module replacement work in the theme's development workflow and how to configure them for HTTPS local environments.

## Overview

Running `npm start` enables two complementary tools:

- **BrowserSync** (port 3000) — live reload for the frontend via snippet mode. Your site URL stays unchanged.
- **webpack-dev-server / Fast Refresh** (port 8887) — hot module replacement for block editor React components. Block state is preserved across updates; no full page reload needed.

For BrowserSync:

- **CSS changes** inject in-place — no full page reload.
- **PHP, HTML, and JS changes** trigger a full page reload.

For block editor HMR, JS/JSX changes to block components hot-swap in the editor instantly.

---

## Quick Start

```bash
npm start
```

Webpack starts watching for file changes and BrowserSync starts on port 3000. Open your local site and edits will reflect automatically.

---

## Requirements

The BrowserSync client script is only enqueued when `WP_ENVIRONMENT_TYPE` is set to `local`. Add this to your `wp-config.php` if it isn't already:

```php
define( 'WP_ENVIRONMENT_TYPE', 'local' );
```

For block editor HMR (Fast Refresh), also add:

```php
define( 'SCRIPT_DEBUG', true );
```

Without `SCRIPT_DEBUG`, WordPress does not support Fast Refresh.

---

## How It Works

1. `npm start` runs webpack in watch mode.
2. When a file changes, webpack rebuilds the affected assets in `assets/build/`.
3. BrowserSync detects the change and notifies the browser via the client script.
4. CSS changes are injected in-place. Everything else triggers a full reload.
5. Block changes are detected in editor by the webpack-dev-server run by the `--hot` option

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

### Multiple sites / custom URL

If port 3000 is already taken (e.g. two local sites running at once), set a different port in `.env.local`:

```
BS_PORT=3001
```

Then define the matching constant in `wp-config.php` so PHP enqueues the client from the right URL:

```php
define( 'ELEMENTARY_THEME_BROWSER_SYNC_URL', 'https://yoursite.local:3001/browser-sync/browser-sync-client.js' );
```

`ELEMENTARY_THEME_BROWSER_SYNC_URL` overrides the auto-detected URL entirely, so it also works for remote setups (ddev, reverse proxy) where the BrowserSync server is on a different host or IP.

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

## Advanced

### Disabling BrowserSync

To disable BrowserSync without removing it from the webpack config, define this constant in `wp-config.php`:

```php
define( 'ELEMENTARY_THEME_DISABLE_BROWSER_SYNC', true );
```

This prevents PHP from enqueuing the BrowserSync client script. The BrowserSync server still starts (webpack still runs it), but the browser won't connect to it. Useful when working purely in the block editor and you don't want the BrowserSync client loading on the frontend.

### Overriding the BrowserSync client URL

By default, PHP constructs the client URL from the site's scheme and host:

```
{scheme}://{host}:3000/browser-sync/browser-sync-client.js
```

To override it entirely — for a non-standard port, a remote dev server, or a reverse proxy setup — define this constant in `wp-config.php`:

```php
define( 'ELEMENTARY_THEME_BROWSER_SYNC_URL', 'https://yoursite.local:3001/browser-sync/browser-sync-client.js' );
```

This takes precedence over the auto-detected URL.

---

## Known Limitations

**BrowserSync port**: BrowserSync requires its own port (3000) separate from your local site. Snippet mode keeps the site URL unchanged — proxy mode would change the URL and break WordPress redirects and cookie domains.

**WDS host validation**: WDS runs on `localhost:8887`. For custom local hostnames (e.g. `yoursite.local`), `allowedHosts: 'all'` is set in the webpack devServer config so the HMR WebSocket connection is accepted.
