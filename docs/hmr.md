# Live reload and block refresh

The theme has two development servers. They complement the WordPress site;
you continue browsing the site's normal URL.

| Watcher | Server | Purpose |
| --- | --- | --- |
| `npm run start:assets` | BrowserSync, port 3001 | Inject CSS changes and reload after JS/PHP/HTML changes. |
| `npm run start:blocks` | webpack dev server, port 8887 | Refresh custom block code during development. Requires block sources. |

```bash
npm start
```

runs both watchers together. For CSS, BrowserSync injects the change in place
with no reload; PHP, HTML, and JS changes trigger a full page reload. Block
editor JS/JSX changes hot-swap through Fast Refresh, preserving editor state.
For the basic HTTP setup, follow
[Local development](local-development.md#automatic-frontend-reload).

## WordPress settings

The frontend BrowserSync client is enqueued only when `WP_ENVIRONMENT_TYPE` is
`local` and the feature/client switches permit it:

```php
define( 'WP_ENVIRONMENT_TYPE', 'local' );
```

Block Fast Refresh also needs `SCRIPT_DEBUG` enabled:

```php
define( 'SCRIPT_DEBUG', true );
```

wp-env's development environment already defaults to
`WP_ENVIRONMENT_TYPE=local`, `WP_DEBUG=true`, and `SCRIPT_DEBUG=true`; no override
is needed for those defaults. If you change them, merge the values into the
development portion of `.wp-env.override.json`, then restart wp-env. In another
environment, use its WordPress configuration.

## How it works

BrowserSync watches `assets/build/**/*`, `**/*.php` (excluding `vendor/`), and
`**/*.html`, and notifies the browser via the enqueued client script. It's
wired into only the frontend/admin JS webpack config, not the style or
block-editor configs — adding it to more than one would start multiple
BrowserSync instances on the same port.

The block dev server (`webpack.blocks.config.js`) is a thin wrapper over
`@wordpress/scripts`' default config; it strips the `devServer.proxy` option
because webpack-dev-server v5 (pinned via `overrides` in `package.json`)
requires `proxy` to be an array, while wp-scripts still emits the v4 object
form, which v5 rejects with `options.proxy should be an array`.

## Local configuration

Copy `.env.local.example` to `.env.local`, then merge in the settings you
need; the file is gitignored. At a minimum, set your local site's hostname:

```dotenv
WP_HOST=acme.local
```

| Variable | Default / usage |
| --- | --- |
| `WP_HOST` | Local hostname without scheme or port, such as `localhost` or `acme.local`. |
| `ENABLE_HMR` | On unless set to `false`, `0`, `no`, or `off` (case-insensitive). Controls BrowserSync, not the separate block dev server. |
| `BS_PORT` | `3001`; read by both webpack and the PHP client URL builder. |
| `BLOCKS_DEV_SERVER_PORT` | `8887`; used by the block watcher. No matching WordPress constant is needed — the editor loads block scripts from the dev server directly. |
| `DISABLE_BS` | Set `true` to skip only the frontend client; the BrowserSync server can still run. |
| `WP_SSL_KEY`, `WP_SSL_CERT` | Certificate paths for an HTTPS BrowserSync server; omit for HTTP. |

Restart watchers after changing these values. For example, another project may
already occupy the defaults:

```dotenv
WP_HOST=localhost
BS_PORT=3002
BLOCKS_DEV_SERVER_PORT=8889
```

By default, PHP builds the client URL as
`{scheme}://{host}:3001/browser-sync/browser-sync-client.js`, deriving the
scheme and host from `is_ssl()` and `home_url()`. Changing `BS_PORT` is enough
to update it. For a reverse proxy or remote server with a different full URL,
define the personalized theme constant in WordPress configuration instead:

```php
define( 'ACME_BLOG_BROWSER_SYNC_URL', 'https://acme.local:3002/browser-sync/browser-sync-client.js' );
```

Use your theme's actual constant prefix. The full URL override takes precedence
over the inferred scheme, hostname, and BrowserSync port.

## HTTPS and custom hostnames

For BrowserSync on an HTTPS site, set `WP_SSL_KEY` and `WP_SSL_CERT` to trusted
certificate files for that hostname. Check the client URL in the browser;
an HTTP client script on an HTTPS page is blocked as mixed content.
Certificates are domain-based, not port-based, so the same certificate your
local site uses also covers port 3001 — no separate cert is needed.

In LocalWP (macOS), certs for a site are under:

```
~/Library/Application Support/Local/run/router/nginx/certs/<domain>.key
~/Library/Application Support/Local/run/router/nginx/certs/<domain>.crt
```

`WP_HOST` also allows the configured hostname through the block server's host
check alongside `localhost`. The theme's block wrapper configures host access
and port; it does not configure HTTPS certificates for the block dev server.
Validate that server's transport separately when using an HTTPS editor.

## Verify or diagnose

- Edit a frontend stylesheet: wait for compilation, then check the style changes.
- Edit PHP/HTML: confirm a frontend reload when BrowserSync is connected.
- For a custom block, edit its editor component and check the editor and console.
- If reload fails, check local environment type, feature flags, server output,
  occupied ports, and browser certificate/WebSocket errors.

Use [init's feature management](initialization.md#change-things-later)
to toggle BrowserSync. Its npm dependencies remain installed when disabled.

## Known limitations

BrowserSync needs its own port (3001), separate from the site's own URL:
snippet mode keeps the site URL unchanged, where proxy mode would change it
and break WordPress redirects and cookie domains. For custom local hostnames,
the block dev server sets `allowedHosts: 'all'` so its HMR WebSocket
connection is accepted from a hostname other than `localhost`.
