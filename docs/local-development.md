# Local development

Start here after [initialization](initialization.md). Commands run from the theme
directory; examples assume the folder is `acme-blog`.

## Start and stop WordPress

```bash
npm run wp-env start
npm run wp-env run cli -- wp theme activate acme-blog
```

The development site is `http://localhost:5890`; the separate test site uses
`http://localhost:5891`. Activation uses the mounted **folder name**, not the
theme display name or text domain. When you finish, stop the environment with:

```bash
npm run wp-env stop
```

For an existing local WordPress installation, use its normal startup and
activation process instead, then run the theme-local commands from this directory.

## Edit source and see the result

| Edit | Output / purpose |
| --- | --- |
| `theme.json`, `templates/`, `parts/`, `patterns/`, `styles/` | WordPress theme configuration and block markup; no asset compilation needed. |
| `src/css/`, `src/js/` | Compiled under `assets/build/css/` and `assets/build/js/`. |
| `src/js/frontend/modules/` | Interactivity modules under `assets/build/js/modules/`. |
| `src/components/<name>/<name>.{js,scss}` | `assets/build/js/components/<name>.js` and `assets/build/css/components/<name>.css`; the component PHP stays under `src/components/`. |
| `src/blocks/` (when added) | Block files under `assets/build/blocks/`. |
| `inc/`, `template-parts/` | PHP behavior and render templates. |

Edit files under `src/`, `inc/`, `templates/`, `parts/`, `patterns/`, and
`styles/`. Treat everything under `assets/build/` as generated output; do not
edit it by hand.

Use `npm run start:assets` for theme assets. When you add custom blocks,
`npm run start:blocks` watches their sources with the block dev server.
`npm start` runs both watchers. These commands stay running until Ctrl+C.

With `start:assets`, edit a stylesheet or script and confirm the watcher writes
the matching file under `assets/build/`; BrowserSync then injects CSS or reloads
the frontend. For template and block markup, refresh the frontend or Site Editor
after the build. With `start:blocks`, edit a custom block and confirm the editor
refreshes and the browser console has no build error. A visible source change on
the active site is the basic success check.

For a one-time development build, use `npm run build:dev`. See
[Asset builds](asset-building-process.md) for entry naming and enqueueing.

### Automatic frontend reload

For the default HTTP wp-env site, add the following to `.env.local`, preserving
any existing settings:

```dotenv
WP_HOST=localhost
ENABLE_HMR=true
BS_PORT=3001
```

The wp-env development environment already sets `WP_ENVIRONMENT_TYPE=local`,
`WP_DEBUG=true`, and `SCRIPT_DEBUG=true`. For another local WordPress
installation, set those values in its `wp-config.php` before using live reload.
Preserve any existing override entries, especially if Dev Tools created them.
Restart wp-env after configuration changes, then start the watcher.

For HTTPS, custom ports, or block refresh, read [Live reload](hmr.md). Leave
`WP_SSL_KEY` and `WP_SSL_CERT` unset in `.env.local` for an HTTP setup.

## Check a change

These host commands run once:

```bash
npm run test:js -- --runInBand --watch=false
npm run lint:js
npm run lint:css
npm run lint:package-json
composer phpcs
composer phpstan
```

For a focused JavaScript test, add its path while keeping watch mode off:

```bash
npm run test:js -- --runInBand --watch=false tests/js/webpack-config.test.js
```

Use PHP compatible with the project's installed checks (PHP 8.2 is the wp-env
baseline). To run PHPCS in that container instead:

```bash
npm run wp-env -- run cli --env-cwd=/var/www/html/wp-content/themes/acme-blog -- vendor/bin/phpcs
```

With wp-env running, the theme's PHP test command runs inside `tests-cli`; its
pretest hook installs Composer dependencies there without scripts.

The wp-env test environment keeps `WP_DEBUG` disabled by default. The logger
tests require it, so merge this test-only setting into `.wp-env.override.json`
before running PHP tests:

```json
{
  "env": {
    "tests": {
      "config": { "WP_DEBUG": true }
    }
  }
}
```

```bash
npm run test:php
```

For a focused PHP test after that setup:

```bash
npm run wp-env -- run tests-cli --env-cwd=/var/www/html/wp-content/themes/acme-blog -- vendor/bin/phpunit --filter AuthorBioTest
```

Use the relevant test name if the example was removed. Keep smoke projects
outside this repository so Jest does not discover another project's tests.
Use the explicit commands above for a terminating review check; aggregate-script
details belong in [maintenance](internal/maintenance.md#checks).

## Build for delivery

```bash
npm run build:prod
```

Verify `assets/build/` contains the CSS, JavaScript, metadata, and any custom
blocks needed by the theme. The directory is gitignored: commit source and
build configuration, and ensure your deployment process builds or packages the
output. This starter theme does not define your project's deployment pipeline.

Review visible behavior and checks before feature integration; make a separate
feature commit when that checkpoint is useful for your workflow.

## Troubleshooting

| Symptom | Check → next action |
| --- | --- |
| wp-env cannot start | Run `docker info`; start Docker and retry if unavailable. For occupied ports, start with `WP_ENV_PORT=5892 WP_ENV_TESTS_PORT=5893 npm run wp-env start`. |
| Frontend assets return 404 | Check `assets/build/`; run `npm run build:dev` and confirm the theme is active. |
| CSS rebuilds but no live reload | Confirm local environment type, watcher, and BrowserSync port; see [HMR](hmr.md). |
| HTTPS reload is blocked | Check that `WP_SSL_KEY` and `WP_SSL_CERT` point to trusted certificates for the site hostname; see [HMR](hmr.md). |
| Site Editor ignores a template file edit | Check for a customized template saved in WordPress; review/reset that customization before testing the file version. |
| PHP tests cannot connect | Start wp-env and rerun `npm run test:php`; confirm the `tests-cli` environment is used. |
| New PHP feature does not load | Check namespace/path and `Main::CLASSES`; see [Development](../DEVELOPMENT.md#adding-a-new-class). |
