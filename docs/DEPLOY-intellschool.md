# Deploying to intellschool.com (multi-tenant SaaS)

Companion to `.env.production`. Upload that file to the server as `.env`.
0
## What the host layout means

| URL | Serves | Tenant resolved? |
|---|---|---|
| `intellschool.com` | Marketing / product website | No — control plane |
| `app.intellschool.com` | Platform admin, signup, billing | No — control plane |
| `<school>.intellschool.com` | That school's full ERP | Yes |
| `www.intellschool.com` | Reserved — redirect to apex | No |

The ERP does **not** answer on `intellschool.com`. `RequireTenantHost` returns 404
for every ERP route on a control-plane host, by design — that guard is what stops
an ERP query from running against the control-plane database.

## 1. DNS and TLS

```
A     intellschool.com        -> <server ip>
A     app.intellschool.com    -> <server ip>
A     *.intellschool.com      -> <server ip>     # every school subdomain
CNAME www.intellschool.com    -> intellschool.com
```

The wildcard record is what lets a newly provisioned school resolve without a DNS
change. It needs a **wildcard TLS certificate** (`*.intellschool.com` plus the
apex) — Let's Encrypt issues these only via DNS-01, not HTTP-01.

Point the web server's document root at `public/`. The repo-root `.htaccess`
rewrites into `public/` if you cannot change the docroot, but a real docroot is
preferable — it keeps `.env` outside the served tree.

## 2. Databases and users

Two databases either way: a control plane, and an (empty) template. School data
lands in neither — each school gets its own.

### Shared hosting — hPanel

You cannot run the SQL below; shared hosting grants no `CREATE`. Instead, in
hPanel → Databases, create:

| Purpose | Example name | `.env` key |
|---|---|---|
| Control plane | `u000000000_platform` | `SAAS_LANDLORD_DB_DATABASE` |
| Template (stays empty) | `u000000000_app` | `DB_DATABASE` |
| One per school | `u000000000_tamjeed` | passed as `--database=` |

hPanel forces the `u<accountID>_` prefix on database names **and** usernames, so
copy every name from it — you cannot choose them. Give the MySQL user rights on
all of the above.

### VPS

```sql
CREATE DATABASE intellschool_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE intellschool_app      CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Control plane + template connection
CREATE USER 'intellschool'@'localhost' IDENTIFIED BY '<strong password>';
GRANT ALL PRIVILEGES ON intellschool_platform.* TO 'intellschool'@'localhost';
GRANT ALL PRIVILEGES ON intellschool_app.*      TO 'intellschool'@'localhost';

-- Tenant cluster user: needs CREATE DATABASE, and rights over every tnt_* schema
CREATE USER 'intellschool_tenant'@'localhost' IDENTIFIED BY '<strong password>';
GRANT ALL PRIVILEGES ON `tnt\_%`.* TO 'intellschool_tenant'@'localhost';
GRANT CREATE ON *.* TO 'intellschool_tenant'@'localhost';

FLUSH PRIVILEGES;
```

The template database stays empty. It exists so the template connection has
something valid to point at; school data never lands there.

Put these four values into `.env` — the deploy will not start without them:
`DB_USERNAME`, `DB_PASSWORD`, `SAAS_CLUSTER_DEFAULT_USERNAME`,
`SAAS_CLUSTER_DEFAULT_PASSWORD`. An empty cluster password is refused outright in
production rather than served.

## 3. Install

```bash
composer install --no-dev --optimize-autoloader
php artisan storage:link
chmod -R ug+rw storage bootstrap/cache
```

Do **not** run `php artisan config:cache` until the `.env` is final. Once the
config cache exists Laravel stops reading `.env` altogether, so a later edit
appears to do nothing.

## 4. Migrate the control plane

```bash
php artisan migrate --force --path=Modules/Saas/database/migrations/landlord
```

This creates the 18 `saas_*` tables in `intellschool_platform`. The migrations
bind themselves to the landlord connection, so they land there regardless of the
default connection.

Do not run a bare `php artisan migrate` — it would also build the full ERP schema
into `intellschool_app`, which is never used and only creates confusion about
where school data lives.

## 5. Seed plans and the first operator

```bash
php artisan db:seed --force --class="Modules\Saas\Database\Seeders\PlanSeeder"
php artisan db:seed --force --class="Modules\Saas\Database\Seeders\PlatformUserSeeder"
```

`PlatformUserSeeder` reads `SAAS_PLATFORM_ADMIN_EMAIL` / `_PASSWORD`. Blank the
password line in `.env` once it has run.

## 6. Provision the first school

Normally from the platform panel: **app.intellschool.com → Tenants → New Tenant**.
The form takes the school's name, its address, and — on shared hosting — the
database hPanel created for it:

| Field | VPS | Shared hosting |
|---|---|---|
| School name | required | required |
| Slug | optional, derived from the name | same |
| Hostname + type | optional, derived from the slug | same |
| Database name | **leave blank** — created for you | `u123456789_tamjeed`, from hPanel |
| Database username | leave blank | `u123456789_tamjeed`, from hPanel |
| Database password | leave blank | the password hPanel showed you |

Leave all three database fields blank and the school opens through the shared
cluster user in `.env`. Fill the username and password and that school gets its
own credentials, stored encrypted — which is what hPanel forces, since it issues
one MySQL user per database and the cluster user has no rights on it.

The CLI does the same thing, if you prefer it or are scripting onboarding:

```bash
php artisan saas:provision --name="Tamjeed School" --slug=tamjeed --locale=en --timezone=UTC
```

```bash
php artisan saas:provision --name="Tamjeed School" --slug=tamjeed --database=u123456789_tamjeed
```

Either way the ERP schema is migrated into that database and seeded. Provisioning
opens the database with the credentials it will actually run on before touching
it, so a wrong name or password fails immediately with a message naming the
problem, rather than part-way through the migrator.

If a step fails, the run is resumable rather than half-applied:

```bash
php artisan saas:provision --resume=<run-uuid>
```

## 6b. Putting a school on its own domain (tamjeed.com)

Subdomains we issue are trusted on creation. A school's own domain is not — DNS
is what decides who answers for `tamjeed.com`, and anyone can point DNS at us, so
adding a hostname cannot by itself be a claim to it. The flow:

1. Platform panel → the tenant → **Add Domain**, type **Custom**.
2. The panel then shows the exact TXT record. Give the school all three values.
3. The school adds at their registrar:
   - `TXT` at `_intellschool-verify.tamjeed.com` = the token shown
   - `A` (or `CNAME`) for `tamjeed.com` pointing at this server
4. Press **Check DNS**. On a match the domain is marked verified and starts
   routing immediately — the host→tenant cache is flushed for you.
5. Optionally **Make primary**. This matters beyond cosmetics: every absolute URL
   the school sends out — password resets, invitations, export links — is built
   from the primary hostname. Outgoing links switch over within a few minutes.

Until step 4 passes, `tamjeed.com` returns 404 rather than a login page. That is
the intended behaviour, not a misconfiguration.

**Each custom domain needs its own TLS certificate.** A wildcard for
`*.intellschool.com` does not cover `tamjeed.com`.

- **Shared hosting:** add the domain in hPanel and issue its certificate there.
- **VPS:** automated. Install `Modules/Saas/deploy/tenant-domain-provision` once
  (instructions in the file header), then per school:

  ```bash
  php artisan saas:provision-domain tamjeed.com --email=ops@intellschool.com
  ```

  It writes an nginx vhost, obtains the certificate over HTTP-01, and reloads —
  in two phases, because nginx will not start against a certificate path that
  does not exist yet. Idempotent: an existing vhost is left alone unless you
  pass `--rewrite`.

  The command refuses a domain that is not registered to a tenant, and one that
  has not passed DNS verification. Both refusals matter: certbot will prove
  control of *any* domain pointed at the server, so without the first check this
  becomes a way to mint certificates for hosts nobody added.

  Use `--staging` while you are getting the config right. Let's Encrypt allows
  five failed attempts per domain per week, and staging has no such limit.

## 7. Verify before announcing

```bash
php artisan saas:verify-isolation          # cross-tenant leakage checks
php artisan route:list | grep intellschool # marketing/platform host binding
```

Then by hand:

- `https://intellschool.com` → marketing site
- `https://app.intellschool.com` → platform login
- `https://al-noor.intellschool.com` → that school's ERP login
- `https://nosuchschool.intellschool.com` → **404**, not a login page. If this
  shows a login page, tenancy is not actually resolving and every school is
  sharing one database.

## 8. Cron

```
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

## Hostinger shared hosting — what does not work, and what to do instead

The code runs on shared hosting. Automated provisioning does not, for two
reasons that both come from the same place:

**1. No `CREATE DATABASE`.** Shared plans do not grant that privilege. Databases
are created through hPanel and given a forced account prefix
(`u123456789_name`), so the derived `tnt_<hash>` name cannot exist there either.
Both halves of the default path are unavailable.

The adoption path handles this. Per school:

1. hPanel → Databases → create `u123456789_tamjeed` and its user. Copy the
   database name, the username, and the password it shows you once.
2. Platform panel → **New Tenant** → paste all three into the Database section.

That is the whole flow. Because hPanel binds each user to its own database, the
school gets **per-tenant credentials** rather than the shared cluster user: they
are stored encrypted against `APP_KEY` on that tenant's row, and the resolver
prefers them over the cluster entry.

Be clear about what encryption buys here. A dump of the landlord database alone
yields nothing usable; someone holding both the dump and `.env` can decrypt it.
That is better than plaintext and worse than a secret manager, and it is the
only option on hosting that has no secret manager to point at. Schools
provisioned on a VPS still store a pointer and no secret at all.

`SAAS_CLUSTER_DEFAULT_*` stays as the fallback for any school you create
*without* filling in those fields.

**2. Wildcard DNS and wildcard TLS.** `*.intellschool.com` needs both a wildcard
A record and a wildcard certificate, and shared hosting free SSL is issued
per-domain over HTTP-01, which cannot produce one. Confirm both on your plan
before promising subdomains to anyone.

If wildcards are unavailable, the system still works — you add each school's
subdomain in hPanel as an ordinary subdomain with its own certificate, and
register it in the platform panel. That is manual per school but entirely
supported. **Custom domains like `tamjeed.com` are unaffected by this**: they are
added individually in hPanel anyway, so they are arguably the easier path on
shared hosting.

Also worth confirming on your plan: SSH access (needed for `artisan`), cron, and
the database count limit.

### When you move to the VPS

Nothing in the application changes. The migration is:

1. Grant `CREATE` to the tenant user, and drop `--database=` from new provisions.
   Existing adopted databases keep working — the name is stored per tenant, so a
   mixture of adopted and derived names is fine.
2. Add the wildcard DNS record and a wildcard certificate.
3. Switch `QUEUE_CONNECTION` to `redis` and run a worker (see below).
4. Move `SESSION_DRIVER` and `CACHE_DRIVER` to `redis` if you like — both are
   already tenant-isolated on the file driver, so this is performance, not
   correctness.

## Two decisions left to you

### Trusted proxies — required if anything terminates TLS in front of Laravel

`app/Http/Middleware/TrustProxies.php` trusts **no** proxies (`$proxies = null`).

- **Direct TLS on this server (Apache/nginx with the certificate):** correct as
  is, change nothing.
- **Behind Cloudflare, a load balancer, or any reverse proxy:** `X-Forwarded-Proto`
  is currently ignored, so Laravel treats HTTPS requests as HTTP and generates
  `http://` links, password-reset URLs and redirects. Set `$proxies` to the
  proxy's actual IP ranges.

Do not set `$proxies = '*'` here. That also trusts `X-Forwarded-Host`, and the
Host header is what selects the tenant — a spoofable host header means a
spoofable tenant. Use explicit IPs, and make sure the proxy overwrites or strips
`X-Forwarded-Host`.

### Queue driver

`.env.production` ships `QUEUE_CONNECTION=sync` — jobs run inline, which is
correct and needs no worker. Provision from the CLI and this is fine.

Move to `redis` when Redis is available, and run a worker:

```bash
php artisan queue:work --queue=provisioning,notifications,default
```

**Never set `QUEUE_CONNECTION=database` while tenancy is on.** The `database`
queue connection declares no explicit connection, so it follows
`database.default` — which during a tenant request is that school's own
database. Jobs would be written into tenant databases and no worker would ever
find them.

## Still fail-closed, deliberately

These stay off in `.env.production` and are not oversights:

| Flag | Blocked on |
|---|---|
| `SAAS_PUBLIC_SIGNUP_ENABLED` | approved pricing, legal docs, verified mail delivery |
| `SAAS_OWNER_INVITATIONS_ENABLED` | same |
| `SAAS_CLAIM_PRICING` / `_CUSTOMERS` / `_UPTIME` / `_CERTS` | published evidence for each claim |
| `SAAS_BILLING_PROVIDER=null` | an approved provider adapter bound in `SaasServiceProvider` |

The marketing views hide each claim while its flag is false, so the site is
coherent with all of them off. Schools can be onboarded by hand with
`saas:provision` meanwhile.
