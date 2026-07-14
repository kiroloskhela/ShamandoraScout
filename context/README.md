# ShamandoraScout — Project Context Index

Persisted investigation artifacts so future sessions do **not** need to re-explore the whole codebase.

| File | Purpose |
|------|---------|
| [architecture-overview.md](./architecture-overview.md) | Current system architecture summary (stack, data flow, inventory) |
| [scalability-investigation-2026-07-14.md](./scalability-investigation-2026-07-14.md) | Full scalability / architecture investigation (backend, frontend, remediation) |
| [database-schema-reality-2026-07-14.md](./database-schema-reality-2026-07-14.md) | schema.sql as source of truth: PK/index/FK gaps vs models; DB edit packages |
| [refactor-wave-status-2026-07-15.md](./refactor-wave-status-2026-07-15.md) | Wave 0–4 + Pre–Wave 5 status on `testing` |
| [audit-logs.md](./audit-logs.md) | SuperAdmin mutating-request audit trail |
| [whatsapp-bridge.md](./whatsapp-bridge.md) | Baileys LocalAuth bridge ops + Laravel contract |

## How to use in future chats

Tell the agent: *“Read `context/scalability-investigation-2026-07-14.md` and `context/architecture-overview.md` before proposing changes.”*

## Investigation date

**2026-07-14** — deep technical investigation of the full repository.

## Status legend used in reports

- **Confirmed** — verified in source / schema with file evidence
- **Potential** — strong risk based on patterns; needs runtime or prod-config confirmation
