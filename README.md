# Trendy Tax Solutions — Custom PHP Website + Vercel Design Preview

A distinct, modern accounting website inspired by the five client-supplied design videos. Includes reusable client assets, responsive site, blog editorial management, protected client document portal, email enquiry handling and SEO fundamentals.

## Folder Structure

```text
trendy-tax-solutions-project/
├── index.php                  # Production PHP HTML routing
├── api.php                    # Production forms, auth, blog, docs API
├── assets/
│   ├── css/style.css          # Responsive brand system and animations
│   ├── js/app.js              # Navigation, forms and dashboard UI
│   └── img/                   # Optimized client-provided brand assets
├── includes/
│   ├── content.php            # Central firm content, services and SEO copy
│   └── site.php               # Shared PHP template for live + static preview
├── server/
│   ├── bootstrap.php          # Environment, sessions and MySQL
│   └── schema.sql             # MySQL tables for production
├── tools/
│   ├── build-preview.php      # Rebuilds static Vercel preview
│   └── create_admin.php       # Initial admin setup from CLI
├── preview/                   # Vercel-ready static site — no PHP or secrets
├── .htaccess                 # Apache SSL rewrite and access restrictions
├── .env.example              # Environment variables template
├── robots.txt / sitemap.php  # SEO directives and production sitemap
└── vercel.json               # Vercel serves /preview as static output
```

## Preview On Your Computer

```bash
php tools/build-preview.php
cd preview
python3 -m http.server 8000
```

Open **http://localhost:8000**. For local PHP page routing (without a configured database), use `php -S 127.0.0.1:8001 tools/dev-router.php` from the project root; production requires Apache rewriting. Any static hosting or Vercel preview will present the same design, including the sample blog layouts. Portal, submissions and publishing are intentionally **not functional on Vercel**. The preview clearly labels this rather than silently claiming a form was sent. Google indexing is disabled for this temporary approval preview.

## Publish The Vercel Preview

1. Upload this folder to a Git repository or use Vercel CLI, with **project root at this folder**.
2. Vercel Framework Preset: `Other`. No framework install required.
3. `vercel.json` tells Vercel to publish the pre-generated `preview/` directory.
4. Deploy, verify every page, then send the deployment URL to the client.
5. After edits to PHP templates, CSS, JS, services or images, run `php tools/build-preview.php` again **and commit the regenerated `preview/` files** before redeploying.

**Do not expose the PHP database or actual client files in the static preview.** If a preview deployment has public access, share only approved imagery, awards, copy and content.

## PHP Hosting Production Setup (Apache / cPanel)

Requirements: PHP **8.1+**, `pdo_mysql`, `fileinfo`, `openssl`, preferably `mbstring`, MySQL/MariaDB, HTTPS, URL rewriting (`mod_rewrite`), working PHP email (`mail()` or SMTP adapter), and a writable documents directory **outside the website document root**.

1. Create a new MySQL database/user in cPanel, with access limited to this database.
2. Import **server/schema.sql** using phpMyAdmin.
3. Copy `.env.example` to `.env`, then update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `SITE_URL`, `MAIL_FROM` and `PRIVATE_STORAGE_PATH`.
4. Set `PRIVATE_STORAGE_PATH` to an absolute location **outside `public_html`**. Make it private and writable by PHP. The API refuses storage within the document root.
5. Deploy project files into the PHP site directory. Keep `.env`, source-only folders and storage inaccessible; `.htaccess` denies access to internal paths. Configure the same restrictions at web server level if you use nginx rather than Apache.
6. Force HTTPS, configure domain SSL and correct email SPF/DKIM. Verify that `.env` and `server/` return `403/404` over HTTP.
7. Create first admin via SSH/terminal, from project directory:

   ```bash
   php tools/create_admin.php admin@example.com "Administrator Name" "A Very Long Unique Password"
   ```

   **Do not paste real admin passwords into shared chats or commit them to Git.**
8. Visit `/admin/` to sign in, create client accounts, publish posts, review enquiries and upload documents. Client accounts sign in at `/portal/`.
9. Configure Cloudflare Turnstile keys in `.env` for public contact spam protection. Enable hosting backups for MySQL **and external document storage**. Implement a tested recovery plan.
10. Verify end-to-end login, upload/download authorization, content publishing, newsletter email confirmation, enquiries, password policy, browser sessions and email deliverability on the real host. None of this can be fully tested in a static preview.

### Security Notes

- MySQL prepared statements, server-side authorization checks, password hashing, session regeneration, CSRF checks, basic login throttling, safe rendering, allowed document MIME types and private external uploads are implemented.
- **This is a pre-production implementation, not an independent security audit.** Before accepting sensitive Australian financial information, complete a professional security/privacy review, penetration testing, log review, backups, user access policy, retention/deletion rules and breach response planning.
- To reduce exposure, the public contact form must **not** be used to send TFNs, banking documents or identity documents. Clients use their authenticated portal.
- Enquiry notifications call PHP `mail()`; hosting and sender-domain configuration are required. Consider a tested SMTP transport for reliable delivery.
- The newsletter uses email verification. Ensure consent records, unsubscribe mechanics and an approved marketing/email retention policy are implemented **before sending campaigns**. A sending/campaign tool is not provided here.
- Draft articles are not publicly visible. New admin posts become indexable only when published.
- A custom client's password recovery workflow (such as secure password reset by email) is **not included yet**; user access must be administered until this is added.
- Personal data privacy statement is a **draft placeholder**, not a final legal/privacy policy.
- Optional AI chatbot is **not installed**. No unapproved provider will be sent client data.

## Client Items To Confirm Before Public Launch

- Company service descriptions, business phone/address/email, official team names and biographies.
- Verify all member association / partner badge usage rights and award claims from provided assets. **Do not publish any badge without confirming current authorization and status** (the supplied CA ANZ brand guidelines especially require permission).
- Replace `/about/` gallery placeholders with team/event photos; the reusable gallery HTML is in `includes/site.php` with the `.gallery-grid` class.
- Replace editorial preview posts with real client-approved posts through the `/admin/` CMS; static sample posts are **not served by the live PHP site**.
- Supply and approve the final Australian Privacy Act 1988 policy and contact form disclosure.
- Provide content and intended URL for the separately mentioned **unindexed/private link**. This is intentionally not invented or exposed.
- Confirm whether additional customer self-registration, password reset, user lifecycle changes, deletion/retention, secure MFA, chatbot and client document *uploads* are required.

## SEO / Discoverability

Semantic headings, `en-AU`, descriptive page titles, descriptions, canonical links, Open Graph tags, internal links, SEO slugs, CMS SEO fields, JSON-LD for accounting service/blog, live sitemap, production robots rules, responsive layout, alt text and noindex rules for private pages are included. The admin stores optional concise summaries for AI/search discovery. Google results and AEO/GEO visibility are **not guaranteed**. The public sitemap excludes drafts and sample preview posts.

## Source Materials

Design scope comes from `Accounting Firm – Australia- dev (1).pdf`; branding / member and award assets from `Archive.zip`; motion and composition references from the five supplied MP4 videos. The former Trendy Tax Solutions site is used for business-specific service/contact details, not copied as a design.
