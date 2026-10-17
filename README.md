# Vietstays v2

A rewrite of the Vietstays WordPress plugin as a **Laravel 13** API with two **Vue 3** apps:

- **Public site** (`/`): browse apartments, get a price quote, book, message the host, apply to become a host.
- **Dashboard** (`/admin`): where hosts, partners and the Vietstays team manage apartments, bookings, customers and settings.

| | URL | Deploys |
|---|---|---|
| Production | https://vietstays.com | Manually, from `main` |
| Staging | https://dev.vietstays.com | Automatically, on every merge to `main` |

---

## Quick start

You need PHP 8.3+, Composer, Node 20+ and MySQL 8.

```bash
# 1. Database
mysql -u root -e "CREATE DATABASE vietstays_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 2. Environment
cp .env.example .env          # defaults: DB vietstays_v2, user root, APP_URL http://vietstays.test
php artisan key:generate

# 3. Dependencies
composer install
npm install

# 4. Schema + legacy data (vv_* tables, WordPress users, buildings)
php artisan migrate
php artisan db:seed           # or: php artisan vietstays:import-legacy

# 5. Run
php artisan serve             # skip if Laragon/Apache already serves public/
npm run dev
```

Then open `APP_URL` for the public site, or `APP_URL/admin` for the dashboard.

**Logging in:** legacy WordPress passwords (phpass and `$wp$` bcrypt) still work, so any account from `db/vietstays.sql` can log in with its old password. To set a password for local testing:

```bash
php artisan tinker
>>> \App\Models\User::where('email', 'you@example.com')->update(['password' => bcrypt('secret')]);
```

Staging has one test account per role; see [DEPLOYMENT.md](DEPLOYMENT.md#test-accounts-on-staging).

---

## What's built

### Public site
- Home page and apartment list and detail pages
- Price quote and booking checkout
- Message thread between guest and host
- Host application form
- Accepting a team invitation

### Dashboard
| Area | What it does |
|---|---|
| **Dashboard** | Stats overview |
| **Bookings** | List, detail, and a calendar where you drag to move bookings |
| **Apartments** | List and detail (availability, price, presentation); add-apartment wizard; photo uploads |
| **Customers** | List and detail, notes, merging duplicate customers, Excel export |
| **Messages** | Host ↔ guest conversations; admin ↔ host thread about management company registrations |
| **Team** | Sales team (with member detail pages), operations team, management company setup wizard (hosts submit a registration request, then an admin reviews it) |
| **Settings** | Email, email templates, languages |
| **Superadmin tools** | Users, host applications, hosts overview, management companies (approve / reject / pause), buildings, countries and locations, price matrix |

### Platform
- **Roles:** `superadmin`, `supervisor`, `partner`, `host`, `staff` and `ambassador`. Routes are gated with the `role:` middleware. See [DEPLOYMENT.md → Role model](DEPLOYMENT.md#role-model) for who can reach what.
- **Languages:** English (default), Norwegian, Vietnamese and Tagalog, via `vue-i18n` and `lang/vietstays/`.
- **Pricing:** base price × district index × building factor. See [PRICE_MATRIX_README.md](PRICE_MATRIX_README.md).
- **CI/CD:** tests run on every PR; merging to `main` deploys staging; production is deployed manually after a DB backup. See [Deploying](#deploying).

---

## Project layout

```
vietstays/
├── app/                        # Models, API controllers, services
├── database/                   # Migrations + seeders (incl. legacy import)
├── resources/js/
│   ├── pages/                  # Dashboard pages
│   ├── pages/public/           # Public site pages
│   ├── router/                 # admin.js (dashboard), public.js (public site)
│   └── i18n/                   # Translations
├── routes/api.php              # JSON API
├── tests/Feature/              # PHPUnit feature tests
├── db/vietstays.sql            # Legacy WordPress dump (vv_* tables)
├── design_handoff_host_dashboard/  # Design mockup + tokens
├── .github/workflows/          # CI and deploys
└── old/                        # Archived WordPress site (reference only)
```

---

## Testing

```bash
php artisan test
```

Tests use in-memory SQLite by default (`phpunit.xml`). CI runs them against MySQL 8 instead, to catch MySQL-specific issues.

---

## Deploying

```
feature branch ──PR (CI runs)──▶ main ──CI passes──▶ staging (automatic)
                                   │
                                   └── Actions → "Deploy to SiteGround" ──▶ production (manual)
```

- **Staging** deploys on every push to `main` once tests pass. It runs migrations but never seeds. You can also run it by hand from the Actions tab, for example to deploy a feature branch or to reseed.
- **Production** is deployed manually and only from `main`. It dumps the database to `~/db-backups/` first and aborts if the backup fails. The site is in maintenance mode while files sync and migrations run.

Environments, secrets and known gotchas are all in **[DEPLOYMENT.md](DEPLOYMENT.md)**. Read it before touching a shared database.

---

## More docs

| File | Topic |
|---|---|
| [DEPLOYMENT.md](DEPLOYMENT.md) | Environments, CI/CD, secrets, role model, gotchas |
| [PRICE_MATRIX_README.md](PRICE_MATRIX_README.md) | Price matrix system |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Price matrix architecture |
| [PRICE_MATRIX_BOOKING_INTEGRATION.md](PRICE_MATRIX_BOOKING_INTEGRATION.md) | Price matrix in the booking form |
| [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md) | Price simulator setup |
| `design_handoff_host_dashboard/` | Design tokens; open `Host Dashboard (standalone).html` for the prototype |

### WordPress → Laravel mapping

| WordPress | Laravel v2 |
|---|---|
| `vv_*` custom tables | Same table names |
| `gyh_posts` (neighbourhood) | `buildings` table |
| `gyh_users` | `users` + `legacy_wp_id` |
| `/vv-admin/*` PHP views | Vue dashboard at `/admin` |
