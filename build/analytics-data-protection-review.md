# Proclaim Analytics — Data Protection Review

**Date:** 2026-08-06
**Scope:** `#__bsms_analytics_events`, `#__bsms_analytics_monthly`, `CwmanalyticsHelper`, `plg_task_proclaim` analytics routine
**Prepared for:** review by a DPO or privacy counsel

> **This is not legal advice.** It reports what the code does and what primary sources say. Two questions at the end require judgement from someone qualified, with access to real production data.

---

## 1. Summary

Proclaim collects first-party analytics into its own MySQL tables. No data leaves the site; there is no third-party processor, no advertising identifier, and the raw IP address is never stored.

The most significant finding is not about retention. **The schema marks three columns "Consent-required", but no consent mechanism exists** — the documented opt-out is unreachable, and the only working alternative signal is effectively defunct. Those columns are collected from approximately every visitor.

| # | Finding | Severity | Tracked |
|---|---|---|---|
| 1 | Consent-required data collected without consent; opt-out cookie never set by anything | **High** | [#1613](https://github.com/Joomla-Bible-Study/Proclaim/issues/1613) |
| 2 | `outbound_click` writes `referrer_url` outside the consent gate | **High** | [#1613](https://github.com/Joomla-Bible-Study/Proclaim/issues/1613) |
| 3 | Column expiry + timestamp coarsening does **not** reach anonymity — proven on 82,565 real events | **Medium** | [#1611](https://github.com/Joomla-Bible-Study/Proclaim/issues/1611) |
| 4 | Full referrer URL incl. query string stored when referrer mode is `full` | **Medium** | [#1612](https://github.com/Joomla-Bible-Study/Proclaim/issues/1612) |
| 5 | `Sec-GPC` (live DNT successor) not honoured; opt-out cookie undocumented as a CMP integration point; `gdpr_mode` silently gates analytics | **Medium** | [#1613](https://github.com/Joomla-Bible-Study/Proclaim/issues/1613) |
| 6 | Admin help text documents an opt-out cookie that nothing sets | Medium | [#1613](https://github.com/Joomla-Bible-Study/Proclaim/issues/1613) |
| 7 | Component-level retention setting is dead code | Low | [#1609](https://github.com/Joomla-Bible-Study/Proclaim/issues/1609) |

Already fixed in [#1610](https://github.com/Joomla-Bible-Study/Proclaim/pull/1610): a blank retention field could delete the entire analytics history, and the "do not purge" toggle was ignored. Retention now defaults to keeping data indefinitely, with purging opt-in.

---

## 2. Data inventory

Every column in `#__bsms_analytics_events`, classified.

### Identifying / pseudonymous — personal data

| Column | Notes |
|---|---|
| `session_hash` | `hash('sha256', $sessionId)`, unsalted. **Pseudonymous, therefore personal data** (GDPR Art. 4(5), Recital 26). Marked *Consent-required* in the schema. |
| `referrer_url` | Full URL including query string, up to 2048 bytes. Only populated when referrer mode is `full`, **or** for `outbound_click` events (unconditionally). Marked *Consent-required*. |
| `referrer_domain` | Host only. Marked *Consent-required*. On its own, very likely not identifying. |

The decisive point on `session_hash` is **singling out**, not reversibility. Whether SHA-256 of a high-entropy Joomla session ID can be brute-forced is beside the point: Recital 26 names singling out as a means of identification, and a per-session hash singles out one visitor across every row carrying it, regardless of hash strength.

### Quasi-identifiers — not flagged, but contribute to identifiability

| Column | Notes |
|---|---|
| `browser`, `os`, `language` | A recognised fingerprinting triple. **Not** marked consent-required. |
| `device_type` | Low cardinality (4-value enum). |
| `country_code` | Always `NULL` — no GeoIP exists anywhere in the codebase. |
| `created` | `DATETIME`, second resolution. **The dominant identifiability driver — see §4.** |

### Non-identifying

`id`, `study_id`, `series_id`, `media_id`, `location_id`, `event_type`, `referrer_type` (6-value enum), `utm_source`, `utm_medium`, `utm_campaign`, `is_guest`.

UTM values are visitor-supplied campaign tags rather than observed behaviour.

### Never collected

Raw IP address (the schema documents this explicitly), raw User-Agent string (classified at log time and discarded), and any account identifier beyond the `is_guest` boolean.

---

## 3. Consent flow audit — the principal finding

### What the code does

```php
$optedOut  = self::isOptedOut();
$consentOn = !$optedOut;
```

`$consentOn` gates the three consent-required columns. `isOptedOut()` returns true only if:

1. the `gdpr_mode` component parameter is set (a site-wide admin kill switch, default off); **or**
2. the request carries `DNT: 1`; **or**
3. a `proclaim_analytics_optout` cookie is present.

### Why this does not amount to consent

**The cookie is never set.** It is read in one place and written in none — no UI, no JavaScript, no controller, no menu item. A visitor cannot opt out except by manually creating a cookie in browser devtools.

**DNT is effectively defunct.** The W3C discontinued the working group; Safari and Firefox removed the setting; Chrome never sent it by default. In practice, approximately no visitor sends it.

**Non-objection is not consent.** `$consentOn = !$optedOut` treats the absence of an objection as permission. GDPR Art. 4(11) requires a freely given, specific, informed and unambiguous *affirmative action*, and Recital 32 states that silence and inactivity do not constitute consent. There is no integration with Joomla's own `plg_system_privacyconsent` or `com_privacy`.

**Net effect:** the columns the schema itself labels *Consent-required* are collected from essentially 100% of visitors under a consent model that does not exist.

### The unconditional bypass

```php
if ($consentOn && $refUrl !== '') { /* referrer fields, correctly gated */ }

// Outbound click: repurpose destUrl as referrer_url column
if ($type === 'outbound_click' && $destUrl !== '') {
    $referrerUrl = substr($destUrl, 0, 2048);   // no $consentOn check
}
```

A visitor who *has* signalled DNT still gets a destination URL and timestamp written. This is our own link target rather than a third party's leakage, so the content risk is lower — but it bypasses the gate the code claims to enforce, and it is the one path that populates `referrer_url` on a default install.

Separately: an opted-out visitor still has an event row inserted; only the consent-tier columns are suppressed. That is defensible **only if** the remaining row is genuinely anonymous — see §4, where it currently is not.

---

## 3a. Two regimes, and the switch that conflates them

Proclaim serves both sites subject to GDPR/UK GDPR and sites that are not (predominantly US churches). These have genuinely different obligations, and a single blanket policy is wrong for one of them:

| | Consent-required regime (EU/UK) | Notice-only regime (most US) |
|---|---|---|
| Legal model | Prior **opt-in** for the consent tier | **Notice** plus, in several states, a right to opt out |
| Current behaviour | **Non-compliant** — see §3 | Broadly acceptable, provided the opt-out works |
| Retention | Purpose-limited, documented | Largely a business decision |

A blanket move to opt-in would impose an unnecessary consent barrier on US sites, where first-party analytics that involves no sale or sharing of data generally does not trigger a statutory opt-out right at all. Equally, the present design cannot be made lawful for EU sites by adjusting retention — the defect is at collection.

**The missing option is the middle one.** Today the model is binary and each end is wrong for one audience:

- `gdpr_mode` **off** → consent-tier data collected from everyone, no consent. Wrong for EU.
- `gdpr_mode` **on** → consent-tier data collected from nobody. Correct but blunt; discards data an EU site could lawfully collect *with* consent.

There is no "collect on consent" setting, which is the actually-compliant EU position.

### The switch is also doing two unrelated jobs

`gdpr_mode` is documented to admins as being about outbound traffic:

> "When enabled, disables all external API calls (GetBible.net, API.Bible) and forces social sharing to Local/Privacy mode (no AddToAny). Only locally downloaded translations will be used. No data leaves your server."

It says nothing about analytics — yet `isOptedOut()` keys off it and silently suppresses the entire consent tier. So:

- An admin enabling it to stop external API calls also loses session and referrer analytics, with no indication that will happen.
- An admin who wants correct analytics consent handling has no control that does only that.

Two unrelated concerns behind one undocumented switch.

### A further documentation defect

The `analytics_gdpr_optout` description tells admins that visitors can opt out via "the `proclaim_analytics_optout` cookie". Nothing in the product sets that cookie (§3). The admin-facing documentation therefore describes a control that does not exist, which is worse than being silent — an administrator could reasonably rely on it when answering a data subject.

### Proclaim should consume the site's consent signal, not build its own

Most Joomla sites already run a cookie-consent banner. Building a second consent mechanism into Proclaim would duplicate it, risk showing visitors two banners, and almost certainly be worse than the dedicated extension. **Proclaim should be a consumer of consent, never a producer of consent UI.**

Two facts make this cheaper than it sounds.

**1. Joomla core offers nothing to integrate with.** `plg_system_privacyconsent` handles *account-registration* consent — it hooks `onUserAfterSave` and injects a field into the registration form. It has no concept of an anonymous visitor. Visitor cookie consent in Joomla is entirely third-party territory, so there is no core API to adopt and no standard to conform to.

**2. The integration surface already exists — it is just undocumented.** `isOptedOut()` already reads a `proclaim_analytics_optout` cookie. That is exactly the right shape for CMP integration; the defect is not that Proclaim fails to set it, but that:

- the admin help text implies *visitors* can use it, suggesting Proclaim provides the UI; and
- no consent extension knows the cookie exists, because it has never been documented as an integration contract.

So the fix is largely **documentation plus a small published contract**, not a feature build: tell administrators to configure their existing banner to set `proclaim_analytics_optout` when a visitor declines analytics, and publish that contract for consent-extension authors.

### The ePrivacy "cookie consent" question likely does not apply here at all

Verified: **Proclaim sets no cookie of its own.** There is no `setcookie()` anywhere in the analytics path. The identifier is derived from Joomla's existing session:

```php
$sessionId   = $app->getSession()->getId();
$sessionHash = hash('sha256', $sessionId);
```

That matters, because ePrivacy/PECR consent — the thing cookie banners exist to obtain — governs *storing or accessing information on a user's device*. Proclaim stores nothing, and the only cookie it depends on is Joomla's own session cookie, which is strictly necessary and consent-exempt.

**What Proclaim does is server-side processing of an identifier, which needs a GDPR Article 6 lawful basis — not necessarily consent.** Legitimate interests is arguable for first-party analytics with no sharing, no profiling and no cross-site tracking, and that argument is strengthened considerably by the finding in §4 that `session_hash` links two events in only 1.6% of sessions.

This suggests the schema's blanket *Consent-required* marking is **over-cautious**, and that the compliance question is narrower than "we need a consent banner". Caveat: CNIL requires consent for analytics falling outside its exemption, and the exemption demands anonymised statistics with no cross-matching — so France is stricter than the general position. This is the point in the review where a DPO adds the most value.

### Global Privacy Control — the signal that is actually alive

`isOptedOut()` honours DNT, which is defunct. It does **not** honour `Sec-GPC: 1`, which is DNT's working successor: recognised under CCPA/CPRA regulations, and sent by Firefox, Brave and DuckDuckGo.

Adding that check is a three-line change and is the single most useful thing available for the notice-only regime, where a respected opt-out signal is the operative requirement.

### Revised shape

1. **Honour `Sec-GPC: 1`** alongside DNT. Three lines, live standard, immediate value.
2. **Document `proclaim_analytics_optout` as a published integration contract** for whatever consent extension the site already runs, and correct the admin help text so it no longer implies Proclaim provides visitor-facing UI.
3. **Do not build a consent banner.**
4. **Decouple `gdpr_mode` from analytics**, or document its analytics effect in its own description.
5. Revisit whether the *Consent-required* marking is correct at all once `session_hash` is dropped (§4) — it may be that nothing in the table requires consent afterwards.

---

## 4. Anonymity of the residual record

The proposal in #1611 was to expire the three consent-required columns and keep everything else indefinitely. **That does not produce anonymous data**, and the distinction matters: genuinely anonymous data falls outside GDPR entirely (Recital 26), whereas a residual record that still singles out is personal data being kept forever with no purpose limit — a worse position than a plain whole-row TTL.

WP29 Opinion 05/2014 (WP216) addresses this pattern directly:

> "when a data controller does not delete the original (identifiable) data at event-level … the resulting dataset is still personal data. Only if the data controller would aggregate the data to a level where the individual events are no longer identifiable, the resulting dataset can be qualified as anonymous."

### Measurement — real production data

Measured against a **live church site**: 82,565 events over three months (May–Aug 2026), 811 distinct sermons. This supersedes the earlier development-sample estimate, which was too small and too degenerate to be meaningful.

**Residual record** = every column except `session_hash`, `referrer_url`, `referrer_domain`.

| `created` resolution | Distinct groups | Rows uniquely identified | % of all rows |
|---|---|---|---|
| Second (as stored) | 82,561 | 82,557 | **100.0%** |
| Hour | 79,588 | 76,698 | **92.9%** |
| Day | 68,462 | 56,075 | **67.9%** |
| Month | 30,329 | 13,996 | 17.0% |

Also dropping the unflagged fingerprint columns (`browser`, `os`, `language`):

| `created` resolution | Rows uniquely identified | % of all rows |
|---|---|---|
| Day | 37,960 | **46.0%** |
| Month | 2,030 | 2.5% |

### This settles the question, and not in favour of the proposed design

**Column removal plus timestamp coarsening does not reach anonymity.** The proposal in #1611 — drop the three flagged columns, coarsen `created` to day — would still leave **67.9% of rows uniquely identifying a single visit**. Dropping the fingerprint columns as well still leaves 46%. Even month-resolution with fingerprint columns removed leaves 2,030 singled-out rows.

The driver is `study_id` cardinality: with 811 distinct sermons, "this sermon, on this day" is frequently a single visit on a site of this size. Coarsening time cannot fix that, because the identifying power is in the content dimension, not the clock.

This is precisely what WP216 describes — retained event-level rows remain personal data, and "only if the data controller would aggregate the data to a level where the individual events are no longer identifiable" is the result anonymous. **Aggregation into counts is the only reliable route.** The existing `#__bsms_analytics_monthly` table is already that shape.

### `session_hash` costs everything and buys almost nothing

| | |
|---|---|
| Rows with `session_hash` populated | 81,409 of 82,565 — **98.6%** |
| Distinct sessions | 76,446 |
| Sessions with exactly **one** event | 75,197 — **98.4%** |
| Sessions with 2 or more events | 1,249 — 1.6% |

The column exists to support unique-visitor and multi-event session analysis. Empirically **it almost never links two events**: 98.4% of sessions contain a single event, so a unique-visitor count derived from it would be within ~2% of simply counting events.

So the single most identifying column in the schema — the one that creates the consent requirement — is delivering close to zero analytical value on real data.

**This makes the cheapest fix also the best one: drop `session_hash` entirely.** That removes the pseudonymous identifier, removes the consent obligation attached to it, and costs almost nothing in reporting. It is a far better outcome than expiring it on a 13-month schedule, and it is a smaller change than building a consent flow to justify keeping it.

### Referrer URLs on this site

`analytics_referrer_mode` is set to something other than the default here — `referrer_url` is populated on 16,266 rows (19.7%).

| | |
|---|---|
| Rows with a query string | 3,763 |
| Query strings that look like search terms | 746 |
| Containing an `@` (possible email) | **0** |
| Containing token/key/auth/session/password params | **0** |

Better than feared: no credentials or email addresses were found. But 746 rows do carry visitor search terms, which is content about the individual rather than analytics. Confirms #1612 is real on sites that enable full referrer mode, at moderate rather than severe magnitude.

---

## 5. Retention as shipped

Following #1610:

| | Behaviour |
|---|---|
| Default | **Data kept indefinitely.** Purging is opt-in. |
| Purge window | 7-day minimum enforced at the point of deletion; invalid values abort the run and log rather than substituting a guess. |
| Aggregates | `#__bsms_analytics_monthly` rows are written by the rollup and never expire. |

GDPR mandates no maximum retention period. Art. 5(1)(e) requires only that data be "kept in a form which permits identification … no longer than is necessary" — the obligation attaches to *identifiability*, not to the record, which is the textual basis for column-level expiry rather than row deletion.

CNIL's 13-month/25-month figures are a French recommendation, not a statutory limit, and they attach to the consent-*exempt* audience-measurement regime — which storing a per-visitor identifier disqualifies us from in any case. They remain useful as a benchmark with an articulable rationale ("une comparaison pertinente des audiences dans le temps"), not as a compliance target. The ICO is explicit that neither PECR nor UK GDPR specifies any timeframe.

**Recommendation:** derive the window for `session_hash` from the stated purpose and document the reasoning. If the purpose is year-over-year engagement comparison, 13 months has a citable rationale. The documented reasoning is what makes a window defensible, more than the number itself.

---

## 6. Data subject rights

A practical gap worth noting: because no stable visitor identifier is retained beyond `session_hash` — which the site cannot map back to a person — Proclaim **cannot service an access or erasure request** against analytics data. There is no way to locate a given individual's rows.

This is a common position for privacy-preserving analytics and is arguably a feature. It should be stated explicitly in the site's privacy notice rather than left implicit, since "we cannot identify you in this dataset" is a meaningful representation to make.

---

## 7. Recommendations, in priority order

1. **Gate the `outbound_click` assignment on `$consentOn`.** One line, no design decisions. (#1613)
2. **Provide a working opt-out** — at minimum something that sets the cookie the code already reads. Required under *both* regimes, and it makes the existing admin-facing documentation true. (#1613)
3. **Honour `Sec-GPC: 1`** alongside DNT — three lines, and unlike DNT it is a live standard sent by Firefox, Brave and DuckDuckGo and recognised under CCPA. (§3a, #1613)
4. **Document `proclaim_analytics_optout` as an integration contract** for the consent banner the site already runs, and correct the admin help text. **Do not build a consent banner into Proclaim.** (§3a, #1613)
5. **Decouple `gdpr_mode` from analytics**, or document its analytics side-effect in its own description. (§3a, #1613)
6. **Drop `session_hash` outright** rather than expiring it. Real data shows 98.4% of sessions contain a single event, so it delivers almost no analytical value while creating the entire consent obligation. This is both the cheapest and the most effective privacy fix available. (#1611)
7. **Do not rely on column removal plus timestamp coarsening to anonymise** — measured at 67.9% of rows still uniquely identifying at day resolution. Aggregation into counts is the only reliable route, and `#__bsms_analytics_monthly` is already that shape. (#1611)
8. **Strip the referrer query string at capture time** rather than at expiry. You cannot leak what was never stored. (#1612)
9. Consider HMAC with a server-side secret for `session_hash` instead of a bare hash (EDPB GL 01/2025 ¶¶89–90). Note precisely what this buys: rotating the secret breaks *cross-epoch* linkage only, and does not retroactively anonymise stored rows.
10. Remove or wire up the dead component-level retention setting. (#1609)

---

## 8. Questions requiring qualified judgement

These are the two points that cannot be resolved by reading code, and they are the reason this document should go to a DPO.

**8.1 — ~~Is the residual record anonymous after the identifying columns expire?~~ ANSWERED — no.**
Measured against 82,565 real events (§4): 67.9% of rows still uniquely identify a single visit at day resolution, 46.0% even with the fingerprint columns also removed. Per-event rows on a site of this size cannot be anonymised by column removal. This no longer needs counsel — it needs a design change to aggregation. What *would* still benefit from review is confirming that the aggregated monthly rows are themselves anonymous at low campus/series cardinality.

**8.2 — What retention window is "necessary" for the stated purpose?**
Art. 5(1)(e) is purpose-relative, so the purpose must be stated first and the window derived from it. Counsel should confirm the stated purpose genuinely supports the window chosen.

**8.3 — What is the lawful basis, and is consent required at all?**
Proclaim stores no cookie of its own (§3a), so the ePrivacy/PECR consent requirement that cookie banners exist to satisfy may simply not apply. What remains is a GDPR Art. 6 basis for server-side processing of a session-derived identifier, and legitimate interests is arguable for first-party analytics with no sharing or profiling. If that holds — and especially if `session_hash` is dropped — the *Consent-required* marking in the schema may be over-cautious. CNIL is stricter than the general position and should be considered if the site has French visitors.

**8.4 — Is the site's stated privacy notice consistent with what is actually collected?**
Not assessed here; requires sight of the published notice. Given §3, any notice claiming visitors can opt out is currently inaccurate.

A further point is now settled empirically rather than being a judgement call: whether the consent tier is worth keeping at all. `session_hash` exists to support unique-visitor counts, and on real data it links two events in only 1.6% of sessions. Dropping it removes the entire consent question at negligible analytical cost — see recommendation 5.

---

## Sources

- [GDPR consolidated text (EUR-Lex)](https://eur-lex.europa.eu/legal-content/EN/TXT/HTML/?uri=CELEX:32016R0679) — Arts. 4(5), 4(11), 5(1)(e); Recitals 26, 32
- [WP29 Opinion 05/2014 on Anonymisation Techniques (WP216)](https://ec.europa.eu/justice/article-29/documentation/opinion-recommendation/files/2014/wp216_en.pdf)
- [CNIL, consolidated cookies recommendation, January 2026](https://www.cnil.fr/sites/default/files/2026-01/recommandation_cookies_consolidee.pdf)
- [ICO, Storage and Access Technologies guidance (final, April 2026)](https://ico.org.uk/for-organisations/direct-marketing-and-privacy-and-electronic-communications/guidance-on-the-use-of-storage-and-access-technologies/what-are-the-exceptions/)
- [EDPB Guidelines 01/2025 on Pseudonymisation](https://www.edpb.europa.eu/system/files/2025-01/edpb_guidelines_202501_pseudonymisation_en.pdf) — consultation version; not confirmed as finally adopted
- CJEU C-413/23 P, *EDPS v SRB*, 4 September 2025 — identifiability assessed from the controller's standpoint at the time of collection
