# Framework Layer

`inc/Framework/` is the upstream-owned base layer for this theme.
It contains shared contracts, traits, and utilities that downstream projects may extend, but should not edit directly.

## What belongs here

- Base traits
- Base interfaces or contracts
- Core utilities that define behaviour for the theme skeleton

## What does not belong here

- Project-specific customizations
- Theme-specific feature code
- Any code that would need direct modification for a downstream project

## Why this exists

This directory separates the skeleton internals from the theme implementation.
Without a clear boundary, downstream developers can accidentally modify base code and make it difficult to merge upstream improvements.

## How to use it

- Treat `inc/Framework/` as a vendored layer.
- Do not edit files inside `inc/Framework/` unless there is absolutely no other option.
- To override behaviour, extend the upstream class or override a method in a project-specific class under `inc/`.

## Example

If you need a custom singleton implementation or a different asset loader, do not modify `inc/Framework/Traits/Singleton.php`.
Instead, extend the base class or create a new class under `inc/`.
