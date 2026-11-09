# Security policy

## Supported versions

| Version | Supported |
|---|---|
| Latest 2.x release and `main` | Yes |
| 1.x | No further releases; start new themes from 2.x |

Theme Elementary is a starter: a theme created from it is a copy, and it does
not update itself. A fix lands in the starter's next release, and the advisory
names the affected files so existing themes can apply it by hand. Code a
project adds after creating its theme belongs to that project's maintainers,
and issues in `rtcamp/wp-primitives` or `@rtcamp/wp-tooling` belong in those
repositories.

## Reporting a vulnerability

Please do not report security issues in public issues, pull requests or
discussions.

Report them privately through GitHub instead: open the repository's
**Security** tab and choose **Report a vulnerability**, or go straight to
<https://github.com/rtCamp/theme-elementary/security/advisories/new>. The
report stays visible only to you and the maintainers, and the fix, the release
and the advisory are coordinated in that same thread.

Include what you can of:

- the affected file or feature, and the starter release or commit;
- the impact, and the conditions an attacker needs;
- steps or a minimal proof of concept that reproduce it.
