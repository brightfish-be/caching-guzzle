# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`brightfish/caching-guzzle`: a framework-agnostic Composer library that caches Guzzle HTTP responses in any PSR-16 store. Laravel is supported but not required, and this is not a Laravel app. PHP `^8.0`, Guzzle `^7`, `psr/simple-cache` `^2|^3`.

## Commands

```bash
composer install                                   # composer.lock is gitignored
composer test                                      # vendor/bin/phpunit tests
vendor/bin/phpunit tests --filter test_caching     # run matching tests
PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix --config=.php_cs.dist.php --allow-risky=yes
```

Use the php-cs-fixer command above rather than `composer format`:
- The installed php-cs-fixer 2.19 refuses to run on PHP above 8.0 without `PHP_CS_FIXER_IGNORE_ENV`, and warns it may then modify code wrongly.
- `composer format` doesn't pass `--config`, and 2.x doesn't auto-detect the `.php_cs.dist.php` filename.
- CI runs the fixer on every push and auto-commits a "Fix styling" commit, so pull before pushing again.

## Architecture

Two classes in `src/`:

- `Middleware` is the invokable Guzzle middleware and holds all the caching logic.
- `Client` extends `GuzzleHttp\Client`. It pushes `Middleware` onto the handler stack, using the `cache_ttl` and `cache_log` config values as constructor arguments, then strips all `cache*` keys from the config before calling Guzzle. As a result, `cache`, `cache_anew` and `cache_key` are not client-wide defaults; they only work per request.

Per-request options (`cache`, `cache_anew`, `cache_ttl`, `cache_key`) arrive through Guzzle's `$options` array.

Behaviour in `Middleware` that isn't obvious and is easy to break:

- **Only the body is stored.** A cache hit is rebuilt as `new Response(200, [], $entry)`, so status details and headers are lost.
- **Only status 200 is saved**, and the body stream is rewound afterwards so callers can still read it.
- **The default key is the URL minus its scheme** (`//example.org/api/resource`). The HTTP method, headers and body are not part of it, so GET and POST to the same URL share an entry, and POST responses are cached too.
- **Default keys contain `/` and `:`**, which PSR-16 reserves, so stores that enforce this will reject them unless `cache_key` is set.
- **TTL `0`** skips the write but still reads from the cache. **TTL `-1`** means forever and is stored as 631152000 seconds (20 years).
- **`cache_anew`** skips the read but still writes.
- **`'cache' => false`** skips both.
- **Logging** uses Laravel's `logger()` when that function exists, otherwise `error_log`.

## Tests

Tests live in `tests/Feature/`; `phpunit.xml` only picks up that directory. They make no network calls: `FeatureTestCase::getClientWithMockResponses()` builds a real client on Guzzle's `MockHandler`, with `Tests\Mocks\Cache`, an array-backed PSR-16 store, behind it. The URLs in the tests are never requested.

That mock store ignores TTLs, so expiry can't be tested with it.

## Project conventions

- Changes need tests, and behaviour changes need a matching update to `readme.md` (see `.github/CONTRIBUTING.md`). SemVer applies to the public API.
- The version lives in both `VERSION.md` and `composer.json`; keep them in sync.
- Work is tracked in `docs/todo/`.
- The CI matrix in `.github/workflows/run-tests.yml` still lists PHP 7.4, which `composer.json` no longer allows.
