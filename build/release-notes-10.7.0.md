# What's new in 10.7.0

## The headline: protected media storage now actually works

10.6.x advised administrators to move files into protected storage, but the feature was half-built (#2033). This release completes it:

- files are **served** from protected storage rather than linked to (#2042), and the site's base path is no longer repeated when a URL is mapped to disk (#2045/#2049);
- Proclaim can **move a file in** itself, instead of telling you to do it by hand (#2041/#2059);
- the **podcast feed no longer emits an enclosure** for a protected file, and System Health says so (#2044/#2060);
- System Health looks **inside** the folder rather than at its top level, so the count is right (#2046/#2051);
- the restricted-media list explains **why** each file is on it and what state it is in (#2061/#2062).

⚠️ Worth Tom's eye: a protected file's delivery keys on **file location**, not on a per-server switch — so a server-level toggle cannot gate it. That shaped several of the fixes above.

## User-facing bugs fixed
- **Setup wizard fatal** creating the podcast — `Factory::getUri()` is a Joomla 3 method (#2075). While testing it, a second defect surfaced: the insert omitted `podcastlink`, which is `NOT NULL` on sites upgraded through the era it was added, and a swallowing `catch` turned that into *"wizard silently creates no podcast"*. Both dev databases are in that state, so it likely affects most upgraded installs.
- **500 for a guest** viewing an unpublished sermon — `Text` was never imported (#2076).
- **UTF-8 titles corrupted** by the description cleanup: a dash-class regex without `/u` ate the trailing byte of `À`, `Ô`, `œ`, Cyrillic `Г`/`Д` (#2077).
- **TLS verification was disabled** on the podcast enclosure probe (#2078) — an on-path attacker could dictate the enclosure length and type written into the feed. Now uses Joomla's HTTP client, so verification follows core config.
- **Play/download events were logged before the access check**, counting views the visitor could not have (#2047).
- **Choosing a server type** no longer submits the form and reloads the page, losing typed work (#2037).

## Scripture (submodules moved)
```
libraries/lib_cwmscripture       1.1.20 -> 1.1.21
plugins/content/scripturelinks   1.2.13 -> 1.2.14
```
- Cross-chapter passages (`Genesis 1:26-2:3` and every reference of that shape) threw instead of returning verses on locally-stored translations.
- *Remove all translations* and the provider clean-up fatalled on methods that were never written; both now work, and that screen reports failures instead of hanging.

Both are released and on ARS; both submodule checkouts pin the same library commit, which `SubmodulePinsTest` enforces.

## Internal, no behaviour change
- **Query modernisation (#1994) completed** — every `quote($var)` on a builder query is bound; ORDER BY is whitelisted rather than wrapped in a no-op `escape()`; OR groups use the builder. Phase 4 was retired with the MySQL-only decision (#1857).
- **#2089** — `CwmadminController`'s 155 hand-rolled JSON responses now go through the shared trait. ⚠️ Payloads were verified **byte-identical** programmatically (155 before, 155 after, zero differing), so no JavaScript consumer is affected.
- Six locales synced; menu preset quicktask strings added.
- **Dependency bumps** — `github/codeql-action` v4.37.9 → v4.38.0 and `friendsofphp/php-cs-fixer` v3.95.24 → v3.95.25. Workflow file and `composer.lock` only; neither reaches the shipped payload.

---
