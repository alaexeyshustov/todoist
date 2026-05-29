# Todoist

A personal PWA todo app with real-time sync across devices, offline support, and Markdown export/import.

**Stack:** Laravel 13 · Alpine.js · Tailwind CSS 4 · Laravel Reverb (WebSockets) · SQLite

---

## Features

- **Lists & todos** — organize todos into lists; an Inbox list is always present
- **Todo properties** — title, due date, priority (low/medium/high), recurrence (daily/weekly/monthly)
- **Subtasks** — one level of nesting per todo
- **Trash & restore** — soft-delete todos before permanent removal
- **Today view** — filter todos due today or overdue
- **Recurring tasks** — when a recurring todo is marked done, the next instance is auto-created
- **Real-time sync** — changes broadcast via WebSockets; all open sessions update instantly
- **Markdown export/import** — export a list to GitHub-flavored task list Markdown; import `.md` files to create new lists
- **PWA** — installable, with a service worker for offline read access

---

## Requirements

- PHP 8.3+
- Composer
- Node.js + pnpm

---

## Setup

```bash
composer run setup   # installs deps, copies .env, generates key, runs migrations
pnpm install
pnpm run build
```

Or step by step:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
pnpm install
pnpm run build
```

---

## Running locally

```bash
composer run dev
```

This starts the HTTP server, queue worker, log viewer, and Vite dev server together.

For real-time sync, also start the WebSocket server in a separate terminal:

```bash
php artisan reverb:start
```

The app is available at `http://localhost:8000`.

---

## Testing & code quality

```bash
composer run test        # Pest test suite
composer run lint        # format with Pint
composer run lint:check  # check formatting without changes
composer run analyse     # static analysis with PHPStan
```

---

## Architecture notes

- **Real-time sync** — Laravel Reverb acts as the WebSocket server; the frontend subscribes via Laravel Echo on a private per-user channel. See `docs/adr/0001-realtime-sync-via-reverb.md`.
- **Markdown export** — intentionally lossy (title + done state + one subtask level); metadata like due date and priority are omitted to keep the output human-readable. See `docs/adr/0002-markdown-export-minimal-format.md`.
- **Offline support** — service worker uses network-first for `/api/*` and cache-first for static assets; returns a structured 503 when offline with no cache. The app shell (`/`) is not cached to avoid stale auth state.
- **Recurring tasks** — handled via a queued job (`CreateNextRecurrence`) dispatched when a recurring todo is completed; the new instance is broadcast to all clients.
- **Authorization** — all todos and lists are scoped to the authenticated user via Laravel policies.
