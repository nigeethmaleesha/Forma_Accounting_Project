# Trendy Tax Solutions — Design Review Notes

## Creative Direction

A bright, expressive, people-first website concept that balances professional trust with a fresh visual identity. Unlike the existing website, the redesigned site uses large editorial headlines, animated colour composition, modern cards, interactive navigation and a memorable services experience.

## Ready To Review In The Vercel Preview

- Home: Introduction, services, calls to action, team introduction, recognition, insights and newsletter design.
- About: Background, approach, mission, vision, values, team section and a four-tile gallery ready for your own images.
- Services: Nine service categories based on the provided reference content and service enquiry links.
- Contact: Contact information, enquiry form design and Hobart map link.
- Insights: Editorial layout and illustrative placeholders for articles.
- Portal: Secure client sign-in and client dashboard interface design.
- Admin: Blog SEO fields, article editor, client management, document upload and enquiry management interface design.

The Vercel link is for **visual approval**. Form submissions, portal access and admin operations are only enabled after the PHP production server, database, email delivery and external private storage are configured and tested.

## Needed Before Going Live

1. Approved service descriptions, phone/email/address, team biographies and photographs.
2. Gallery photographs, and confirmation that the supplied award and association logos may be publicly displayed.
3. Approved Australian privacy policy and data retention/security practices.
4. Approved articles and subscription terms (the example articles are preview-only).
5. Exact destination/content of the separate unindexed link.
6. PHP/MySQL hosting credentials, secure off-webroot file storage, backup plan, working email transport and spam-verification keys.
7. Final QA on the production environment including document access controls, browser/device checks and vulnerability assessment.

## Project Approach

The page templates and content are shared across environments. After changes are approved, the static preview is regenerated from the same PHP files, while live functionality remains on the secure PHP host.
