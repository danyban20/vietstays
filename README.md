# Vietstays v2 — Laravel + Vue Host Dashboard

Standalone rewrite of the Vietstays WordPress plugin as a **Laravel 13** API + **Vue 3** SPA.

## Stack

- **Backend:** Laravel 13, Sanctum (session SPA auth), MySQL
- **Frontend:** Vue 3, Vue Router, Pinia, Vite
- **Design:** `design_handoff_host_dashboard/` (Host Dashboard standalone mockup + README tokens)
- **Legacy data:** `db/vietstays.sql` (WordPress export with `vv_*` tables)

## Folder layout

```
vietstays/                  # Laravel app root (document root: public/)
├── app/
├── database/
├── resources/js/           # Vue SPA (host dashboard)
├── routes/api.php
├── design_handoff_host_dashboard/
├── db/vietstays.sql
└── old/                    # Archived WordPress site + plugin
```

## Setup (Laragon)

Point **vietstays.test** at `D:\laragon\www\vietstays\public` (already configured in Laragon).

### 1. Create the database

```sql
CREATE DATABASE vietstays_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Configure environment

```bash
cd D:\laragon\www\vietstays
copy .env.example .env
php artisan key:generate
```

Edit `.env` — set `DB_DATABASE=vietstays_v2`, `DB_USERNAME`, `DB_PASSWORD`, and `APP_URL=http://vietstays.test`.

Laragon vhost document root: `D:\laragon\www\vietstays\public`.

### 3. Install dependencies

```bash
composer install
npm install --legacy-peer-deps
```

### 4. Migrate & seed legacy data

```bash
php artisan migrate
php artisan db:seed
```

Or one-shot:

```bash
php artisan vietstays:import-legacy
```

This imports all `vv_*` tables, WordPress users (with roles), and buildings from `gyh_posts` (neighbourhood type).

### 5. Run

```bash
# Terminal 1 (optional if using Laragon Apache)
php artisan serve --host=vietstays.test --port=8000

# Terminal 2
npm run dev
```

Open `http://vietstays.test`.

## Login

Legacy WordPress passwords are supported via `WordPressPasswordVerifier` (phpass + `$wp$` bcrypt).

Example accounts from the SQL dump:

| Email | Role |
|-------|------|
| dan@wiise.no | admin |
| partner1@visitvietnam.no | partner |
| dev2@wiise.no | host |

Use the same password as on the WordPress site.

To set a new Laravel password for testing:

```bash
php artisan tinker
>>> \App\Models\User::where('email','dan@wiise.no')->update(['password' => bcrypt('secret')]);
```

## API endpoints

| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/login` | Session login |
| POST | `/api/logout` | Logout |
| GET | `/api/user` | Current user |
| GET | `/api/dashboard` | Dashboard stats |
| GET | `/api/apartments` | Apartment list |
| GET | `/api/apartments/{id}` | Apartment detail |
| GET | `/api/bookings` | Booking list |
| GET | `/api/bookings/{id}` | Booking detail |

## Vue pages (host dashboard)

- **Dashboard** — stats overview
- **Bookings** — list, calendar placeholder, detail
- **Apartments** — card list, detail tabs (Availability / Price / Presentation)

UI follows design tokens from `design_handoff_host_dashboard/README.md` (green `#12352b`, orange `#e0793a`, sand backgrounds).

## Migration from WordPress plugin

| WordPress | Laravel v2 |
|-----------|------------|
| `vv_*` custom tables | Same table names (legacy migrations) |
| `gyh_posts` neighbourhood | `buildings` table |
| `gyh_users` | `users` + `legacy_wp_id` |
| `/vv-admin/*` PHP views | Vue SPA routes |
| Plugin classes | `App\Models\*` + API controllers |

## Next steps

- [ ] Apartment wizard (5-step modal from design handoff)
- [ ] Booking calendar drag-and-drop
- [ ] Full CRUD for bookings/apartments
- [ ] Admin vs host role middleware
- [ ] i18n (nb/en/vi keys from design README section 12)

## Design reference

Open `design_handoff_host_dashboard/Host Dashboard (standalone).html` in a browser for the interactive prototype (Norwegian UI copy).
