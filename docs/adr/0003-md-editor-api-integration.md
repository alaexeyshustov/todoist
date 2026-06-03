# ADR 0003: Expose token-based API for md-editor integration

## Status
Accepted

## Context
md-editor is a companion Android app for editing Markdown files in an Obsidian vault. The user wants to browse todoist Lists, import them as `.md` files, edit them on the phone, and push changes back.

The existing API uses session-based auth (web guard, browser cookies). A native Android app cannot use session cookies, so a token-based auth mechanism is required.

Alternatives considered:
- **File-based bridge** — todoist exports a List to a shared vault path; md-editor reads the file. No backend changes needed, but sync requires manual export/import steps and has no write-back path.
- **Reverb WebSocket in md-editor** — md-editor subscribes to the Reverb channel for real-time updates. High complexity, requires a WebSocket client in NativeScript, and still needs token auth.
- **Token-based REST API** — md-editor calls the existing REST endpoints with a Bearer token. Reuses all existing routes; only auth mechanism and one new sync endpoint need to be added.

## Decision
Add Laravel Sanctum for token-based authentication. Expose two new unauthenticated/authenticated endpoints:

- `POST /api/login` — accepts email and password, returns a Sanctum personal access token with a one-week expiry.
- `PUT /api/lists/{id}/sync` — accepts a Markdown body, deletes all existing Todos in the List, and recreates them from the Markdown. Uses the same parsing logic as the existing Import action.

All other existing routes remain unchanged and continue to work for the PWA via session auth.

## Consequences
- md-editor can authenticate with a login screen and store the token locally.
- All existing List and Todo CRUD endpoints become available to md-editor under Bearer auth.
- The sync endpoint is a full replace: every sync wipes due dates, priorities, and recurrence rules on all Todos in the synced List. Lists with rich metadata should not be synced from md-editor.
- Tokens expire after one week; md-editor must re-authenticate when a token expires.
