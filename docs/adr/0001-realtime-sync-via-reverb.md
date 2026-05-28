# ADR 0001: Real-time sync via Laravel Reverb (WebSockets)

## Status
Accepted

## Context
The app runs as a PWA on multiple devices simultaneously. Changes made on one device (e.g. completing a todo on the phone) should appear on other open devices without requiring a reload.

Alternatives considered:
- **Pull-on-load** — simple, zero infrastructure, but changes don't appear until the user reopens the app.
- **Polling** — changes appear after N seconds, but wastes requests when nothing has changed.
- **WebSockets (Reverb)** — changes broadcast instantly to all connected clients.

## Decision
Use Laravel Reverb as the WebSocket server with Laravel Broadcasting to push events to connected clients. The frontend subscribes to a private channel and updates the UI on incoming events.

## Consequences
- Requires running a Reverb server process alongside the Laravel app.
- Frontend needs a WebSocket client (Laravel Echo).
- Adds complexity compared to pull-on-load, but enables instant cross-device sync which was an explicit requirement.
