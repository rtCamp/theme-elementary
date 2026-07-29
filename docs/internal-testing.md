# Internal testing (v2)

A short guide to test `npm run init` (theme rename, feature toggles, example-set removal) before `@rtcamp/wp-tooling` ships on the public npm registry. It resolves the engine from a local `wp-tooling` clone via a `file:` path, so no registry token is needed.

> Why not `npm link`? This repo's [`.npmrc`](../.npmrc) maps the `@rtcamp` scope to GitHub Packages, so `npm install` always tries that registry for `@rtcamp/*` and 404s without a `read:packages` token — even with the package linked. Pointing the dependency at a local `file:` path bypasses the registry and is the reliable interim method. Once the package is published (or you export a `GITHUB_TOKEN`), drop the `file:` edit and follow the [quick-start guide](quick-start-guide.md) instead.

## Prerequisites

- Node (`nvm use`; version in [`.nvmrc`](../.nvmrc)).
- `rtCamp/wp-tooling` cloned **as a sibling** of this repo, on the `release/v1.0.0` branch (the init engine landed there).
- A **fresh/throwaway clone** of this skeleton to test against — `npm run init` rewrites files in place.

## Steps

```bash
# 0. From inside the throwaway theme clone, the Node version from .nvmrc:
nvm use

# 1. Clone wp-tooling as a sibling on the engine branch (skip if you already have it):
git clone git@github.com:rtCamp/wp-tooling.git ../wp-tooling
( cd ../wp-tooling && git checkout release/v1.0.0 )

# 2. Point the @rtcamp dependency at the local package (LOCAL ONLY — do not commit).
#    Add the tailwind-config line too if you will test the Tailwind feature.
npm pkg set "devDependencies.@rtcamp/wp-tooling=file:../wp-tooling/node-packages/wp-tooling"
npm pkg set "devDependencies.@rtcamp/tailwind-config=file:../wp-tooling/node-packages/tailwind-config"

# 3. Install and initialize:
composer install     # needed for the framework (VCS repo) + sync-ai; init still runs without it
npm install --install-links   # on ERESOLVE: add --legacy-peer-deps
npm run init         # or non-interactive: npm run init -- --name="Test Theme" --yes --remove-examples=shortcode,patterns

# 4. When done, revert the local-only package.json edit:
git checkout package.json
```

## What to verify

- **Identity rename:** theme name, text domain, namespace (`rtCamp\Theme\Elementary` becomes yours), constant/function/CSS prefixes, composer package, the `style.css` header, and `functions.php` constants renamed.
- **Example sets:** removing a set (e.g. `shortcode`, `patterns`) deletes its files under `inc/Modules/<Set>/` (and coupled assets) and strips its `use` import and `Main::CLASSES` line; kept sets still load; the result is valid PHP (`composer dump-autoload`, `php -l`).
- **HMR feature:** toggling (`npm run init -- --enable=hmr` / `--disable=hmr`) flips `ENABLE_HMR` in `.env.local`.
- **Tailwind feature:** `npm run init -- --enable=tailwind` adds `src/css/frontend/tailwind.css` + `postcss.config.js` and flips the `*_ENABLE_TAILWIND` constant in `functions.php` to true. Note: enabling it rewrites the `@rtcamp/tailwind-config` spec back to `^0.1.0`; re-apply the `file:` line from step 2 before the next `npm install` if you want to run the Tailwind build.

## Notes

- The init engine's own output reports what it renamed, removed, and toggled — trust it to verify rather than grepping `inc/` by hand.
- Manage mode (after first init): `npm run init -- --list` shows feature status; `--enable=`/`--disable=`/`--features=` change it.
