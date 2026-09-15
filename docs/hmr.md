# Live reload and block refresh

The theme has two development servers. They complement the WordPress site;
you continue browsing the site's normal URL.

| Watcher | Server | Purpose |
| --- | --- | --- |
| `npm run start:assets` | BrowserSync, port 3001 | Inject CSS changes and reload after JS/PHP/HTML changes. |
| `npm run start:blocks` | webpack dev server, port 8887 | Refresh custom block code during development. Requires block sources. |

`npm start` runs both. For the basic HTTP setup, follow
[Local development](local-development.md#automatic-frontend-reload).

## WordPress settings

The frontend BrowserSync client is enqueued only when `WP_ENVIRONMENT_TYPE` is
`local` and the feature/client switches permit it. Block Fast Refresh also needs
`SCRIPT_DEBUG` enabled. wp-env's development environment already defaults to
`WP_ENVIRONMENT_TYPE=local`, `WP_DEBUG=true`, and `SCRIPT_DEBUG=true`; no override
is needed for those defaults. If you change them, merge the values into the
development portion of `.wp-env.override.json`, then restart wp-env. In another
environment, use its WordPress configuration.

## Local configuration

Merge the settings you need into `.env.local`; the file is gitignored.

| Variable | Default / usage |
| --- | --- |
| `WP_HOST` | Local hostname without scheme or port, such as `localhost` or `acme.local`. |
| `ENABLE_HMR` | On unless set to `false`, `0`, `no`, or `off` (case-insensitive). Controls BrowserSync, not the separate block dev server. |
| `BS_PORT` | `3001`; read by both webpack and the PHP client URL builder. |
| `BLOCKS_DEV_SERVER_PORT` | `8887`; used by the block watcher. |
| `DISABLE_BS` | Set `true` to skip only the frontend client; the BrowserSync server can still run. |
| `WP_SSL_KEY`, `WP_SSL_CERT` | Certificate paths for an HTTPS BrowserSync server; omit for HTTP. |

Restart watchers after changing these values. For example, another project may
already occupy the defaults:

```dotenv
WP_HOST=localhost
BS_PORT=3002
BLOCKS_DEV_SERVER_PORT=8889
```

Changing `BS_PORT` is enough for the normal client URL. For a reverse proxy or
remote server with a different full URL, define the personalized theme constant
in WordPress configuration instead:

```php
define( 'ACME_BLOG_BROWSER_SYNC_URL', 'https://acme.local:3002/browser-sync/browser-sync-client.js' );
```

Use your theme's actual constant prefix. The full URL override takes precedence
over the inferred scheme, hostname, and BrowserSync port.

## HTTPS and custom hostnames

For BrowserSync on an HTTPS site, set `WP_SSL_KEY` and `WP_SSL_CERT` to trusted
certificate files for that hostname. Check the client URL in the browser;
an HTTP client script on an HTTPS page is blocked as mixed content.

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
