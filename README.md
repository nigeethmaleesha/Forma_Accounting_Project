# Forma Advisory — Vercel static client preview (recommended for review)

This is a **pre-rendered version of the standalone PHP site**, built from the genuine PHP page output: the same design, navigation, services, articles, newsletter editions, images, animations, and responsive CSS. There are no PHP files in this ZIP, so Vercel will not download PHP instead of showing the website.

## Deploy
1. Upload the **contents of this folder** as a new GitHub repo. You must have `index.html`, `assets/`, `about/`, and `vercel.json` at the repo root.
2. Vercel > Add New > Project > Import this repo.
3. Framework Preset = **Other**. Root Directory = repository root `./`. Output Directory = default / `.`, no build command.
4. Deploy and visit the Vercel URL. Test Home, About, Services, Newsletter, Insights, Contact, CSS and photos.
5. Do not use the original PHP repository for a static deployment. It includes `index.php`, which Vercel otherwise serves as a downloadable file without a PHP runtime.

## Important limitations
- All 19 public routes are included, plus 404 and assets.
- On this **static preview**, the contact form checks browser fields and displays a clear preview-only note, but **does not send or store messages**. Do not imply to the client that email delivery is enabled.
- If you need server-side PHP enquiries for the preview, use the separate `PHP_Runtime` package with the community runtime and approved email provider setup.
- Article/newsletter edits in the final PHP website are done in `app/content.php`. To update the static preview, regenerate it from the PHP version.
- Final production PHP hosting should use the original `Forma_Accounting_Standalone_PHP_Complete_Project.zip`, not this static preview package.
- Branding, client contact details, images and privacy copy remain pending the client's approval.
