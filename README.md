# whmcs-addon-starter

Production-ready boilerplate for a **WHMCS addon module** — admin page, client-area page, activation/deactivation/upgrade lifecycle, hooks, and a language file. Drop it in and start writing business logic instead of wiring.

## Install

1. Rename the folder and `addonstarter.php` to your module name (lowercase), e.g. `myreporter/` + `myreporter.php`.
2. Replace every `addonstarter` / `AddonStarter` / `Addon Starter` occurrence with your module name.
3. Upload to `/modules/addons/` on your WHMCS install.
4. Setup → Addon Modules → Activate → configure the fields.

## What's inside

| File | Purpose |
|---|---|
| `addonstarter.php` | `config()`, `activate()`, `deactivate()`, `upgrade()`, `output()`, `sidebar()`, `clientarea()` |
| `lib/Admin/Controller.php` | Thin controller: paged event-log admin page, XSS-safe output helper, debug logging |
| `hooks.php` | Example hooks (`ClientLogin` logging, client-area navbar link) — copy to `/includes/hooks/` |
| `lang/english.php` | Language strings (rename for more languages) |
| `templates/clientarea/` | (your Smarty templates go here) |

## Notes

- `activate()` is idempotent (`IF NOT EXISTS` tables); `deactivate()` keeps data — real deletion belongs behind an explicit uninstall action on your own page.
- Settings fields support `encrypt()`/`decrypt()` for secrets like API keys.
- The controller records events in `tbladdon_starter_log` — a ready-made audit trail.

Requires WHMCS 8.x, PHP 7.4+. MIT licensed.
