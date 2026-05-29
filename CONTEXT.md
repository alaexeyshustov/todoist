# Context

A personal PWA todo app that syncs in real-time between a computer and an Android phone via a shared Laravel backend.

## Glossary

### Todo
A task item with a title, a done flag (complete/incomplete), and membership in exactly one List.

### List
A named collection of Todos. Every Todo belongs to exactly one List. The default List is **Inbox**. Lists are user-managed — the user can create, rename, and delete them from the UI.

### Inbox
The default List. Todos that haven't been assigned to a specific List belong here.

### Export
The act of serializing a single List and its Todos into a Markdown file. The file starts with `# {List name}` and uses GitHub-style task list syntax (`- [ ]` / `- [x]`). Subtasks are indented one level. Metadata fields (due date, priority, recurrence) are intentionally omitted — the format prioritizes human readability over full fidelity.

### Import
The act of creating a new List from a Markdown file. The List name is taken from the `# Heading` in the file (falling back to the filename). If the name collides with an existing List, the user is prompted to choose a different name.
