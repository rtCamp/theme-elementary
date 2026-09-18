# Initialize and manage the theme

Initialization turns an acquired starter theme into a named project. It does not
install WordPress or implement new feature classes. It starts after acquisition
and dependency installation and ends after the identity, capabilities, generated
state, and cleanup have been applied.

## Before init

Run from the theme root: the directory containing `package.json`,
`composer.json`, and `bin/init.js`. For a theme inside another repository, this
is its `wp-content/themes/<directory>` directory; keep the parent repository's
tooling at the parent root. Have Composer 2, PHP 8.2+, Node/npm matching
[`.nvmrc`](../.nvmrc), and the dependencies installed. Follow the [dependency
installation steps](getting-started.md#2-install-dependencies) when they are not
installed. Confirm the working tree is clean before continuing:

```bash
git status --short
```

The command should produce no output. Init rewrites and removes starter files;
use a clean checkout or a disposable copy so the pre-init state remains your
recovery point.

## CLI or AI

| Entry point | Role |
| --- | --- |
| Clone / Composer acquisition | Place the starter in the intended theme directory using the supported repository workflow; see [Getting Started](getting-started.md). |
| `npm run init` | Run the theme's identity and capability wizard. |
| `/init` | Guide dependency installation and the same init flow with confirmations; a feature brief can be handed to scaffold afterwards. |
| `/scaffold` | Implement a new feature after personalization. |
| `/setup` | Generic tooling bootstrap; it is not an acquisition or personalization path for this starter. |

Claude skills remain after cleanup. Copilot's `/init` and `/scaffold` prompts
ship in `.github/prompts`, which cleanup removes. Use the retained skill with
an assistant that supports it, or the CLI, for subsequent work. The maintained
[AI entry points](https://github.com/rtCamp/theme-elementary/tree/theme-elementary-v2/.claude/skills)
describe assistant-specific instructions.

The lifecycle is complete at each checkpoint: acquisition puts the starter in
the target directory, installation provides `vendor/autoload.php` and
`node_modules/@rtcamp/wp-tooling`, initialization writes the named project state,
and feature work starts after the review checkpoint and ends with the new feature
wired and verified through [Scaffolding](scaffolding.md).

The [Getting Started](getting-started.md) guide verifies the clone route. If a
parent project acquires the starter through its Composer/VCS workflow, continue
from the same theme-root checkpoint after that workflow has placed the files.

## What the wizard asks

1. **Confirm setup and name the theme.** A first run has no `.wp-scaffold.json`;
   the setup confirmation defaults to No.
2. **Review identity.** Accept the derived values or edit fields before applying;
   the initial "Looks good?" confirmation defaults to Yes.
   For `Acme Blog`, the namespace is `rtCamp\Theme\Acme_Blog`, package
   `rtcamp/acme-blog`, text domain `acme-blog`, function prefix `acme_blog_`,
   constant prefix `ACME_BLOG`, and CSS prefix `acme-blog-`. Version defaults to
   `1.0.0`.
3. **Select capabilities.** Keep or remove the supplied examples and choose
   optional features. Space toggles a selection; Enter confirms it. Review the
   planned changes before applying them.
4. **Persist, regenerate, and clean up.** After selection, init writes
   `.wp-scaffold.json`, regenerates the Composer autoloader, and removes the
   configured starter-only targets. There is no separate cleanup prompt. The
   wrapper then runs `sync-ai` after a successful change.
5. **Git and hooks.** A new project may start a new Git repository. The Git
   confirmation defaults to No. Accepting it deletes the existing `.git` and its
   starter history; decline it when keeping that history or working inside
   another repository. Hook installation defaults to Yes after a new repository
   is initialized, and the initial commit is then created automatically.
   Non-interactive `--yes` skips optional Git setup.

Examples are kept by default. HMR is on; Tailwind and Dev Tools are off. See
[Included features](features.md) for their purpose and source locations.

## What changes

| Area | Result |
| --- | --- |
| Identity fields | Name, version, text domain, package, namespace, function prefix, constant prefix, and CSS prefix are derived and reviewed. |
| Affected files | `style.css`, `functions.php`, `composer.json`, `package.json`, and text or file basenames containing starter identity tokens are personalized. |
| Version | Written to `style.css` and `package.json`. |
| Examples | Kept groups remain. Removed groups delete their configured paths and registration regions; markers are removed in either case. See [Included features](features.md). |
| Optional features | HMR updates `.env.local`; Tailwind adds its entry/config and declarations; Dev Tools adds Composer/scripts and a gitignored `.wp-env.override.json`. |
| State | `.wp-scaffold.json` records identity and feature choices; do not hand-edit it. |
| Autoload | Setup regenerates Composer's autoloader. |
| Cleanup | Before the wrapper's final sync, `.github` and `languages` are removed, including workflows, Copilot prompts, theme-specific GitHub rules, and the starter POT file. |
| AI instructions | After a successful change, the wrapper runs `sync-ai`; in a standalone project with the framework installed, it refreshes the generated framework PHP instructions. It does not restore the removed prompts or theme-specific rules. |
| Retained files | Theme source, `bin/`, Claude skills, documentation, and test infrastructure remain; files belonging to removed example sets do not. |

Init does **not** generate a POT file. Run `npm run pot` separately when preparing
translations; it recreates the language output. Review cleanup before treating
the initialized project as your baseline.

## Review the result

After setup, review the generated state and the cleanup result before adding
features:

1. Run `npm run init -- --list` (or `--list --json`) and confirm the retained
   example sets are present and the selected feature states match your choices.
2. Inspect `style.css`, `functions.php`, `composer.json`, `package.json`, and
   `.wp-scaffold.json` for the resolved name, namespace, prefixes, version, and
   package metadata.
3. Review `git status` and the diff. Confirm removed example paths are gone and
   that the output lists the `.github`/`languages` cleanup. Check the generated
   framework instruction file if `sync-ai` ran.
4. Start WordPress and verify the named theme and any retained examples load.

Follow dependency instructions printed by init. Make a baseline commit after
these checks so feature work has a known starting point.

## Change things later

Identity edits and optional feature toggles are repeatable manage-mode operations.
Example removal is a one-time setup decision: its markers are consumed, so a
later init run cannot restore a removed group or safely remove another group.
Restore a clean starter copy to recover an example, or add new functionality
through [Scaffolding](scaffolding.md).

Later, bare `npm run init` opens management for identity and optional features.
Useful commands from the theme root:

```bash
npm run init -- --list
npm run init -- --list --json
npm run init -- --enable=hmr --yes
npm run init -- --disable=hmr --yes
npm run init -- --features=hmr --yes
```

`--features` sets the entire enabled set; `--enable` and `--disable` change one
feature without replacing the others. Do not combine these forms. Help and
`--list` are read-only. For machine-readable JSON without npm's banner, use
`node bin/init.js --list --json`.

Example removal belongs to initial setup: `--remove-examples=shortcode,patterns`
removes those groups, and `--keep-examples` keeps every group. Do not use
`--reinit` to restore examples or remove more groups after their markers have
been consumed. Review source and registration together when making a manual
change.

### Optional dependencies

- **Tailwind:** `npm run init -- --enable=tailwind --yes` changes declarations and
  creates the entry/config files; npm installation is separate. See
  [Tailwind](tailwind.md) for the theme integration and installation checks.
- **Dev Tools:** with repository access and WordPress 6.9+, run
  `npm run init -- --enable=dev-tools --yes`, then follow init's printed Composer
  update, wp-env startup, theme activation, and `npm run dev:connect` steps.
  The gitignored override contains this machine's paths, so each developer
  enables it locally. Disconnect with `npm run dev:disconnect` before disabling
  it, then follow the Composer update instruction. Package configuration and
  usage belong in the [Dev Tools guide](https://github.com/rtCamp/wp-devtools/blob/release/v1.0.0/README.md#install-consumer-project).

## When init fails

| Symptom | Check and next action |
| --- | --- |
| Engine cannot load | Finish dependency installation from the theme root; confirm `node_modules/@rtcamp/wp-tooling` exists. |
| Command option rejected | Run `npm run init -- --help`; correct the command before retrying. |
| Partial setup or interrupted operation | Inspect the diff and state file before continuing. Restore your pre-init checkout/backup if necessary; do not blindly rerun destructive setup. |
| Feature enabled but dependency missing | Follow the package-install/update instruction; a declaration is not an installed package. |
| Retained example or capability is missing | Run `npm run init -- --list` and inspect the recorded selection and paths. If the group was removed, recover from a clean starter copy; manage mode cannot restore consumed markers. |
| Git step fails after personalization | Inspect the completed theme changes, configure Git, and finish repository setup manually. |
| Copilot prompts disappeared | This is cleanup behavior; see [CLI or AI](#cli-or-ai). |

Cleanup has a tracked implementation follow-up: retained starter instructions can
still refer to AI files that cleanup removes. Keep that repair separate from this
guide and follow the [maintenance known gaps](internal/maintenance.md#known-gaps).

Generic engine behavior belongs upstream in
[wp-tooling](https://github.com/rtCamp/wp-tooling/tree/main/node-packages/wp-tooling);
use the local help output for the options supported by your installed revision.
