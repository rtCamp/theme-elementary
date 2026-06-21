# Lint Configs

The theme uses two shareable configs from [`wp-tooling`](https://github.com/rtCamp/wp-tooling) to standardise linting across rtCamp projects:

| Package | Purpose |
|---|---|
| `@rtcamp/eslint-config` | ESLint flat config for WordPress JS |
| `@rtcamp/stylelint-config` | Stylelint config for WordPress SCSS/CSS |

---

## ESLint — `@rtcamp/eslint-config`

### What it includes

- `@wordpress/eslint-plugin` recommended rules
- `@eslint-community/eslint-plugin-eslint-comments` recommended rules
- `eslint-plugin-jest` flat/recommended rules, scoped to `**/*.test.js`

Requires ESLint v9+ (flat config only — no legacy `.eslintrc` support).

### Install

```bash
npm install --save-dev @rtcamp/eslint-config eslint @wordpress/eslint-plugin
```

`eslint` and `@wordpress/eslint-plugin` must be listed explicitly — `@wordpress/scripts` pulls in `eslint@9` transitively, which conflicts with `@rtcamp/eslint-config`'s `eslint@^10` peer dependency.

### Usage in this theme

Spread the shared config in `eslint.config.mjs` and add theme-specific overrides on top:

```js
import rtcampEslint from '@rtcamp/eslint-config';

export default [
    ...rtcampEslint,

    // Theme-specific overrides
    {
        languageOptions: { sourceType: 'module' },
        rules: {
            'jsdoc/check-indentation': 'error',
            '@wordpress/dependency-group': 'error',
        },
    },
];
```

---

## Stylelint — `@rtcamp/stylelint-config`

### What it includes

- `@wordpress/stylelint-config` — WordPress CSS rules
- `@wordpress/stylelint-config/scss` — SCSS-specific rules

### Install

```bash
npm install --save-dev @rtcamp/stylelint-config
```

### Usage in this theme

In `.stylelintrc.json`:

```json
{
    "extends": "@rtcamp/stylelint-config",
    "ignoreFiles": [
        "**/*.js"
    ],
    "rules": {}
}
```

`@rtcamp/stylelint-config` already extends both the base WordPress config and its SCSS variant, so the explicit `/scss` reference is no longer needed.

The existing `.stylelintignore` file continues to work unchanged.

---

## Running the linters

```bash
# Check JS
npm run lint:js

# Fix JS
npm run lint:js:fix

# Check CSS/SCSS
npm run lint:css

# Fix CSS/SCSS
npm run lint:css:fix

# Run all linters in parallel
npm run lint:all
```
