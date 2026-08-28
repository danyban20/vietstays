# Production fix — vietstays.com shared hosting

Laravel lives in `public_html/public/` but the domain root points at `public_html/`.

## 1. Upload these two files to `public_html/` (site root)

| Local file | Upload as |
|------------|-----------|
| `deploy/production-root-index.php` | `index.php` |
| `deploy/production-root.htaccess` | `.htaccess` |

This fixes **403 on https://vietstays.com/** and redirects `/public` → `/`.

## 2. Upload rebuilt frontend assets

After `npm run build`, upload the whole `public/build/` folder.

## 3. Upload updated PHP/Blade (or sync whole project)

At minimum upload:

- `resources/views/public.blade.php`
- `resources/views/admin.blade.php`

## 4. Confirm production `.env`

```env
APP_URL=https://vietstays.com
APP_ENV=production
APP_DEBUG=false
```

Then on server (SSH or hosting terminal):

```bash
php artisan config:clear
php artisan config:cache
```

## 5. Test

- https://vietstays.com/ — homepage (after step 1)
- https://vietstays.com/public/ — should redirect to `/`
- https://vietstays.com/public/api/public/apartments/search — JSON (API OK)

## Better long-term fix

In hosting panel, set document root to `public_html/public` and set `APP_URL=https://vietstays.com` (no `/public`).
