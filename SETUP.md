# Mu5tasar PostgreSQL and security setup

## Connection

The live Supabase schema and Connect panel were inspected on 2026-09-15 for project `nluvqdwcouoloynplhjq`. No schema, policies, grants, or existing records were changed. Both tables retain RLS, and neither `anon` nor `authenticated` has SELECT access. The existing PHP session authentication remains responsible for application access.

`.env.example` contains the exact **session pooler** settings shown in Connect:

| Variable | Value |
| --- | --- |
| `DB_HOST` | `aws-0-ap-northeast-1.pooler.supabase.com` |
| `DB_PORT` | `5432` |
| `DB_NAME` | `postgres` |
| `DB_USER` | `postgres.nluvqdwcouoloynplhjq` |
| `DB_PASSWORD` | Set privately in the hosting environment |
| `DB_SSLMODE` | `verify-full` |
| `DB_SSLROOTCERT` | Absolute path to the trusted CA certificate outside the web root |
| `SESSION_COOKIE_SECURE` | `1` for production HTTPS |

The session pooler is the IPv4-compatible choice for traditional PHP hosting. The actual deployment host's extension support and outbound port access still need verification. If deploying a persistent PHP server with working IPv6, Connect also showed direct host `db.nluvqdwcouoloynplhjq.supabase.co`, port `5432`, database `postgres`, user `postgres`. Change the host and username together. See [Supabase connection guidance](https://supabase.com/docs/guides/database/connecting-to-postgres).

Set variables in the PHP worker's environment using your hosting secret settings or private server configuration. `.env.example` is documentation, not an automatically loaded file. Do not place a populated `.env` in a public directory. `DB_PASSWORD` is the raw database password, not a URL-encoded password, API key, or Supabase Auth password. The Connect panel does not reveal the current password; retrieve it from your existing password store or manage it privately in Supabase.

TLS cannot be disabled in code. The default `verify-full` verifies encryption, the certificate chain, and hostname; configure a trusted CA for the chosen endpoint through `DB_SSLROOTCERT` or libpq's trusted root configuration. `require` and `verify-ca` are also supported explicitly, but retain `verify-full` for production identity verification.

The verified Connect username uses the existing server-side `postgres` role, which owns these tables. Keep that credential exclusively on the PHP server. No browser grants or RLS policy changes are needed. No Supabase client JavaScript or Supabase Auth integration was added.

## PHP and hosting

- Use PHP 8.0+ with `PDO`, `pdo_pgsql`, `fileinfo`, and sessions enabled. Configure these for the web PHP runtime as well as CLI. The Windows installation here contains both extension DLLs, but loads no `php.ini` by default.
- Set `display_errors=Off` and `display_startup_errors=Off` in production PHP configuration as well as the application's runtime settings. Use private server error logs.
- Serve `main/` as the document root; all existing page names remain unchanged. Keep `config/`, tests, environment values, and certificates outside it. If retaining a deployment with `/main/` in URLs, deny access to `config/`, `tests/`, dotfiles, and setup files in the server configuration. Apache deny rules are included; Nginx/IIS need equivalent rules.
- Allow the PHP worker to write to `main/uploads/`. Serve that directory strictly as static JPEG, PNG, GIF, or WebP images, never executable PHP or scripts. Apache rules are included; enable the relevant overrides or implement equivalent rules in the host. Apply `X-Content-Type-Options: nosniff` and the supplied sandbox CSP to uploaded assets.
- Image uploads are optional, content-validated, limited to 5 MB each, and given random filenames. SVG and non-image uploads are rejected. Configure `upload_max_filesize` at least `5M` and `post_max_size` above the combined size of five images, such as `30M`.
- Use HTTPS with `SESSION_COOKIE_SECURE=1`. Set it to `0` only when testing locally over HTTP. Logout now requires the existing navigation button's token-protected POST; requesting `logout.php` with GET returns 405.
- The removed configuration contained a hardcoded legacy MySQL password. Rotate that old credential wherever it is still valid; removing the file contents does not invalidate prior exposure.

## Verification

Run from the project directory:

```powershell
Get-ChildItem config,main,tests -Recurse -Filter '*.php' | ForEach-Object { php -l $_.FullName }
php tests/security.php
node tests/http-security.mjs
php tests/database.php
```

`tests/security.php` needs PHP DOM for template checks. `tests/http-security.mjs` uses Node 20+ and starts/stops its own local PHP server. It disables database configuration, creates isolated test sessions, rejects hostile uploads, and removes its temporary image and session data. It does not log into a real user account. The read-only database check needs the environment settings above.

For this Windows PHP installation, extension flags can be supplied without changing system configuration:

```powershell
php -d 'extension_dir=C:/Program Files/php/ext' -d extension=pdo_pgsql -d extension=fileinfo tests/security.php
$env:PHP_TEST_ARGS = '["-d","extension_dir=C:/Program Files/php/ext","-d","extension=pdo_pgsql","-d","extension=fileinfo"]'
node tests/http-security.mjs
php -d 'extension_dir=C:/Program Files/php/ext' -d extension=pdo_pgsql -d extension=fileinfo tests/database.php
```

Completed: syntax checks, 48 helper/template checks, and 42 HTTP checks. Stored-XSS tests render the actual templates with hostile fixtures; reflected-XSS tests exercise HTTP input and error responses. SQL-injection checks cover rejected quiz IDs and native prepared-statement code; the exact quiz insert was also prepared and EXPLAINed against live PostgreSQL without writing records. Both tables' RLS and blocked browser access were reconfirmed.

Not completed: direct PHP-to-Supabase authentication and successful login/registration/quiz-save/read/score flows against the live database, because the database password and CA configuration are absent. The PDO extension was enabled only for test processes. Once configured, run `tests/database.php`, then use a disposable account and quiz for the full workflow and remove only those test records and uploaded files.
