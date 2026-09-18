# Maintainer guidance

For developers maintaining Theme Elementary itself. Downstream theme developers
should start with [Getting Started](../getting-started.md) and
[Local development](../local-development.md).

The [maintenance guide](maintenance.md) covers:

- Verifying the advertised developer journeys against a specific revision.
- Working on a dependency locally and restoring its source override.
- Publishing and checking this repository's documentation.
- Tracking implementation gaps without adding workarounds to onboarding.

[Knowledge graph](knowledge-graph.md) covers optional code-navigation setup and refreshes.

Documentation and smoke results must identify the theme commit and installed
dependency revisions. The guides target `theme-elementary-v2` and changes built
on it; do not assume `main` or a separately installed dependency checkout has
the same behavior.

## Temporary procedure status

The registry-bypass pilot from the former `docs/internal-testing.md` is retired:
the current [npm declaration](../../package.json) uses GitHub's `npm/wp-tooling`
branch, and [Composer](../../composer.json) resolves the framework through VCS.
Local source overrides are only for deliberate dependency development.

The remaining [validation workarounds](maintenance.md#temporary-validation-procedures)
have an applicable revision and a retirement condition. Recheck their status
when the theme or its dependencies change.
