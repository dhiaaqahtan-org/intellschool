# Modules/Saas — Marketing website

The public SaaS product website for the school ERP, built as the `Modules/Saas`
marketing surface described in `SAAS_MULTITENANT_NWIDART_IMPLEMENTATION_PLAN.md`
§4.1 and §10: **server-rendered Blade + Vue 3 interactive islands, no Livewire,
module-local Vite build.**

---

## Look at it right now

Open this file in a browser — no build step, no server, no dependencies:

```
Modules/Saas/preview/index.html
```

It is the same markup the Blade views produce, loading the same stylesheet and
the same enhancement script from `resources/assets/`. Use it for design review;
use the Blade views for anything that ships.

---

## What was built

| Area | Status |
|---|---|
| Design system (`resources/assets/css/marketing.css`) | Complete — tokens, components, dark hero + light body, RTL via logical properties |
| Enhancement layer (`resources/assets/js/marketing/enhance.js`) | Complete — scroll reveal, 3D pointer parallax, hero canvas, counters, sticky header, WAI-ARIA tabs |
| Home page (`resources/views/marketing/home.blade.php`) | Complete — 11 sections |
| Demo page + endpoint | Complete — works with and without JavaScript |
| Vue islands (`RoleExplorer.vue`, `DemoForm.vue`) | Complete |
| English + Arabic copy | Complete (Arabic is a **first draft** — see below) |
| Host isolation (route domain constraint + `RequireLandlordHost`) | Complete |
| Legal pages | Routed placeholders only, `noindex` |
| Rest of the plan's sitemap (§10.3) | **Not built** — see "Not built yet" |

### The 3D / animation approach

The product visuals are **live HTML recreations of the application UI**, not
exported screenshots, floated in a CSS `perspective` scene:

- crisp at any zoom and on any DPI, unlike a PNG;
- a few kilobytes instead of a few hundred;
- reflow into a readable stack on narrow screens instead of shrinking to
  illegibility;
- animate (bar charts grow, attendance cells stagger in, donut fills) without
  video or a canvas library;
- contain **no real student data**, because there is none to leak.

They were modelled on the actual application: Vue 3 + Tailwind (`tw-` prefix) +
Inter, and the real domain vocabulary from the permission matrix — fee heads,
receipts, batches, divisions, ledgers, gate passes.

Deliberately **not** used: Three.js or WebGL. A 3D library in the hero would
blow the LCP ≤ 2.5s target in plan §10.5 for decoration. The depth comes from
CSS 3D transforms, and the hero backdrop is a ~60-node 2D canvas that pauses
itself when scrolled out of view or when the tab is hidden.

Every motion effect is gated on a single `--mo` custom property, which
`prefers-reduced-motion: reduce` sets to `0`, plus explicit `animation: none`
overrides. With JavaScript disabled the `.no-js` rules reveal everything
immediately — no content is ever trapped behind an observer that never fires.

### Positioning vs the four reference sites

Kinderpedia, eSkooly, OurSchoolSoftware and SchoolSMS all lead with the same
two claims: a long feature list, and social proof (school counts, logos,
ratings). We can't match the second — there are no customers yet, and inventing
them is not an option. So the site leads with the two things that are
*verifiable today*:

1. **Depth, quantified.** 34 modules with their real endpoint counts, 17 roles
   with their real permission counts. Competitors say "all-in-one"; this shows
   the number and lets a buyer check it.
2. **Isolation as the differentiator.** None of the four says anything about how
   one school's data is separated from another's — because they pool it. A
   database per tenant is a genuine architectural advantage and it gets a full
   section with a diagram.

---

## Install

```bash
composer require nwidart/laravel-modules:^12.0
```

Add to the **root** `composer.json`, preserving existing `allow-plugins` entries:

```json
{
  "extra": { "merge-plugin": { "include": ["Modules/*/composer.json"] } },
  "config": { "allow-plugins": { "wikimedia/composer-merge-plugin": true } }
}
```

Then:

```bash
composer dump-autoload
php artisan module:enable Saas
```

Build the module assets:

```bash
cd Modules/Saas
npm install
npm run build
```

Output goes to `public/build-saas/`, kept separate from the core application's
existing `public/build/` manifest.

### Environment

```dotenv
SAAS_BRAND_NAME="Your Product"
SAAS_MARKETING_HOST=www.yourproduct.com
SAAS_PLATFORM_HOST=app.yourproduct.com
SAAS_TENANT_SUFFIX=.yourproduct.com
SAAS_LEGAL_NAME="Your Company Ltd"
SAAS_COMPANY_REG="..."
SAAS_COMPANY_ADDRESS="..."
SAAS_CONTACT_EMAIL=hello@yourproduct.com
```

### Verify route ordering before you trust it

The core `App\Providers\RouteServiceProvider` registers `routes/site.php`, which
also claims `/` — that is the tenant's own public school website. The marketing
routes carry a domain constraint so they only match the marketing host, but they
must still be registered **first**. nwidart's provider is package-discovered and
boots before app providers in a default Laravel 12 install, so this normally
works out. Confirm it rather than assuming:

```bash
php artisan route:list --path=/
```

The domain-constrained marketing route must appear above the site route.
`RequireLandlordHost` is the second control if ordering ever regresses.

---

## Before this goes public

These are blocking, not nice-to-have.

### Legal and provenance

- [ ] **Licensing.** The source directory is named `... v5.5.0 Nulled`. Publishing
      a commercial SaaS on an unlicensed copy is a blocker (plan §19, Phase 0).
      Resolve this before any of the rest matters.
- [ ] Terms, privacy notice, DPA and subprocessor list drafted by counsel.
      `resources/views/marketing/legal/stub.blade.php` is a routing placeholder
      carrying `noindex` and is **not** an operative agreement.
- [ ] Company legal name, registration number and registered address in
      `config/saas.php` — several jurisdictions require these in the footer.

### Brand and content

- [ ] `SchoolOS` is a placeholder. Pick a real name, check the trademark, set
      `SAAS_BRAND_NAME`.
- [ ] Have the Arabic copy reviewed by a native speaker who knows school
      administration terminology. `resources/lang/ar/marketing.php` was drafted
      alongside the English and carries a warning header. Finance and academic
      vocabulary varies by country.
- [ ] Add a real Open Graph image (1200×630).

### Claim gates

`config/saas.php` has a `claims` block. Every flag is `false` and each one hides
a specific claim until its evidence exists:

| Flag | Unlocks | Needs |
|---|---|---|
| `publish_pricing` | Plan cards | Commercially approved prices; read them from `saas_plans`, never hard-code |
| `publish_customers` | Logos, testimonials | Signed customer references |
| `publish_uptime` | Availability figures | Monitoring history and a published SLA |
| `publish_certifications` | Compliance badges | Completed external assessment |
| `publish_mobile_ga` | Store links, offline guarantees | Green `flutter analyze`/test/build plus the two-tenant device test |

Do not flip a flag to make a section look fuller.

### Re-verify the numbers each release

The hero facts and module/role counts come from `config/saas.php` and
`PageController`, sourced from `docs/instikit-modules-endpoints.md` and
`docs/instikit-role-capabilities.md` (verified 2026-07-26). Regenerate those
docs and update the config after any release that changes routes or
permissions — a stale "1,701 endpoints" is a false advertising claim, not a
cosmetic drift.

### Testing not yet done

- [ ] Real-browser check at 375px. The preview pane used during development
      could not go below ~580px; everything from 582px up was verified and no
      horizontal overflow was found at 1440 / 1180 / 900 / 582, LTR and RTL.
- [ ] Screen reader pass (NVDA or VoiceOver) on the tabs and the demo form.
- [ ] Lighthouse / field Core Web Vitals against the plan's LCP ≤ 2.5s,
      INP ≤ 200ms, CLS ≤ 0.1 targets.
- [ ] `npm run build` has not been run — Node was not available in this
      environment. The Vite config and `package.json` are written but unproven.
- [ ] Vitest specs for the two Vue islands (`tests/Js/`).
- [ ] Feature test asserting marketing routes 404 on a tenant host.

---

## Not built yet

From the plan's recommended sitemap (§10.3), still to do in Phase 7: per-role
pages, per-module pages, a multi-campus page, a dedicated security page,
resources/help centre, company/contact, and the signup + provisioning-progress
flow. Routes for these were deliberately **not** registered — a route pointing
at an empty view is worse than a missing route. Navigation currently targets
sections of the home page.

The demo endpoint logs that a request arrived and nothing else. It is not wired
to a mailer, CRM or database on purpose: storing personal data before a
retention policy exists would be the wrong order of operations. See the `TODO`
in `DemoRequestController`.

---

## Layout

```
Modules/Saas/
├── app/
│   ├── Http/
│   │   ├── Controllers/Marketing/{PageController,DemoRequestController}.php
│   │   ├── Middleware/RequireLandlordHost.php
│   │   └── Requests/StoreDemoRequest.php
│   └── Providers/{SaasServiceProvider,RouteServiceProvider}.php
├── config/saas.php              # brand, hosts, verified facts, claim gates
├── preview/index.html           # standalone, no build required
├── resources/
│   ├── assets/
│   │   ├── css/marketing.css    # the whole design system
│   │   └── js/marketing/
│   │       ├── enhance.js       # framework-free progressive enhancement
│   │       ├── mount.js         # island registry
│   │       ├── app.js           # Vite entry
│   │       └── components/{RoleExplorer,DemoForm}.vue
│   ├── lang/{en,ar}/marketing.php
│   └── views/marketing/
│       ├── layouts/app.blade.php
│       ├── partials/            # header, footer, icons, 3 product stages, plans
│       ├── home.blade.php
│       ├── demo.blade.php
│       └── legal/stub.blade.php
├── routes/marketing.php
├── module.json · composer.json · package.json · vite.config.js
```

### Design tokens

Generated with the `ui-ux-pro-max` skill (pattern *Enterprise Gateway*, style
*Soft UI Evolution*): primary `#2563eb`, accent `#ea580c`, Plus Jakarta Sans for
Latin and IBM Plex Sans Arabic for Arabic. All of it lives in the `:root` block
at the top of `marketing.css` — rebrand by editing that block, not by grepping
for hex values.

### Adding an island

1. Drop the SFC in `resources/assets/js/marketing/components/`.
2. Register it in `mount.js`.
3. In Blade, wrap **working server-rendered markup** in
   `<div data-vue-component="your-name">` with a
   `<script type="application/json" data-props>` child.

The fallback markup is not optional. If the chunk fails to load, that markup is
what the visitor gets — so it has to work on its own.
