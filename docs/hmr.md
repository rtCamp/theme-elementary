# Live reload and block refresh

This document explains how live reload and block-editor hot module
replacement work in the theme's development workflow, and how to configure
them — including for HTTPS local environments.

The theme has two development servers. They complement the WordPress site;
you continue browsing the site's normal URL.

| Watcher | Server | Purpose |
| --- | --- | --- |
| `npm run start:assets` | BrowserSync, port 3001 | Inject CSS changes and reload after JS/PHP/HTML changes. |
| `npm run start:blocks` | webpack dev server, port 8887 | Refresh custom block code during development. Requires block sources. |

For BrowserSync, CSS changes are injected in place with no reload; PHP, HTML,
and JS changes trigger a full page reload. For the block editor, JS/JSX
changes to block components hot-swap through Fast Refresh, preserving editor
state — no full reload needed.

## Quick start

```bash
npm start
```

runs both watchers together. Webpack starts watching for file changes and
BrowserSync starts on port 3001; open your local site and edits reflect
automatically. For the basic HTTP setup, follow
[Local development](local-development.md#automatic-frontend-reload).

## WordPress settings

The frontend BrowserSync client is only enqueued when `WP_ENVIRONMENT_TYPE`
is set to `local`. Add this to `wp-config.php` if it isn't already:

```php
define( 'WP_ENVIRONMENT_TYPE', 'local' );
```

Block Fast Refresh also needs `SCRIPT_DEBUG` enabled — without it, WordPress
does not support Fast Refresh:

```php
define( 'SCRIPT_DEBUG', true );
```

wp-env's development environment already defaults to
`WP_ENVIRONMENT_TYPE=local`, `WP_DEBUG=true`, and `SCRIPT_DEBUG=true`; no
override is needed for those defaults. If you change them, merge the values
into the development portion of `.wp-env.override.json`, then restart
wp-env. In another environment, use its WordPress configuration.

## How it works

### Theme assets (`start:assets` + BrowserSync)

1. `start:assets` runs the asset watcher in watch mode using
   `webpack.config.js`.
2. When a file changes, webpack rebuilds the affected assets in
   `assets/build/`.
3. BrowserSync detects the change and notifies the browser via the enqueued
   client script.
4. CSS changes are injected in place. Everything else triggers a full reload.

BrowserSync watches `assets/build/**/*`, `**/*.php` (excluding `vendor/`),
and `**/*.html`. It's wired into only the frontend/admin JS webpack config,
not the style or block-editor configs — adding it to more than one would
start multiple BrowserSync instances on the same port.

### Blocks (`start:blocks` + Fast Refresh)

5. `start:blocks` starts a webpack dev server, using
   `webpack.blocks.config.js`.
6. JS/JSX changes to block components hot-swap in the editor without a full
   reload; block state is preserved.

`webpack.blocks.config.js` is a thin wrapper over `@wordpress/scripts`'
default config. It exists mainly to strip the `devServer.proxy` option:
webpack-dev-server v5 (pinned via the `overrides` block in `package.json`)
requires `proxy` to be an array, while wp-scripts still emits the v4 object
form, which v5 rejects with `options.proxy should be an array`. The wrapper
also applies the dev-server port from `BLOCKS_DEV_SERVER_PORT`.

## Local configuration

Copy `.env.local.example` to `.env.local`, then merge in the settings you
need; the file is gitignored. At a minimum, set your local site's hostname:

```dotenv
WP_HOST=acme.local
```

`WP_HOST` is your local site's hostname, without protocol or port — set it
to match your local hostname exactly.

| Variable | Default / usage |
| --- | --- |
| `WP_HOST` | Local hostname without scheme or port, such as `localhost` or `acme.local`. |
| `ENABLE_HMR` | On unless set to `false`, `0`, `no`, or `off` (case-insensitive). Controls BrowserSync, not the separate block dev server. |
| `BS_PORT` | `3001`; read by both webpack and the PHP client URL builder. |
| `BLOCKS_DEV_SERVER_PORT` | `8887`; used by the block watcher. No matching WordPress constant is needed — the editor loads block scripts from the dev server directly. |
| `DISABLE_BS` | Set `true` to skip only the frontend client; the BrowserSync server can still run. |
| `WP_SSL_KEY`, `WP_SSL_CERT` | Certificate paths for an HTTPS BrowserSync server; omit for HTTP. |

### Multiple sites / custom ports

If port 3001 is already taken — for example, two local sites running at once
— set a different port in `.env.local`:

```dotenv
WP_HOST=localhost
BS_PORT=3002
BLOCKS_DEV_SERVER_PORT=8889
```

By default, PHP builds the client URL as
`{scheme}://{host}:3001/browser-sync/browser-sync-client.js`, deriving the
scheme and host from `is_ssl()` and `home_url()`. Changing `BS_PORT` is
enough to update it. For a reverse proxy or remote server with a different
full URL — where the BrowserSync server is on a different host or IP
entirely — define the personalized theme constant in WordPress configuration
instead:

```php
define( 'ACME_BLOG_BROWSER_SYNC_URL', 'https://acme.local:3002/browser-sync/browser-sync-client.js' );
```

Use your theme's actual constant prefix. This override takes precedence over
the inferred scheme, hostname, and BrowserSync port, so it also works for
remote setups (ddev, reverse proxy) where BrowserSync runs on a different
host entirely.

## HTTPS and custom hostnames

If your local site runs on HTTPS, set `WP_SSL_KEY` and `WP_SSL_CERT` to
trusted certificate files for that hostname. This is required to avoid mixed
content errors — the BrowserSync client script on port 3001 must also be
served over HTTPS. Since certificates are domain-based, not port-based, the
same certificate your local site uses also covers port 3001; no separate
cert is needed. Check the client URL in the browser if you're unsure.

**Finding cert paths in LocalWP (macOS):**

```
~/Library/Application Support/Local/run/router/nginx/certs/<domain>.key
~/Library/Application Support/Local/run/router/nginx/certs/<domain>.crt
```

`WP_HOST` also allows the configured hostname through the block server's
host check alongside `localhost`. The theme's block wrapper configures host
access and port; it does not configure HTTPS certificates for the block dev
server. Validate that server's transport separately when using an HTTPS
editor.

## Disabling or narrowing HMR

HMR (BrowserSync live reload) is controlled by a single master switch in
`.env.local`, honoured by both the build (BrowserSync server) and PHP
(client enqueue):

```dotenv
ENABLE_HMR=false
```

Default is on — the key only needs setting to turn HMR off. With it off,
`npm start` skips the BrowserSync server entirely and PHP skips the client,
so there's no live reload and no console noise from a client pointing at a
server that isn't running. The `browser-sync` dev dependency stays installed,
so flipping it back on needs no reinstall. Use
[init's feature management](initialization.md#change-things-later) to toggle
this from the CLI instead of hand-editing `.env.local`.

To keep the BrowserSync server running but stop PHP from enqueuing its
client — for example when working purely in the block editor — set
`DISABLE_BS=true` in `.env.local` instead. The server still starts, but the
browser won't connect to it.

## Troubleshooting

- Edit a frontend stylesheet: wait for compilation, then check the style
  changes.
- Edit PHP/HTML: confirm a frontend reload when BrowserSync is connected.
- For a custom block, edit its editor component and check the editor and
  console.
- If reload fails, check local environment type, feature flags, server
  output, occupied ports, and browser certificate/WebSocket errors.

## Known limitations

BrowserSync needs its own port (3001), separate from the site's own URL:
snippet mode keeps the site URL unchanged, where proxy mode would change it
and break WordPress redirects and cookie domains. For custom local
hostnames, the block dev server sets `allowedHosts: 'all'` so its HMR
WebSocket connection is accepted from a hostname other than `localhost`.
