# Block Development Guide

The **Elementary Theme** provides a structured workflow for creating and managing Gutenberg blocks.
This guide explains how to create blocks, build them, and organize them within the theme.

---

## Block Naming Rules

Block names must follow WordPress slug conventions.

A valid block name:

- Uses **lowercase letters**, **numbers**, and **hyphens**
- Must **start and end** with a letter or number
- Cannot contain spaces or underscores

### Valid Examples

```
hero-section
testimonial-slider
cta2
```

### Invalid Examples

```
-hero
hero-
Hero Section
hero_section
```

If an invalid name is used, the CLI command will exit with an error.

---

## Creating a Block

Blocks are created using the custom CLI command included in the theme.

### Command

```bash
npm run create:block <block-name> -- [options]
```

The `--` separator is required to pass arguments through `npm run` to the underlying script.

### Example

```bash
npm run create:block hero-section -- --title="Hero Section" --category="text"
```

This command runs the `bin/create-block.js` script, which internally uses `@wordpress/create-block` to scaffold the block inside the theme.

The generated block is placed in `assets/src/blocks/<block-name>`.

---

## Excluding Blocks From Build

To exclude a block from being built or registered, prefix the folder name with an underscore.

Example:

```
assets/src/blocks/_example-block
```

Blocks prefixed with `_`:

- Are **not compiled during the build process**
- Are **not registered in WordPress**
- Can be used for **experimental or work-in-progress blocks**

---

## Block Directory Structure

Each block resides in its own directory within the `assets/src/blocks` folder. The typical structure is:

```
assets/
└── src/
    └── blocks/
        ├── hero-section/
        │   ├── block.json
        │   ├── index.js
        │   ├── edit.js
        │   ├── save.js
        │   ├── editor.scss
        │   └── style.scss
        ├── testimonial-slider/
        │   ├── block.json
        │   ├── index.js
        │   ├── edit.js
        │   ├── save.js
        │   ├── editor.scss
        │   └── style.scss
        └── _example-block/     # Excluded block (prefixed with `_`)
```

---

## Default Block Options

The CLI automatically applies the following defaults:

| Option | Default |
|---|---|
| `--variant` | `static` |
| `--namespace` | `elementary-theme` |
| `--no-plugin` | always applied |

These options only need to be provided if you want to override them.

Example:

```bash
npm run create:block hero-section -- --variant=dynamic
```

---

## All Available Options

| Parameter | Default | Description |
|---|---|---|
| `--title` | *(block name)* | **(Recommended)** Display name shown in the block editor |
| `--variant` | `static` | Block variant type. Override only if you need `dynamic` |
| `--namespace` | `elementary-theme` | Block namespace. Override only if targeting a different namespace |
| `--category` | *(none)* | Block category, e.g. `text`, `media`, `design`, `widgets` |
| `--icon` | *(none)* | Dashicon name or custom SVG for the block |
| `--keywords` | *(none)* | Comma-separated keywords to help users find the block |

> You can also pass any additional parameter supported by `@wordpress/create-block`.

---

## Development Workflow

### Start Development Mode

```bash
npm run start
```

Runs the build process in **watch mode**, automatically rebuilding blocks when files change.
Use this during active development.

### Production Build

```bash
npm run build:blocks
```

Compiles all block files from `assets/src/blocks` into `assets/build/blocks`.
Blocks prefixed with `_` are excluded from the output.

### Build Blocks Manifest

```bash
npm run build:block-manifest
```

Generates the `blocks-manifest.php` file by scanning compiled block metadata from `assets/build/blocks/`.
Always run this **after** `build:blocks` — the manifest is built from compiled output, not source files.
This allows WordPress to efficiently register and manage block metadata at runtime.

---

## Automatic Block Registration

Blocks are automatically registered using metadata from the compiled blocks inside `assets/build/blocks`.

The generated `blocks-manifest.php` file allows the theme to efficiently load and register all blocks during WordPress initialization. Excluded blocks (with `_` prefixes) are skipped during both the build step and registration.

No manual PHP block registration is required.

---

## Troubleshooting

| Error | Cause | Fix |
|---|---|---|
| `Error: Block name is required.` | No block name was passed to the command | Provide a valid block name: `npm run create:block my-block` |
| `Error: Block name must start and end with a lowercase letter...` | Block name contains uppercase letters, spaces, leading/trailing hyphens, or underscores | Rename to a valid slug, e.g. `my-block` instead of `My Block` or `-my-block` |
| `Error: Block "my-block" already exists at: ...` | A folder with that name already exists in `assets/src/blocks` | Choose a different name, or manually remove the existing folder first |
| `create-block exited with status 1` | `@wordpress/create-block` encountered an internal error | Check that `npx` is available and dependencies are installed (`npm install`) |
| Block not appearing in editor | Block was not built or is prefixed with `_` | Run `npm run build:blocks` and ensure the folder name does not start with `_` |

---

## Best Practices

- Use **clear and descriptive block slugs**
- Always pass `--title` for a readable block name in the editor
- Use `_` prefix for experimental or draft blocks
- Run `npm run start` during active development for automatic rebuilds
- Run `npm run build:blocks` before production deployments
- Test blocks inside the WordPress editor after creation
