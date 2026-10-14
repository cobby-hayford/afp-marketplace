# African Film Producers — Content Marketplace

A production-oriented B2B discovery and deal-introduction marketplace for African film, music, television and digital content. The platform connects approved producers and organisations with buyers, broadcasters, distributors, investors and creative partners. It deliberately does **not** implement carts, checkout, licensing payments, automatic delivery, DRM, or full-film hosting.

## Stack
- Laravel 13 / PHP 8.3+
- React 19.2 + TypeScript + Inertia 3
- Tailwind CSS 4 + Vite 8
- PostgreSQL production database
- Database queue by default; Redis-ready later
- Laravel Reverb private channels for notifications/message updates
- Laravel Storage abstraction: local development, S3/R2-compatible production

## Requirements
- PHP 8.3+
- Composer 2
- Node.js 22 LTS recommended (use a Node release supported by Vite 8)
- npm
- PostgreSQL 15+ recommended

## Installation
```bash
git clone <repository-url>
cd afp-marketplace
composer install
npm install
cp .env.example .env
php artisan key:generate
# Configure PostgreSQL credentials in .env
php artisan migrate
php artisan db:seed
php artisan storage:link
npm run build
composer run dev
```

`DatabaseSeeder` loads production-safe reference data and editable public pages. It does **not** create demo accounts.

## Demo data
For a local/demo environment only:
```bash
php artisan db:seed --class=DemoSeeder
```

`DemoSeeder` creates development-only accounts and records including **Tindana**, producers from Ghana, Nigeria, Kenya, South Africa and Senegal, a production company, broadcaster, buying request, collaboration request, opportunity and news. Example sign-ins are `ama.demo@example.test` (producer) and `maya.demo@example.test` (broadcaster), both using `demo-password`. Never run these credentials or the demo seeder in production.

### Dependency lock files
The generation environment could not reach Composer/npm registries, so it could not produce trustworthy `composer.lock` or `package-lock.json` files. On the **first connected development machine**, run `composer update` and `npm install`, review the resolved versions, run the full QA commands below, and commit both lock files. After that, use `composer install` and `npm ci` for reproducible installs and production deployments.

## Tests and quality
The default `phpunit.xml` uses in-memory SQLite for a fast portable check. Before release, run the same suite against PostgreSQL using the dedicated configuration:

```bash
cp .env.testing.example .env.testing
# Set the isolated PostgreSQL test credentials in .env.testing
php artisan key:generate --env=testing
php artisan test
./vendor/bin/phpunit --configuration phpunit.postgresql.xml
./vendor/bin/pint --test
npm run typecheck
npm run lint
npm run build
```

## First administrator
```bash
php artisan app:create-admin
```
The command interactively requests name, email and a strong password, creates the initial `super_administrator`, and verifies that admin email. No production admin password is stored in source.

## Application surfaces
- Public marketplace: `/`, `/trade-hall`, `/projects`, `/producers`, `/stands`, `/buying-requests`, `/collaboration`, `/opportunities`, `/news`
- Member portal: `/dashboard`, profile/organisation/stand/project management, view requests, messages, saved items and notifications
- Admin portal: `/admin` with member/project/organisation/buyer/collaboration/verification queues, private internal review notes, CMS, taxonomies, reports, audit log and settings

Policies, middleware, Form Requests and server-side ownership checks protect every sensitive workflow. Restricted screeners and private storage paths are never serialized for unauthorized users.

## Validation note for this generated repository
The source tree was statically reviewed in the generation environment and **193 PHP files** pass `php -l`; TSX parsing also produced no syntax diagnostics. The environment did **not** contain Composer or PostgreSQL and could not reach the npm registry, so dependency installation, migrations, the Laravel test suite, Pint, full TypeScript dependency-aware checking, ESLint and the Vite production build could not be executed there. Run the commands above in a connected workstation/CI environment before deployment.

See `docs/ARCHITECTURE.md`, `docs/IMPLEMENTATION_PLAN.md`, `docs/QA_CHECKLIST.md`, and `docs/DEPLOYMENT.md`.
