# Read-only information requests: SACK migration, stage 1

This change moves user bandwidth, hotspot totals and dictionary descriptions to
same-origin GET requests with JSON responses. It does not change RADIUS policy,
database schema, user actions, billing operations or dynamic attribute helpers.
SACK remains available for those unmigrated consumers.

## Internal endpoint contracts

Paths are relative to the operators portal (`library/ajax/`).

| Endpoint | Required GET parameter | Successful JSON fields | Existing ACL |
| --- | --- | --- | --- |
| `user_info.php` | `username` | `upload`, `download` (formatted strings) | `acct_username` |
| `hotspot_info.php` | `hotspot` | `upload`, `download`, `hits` (integer or `(n/a)`) | `acct_hotspot_accounting` |
| `vendor_attribute_info.php` | `attribute` | `description` (plain text) | `mng_rad_attributes_list` |

Examples: `{"upload":"1 KB","download":"(n/a)"}` and
`{"description":"Description from the dictionary"}`.

The former action flags and `divContainer` are no longer needed. DOM targets stay
in the browser. Existing trimming and percent-sign removal in lookup values are
preserved; changing that legacy normalization is not part of this migration.

Missing, blank or array parameters return JSON errors with HTTP 400; non-GET
requests return 405 and `Allow: GET`. Query failures return 500 without the default
PEAR HTML error callback. Successful/error JSON responses use `Cache-Control:
no-store`. Authentication and ACL guards are unchanged: unauthenticated sessions
redirect to login and denied permissions return 403. The frontend handles those
before attempting JSON decoding, and rejects HTML or malformed JSON responses.

`request.js` provides only the required JSON GET transport. It does not implement
mutation retries or speculative CSRF support. `readonly_info.js` renders text and
DOM nodes, never response code or HTML. The latest request wins independently for
each target, and detached targets are ignored.

## Migration scope and deployment

All 18 read-only page consumers use the new scripts. The legacy scripts remain on
`mng-list-all.php`, `mng-search.php`, `bill-pos-list.php` and
`rep-batch-details.php` because their mutation controls still need SACK. Other
unmigrated pages/assets are unchanged. `acct-plans-usage.php` now explicitly loads
the scripts required by its information control.

These are internal endpoints, but their response format and accepted HTTP method
are breaking changes for custom clients. Update custom clients from executable
JavaScript/POST to JSON/GET. Deploy the PHP pages, endpoints and new JavaScript
assets together; reload already-open pages and invalidate cached HTML/assets as
needed. Roll back the complete code change, not just the endpoints. No SQL
migration is required.

There is no root `package.json`, Changesets configuration, `RELEASE.md` or
`.devcontainer/README.md` in this baseline. No npm package or release changeset is
introduced for this PHP/JavaScript migration.

## Automated checks

From the repository root:

```sh
node --test tests/readonly-info.test.cjs
python3 tests/readonly_info_http.py
git diff --check
```

The HTTP test requires PHP CLI. `PHP_COMMAND` can supply a PHP container command
that mounts `/tmp` at the same path and uses the host network (the test PHP server
binds only to loopback). Use a trusted local image with PHP, for example:

```sh
PHP_COMMAND='docker run --rm --network host -v /tmp:/tmp --entrypoint php YOUR_PHP_IMAGE' \
  python3 tests/readonly_info_http.py
```

The Node tests use a DOM/fetch double: encoding, rendering, HTTP/network/session
errors, malformed JSON, out-of-order requests, detached targets and all page
asset references. They also check that mixed read/write pages retain SACK.

The HTTP suite executes the actual endpoints, session guard, ACL guard and byte
formatter in an isolated temporary application, with a **synthetic database** and
seeded local sessions. It checks successful responses, missing data, invalid
parameters, wrong methods, expired/absent sessions, ACL denials and query errors.
It does not test password login, a real SQL server or production deployment.
It neither reads nor modifies the lab database.

Also run `php -l` on changed/new PHP files and `node --check` on the new JS files.

## Application-level verification before deployment

On an isolated source-backed application with representative data:

- Open a user traffic dropdown, hotspot dropdown and dictionary description.
  Confirm labels, units, `(n/a)` cases and quotes/ampersands/Unicode display.
- Click several rows quickly; each result must stay in its own row. Throttle the
  network and repeat a click; an earlier result must not replace a newer one.
- Deny the corresponding ACL and expire a session; verify a visible error rather
  than stale data or a false success. Test a server/network failure too.
- Check each migrated page's scripts load, including accounting by plan and IP
  pool listings. Check that existing mutation controls still load their legacy
  dependencies without invoking mutations against production data.
- Inspect the network panel and console: JSON GET responses, no response `eval`,
  no JavaScript exceptions. No RADIUS/NAS test is required for the transport change.
