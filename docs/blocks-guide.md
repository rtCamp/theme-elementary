# Documentation: Block Development Commands and Workflow in Elementary Theme

The **Elementary Theme** is a starter theme for developing WordPress block-based themes. This documentation outlines the commands available for managing blocks, the process for creating blocks, and the structure for organizing and excluding blocks in your project.

---

## How to Create a New Block

Follow these steps to create a new block:

1. **Run the `create:block` Command**  
   Execute the following command, replacing `<block-name>` with the desired name of your block:
   ```bash
   npm run create:block <block-name> -- --title="Your Block Title"
   ```

2. **Customize the Block**  
   Edit the block’s files in the `assets/src/blocks/<block-name>` directory. You can define the block’s functionality, styles, and scripts in the respective files generated.

3. **Build the Blocks**  
   Once your block development is complete, build the block to generate production-ready assets:
   ```bash
   npm run build:blocks
   ```

4. **Verify Block Registration**  
   The block will be automatically registered based on the manifest and folder structure. Check your WordPress site’s block editor to ensure the block is listed.

---

## How to Exclude a Block

If you need to exclude a block from being registered or built:

1. **Prefix the Block Folder Name with an Underscore**  
   Rename the block’s folder in `assets/src/blocks` to start with `_` (e.g., `_example-block`).

2. **Effect of the Underscore**  
   - Blocks with folder names starting with `_` are ignored during the build process.
   - They are not registered in WordPress.

---

## Folder Structure for Blocks

Each block resides in its own directory within the `assets/src/blocks` folder. The typical structure for a block is as follows:

```
assets/
└── src/
    └── blocks/
        ├── hero-section/
        │   ├── index.js        # Block script
        │   ├── style.css       # Frontend styles
        │   ├── editor.css      # Editor-specific styles
        │   └── block.json      # Block metadata
        ├── testimonial-slider/
        │   ├── index.js
        │   ├── style.css
        │   ├── editor.css
        │   └── block.json
        └── _example-block/     # Excluded block (prefixed with `_`)
```

---

## Automatic Block Registration

Blocks are automatically registered during the WordPress `init` action. The `blocks-manifest.php` file ensures all blocks in the `assets/build/blocks` directory are registered efficiently. Excluded blocks (with `_` prefixes) are skipped during registration.

---

## Commands for Managing Blocks

### 1. **Create a Block**
Command:  
```bash
npm run create:block
```

**Description**:  
This command generates a new block using the `@wordpress/create-block` package. It automatically sets up the necessary files and folders in the `assets/src/blocks` directory.

**Usage**:  
```bash
npm run create:block <block-name> -- --title="Block Title" --namespace="elementary-theme" --category="common" --icon="smiley" --keywords="block,custom"
```

**Parameters**:
- `--title`: (Optional) The display name of the block.
- `--variant`: (Optional) The variant type of block, default is `static`.
- `--namespace`: (Optional) The namespace for the block, default is `elementary-theme`.
- `--category`: (Optional) The block category, e.g., `common`, `widgets`.
- `--icon`: (Optional) An icon for the block.
- `--keywords`: (Optional) Keywords to help users find the block.
> Note: You can pass additional paramenters that can be used by `@wordpress/create-block` command.

Example:  
```bash
npm run create:block hero-section -- --title="Hero Section" --category="layout"
```

### 2. **Build All Blocks**
Command:  
```bash
npm run build:blocks
```

**Description**:  
Compiles all block files from `assets/src/blocks` and outputs the production-ready code into `assets/build/blocks`.

### 3. **Build Blocks Manifest**
Command:  
```bash
npm run build:block-manifest
```

**Description**:  
Generates the `blocks-manifest.php` file, which provides metadata about the blocks. This helps WordPress efficiently register and manage block metadata.

---

## Best Practices

- **Use meaningful names** for blocks and their folders to enhance clarity.
- **Test blocks in the WordPress editor** after creation and build to ensure proper functionality.
- **Exclude experimental blocks** by prefixing their folder names with `_`.

By following this workflow, you can streamline the process of developing and managing custom blocks in the Elementary Theme.