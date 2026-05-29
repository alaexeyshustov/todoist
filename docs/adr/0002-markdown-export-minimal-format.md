# ADR 0002: Markdown export uses minimal, lossy format

## Status
Accepted

## Context
Todos carry rich metadata: due date, priority, recurrence, and subtasks. When exporting a List to Markdown there were two format options:

- **Full fidelity** — encode all fields inline (e.g. `- [ ] Buy groceries 📅 2026-06-01 ⚡ high 🔁 weekly`). Round-trip safe but unreadable in standard Markdown viewers.
- **Minimal** — title, done flag, and subtasks only, using standard GitHub task list syntax. Human-readable and portable, but metadata is dropped on export.

## Decision
Export uses the minimal format. The file contains only `title`, `done` state, and one level of subtasks. Metadata fields (due date, priority, recurrence) are not included.

## Consequences
- Exported files are clean, readable Markdown that works in any Markdown viewer (Obsidian, GitHub, etc.).
- Export is intentionally lossy — re-importing a previously exported file will not restore due dates, priorities, or recurrence rules.
- This trade-off is acceptable because export targets the portability/readability use case, not backup/restore.
