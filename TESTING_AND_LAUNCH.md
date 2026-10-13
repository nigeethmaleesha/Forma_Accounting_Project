# Verification and launch checklist

## Tested locally for this package

- [x] PHP 8.4 CLI syntax validation for all `.php` files
- [x] PHP development server successfully returns the public pages
- [x] Shared navigation and internal URL routing works at HTTP level
- [x] Sample articles, service detail pages and newsletter editions use PHP data
- [x] 404 response for unknown page and preview robots blocking
- [x] Form: CSRF token issued; malformed email rejected (422); invalid token rejected (419)
- [x] Form: valid test enquiry accepted and written to private log **only in local `log` mode**; test log removed
- [x] Local photos and static CSS/JS bundled in `public/assets/`
- [x] Default mode suppresses search indexing and leaves email sending disabled until configured

## Before approving or going live (client-dependent; NOT completed)

- [ ] Approve real trading/legal company name, logo, exact brand colours, Australian street/city and real contact details
- [ ] Replace concept photographs with the client's authorised photos, where appropriate
- [ ] Approve team bios, professional memberships/credentials and service authorisations (including tax/superannuation)
- [ ] Review all copy and newsletter/article content; remove development disclaimers and placeholder notices as appropriate
- [ ] Approve legally appropriate privacy policy, data retention, provider transfers and complaint-handling details
- [ ] Set live email credentials / sending domain; verify SPF/DKIM and test actual delivery
- [ ] Provide production domain, HTTPS, PHP runtime and rewrite support
- [ ] Check 320px/375px/768px/1024px/1440px widths in Chrome, Safari, Firefox on actual devices
- [ ] Verify keyboard navigation, screen readers, form feedback, contrast and reduced-motion behaviour
- [ ] Check contact form spam protection against real traffic; optionally enable hosted CAPTCHA/WAF
- [ ] Ensure production SITE_MODE, SITE_URL, generated sitemap, meta descriptions and consent disclosures are correct
- [ ] Configure backups, monitoring, server patches and restore testing

## Deferred by agreement

- Newsletter/blog content administration (currently code-edited)
- Customer login, profile and confidential document portal (separate system, no public navigation link)
- Optional chatbot

**Visual QA note:** Automated headless screenshot execution was blocked in the sandbox. Browser rendering on the client's target devices remains to be verified. Do not regard PHP syntax or link checks as full browser acceptance testing.
