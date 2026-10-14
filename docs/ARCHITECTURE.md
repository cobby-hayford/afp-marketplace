# Architecture

## Application shape
One Laravel 13 application serves three Inertia surfaces: the public marketplace, authenticated member portal, and protected admin portal. React + TypeScript renders all interactive pages; Laravel owns authentication, authorization, persistence, file access, moderation and business workflows.

## Core identity model
`users` stores system role (`member`, `moderator`, `administrator`, `super_administrator`) separately from marketplace account type. `profiles` stores public/professional identity and moderation status. `organisations` and `organisation_members` model companies and internal membership roles. `stands` provide public trade-hall presences for profiles or organisations.

## Marketplace model
Projects belong to a user and may belong to an organisation/stand. Relational taxonomies cover country, content type, stage, genres, languages and looking-for tags. Team members can link to a registered profile or carry an external display name. Project media and public/restricted documents are separate records. `project_rights` stores structured commercial-right availability by right type and territory rather than burying those facts in JSON. Original languages and subtitle languages share a normalized pivot with an explicit `kind` discriminator. The restricted screener is stored on the project but is only serialized after an ownership/admin/access-grant check.

## Moderation and verification
Profile/project/buying-request/collaboration states are independent from production stages. `moderation_actions` preserves state transitions and member-facing moderator comments. `admin_notes` is a separate administrator-only polymorphic record used for internal user/application review notes and is never exposed on public/member surfaces. `verification_records` represents association/company/festival checks without implying rights ownership or commercial quality. `audit_logs` records sensitive administrative and access actions.

## Request-to-view and messaging
`view_requests` represent the buyer's request. Approval is transactional: request status changes, an `access_grant` is created, a contextual conversation is found/created, participants are synchronized, and notifications are created. Conversation policies prevent IDOR. Messages persist in PostgreSQL first and broadcast on a private `conversation.{id}` Reverb channel after commit.

## Storage
Public artwork uses the `public` disk. Pitch decks/press kits, membership evidence and festival evidence use the `private` disk. Private files are never linked directly; download routes resolve the model, authorize access, then stream from Storage. Production may map both disks to S3-compatible storage with separate prefixes/buckets/policies.

All ISO countries are seeded; African country records are explicitly marked with `is_african` and region metadata for country landing pages while international buyers can still register.

## Search/performance
Listing queries use indexed scalar columns, relational filters, eager loading and pagination. Query parameters persist in marketplace filters. The MVP uses case-normalized SQL `LIKE` queries that remain PostgreSQL-compatible and does not require Elasticsearch/Algolia.

## SEO
Public controllers provide page-specific title/description/canonical data to Inertia. Project/profile/news pages include JSON-LD composed only from actual stored data. Sitemap output excludes drafts, authenticated pages and restricted resources.

## Security decisions
Authorization is always server-side through middleware, policies and Form Request `authorize()` methods. Suspended users are blocked from restricted mutations. Rate limiters protect authentication, messaging, access requests, responses, reports and contact submissions. HTML message bodies are rendered as text on the client. File types and sizes are allow-listed.
