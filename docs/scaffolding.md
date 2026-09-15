# Add a feature with CLI or AI

Create an `acme_blog_site_name` shortcode that returns the escaped WordPress
site title. Both routes below target the same Acme Blog project and register the
class in `inc/Main.php`.

| Route | Produces | Your remaining work |
| --- | --- | --- |
| CLI | Class/test stubs and wiring instructions | Implement behavior, apply theme-specific wiring, expand tests, and run checks. |
| AI `/scaffold` | A guided implementation using the same generator, tests, and wiring | Review the proposed changes, approve required actions, and verify the result. |

## Before generating

Finish [initialization](initialization.md) and install dependencies. Work from the
theme root. Use your project's namespace from `composer.json`; the examples use
`rtCamp\Theme\Acme_Blog` and text domain `acme-blog`.

Theme classes go under `inc/Modules/`; tests mirror source under `tests/php/`.
Registration is directly in `Main::CLASSES`, not a per-kind module. For blocks,
the source/build locations are `src/blocks/` and `assets/build/blocks/`.

## CLI route

Discover available scaffolds using the installed engine:

```bash
npx wp-tooling list --json
```

Preview the shortcode with explicit theme conventions:

```bash
npx wp-tooling add wp/shortcode --non-interactive --json --dry-run \
  --namespace='rtCamp\Theme\Acme_Blog\Modules\Shortcodes' \
  --base_path=inc/Modules/Shortcodes \
  --tests_namespace='rtCamp\Theme\Acme_Blog\Tests\inc\Modules\Shortcodes' \
  --tests_path=tests/php/inc/Modules/Shortcodes \
  --tag=acme_blog_site_name --class=SiteName
```

Remove `--dry-run` to generate. Inspect:

- `inc/Modules/Shortcodes/SiteName.php`
- `tests/php/inc/Modules/Shortcodes/SiteNameTest.php`

The generic wiring output may point to a module file. For this theme, add this
entry to the existing `Main::CLASSES` array in `inc/Main.php` instead:

```php
\rtCamp\Theme\Acme_Blog\Modules\Shortcodes\SiteName::class,
```

Do not replace the existing array or register the same class twice. The framework
dependency is already installed; generating a shortcode does not require
reinstalling it.

Before implementing, add a failing behavior test to the generated test class:

```php
/**
 * Render the site name with HTML escaped.
 */
public function test_escapes_the_site_name(): void {
    update_option( 'blogname', 'Acme & Partners' );
    $this->assertSame( 'Acme &amp; Partners', do_shortcode( '[acme_blog_site_name]' ) );
}
```

Use the theme's `Tests\TestCase` base for the generated test class and retain
registration coverage. Then replace the generated `render()` method's empty
implementation with:

```php
return esc_html( get_bloginfo( 'name' ) );
```

Keep the generated method signature. Add a plain-title case, complete the test
docblocks, and run the focused test plus PHP checks from
[Local development](local-development.md#check-a-change). Use `SiteNameTest` as
the test filter. `composer dump-autoload` refreshes the project autoloader.

## AI route

In an assistant supporting the retained Claude skill, request:

```text
/scaffold Add an acme_blog_site_name shortcode to Acme Blog that returns the
escaped WordPress site title. Use SiteName under inc/Modules/Shortcodes,
tests under tests/php/inc/Modules/Shortcodes, and register it in Main::CLASSES.
Test registration, a plain title, and a title containing an ampersand.
```

The assistant confirms conventions and asks you to approve the test checklist
before generating. It shows the wiring diff and waits for your consent before
applying it, then runs failing behavior tests and implements and checks the
feature. Installation/build actions also need your consent. Check the actual
diff and test results; a generated stub alone is not a finished feature.

Copilot prompts are available in the starter before cleanup. For their retention
behavior, see [Initialization](initialization.md#cli-or-ai). You do not need to
run the CLI route as well as the AI route.

## Verify and continue

Add `[acme_blog_site_name]` in a Shortcode block and view the frontend. It should
show the WordPress site title, with no raw HTML interpreted from the title.
Review the source, registration, and passing checks before committing the feature.

For another scaffold, use the installed `list`/help output and the
[upstream scaffold catalogue](https://github.com/rtCamp/wp-tooling/tree/main/node-packages/wp-tooling/scaffolds).
See the [engine reference](https://github.com/rtCamp/wp-tooling/blob/main/node-packages/wp-tooling/docs/ai-orchestration.md#2-the-engine-surface)
for invocation options and each catalogue entry's `scaffold.json` for its inputs
and defaults. Use `npx wp-tooling add --help` for your installed version's flags.
Full framework behavior belongs in the
[shortcode reference](https://github.com/rtCamp/wp-framework/blob/main/docs/abstracts.md#abstractshortcode).
For a manual feature, read [Development](../DEVELOPMENT.md).

## Troubleshooting

| Symptom | Check → next action |
| --- | --- |
| Files appear under `includes/` | Pass the explicit namespace and paths above; inspect the dry run before generating again. |
| Required input missing | Read the engine's error and provide that input; do not guess a flag. |
| Class exists but shortcode is literal text | Check `Main::CLASSES`, namespace/path, and active theme. |
| Feature runs twice | Remove duplicate registration, not the class implementation. |
| A generated block has missing assets | Build its source and verify its configured block build directory. |

Persistent content types and taxonomies belong in a companion plugin so they
remain available when the theme changes. Generate and register them in that
plugin, following its namespace, paths, and bootstrap conventions.
