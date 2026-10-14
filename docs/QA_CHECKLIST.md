# QA Checklist

## Authentication and membership
- [ ] Register producer and buyer accounts.
- [ ] Confirm unverified email cannot submit profile/project.
- [ ] Verify email, complete professional profile and submit.
- [ ] Confirm non-admin cannot access approval endpoints.
- [ ] Approve/request changes/reject from admin queue and verify notifications.
- [ ] Add an internal admin note and confirm it never appears on member/public responses.
- [ ] Suspend a member, verify restricted mutations are blocked, then restore and confirm the previous profile approval state is preserved.

## Projects and privacy
- [ ] Create draft project; confirm it is absent from public catalogue and direct public route.
- [ ] Submit and approve project; confirm it becomes public/searchable.
- [ ] Confirm a draft cannot be published by replaying the admin approval endpoint.
- [ ] Unpublish/suspend/archive a published project and confirm public discovery closes immediately.
- [ ] Confirm owner can edit and unrelated member receives 403.
- [ ] Upload poster/gallery and allowed documents; reject disallowed MIME/oversized uploads.
- [ ] Confirm restricted screener URL is absent from HTML/Inertia props before access approval.
- [ ] Confirm private/request-required document download returns 403 before authorization and succeeds after grant.

## Marketplace
- [ ] Test project search, every main filter, sorting, pagination and sharable query parameters.
- [ ] Test producer directory filters and country pages.
- [ ] Test stands and global grouped search.
- [ ] Verify mobile filter drawer and no horizontal overflow at common breakpoints.

## Buyer/business workflows
- [ ] Create/submit/approve buying request.
- [ ] Respond using own published project; verify another user's project cannot be used.
- [ ] Create/submit/approve collaboration post and connect as another eligible member.
- [ ] Request project access, approve as owner, verify access grant and contextual conversation.
- [ ] Revoke access and verify restricted resources close immediately.

## Messaging/notifications
- [ ] Participants can open/send; non-participants receive 403.
- [ ] Suspended member cannot send.
- [ ] Verify unread notification badge, mark one read, mark all read.
- [ ] Verify private Reverb channel authorization mirrors conversation policy.
- [ ] Stop WebSocket process and confirm historical messages still load/persist.

## Admin/CMS/SEO
- [ ] Ordinary member denied `/admin`.
- [ ] Moderator/admin permissions match intended gates; only super admin changes privileged roles.
- [ ] Create/publish/unpublish opportunity and news post.
- [ ] Confirm audit log entries for moderation, role and access actions.
- [ ] Confirm sitemap contains only public entities and robots excludes private surfaces.
- [ ] Inspect title/meta/canonical/OG and JSON-LD for representative project/profile/news pages.

## PostgreSQL compatibility
- [ ] Run migrations from an empty PostgreSQL database.
- [ ] Copy `.env.testing.example` to `.env.testing`, configure an isolated PostgreSQL database, and run `./vendor/bin/phpunit --configuration phpunit.postgresql.xml`.
- [ ] Run `php artisan db:seed` and development-only `DemoSeeder` on a disposable PostgreSQL database.
- [ ] Exercise indexed project/producer/global search queries on PostgreSQL.

## Production readiness
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, HTTPS and secure cookies.
- [ ] Queue worker, scheduler and Reverb supervised.
- [ ] Database and object-storage backup/restore procedure tested.
- [ ] Demo seeder not executed; secure admin created via Artisan command.
- [ ] `php artisan test`, Pint, TypeScript check, lint and `npm run build` all pass.
