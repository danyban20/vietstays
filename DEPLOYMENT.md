# Deployment & CI/CD

How this app gets from a laptop to `vietstays.com`, what changed in the role
model recently, and the gotchas that already bit us once — read this before
touching deploy workflows or running anything against a shared database.

## Environments

| | Production | Staging |
|---|---|---|
| URL | https://vietstays.com | https://dev.vietstays.com |
| SiteGround site | `vietstays.com` | same SiteGround account, subdomain `dev.vietstays.com` |
| Path on server | `~/www/vietstays.com/public_html` | `~/www/dev.vietstays.com/public_html` |
| Database | real, has live data | disposable — safe to wipe/reseed |
| SSH | `ssh.vietstays.com:18765`, user `u24-ndyryxzruyeg` | same host/user, same key |

Both sites live under the **same** SiteGround account as `vietstays.com` —
staging is *not* on the `wiise.no` agency hosting (that was the original
plan; we switched to a `dev.vietstays.com` subdomain instead since we
already had SSH there, it's free, and it keeps test→prod deploys on one
account).

## GitHub Actions workflows

- **`ci.yml`** — runs on every push to `main` and every PR: `composer
  install`, `npm run build`, spins up a MySQL 8 service, `migrate --force`
  + `db:seed --force`, then `php artisan test`. Also declared as
  `workflow_call` so `deploy.yml` can reuse it as a gate.
- **`deploy.yml`** — **manual only** (`workflow_dispatch`, "Deploy to
  SiteGround" in the Actions tab). Deploys to **production**.
  1. `test` job: re-runs the full CI suite. Deploy does not proceed if this
     fails — you can't deploy red tests to prod.
  2. Builds `composer install --no-dev` + `npm run build`.
  3. rsyncs everything to `vietstays.com`'s `public_html`, **excluding**
     `.env`, `storage/`, `public/uploads/`, `public/hot` — those are
     server-owned state, never overwritten by a deploy.
  4. Over SSH: `mysqldump | gzip` the live DB to `~/db-backups/` (outside
     `public_html`, so rsync never touches it; keeps the last 10), *then*
     `php artisan migrate --force` (skippable via the `run_migrations`
     input), then re-caches config/routes/views.
- **`deploy-staging.yml`** — same idea, **manual only**, targets
  `dev.vietstays.com`. Not gated on tests (staging is where you push
  work-in-progress to look at it). Has an opt-in `run_seed` input to run
  `php artisan db:seed --force` — safe here, **never do this on
  production** (see below).

### Required GitHub secrets (Settings → Secrets and variables → Actions)

| Secret | Used by | Value |
|---|---|---|
| `SG_SSH_HOST` | both deploys | `ssh.vietstays.com` |
| `SG_SSH_PORT` | both deploys | `18765` |
| `SG_SSH_USER` | both deploys | `u24-ndyryxzruyeg` |
| `SG_SSH_PRIVATE_KEY` | both deploys | private half of a dedicated `ed25519` key, whose public half is added under SiteGround → Site Tools → Devs → SSH Keys Manager. **Not** the same key as any personal SSH key — generate a fresh one for CI. |
| `SG_DEPLOY_PATH` | `deploy.yml` | `/home/u24-ndyryxzruyeg/www/vietstays.com/public_html` |
| `SG_STAGING_DEPLOY_PATH` | `deploy-staging.yml` | `/home/u24-ndyryxzruyeg/www/dev.vietstays.com/public_html` |

### How to deploy

1. Push/merge your branch to `main`.
2. GitHub → **Actions** → **"Deploy to staging (dev.vietstays.com)"** →
   **Run workflow**. Click through the site, check the browser console for
   errors.
3. Happy with it? **Actions** → **"Deploy to SiteGround"** → **Run
   workflow**. This is production — there's no undo button beyond the DB
   backup and whatever's in git history.

## Gotchas (things that already went wrong once)

- **Never run `php artisan db:seed` (the full seeder list) against
  production.** `LocationSeeder` `TRUNCATE`s `vv_cities`/`vv_districts` and
  `BuildingSeeder` `TRUNCATE`s `buildings`, then reimports from the static
  WordPress dump baked into the repo. Anything created directly in
  production since that dump was captured — a building added through the
  new Buildings admin page, a manually-added district — is gone,
  permanently, no soft-delete, no undo. If production ever needs a seeder
  re-run, run the *specific* non-destructive ones by name:
  ```
  php artisan db:seed --class=DistrictCodeSeeder --force
  php artisan db:seed --class=CountrySeeder --force
  php artisan db:seed --class=PriceMatrixSeeder --force
  ```
  These three only `updateOrCreate`/insert — never truncate. Staging is the
  opposite: `db:seed --force` there is fine and expected.

- **`public/hot` must never reach a server.** If it exists, Laravel's
  `@vite()` directive points the browser at a Vite *dev* server
  (`127.0.0.1:5173`) instead of the built `public/build/` assets — the
  whole admin UI goes blank with CORS errors in the console. It's
  `.gitignore`d, but a manual `rsync` from a machine that ever ran `npm run
  dev` can still drag it along; both deploy workflows exclude it
  explicitly now. If you ever see this, `rm public/hot` on the server and
  re-cache.

- **A fresh docroot has no `storage/` structure.** `storage/` is excluded
  from every deploy (it's server-owned: sessions, logs, framework cache).
  That's correct for an existing site, but a brand-new subdomain starts
  with *nothing* there — `php artisan view:cache` fails with "View path
  not found" until `storage/framework/{cache/data,sessions,views}`,
  `storage/logs`, `storage/app/public`, and `bootstrap/cache` exist.
  `deploy-staging.yml` creates these on every run (harmless once they
  exist); do the same by hand if you ever bootstrap a new environment
  outside the workflow. Also: run `config:cache` *after* those directories
  exist, not before — `config('view.compiled')` resolves via `realpath()`,
  which silently caches to `false` if the target directory didn't exist
  yet at cache time.

- **SiteGround's docroot for these sites is `public_html/`, not
  `public_html/public/`.** Laravel's own docroot is `public/`. The fix
  lives at `public_html/index.php` (`require __DIR__.'/public/index.php'`)
  and `public_html/.htaccess` (rewrites everything through `public/`) —
  source files are `deploy/production-root-index.php` and
  `deploy/production-root.htaccess`. Neither is part of the normal Laravel
  skeleton and neither is synced by the deploy workflows (they're
  root-level, outside the app's own `public/`), so a brand-new subdomain
  needs them copied in by hand once. Without them you'll see SiteGround's
  default "Under construction" page instead of the app.

- **The legacy `deploy/*.mjs` / `deploy/ftp-upload.ps1` FTP scripts are
  now redundant** — everything they did is handled by `deploy.yml`. They
  still work if SSH is ever unavailable, but prefer the GitHub Actions
  workflows going forward so there's one deploy path, not two.

## Role model

`admin` was renamed to **`superadmin`** (data migration
`2026_09_14_041959_rename_admin_role_to_superadmin`), and a new
**`supervisor`** role was added alongside it. Both are equivalent for the
purposes of the routes gated `role:superadmin,supervisor` (the four
"Superadmin tools" pages — Buildings, Countries & locations, Price matrix,
Hosts overview). Only `superadmin` can reach the stricter
`role:superadmin`-only routes (`/admin/users`, host applications, email/
language settings) — `supervisor` cannot.

If your local dev DB still has users with `role = 'admin'`, re-seed
(`php artisan migrate:fresh --seed`) or manually update those rows —
`User::isAdmin()` now checks for `'superadmin'`, so an un-migrated
`'admin'` row will silently lose admin access.

### Test accounts on staging

One account per role exists on `dev.vietstays.com` for QA (all password
`password`): `superadmin@vietstays.test`, `supervisor@vietstays.test`,
`partner@vietstays.test`, `host@vietstays.test`, `staff@vietstays.test`,
`ambassador@vietstays.test`.
