# What the theme includes

The starter theme provides infrastructure you build on, examples you can adapt, and optional development tools you can turn on when you need them. This page is a map of all three — what each one is, where its source lives, and how to see it working. [Initialization](initialization.md) owns the commands that select and toggle them; [Local development](local-development.md) owns builds and checks.

## Theme infrastructure

These facilities are part of the starter and remain after an example is removed. The loaders and utilities are theme-owned adapters around the linked wp-framework services.

| Facility | Ownership and source | Default / lifecycle | Try or verify |
| --- | --- | --- | --- |
| [Block-theme layout](https://developer.wordpress.org/block-editor/how-to-guides/themes/block-theme-overview/) | Skeleton: `theme.json`, `templates/`, `parts/`, `patterns/`, `styles/` | Included; edit these files directly. | Open Appearance → Editor and edit a template or style. |
| [Bootstrap and registration](../DEVELOPMENT.md#adding-a-new-class) | Skeleton: `functions.php`, `inc/Main.php`, `inc/Core/`; framework: `Loader` and `Registrable` ([contracts](https://github.com/rtCamp/wp-framework/blob/main/docs/contracts.md)) | Included; add new classes to `Main::CLASSES`. | Add a class and run `composer dump-autoload`. |
| [Asset loading](asset-building-process.md) | Skeleton: `inc/Core/Assets.php`, `src/css/`, `src/js/`; framework: `AssetLoader` ([loaders](https://github.com/rtCamp/wp-framework/blob/main/docs/loaders.md#assetloader)) | Included; source is editable and `assets/build/` is generated. | Run a development build and inspect the loaded frontend assets. |
| [Components](https://github.com/rtCamp/wp-framework/blob/main/docs/loaders.md#componentloader) | Skeleton: `inc/Core/Components.php`, `src/components/`; framework: `ComponentLoader` | Loader included; button and card examples can be removed during init. | Render a component with `Util::component()` as shown below. |
| [Template helpers](https://github.com/rtCamp/wp-framework/blob/main/docs/loaders.md#templateloader) | Skeleton: `inc/Core/Templates.php`, `template-parts/`; framework: `TemplateLoader` | Loader included; the supplied author template follows the shortcode example's lifecycle. | Render a part with `Util::get_template()` and check the frontend. |
| [Runtime feature flags](https://github.com/rtCamp/wp-framework/blob/main/docs/utilities.md#featureselector) | Skeleton: `inc/Core/FeatureRegistry.php`, `inc/Abstracts/AbstractThemeFeature.php`; framework: `FeatureSelector` | Registry included; retained feature flags are toggled in Settings → Features. | Toggle a retained example and check the next request. |
| [Logging](https://github.com/rtCamp/wp-framework/blob/main/docs/utilities.md#logger) | Skeleton: `inc/Core/Logger.php`, `Util::logger()`; framework: `Logger` | Included; writes only when `WP_DEBUG` is enabled. | Call `Util::logger()->info( 'Theme loaded' )` and inspect the WordPress debug log. |
| [Encryption service](https://github.com/rtCamp/wp-framework/blob/main/docs/utilities.md#encryptor) | Skeleton: `inc/Core/Encryption.php`, `Util::encryption()`; framework: `Encryptor` | Included; configure the theme encryption key before use. | Encrypt and decrypt a value, then confirm the round trip. |
| [Quality checks](local-development.md) | Skeleton: `tests/`, `package.json`, `composer.json` | Included; run focused checks for each change. | Run the [development checks](local-development.md#check-a-change). |

## Supplied examples

All example sets are kept by default. Removing a set during init deletes its configured files and consumes its registration markers; it is not an on/off switch you can later use to restore the example.

| Init key | Ownership and source | Default / lifecycle | How to see it |
| --- | --- | --- | --- |
| `block-extension` | Skeleton: `inc/Modules/BlockExtensions/MediaTextInteractive.php`, `patterns/media-text-interactive.php`, `src/js/frontend/modules/media-text.js` | Kept by default; remove during first init only. The `media-text-interactive` runtime flag can be toggled in Settings → Features. | Insert the supplied media/text pattern, build the module, and check its frontend interaction. |
| `settings` | Skeleton: `inc/Modules/Settings/ThemeOptions.php` | Kept by default; remove during first init only. | Open the theme's settings page under Settings; save Example Text and reload. |
| `shortcode` | Skeleton: `inc/Modules/Shortcodes/AuthorBio.php`, `template-parts/author-bio.php` | Kept by default; remove during first init only. The `author-bio` runtime flag can be toggled in Settings → Features. | Populate a user's biography and add the renamed author-bio shortcode to a Shortcode block, using that user's ID. |
| `components` | Skeleton: `src/components/button/`, `src/components/card/` | Kept by default; remove during first init only. | Call a component from a PHP template and build its assets. |
| `patterns` | Skeleton: `patterns/page-creation-pattern.php` | Kept by default; remove during first init only. | Create a page and use the supplied page-creation pattern. |

For an Acme Blog project that retained the button example, call this from a PHP template:

```php
\rtCamp\Theme\Acme_Blog\Helpers\Util::component(
    'button',
    [ 'label' => __( 'Read more', 'acme-blog' ), 'url' => home_url( '/' ) ]
);
```

The [development guide](../DEVELOPMENT.md#adding-a-new-class) shows the manual extension pattern. To generate a new feature, follow [Scaffolding](scaffolding.md). Original examples remain browsable in the [starter theme source](https://github.com/rtCamp/theme-elementary/tree/theme-elementary-v2/inc/Modules) after you remove them from your project.

## Optional development features

Unlike the supplied examples, these aren't source you keep or remove once — they're features you can turn on or off at any time through init.

| Init key | Ownership and source | Default / lifecycle | Theme-visible result | Verify |
| --- | --- | --- | --- | --- |
| `hmr` | Skeleton: `webpack.config.js`, `inc/Core/Assets.php`, `.env.local` | On by default; toggle repeatedly with init or `ENABLE_HMR`. Dependencies remain installed. | BrowserSync watches compiled assets and reloads or injects changes. | Start the asset watcher in a local WordPress environment and edit CSS. See [Live reload](hmr.md). |
| `tailwind` | Skeleton: `bin/features/tailwind/`, `webpack.config.js`, `functions.php`, `inc/Core/Assets.php`; tooling package: `@rtcamp/tailwind-config` | Off by default; enable or disable with init. The generated token file and build output are ignored. | Adds the entry and PostCSS config, declares dependencies, and enables the Tailwind enqueue constant. | Run `npm install`, build, and inspect a utility class. See [Tailwind](tailwind.md). |

The Settings → Features runtime switches, such as `author-bio` and `media-text-interactive`, control whether retained PHP hooks register. They are separate from init's optional development features and never delete source files.
