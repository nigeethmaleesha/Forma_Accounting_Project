# Forma Advisory — Standalone PHP accounting website

**Architecture:** Native PHP 8.1+ + HTML/CSS/vanilla JavaScript. **No WordPress, no Node.js, no build command, no database, no Vercel requirement.** Designed for a conventional PHP host such as Apache/cPanel with `mod_rewrite` and HTTPS.

**Important:** “Forma Advisory” is a *concept placeholder* rather than a verified client business. The colours are proposed, not approved by the Australian client. All local photographs are synthetic concept images. Service scope, firm credentials and legal statements need approval before public release.

## Quick local run (Windows / VS Code)

1. Install PHP 8.1+ and make sure `php -v` works in the terminal.
2. Open this project folder (`Forma_Advisory_Standalone_PHP`).
3. Run:

```powershell
php -S 127.0.0.1:8000 -t public public/router.php
```

4. Open **http://127.0.0.1:8000/**. To stop, press **Ctrl+C**.

If PHP is not in PATH, install PHP or use the PHP executable provided by XAMPP, e.g.:

```powershell
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public public/router.php
```

**Do not open `.php` files via VS Code Live Server.** A PHP runtime is needed to render them.

## Project structure

```text
Forma_Advisory_Standalone_PHP/
  app/
    bootstrap.php              Central configuration, security, helpers
    content.php                EDIT NEWSLETTER / BLOG / SERVICE CONTENT HERE
    layout.php                 SEO head + shared layout
    views/                     Home, About, Services, Contact, articles, etc.
    partials/                  Shared navigation, footer, cards, CTA
  public/                      WEB DOCUMENT ROOT (only expose this folder)
    index.php                  Clean URL router and page controller
    api/inquiry.php            PHP enquiry endpoint
    assets/                    Local images, CSS, JS
    .htaccess                  Apache URL rewriting
    router.php                 PHP built-in server router for local QA
    robots.php, sitemap.php    Preview/production SEO controls
  storage/                     PRIVATE: rate limits, temporary preview enquiry log
  config.local.php.example     Example private hosting configuration
  README.md
  TESTING_AND_LAUNCH.md
```

## Included pages and content

- Home, About (including proposed mission, vision, values and team placeholder)
- Services index + six detail pages
- Insights/blogs index + three article detail pages
- Newsletter listing + three manually entered editions
- Contact enquiry + privacy notice **draft**
- Designed 404 page

19 public page routes in all. New newsletter issues, services and article records are added to `app/content.php`, with listings and detail pages generated from the same data. There is **no admin dashboard yet** (as requested). The deferred customer login/profile/document portal remains separate and **is not linked** to the public site. Chatbot is optional and not included.

## Editing the concept website

- **Company brand details:** `config.local.php` controls the page title brand name, but replace the concept logo text in `app/partials/header.php` and `footer.php` and the favicon SVG to match actual branding.
- **Service, newsletter and insights:** `app/content.php` (single source for listings and individual pages).
- **Homepage and other page sections:** `app/views/*.php`.
- **CSS / animations / responsive styling:** `public/assets/css/style.css`, `public/assets/js/main.js`.
- **Media:** `public/assets/images` (synthetic concept assets — replace/approve as needed).

## Real enquiry email setup

By default, email delivery is intentionally **disabled** until a genuine recipient and sending domain are configured; the website never reports a message as sent when delivery is unavailable.

1. Copy `config.local.php.example` to `config.local.php` **outside `public/`**.
2. Add a valid `CONTACT_TO_EMAIL` and a verified `CONTACT_FROM_EMAIL`.
3. Select `MAIL_MODE = 'mail'` if your PHP host provides a correctly configured mail transport. `mail()` success means the host accepted the message, not guaranteed inbox delivery.
4. Alternatively, use `MAIL_MODE = 'resend'` and enter `RESEND_API_KEY` on a host with the PHP cURL extension. Configure DNS/domain verification at the email provider.
5. Locally only, use `MAIL_MODE = 'log'` while `SITE_MODE = 'preview'` to validate form submissions **without sending email**. Test enquiries are written privately to `storage/preview-inquiries.log`; delete them after QA.
6. Submit a real test, check mailbox/spam and the sender domain configuration before launching the contact form.

The enquiry endpoint checks CSRF, same-origin requests, an invisible honeypot field, required fields, consent, character limits and basic per-IP rate limiting (3 submissions per 15 minutes by default). It collects no TFNs, passwords or sensitive uploads; never collect private financial documents through this public form. Further WAF/CAPTCHA protection can be added for higher-risk traffic. The private `storage/` folder must be writable by PHP and inaccessible via the web.

## Deploy to PHP hosting (Apache/cPanel)

**Preferred hosting arrangement (secure):** set the domain's document root to the project's `public/` directory (e.g. `/home/account/project/public`). Upload `app/`, `storage/` and `config.local.php` **one level above the web root**. Upload the files *inside* `public/` to the document root if your host uses a separate `public_html` folder. Ensure the folder structure still lets `public/index.php` resolve `../app/bootstrap.php`.

Example shared hosting layout:

```text
/home/account/
  app/                  <- outside public_html
  storage/              <- outside public_html, PHP writable
  config.local.php      <- outside public_html
  public_html/           <- document root, contents of public/
    index.php
    api/inquiry.php
    assets/
    .htaccess
    robots.php
    sitemap.php
    router.php
```

If your host cannot place private PHP files **outside** the public document root, do **not** deploy as-is: ask the host about changing the document root, or request a revised layout with server-specific access rules before going live.

**Host prerequisites:** PHP 8.1+, HTTPS, Apache `mod_rewrite` (or equivalent Nginx `try_files` configuration), writable private storage, `filter`/`session`/`json` PHP extensions, and working email transport (or configured Resend + cURL). This package does not require MySQL.

When brand/legal/site content and email are approved:

- Set `SITE_URL` to the production HTTPS domain (without trailing slash).
- Change `SITE_MODE` from `preview` to `production`. This enables canonical URLs, indexable pages, `robots.txt` and `sitemap.xml`. **Do not change this for a pre-approval preview.**
- Keep `config.local.php` secret and excluded from source control.
- Configure backups, web application firewall, monitoring, SSL renewal and PHP updates at your host. This implementation does not configure hosting-managed maintenance jobs.

## Preview versus production

The website defaults to `noindex,nofollow` and `robots.txt` disallow. **Noindex is not access control** — use password protection on the preview domain if client confidentiality is important. Privacy page is a draft requiring approval with reference to the Australian Privacy Act 1988 and the actual firm's handling of data.

Read **TESTING_AND_LAUNCH.md** before sharing the live site.
