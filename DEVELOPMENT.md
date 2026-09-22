# Development guide

This guide shows how to extend the theme by hand: where new code belongs,
which framework base class to start from, and worked examples for adding a
class, a post type, and a taxonomy. Use it after
[initialization](docs/initialization.md). Run the commands from the theme
directory (the directory containing `composer.json` and `package.json`). The
examples use an initialized project named **Acme Blog**; replace its
namespace and text domain with the values in your own `composer.json` and
`style.css`.

The theme sits on two layers: `vendor/rtcamp/wp-framework/` is the upstream
framework — reusable scaffolding (the loader and abstract base classes)
installed as a Composer dependency — and `inc/` is everything theme-specific,
extending those framework abstracts and registering theme services. The
`vendor/` boundary is enforced by convention, not code: anything edited there
is overwritten on the next `composer install`.

## Before adding code

The theme has a small set of locations with distinct responsibilities:

| Location | Purpose |
| --- | --- |
| `inc/` | Theme PHP classes, loaded through Composer's PSR-4 autoloader. |
| `inc/Main.php` | Theme bootstrap and the `Main::CLASSES` registration list. |
| `tests/php/` | PHPUnit tests that mirror the `inc/` class path. |
| `src/` | Editable JavaScript, CSS, component, and block sources. |
| `assets/build/` | Generated asset output; never edit it by hand. |
| `templates/`, `parts/`, `patterns/`, `styles/`, `theme.json` | Block-theme markup and configuration. |
| `vendor/` | Framework code, Composer-managed. Do not edit — `composer install` overwrites it; changes belong in the framework repository. |

After initialization, read the `autoload.psr-4` entry in `composer.json` before
creating a namespace. The starter uses
`rtCamp\Theme\Elementary\`; an Acme Blog project uses
`rtCamp\Theme\Acme_Blog\` and maps it to `inc/`. Keep directory segments and
class names aligned with that mapping. Keep the working tree clean enough to
review the generated changes, and install dependencies before adding a class:

```bash
composer install
```

Theme classes are registered by `Main::CLASSES`. The framework `Loader` creates
each listed class and calls its `register_hooks()` method when it implements
`Registrable`. A class that is not in this list does not run, even when its file
autoloads successfully. Read the framework's short
[registration and loader overview](https://github.com/rtCamp/wp-framework/blob/v1.0.1/docs/architecture.md)
for the lifecycle details.

## Picking a base

| Feature | Extends / implements |
| --- | --- |
| Settings page | `AbstractSettingsPage` |
| Admin (non-settings) page | `AbstractAdminPage` |
| Dynamic block (server-side render) | `AbstractBlock` |
| REST controller | `AbstractRESTController` |
| Shortcode | `AbstractShortcode` |
| Anything else that just wires hooks | `Registrable` interface |
| Same, but registration is conditional | `ConditionallyRegistrable` interface — `Loader` checks `can_register()` before calling `register_hooks()` |

### Conditional registration

A class can opt out of registration at runtime by implementing
`ConditionallyRegistrable` instead of `Registrable`:

```php
final class DevToolbarExtension implements ConditionallyRegistrable {
	public function can_register(): bool {
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	public function register_hooks(): void {
		// Wire dev-only hooks here.
	}
}
```

The `Loader` calls `can_register()` first and skips `register_hooks()` when
it returns false.

## Adapt an included example

This example requires the retained author-bio shortcode. First add an assertion
for `By Ada Lovelace` to its existing render test in
`tests/php/inc/Modules/Shortcodes/AuthorBioTest.php`. Run `AuthorBioTest` using
the focused PHP command in [Local development](docs/local-development.md#check-a-change)
and confirm that assertion fails. Then edit `template-parts/author-bio.php`
and change the name line to:

```php
<p class="acme-blog-author-bio__name">
	<?php echo esc_html( sprintf( __( 'By %s', 'acme-blog' ), $name ) ); ?>
</p>
```

Keep the generated CSS prefix and text domain from your project. The existing
`AuthorBio::class` entry in `inc/Main.php` activates the shortcode, and the
`author-bio` flag is enabled by default under Settings → Features. Use the
generated shortcode tag (for Acme Blog, `[acme_blog_author_bio]`) in a Shortcode
block and confirm that the frontend displays **By** followed by the author's
name. Rerun `AuthorBioTest` and confirm it passes.

## Adding a new class

The following small feature appends a notice to post content. It shows the four
pieces every theme feature needs: a destination, behavior, registration, and a
check.

Start with `tests/php/inc/Modules/ReadingTimeTest.php`, extending the theme's
`TestCase`. The test namespace follows the project's `autoload-dev.psr-4`
mapping:

```php
<?php

declare( strict_types = 1 );

namespace rtCamp\Theme\Acme_Blog\Tests\inc\Modules;

use rtCamp\Theme\Acme_Blog\Modules\ReadingTime;
use rtCamp\Theme\Acme_Blog\Tests\TestCase;

final class ReadingTimeTest extends TestCase {
	public function test_appends_the_notice(): void {
		$this->assertSame(
			'Article body<p class="acme-blog-reading-time">Reading time: about one minute</p>',
			( new ReadingTime() )->append_notice( 'Article body' )
		);
	}

	public function test_leaves_empty_content_unchanged(): void {
		$this->assertSame( '', ( new ReadingTime() )->append_notice( '' ) );
	}
}
```

Next create `inc/Modules/ReadingTime.php` from the class below, initially using
only `return $content;` as the body of `append_notice()`. Run the focused PHP
command from [Local development](docs/local-development.md#check-a-change) with
`--filter ReadingTimeTest`: the notice assertion should fail. Then implement
the method as shown and rerun the test to confirm both assertions pass.

```php
<?php

declare( strict_types = 1 );

namespace rtCamp\Theme\Acme_Blog\Modules;

use rtCamp\WPFramework\Contracts\Interfaces\Registrable;

final class ReadingTime implements Registrable {
	public function register_hooks(): void {
		add_filter( 'the_content', [ $this, 'append_notice' ] );
	}

	public function append_notice( string $content ): string {
		if ( '' === trim( $content ) ) {
			return $content;
		}

		return $content . '<p class="acme-blog-reading-time">'
			. esc_html__( 'Reading time: about one minute', 'acme-blog' )
			. '</p>';
	}
}
```

Add the class to the existing registration list without replacing any entries:

```php
// inc/Main.php
use rtCamp\Theme\Acme_Blog\Modules\ReadingTime;

public const CLASSES = [
	// Keep every existing entry here.
	ReadingTime::class,
];
```

The real list already contains the core services and retained examples; the
snippet shows only the new import and entry. Run `composer dump-autoload`, then
run the focused test and open a post on the active site. The notice should
appear after the post content. For generated classes and their test stubs, use
[Scaffolding](docs/scaffolding.md); the CLI and AI routes are alternatives.

The framework supplies the `Registrable` contract and loader. Its
[contracts reference](https://github.com/rtCamp/wp-framework/blob/v1.0.1/docs/contracts.md)
and [abstract-class cookbook](https://github.com/rtCamp/wp-framework/blob/v1.0.1/docs/abstracts.md)
cover inherited methods and lifecycle behavior.

## Register a post type and taxonomy

Content that must survive a theme switch belongs in a companion plugin. Do not
put these classes in the theme or add them to the theme's `Main::CLASSES`.

These examples extend an **existing, configured framework-based plugin** at
`wp-content/plugins/acme-content/`. Before adding the classes, it must have:

- An installed `rtcamp/wp-framework` 1.0.x dependency and Composer mapping
  `Acme\Content\` to `inc/`.
- A plugin entry file with a WordPress plugin header, loading its Composer
  autoloader through `inc/Autoloader.php` and booting `Acme\Content\Main`
  before `init`.
- A `Main` class using the framework `Loader`, and `PostTypes` and `Taxonomies`
  modules extending `AbstractModule` with `get_classes()` lists.
- A configured PHPUnit suite that boots the plugin.

The theme does not create this plugin. If it is not set up, complete its
bootstrap first using the framework's
[module and loader guide](https://github.com/rtCamp/wp-framework/blob/v1.0.1/docs/architecture.md#modules-loaders-that-hold-loaders).
Paths below are relative to the plugin root; use that directory for Composer
commands and its WordPress environment for `wp` commands.

```text
acme-content.php                 # Autoloader::autoload() → Main::get_instance()
inc/Main.php                     # plugin Main::CLASSES
inc/Modules/PostTypes.php        # owns post-type classes
inc/Modules/PostTypes/Book.php
inc/Modules/Taxonomies.php       # owns taxonomy classes
inc/Modules/Taxonomies/Genre.php
tests/php/PostTypesTest.php
tests/php/TaxonomiesTest.php
```

The root plugin file loads `inc/Autoloader.php` and calls
`Acme\Content\Main::get_instance()`. The plugin's `Main::CLASSES` must contain
`\Acme\Content\Modules\PostTypes::class` and
`\Acme\Content\Modules\Taxonomies::class`; each module's `get_classes()` returns
its concrete classes. Activate the plugin with `wp plugin activate acme-content`.
Before adding each class below, add its registration assertion to the plugin's
tests and run the focused test to confirm it fails.

### Post type

Put this class in `inc/Modules/PostTypes/Book.php`:

```php
<?php

declare( strict_types = 1 );

namespace Acme\Content\Modules\PostTypes;

use rtCamp\WPFramework\Contracts\Abstracts\AbstractPostType;

final class Book extends AbstractPostType {
	public static function get_slug(): string {
		return 'book';
	}

	public function get_singular_label(): string {
		return __( 'Book', 'acme-content' );
	}

	public function get_plural_label(): string {
		return __( 'Books', 'acme-content' );
	}

	public function get_menu_icon(): string {
		return 'dashicons-media-document';
	}
}
```

In `inc/Modules/PostTypes.php`, append
`\Acme\Content\Modules\PostTypes\Book::class` to the existing array returned by
`get_classes()`. Keep its other entries and run `composer dump-autoload`.

`AbstractPostType` registers the type on WordPress's `init` hook. After
activating the plugin, confirm `wp post-type list` includes `book` and create a
book in the admin. Rerun `tests/php/PostTypesTest.php` and confirm its
`post_type_exists( 'book' )` assertion passes.

### Taxonomy

Put this class in `inc/Modules/Taxonomies/Genre.php`. Its object type list is
the association that makes the taxonomy available to books:

```php
<?php

declare( strict_types = 1 );

namespace Acme\Content\Modules\Taxonomies;

use rtCamp\WPFramework\Contracts\Abstracts\AbstractTaxonomy;

final class Genre extends AbstractTaxonomy {
	public static function get_slug(): string {
		return 'genre';
	}

	public static function get_object_types(): array {
		return [ 'book' ];
	}

	public function get_singular_label(): string {
		return __( 'Genre', 'acme-content' );
	}

	public function get_plural_label(): string {
		return __( 'Genres', 'acme-content' );
	}
}
```

In `inc/Modules/Taxonomies.php`, append
`\Acme\Content\Modules\Taxonomies\Genre::class` to the existing array returned
by `get_classes()` and run `composer dump-autoload`.

After plugin activation, `wp taxonomy list` should include `genre`, and the
Book editor should show its Genre control. Rerun `tests/php/TaxonomiesTest.php`:
`taxonomy_exists( 'genre' )` should be true and its `object_type` should contain
`book`. The framework's
[post-type and taxonomy reference](https://github.com/rtCamp/wp-framework/blob/v1.0.1/docs/abstracts.md#content-registration)
documents the available overrides. Its [module and loader guide](https://github.com/rtCamp/wp-framework/blob/v1.0.1/docs/architecture.md#modules-loaders-that-hold-loaders)
explains why the plugin module owns these classes.

## Notes

Do not use `classmap` autoloading for namespaced classes — it works against
the PSR-4 mapping every convention above assumes. Keep the project's PSR-4
root aligned with `inc/` as directory segments and class names change.

## When a feature does not appear

Check the failure at the first boundary that can explain it:

| Symptom | Check and next action |
| --- | --- |
| PHP class is found but no behavior runs | Confirm the namespace matches the `composer.json` PSR-4 prefix, the file path mirrors it, and the class is in the correct `Main::CLASSES` list (or companion-plugin module). Run `composer dump-autoload` and reload WordPress. |
| Shortcode or hook is missing | Confirm the class implements `Registrable`, its `register_hooks()` method adds the expected hook, and the active theme/plugin is the one you edited. Run the focused PHPUnit test. |
| A block or script is missing | Check the editable source under `src/`, run `npm run build:dev`, and confirm the matching file exists under `assets/build/`. Do not edit generated output. |
| The page shows a PHP error | Check the WordPress debug log and the first file/line in the error. Run the focused test and `composer phpcs`; fix the namespace, hook signature, or escaping issue reported there. |
| Companion content type is absent | Confirm the plugin is active, the concrete class is returned by its owning module's `get_classes()`, and `Main::CLASSES` contains that module. Run `wp post-type list` or `wp taxonomy list` again. |
| Composer dependencies aren't installed | Not a hard crash — `inc/Autoloader.php` shows an admin notice instead of fataling; run `composer install` and reload. |

Finish a change with the full checks and browser review in [Local
development](docs/local-development.md#check-a-change). Keep the detailed
framework API in its upstream documentation and use the [feature
catalogue](docs/features.md) to see what this starter already supplies.
