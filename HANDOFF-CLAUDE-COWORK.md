# Farvam marketing website: handoff and deployment guide

> For: Claude Cowork (or any engineer) deploying this project to the owner's server.
> The owner speaks Persian; reply to them in Persian. This document is in English so the
> technical steps are unambiguous; Persian notes for the owner are at the end.

---

## 1. What this is

**Farvam (فَروَم)** is a SaaS ecosystem for the Iranian gold and jewelry trade:
weight-based (gram / 750-equivalent) and rial accounting, automatic multi-party
settlement ("مال‌گردانی"), photo-evidence of scale/seal per voucher, a white-label
trading room, staff permissions and workshop loss tracking. The product itself already
runs at `https://farvamcertification.ir` (the owner's panel). **This package is NOT the
product.** It is the **marketing website** that sells it, with its own small admin panel.

- Language / direction: Persian, RTL. Headings use a self-hosted Nastaliq font.
- Main offer: **one month free for every customer** (toggle and text editable in admin).
- Contact baked into default content (all editable in admin):
  phone/WhatsApp `09138143073`, office "اصفهان، بازار حکیم، پاساژ حکیم، طبقه دوم",
  product panel `https://farvamcertification.ir`, company site `https://farvamgold.ir`.

## 2. Tech stack and requirements

- **Plain PHP (no framework, no database, no Composer, no build step).** All data is JSON in `data/`.
- PHP **7.4+** (tested on 8.4). Extensions: `json`, `mbstring`, `session`; `gd` with WebP
  for automatic image conversion on upload (optional; without it upload WebP directly).
- Web server: Apache or LiteSpeed (uses `.htaccess` for clean URLs and folder protection).
  Nginx works with the rules in section 6.
- No external services needed. Fonts are self-hosted; no Google Fonts calls.

## 3. Package contents (`farvam-site.zip` → folder `farvam/`)

```
farvam/
├── index.php              Home page (all sections rendered from data/content.json)
├── for.php                Trade landing pages: /for/{vitrin|bonak|kifi|kargah|abshode|sekke}
├── present.php            Guided, swipeable product presentation (from data/presentation.json)
├── blog/index.php         Article list          blog/article.php  Single article (/blog/{slug})
├── sitemap.php            XML sitemap (served as /sitemap.xml via .htaccess)
├── lead.php               POST endpoint: stores demo/free-month requests in data/leads.json
├── admin/index.php        Admin panel (password login, CSRF, rate-limited)
├── config.php             Site URL, base path, pretty URLs, initial admin password hash, timezone
├── lib.php                Helpers (content access, escaping, URLs, video embeds, SEO JSON-LD)
├── partials/              layout.php (head/SEO/header/footer), sections.php (reusable sections)
├── assets/                style.css, app.js, fonts/ (Nastaliq + Vazirmatn woff2)
├── images/                Screenshots (.webp), logos, og-cover.png (link preview)
├── files/                 farvam-presentation.pptx + admin uploads (videos, photos)
├── data/                  content.json, articles.json, engine.json, presentation.json
│                          (+ runtime: leads.json, admin.json, backups; auto-created)
├── build-static.php       CLI only: renders a static HTML copy (no admin) — not needed on server
├── robots.txt, .htaccess, README.md (Persian install notes)
```

## 4. Features (what to verify after deploy)

**Public site**
- Home page: hero with A/B-tested headline (variant stored per visitor and sent with the lead),
  3D gold logo, gold-dust animated background, light/dark theme following the device (+ manual toggle).
- One-month-free offer: top bar, offer section, optional deadline countdown, form badge.
- Loss calculator (sout/day → grams/year → toman); result pre-fills the request form.
- Four product pillars, panel screenshot gallery (accounting / operations / management tabs),
  comparison table, trade tabs, security chain, FAQ, onboarding steps.
- "Farvam in your words" dynamic pitch engine: 6 trades × 5 tones × 5 sales formulas
  (PAS, AIDA, BAB, FAB, 4P); copy / send to WhatsApp.
- Six trade landing pages with pains, before/after, mechanism, FAQ, preselected form.
- Guided presentation (`present.php`) + downloadable PowerPoint (`files/farvam-presentation.pptx`).
- Video slots (hero modal, video section, per-trade) for Aparat / YouTube links or uploaded MP4.
- Instagram + Farvam-site follow cards, referral section, testimonials, pricing, founder/about,
  eNamad badge — each **hidden until filled/enabled in admin**.
- 10 long-form SEO articles with TOC, FAQ, related posts, share buttons.
- Request form → saved server-side (`lead.php`, honeypot + 5/hour/IP limit) **and** WhatsApp
  message prefilled to the owner; captures trade, city, source, UTM campaign and A/B variant.
- SEO: per-page title/description/canonical/OG/Twitter, JSON-LD (Organization/LocalBusiness,
  SoftwareApplication with free Offer, FAQPage, BlogPosting, BreadcrumbList), sitemap, robots.
- Mobile sticky bar (call / WhatsApp / free month). Performance: self-hosted subsetted fonts,
  lazy images, throttled canvas animation that pauses while scrolling.

**Admin panel** (`/farvam/admin/`)
| Tab | Purpose |
|---|---|
| درخواست‌ها (Requests) | Leads table + CSV (Excel) export |
| متن‌ها و لینک‌ها (Texts & links) | Every text, phone, WhatsApp, links, social IDs, offer, SEO, analytics & conversion code, pricing, about, testimonials, referral, guarantees, eNamad |
| مقاله‌ها (Articles) | Create / edit / delete / draft articles |
| جای تصاویر (Image placement) | Choose which screenshot appears in each of ~41 slots |
| تصاویر (Images) | Upload a new image or replace an existing one |
| ویدیو و فایل (Media) | Upload MP4 / photos / PDF / new PPTX |
| ارائه معرفی (Presentation) | Edit slide texts of the guided presentation |
| فونت تیترها (Heading font) | Upload a licensed custom Nastaliq/Shekasteh font |
| موتور توضیحات (Pitch engine) | Edit pitch-engine sentences and tones (JSON) |
| ویرایش پیشرفته (Advanced) | Raw JSON editor for content (auto backup before save) |
| امنیت (Security) | Change admin password |

## 5. Deployment steps (cPanel / DirectAdmin, Apache or LiteSpeed)

1. **Back up** anything already at `public_html/farvam/` (if this is an update, see section 8).
2. Upload `farvam-site.zip` to `public_html/` and **Extract** → creates `public_html/farvam/`.
   Target URL: `https://farvamcertification.ir/farvam/`.
3. Permissions: folders `755`, files `644`. These folders must be **writable by PHP**
   (use `775` if `755` doesn't allow saving): `farvam/data`, `farvam/images`, `farvam/files`,
   `farvam/assets/fonts`.
4. Copy `farvam/robots.txt` to the domain root as `public_html/robots.txt`
   (merge with an existing one if present; keep the `Sitemap:` line).
5. If the site will live somewhere other than `/farvam/`, edit `config.php`:
   `SITE_URL` (no trailing slash) and `BASE_PATH` (leading + trailing slash; `'/'` for domain root).
6. Make sure HTTPS is active for the domain (session cookies are `Secure` on HTTPS).
7. Log in at `/farvam/admin/` with the initial password (section 9) and **change it** in «امنیت».
8. Submit `https://farvamcertification.ir/farvam/sitemap.xml` in Google Search Console
   (paste the verification code in admin → «سئو و متا» → «کد تأیید Google Search Console»).

### Docker + Caddy (the owner's actual server: Ubuntu 24.04, Docker, Caddy on 80/443)

Everything is in `deploy/`. The site runs in its own container **`panel-market`**
(php:8.3-apache + SQLite) bound to `127.0.0.1:8430`, in `/opt/panel-market`, with its own
network `panel-market-net`. It is fully separate from the owner's product container
**`farvam`** (`127.0.0.1:8420`), which must never be touched.

- **Storage.** With env `FARVAM_DB` set (the image does this), `lib.php` stores every dataset
  (content, articles, presentation, engine, leads, admin password hash, rate limits) in SQLite
  (`/var/lib/panel-market/panel-market.sqlite`, volume `panel-market-db`). The last 30 versions
  of content/articles/presentation/engine are kept and can be restored in admin → «ویرایش پیشرفته».
  Shipped `data/*.json` are defaults until the first save. Without `FARVAM_DB` (shared hosting)
  the JSON files are used as before.
- **Media** in volumes `panel-market-images`, `panel-market-files`, `panel-market-fonts`.
  The entrypoint adds shipped files that are missing (cp -n) and never overwrites uploads.
- **Package on the owner's PC:** `panel-market-deploy.zip` + `deploy-panel-market.ps1` +
  `deploy-panel-market.bat` in one folder → double-click the `.bat` → `scp` upload, then
  `server-install.sh` over `ssh` (password prompted by Windows OpenSSH; never sent to Claude).
- **`server-install.sh`** (idempotent):
  1. Back up the current state.
  2. Build.
  3. One-time migration from the first installer's `farvam-site` container: JSON is imported
     into SQLite and media volumes are copied; the old volumes are kept.
  4. `docker compose up -d` and check that the DB is OK.
  5. Insert `import /etc/caddy/panel-market.caddy` into the `farvamcertification.ir` site block
     (with a backup, `caddy validate`, and auto-restore on failure), then reload Caddy.
  6. Install a daily backup cron at 03:30 and smoke-test the URLs.
- **Backups:** `bash /opt/panel-market/backup.sh` → `/opt/panel-market/backups/*.tar.gz`
  (DB snapshot via `VACUUM INTO` + media, newest 7 kept). Restore:
  `bash /opt/panel-market/restore.sh <file>`. Never run `docker compose down -v`.
- **Code updates without the installer:** admin → «به‌روزرسانی» downloads the latest
  `site/` from GitHub (`farvam/farvam-bot`, branch set in `site/updater.php`), lints it,
  keeps the previous code for one-click rollback and never touches DB/media. The applied code is
  kept in the DB volume and re-applied by the entrypoint unless a newer image is installed.
- Logs: `docker logs -f panel-market`. Remove the route: delete the `import` line from the
  Caddyfile and `systemctl reload caddy`.

### Nginx (only if not Apache/LiteSpeed)
```nginx
location ^~ /farvam/data/      { deny all; }
location ^~ /farvam/partials/  { deny all; }
location ~ ^/farvam/(config|lib|build-static)\.php$ { deny all; }
location ~ ^/farvam/(files|images)/.*\.(php|phtml|phar)$ { deny all; }
location = /farvam/sitemap.xml { rewrite ^ /farvam/sitemap.php last; }
location ~ ^/farvam/blog/?$    { rewrite ^ /farvam/blog/index.php last; }
location ~ ^/farvam/blog/([a-z0-9-]+)/?$ { rewrite ^/farvam/blog/(.*)$ /farvam/blog/article.php?slug=$1 last; }
location ~ ^/farvam/for/([a-z]+)/?$      { rewrite ^/farvam/for/(.*)$ /farvam/for.php?role=$1 last; }
```
If clean URLs can't be configured, set `PRETTY_URLS` to `false` in `config.php`.

## 6. Post-deploy checklist (all should pass)

| Check | Expected |
|---|---|
| `GET /farvam/` | 200, Persian page with gold 3D logo |
| `GET /farvam/for/bonak` | 200 trade page |
| `GET /farvam/blog/gold-accounting-guide` | 200 article |
| `GET /farvam/present.php` | 200 presentation; arrows/swipe work |
| `GET /farvam/sitemap.xml` | 200 XML listing ~19 URLs |
| `GET /farvam/data/content.json` | **403** (must be blocked) |
| `GET /farvam/config.php` | **403** |
| `GET /farvam/files/farvam-presentation.pptx` | 200 download |
| Submit the form on the home page | green "request saved" message; row appears in admin → درخواست‌ها |
| Admin → متن‌ها و لینک‌ها → change a text → save | change visible on the site |

## 7. Security notes

- Admin password is stored only as a bcrypt hash (`config.php` initially, then `data/admin.json`
  after the owner changes it). 5 failed logins → 15-minute lock per IP. CSRF tokens on all forms.
- `data/`, `partials/`, `config.php`, `lib.php` are blocked by `.htaccess`; script execution is
  denied in `files/` and `images/`.
- `data/leads.json` contains customer names and phone numbers — never expose or commit it.
- Admin-editable HTML fields (`*_html`, analytics/conversion code, eNamad) are trusted owner input.

## 8. Updating later (do NOT lose the owner's edits)

The owner's live data is in `data/*.json`, `images/`, `files/`, `assets/fonts/custom-display.*`.
When deploying a newer package, overwrite **code only** (`*.php`, `partials/`, `assets/style.css`,
`assets/app.js`, `admin/`, `blog/`) and **keep** `data/`, `images/`, `files/` from the server
(merge new keys into `data/content.json` if a release adds them).

## 9. Credentials

- Admin URL: `https://farvamcertification.ir/farvam/admin/`
- Initial password: given to the owner privately (never write it in this public repo); it must be
  changed in «امنیت» after the first login. The new password is stored only as a bcrypt hash in the database.

## 10. Content the owner still needs to supply (hidden on the site until filled in admin)

Instagram ID, customer testimonials, pricing/plans, founder name/photo/story, offer deadline,
intro videos (Aparat/YouTube/MP4), eNamad code, Search Console code, analytics/conversion code,
a screenshot of the trading desk. Two defaults are commitments the owner must confirm or change:
referral reward ("one extra free month for both") and callback promise ("first business hour").

---

## یادداشت فارسی برای صاحب سایت

این بسته «سایت تبلیغاتی و فروش» فَروَم است، نه خود نرم‌افزار. بعد از نصب:
۱) در پنل مدیریت رمز را عوض کنید؛ ۲) آیدی اینستاگرام، ویدیوها، نظر مشتری‌ها و قیمت‌ها را وارد کنید؛
۳) در «جای تصاویر» مطمئن شوید هر تصویر در جای درست است؛ ۴) هر روز بخش «درخواست‌ها» را ببینید و طبق برنامه پیگیری کیت تبلیغات تماس بگیرید.
