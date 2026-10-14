# AGENTS.md

Before editing this repository, read `README.md` and the relevant files in `docs/`.

Rules for all coding agents:
- Follow Laravel 13 conventions and keep the app as one Laravel + Inertia React application.
- Run automated backend tests after backend changes.
- Run TypeScript checks and a production Vite build after frontend changes.
- Never bypass policies, middleware, Form Request authorization, or server-side ownership checks.
- Never expose restricted screener URLs, private documents, hidden contact data, internal notes, or audit metadata to unauthorized clients.
- Never commit secrets or real production credentials.
- Keep migrations reversible and enforce integrity with foreign keys/indexes/unique constraints.
- Avoid unnecessary dependencies and premature infrastructure.
- Preserve responsive/mobile behavior and WCAG-oriented semantics.
- Keep controllers thin; use actions/services for multi-write workflows.
- Update documentation when architecture, workflows, setup, or deployment changes.
- Do not introduce shopping-cart, checkout, payment, DRM, or automatic licensing behavior into the MVP.
