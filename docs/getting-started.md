# Getting started

Create an **Acme Blog** theme, activate it in local WordPress, and see a source
edit on the frontend. Run commands from the theme directory unless a step says
otherwise. These instructions use the `theme-elementary-v2` branch.

## Before you start

Have Git, Composer 2, PHP 8.2+, Node and npm matching [`.nvmrc`](../.nvmrc), and a
running Docker installation for the recommended `wp-env` route. Use `nvm` or
another Node version manager to select the version in `.nvmrc`.

```bash
git --version
composer --version
php --version
node --version
npm --version
docker info
```

An existing local WordPress installation can replace `wp-env`; Docker is still
needed for this repository's container-based PHP test command.

## 1. Get the starter theme

For a standalone project, run this from the directory that will contain it:

```bash
git clone --branch theme-elementary-v2 https://github.com/rtCamp/theme-elementary.git acme-blog
cd acme-blog
```

With `nvm`, select the project's Node version:

```bash
nvm install
nvm use
```

For an existing WordPress repository, place the starter files in its theme
directory, such as `wp-content/themes/acme-blog`, according to that repository's
checkout workflow. Run theme-local commands from **that directory** and keep the
parent repository's environment and deployment tooling at its root. Do not run
theme init from the repository or `wp-content` root. If the parent repository owns
the theme history, do not add a nested `.git`. On VIP,
follow the [VIP local-development guide](https://docs.wpvip.com/local-development/)
for project-level setup; this guide covers only the theme-local steps.

## 2. Install dependencies

```bash
composer install
```

Composer's install hook also runs `npm i`, which runs `sync-ai`. Do not repeat
npm installation immediately afterwards. If you used Composer's `--no-scripts`
option, run `npm install` separately.

At this point, `vendor/autoload.php` and `node_modules/@rtcamp/wp-tooling` should
exist. Resolve installation errors before continuing; see
[initialization troubleshooting](initialization.md#when-init-fails).

## 3. Personalize

Review `git status` and resolve unrelated changes before running:

```bash
npm run init
```

Use **Acme Blog** as the theme name. For this first walkthrough, keep the examples
and the default feature selection: HMR on, Tailwind and Dev Tools off. The
[initialization guide](initialization.md) explains each decision, including
cleanup and optional Git initialization.

Alternatively, open the clone in your AI assistant and ask:

```text
/init Set up this theme as "Acme Blog". Keep the examples and default features.
```

Use one route. The AI route can handle dependency installation too if you start
it before step 2.

Review the resulting `style.css`, Composer namespace, `.wp-scaffold.json`, and
removed files. Initialization changes the display name; the theme's directory
remains `acme-blog`. A baseline commit after this review gives you a useful
checkpoint before feature development.

## 4. Start WordPress and activate the theme

For a standalone project, use the bundled `wp-env` route below. If the theme is
inside an existing WordPress repository, skip the bundled `wp-env` route and start
that repository's environment using its own instructions. Use its activation
command and site URL, and run `npm run build:dev` from the theme directory.

### Standalone `wp-env` route

The committed `.wp-env.json` mounts the theme, uses PHP 8.2, and configures the
development and test ports.

From the theme directory, run:

```bash
npm run wp-env start
npm run wp-env run cli -- wp theme activate acme-blog
npm run build:dev
```

For the standalone `wp-env` route, open [the local site](http://localhost:5890) and
[WordPress admin](http://localhost:5890/wp-admin/). A fresh wp-env installation
uses `admin` / `password`. Under Appearance, confirm **Acme Blog** is active,
then open the Site Editor and check that the theme templates load.

If your folder has another name, use that exact name in the activation command.
For an existing WordPress environment, open its configured site and admin URLs;
confirm the theme is active and its templates load in the Site Editor there.

## 5. Make a visible edit

Start the theme asset watcher:

```bash
npm run start:assets
```

In `src/css/frontend/styles.scss`, temporarily add `body { outline: 3px solid red; }`.
Wait for a successful rebuild and refresh the frontend. Remove the rule after
checking it. Stop the watcher with Ctrl+C.

Manual refresh works without further configuration.
[Local development](local-development.md) shows how to enable automatic reload,
watch custom blocks, run checks, and create a production build.

Next, [explore the supplied features](features.md),
[generate a feature](scaffolding.md), or [extend the theme manually](../DEVELOPMENT.md).
