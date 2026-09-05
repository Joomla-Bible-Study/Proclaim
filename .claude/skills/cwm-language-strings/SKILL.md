---
name: cwm-language-strings
description: House style for Proclaim's language strings — American English, the JBS_ key prefix families, the product glossary (message, teacher, media file), and .ini formatting. Use whenever adding, editing, or reviewing a language string, naming a new key, or writing user-facing text in this repo. Overrides the generic Joomla convention of COM_<ELEMENT>_ key prefixes.
---

# Proclaim language string house style

Everything below is measured from this repository's own `en-GB*.ini` files, not assumed. Counts are
from 5,482 keys across 45 files (excluding `tests/`).

## 1. Key prefixes — `JBS_`, not `COM_PROCLAIM_`

**This is the rule that contradicts generic Joomla guidance, so state it before anything else.**

Standard Joomla convention is `COM_<ELEMENT>_*`. Proclaim does not follow it, and must not start:

| Prefix | Keys |
|---|---|
| `JBS_` | 4,771 |
| `PLG_` | 243 |
| `LIB_` | 166 |
| `COM_` | 152 |
| `MOD_` | 85 |

Renaming 4,771 keys to `COM_PROCLAIM_*` would invalidate **every translation in every language**,
because translations key off the key name. The legacy prefix stays. A general-purpose Joomla skill
will propose `COM_PROCLAIM_` for new strings — that is correct for Joomla in general and wrong here.

### Sub-prefix families

New component keys join an existing family rather than inventing one:

| Family | Keys | Covers |
|---|---|---|
| `JBS_ADDON_` | 724 | Add-on management |
| `JBS_TPL_` | 630 | Front-end templates |
| `JBS_ADM_` | 567 | Admin UI |
| `JBS_CMN_` | 403 | Shared/common |
| `JBS_PDC_` | 278 | Podcast |
| `JBS_MED_` | 260 | Media |
| `JBS_WIZARD_` | 210 | Setup wizards |
| `JBS_HEALTH_` | 178 | Health checks |
| `JBS_ANA_` | 164 | Analytics |
| `JBS_BBK_` | 146 | Bible books |

`COM_PROCLAIM_` (152) is not a family to grow — it holds Joomla-mandated keys that the CMS itself
looks up by convention (`COM_PROCLAIM_N_ITEMS_PUBLISHED` and friends, which `AdminController`
resolves by name). **Those must keep the `COM_` form.** Anything Joomla does not look up by
convention belongs in a `JBS_` family.

Plugin, module, and library extensions follow normal Joomla convention with their own
`PLG_` / `MOD_` / `LIB_` prefixes — the `JBS_` exception is the component's.

## 2. English variety — American

Measured: **3 British spellings against 226 American**. This is already an American-English codebase;
the rule records reality rather than changing it.

Write American spellings in values: *organize*, *customize*, *color*, *behavior*, *center*,
*catalog*, *authorize*, *analyze*.

The three outliers, for a one-time pass — not an ongoing lint:

| File / line | Key | Fix |
|---|---|---|
| `admin/language/en-GB/en-GB.com_proclaim.ini:808` | `JBS_ADM_RESET_STATS_DESC` | reorganisation → reorganization |
| `admin/language/en-GB/en-GB.com_proclaim.ini:1491` | `JBS_MIG_LOCATION_WIZARD_NEEDED` | Admin Centre → Admin Center |
| `admin/language/en-GB/en-GB.com_proclaim.ini:4238` | `JBS_HEALTH_TEMPLATECODE_FILES_MISSING` | customisation → customization |

Not outliers: *analysis* and *license* are correct in both varieties (the British noun is *licence*,
but the verb and the software sense are *license* everywhere).

### Why the files are still named `en-GB`

`en-GB` is Joomla's fallback locale. Core always installs it, and any key missing from the active
language falls back to it. The directory name is a Joomla mechanism, not a claim about spelling — an
`en-US` tree would reach almost nobody (most US Joomla sites run `en-GB`, because that is what the
installer gives them) while doubling every string and giving translators two English sources to
reconcile. **Do not create one.** American English lives inside the `en-GB` files.

The cost is that Proclaim's strings sit next to core's "Customise" and "Colour" in the same admin
screen. That is accepted, and it is common among extensions with US audiences.

## 3. Glossary — one word per concept

The real inconsistency in this codebase is vocabulary, not spelling. Use the canonical term:

| Concept | Use | Not | Current split |
|---|---|---|---|
| A single teaching item | **message** | sermon, study | 365 / 87 / 30 |
| Who delivered it | **teacher** | speaker, presenter | 174 / 11 / 0 |
| An attached file | **media file** | mediafile | 128 / 11 |
| A grouping of messages | **series** | — | 180 |
| Audio distribution | **podcast** | — | 173 |

`message` is canonical because it already leads 365 to 87, so it is the least churn, and it covers
teaching content that is not literally a sermon.

**Do not mass-rename existing strings to match.** Apply the glossary to new and edited strings; a
sweep of the 87 `sermon` uses is a separate, deliberate decision, because changing user-visible
wording invalidates translator review state even though it does not break the translations
themselves (those key off key names).

Where `sermon` or `study` appears in a **key name**, leave it — renaming keys breaks translations.
The glossary governs values.

## 4. `.ini` mechanics

- **Spacing:** `KEY = "Value"` with spaces. Currently 4,080 spaced against 1,375 tight — the spaced
  form is the house style; do not convert existing tight lines wholesale, but write new keys spaced.
- **Values are always double-quoted.** Joomla's parser requires it.
- **No apostrophe escaping needed** inside double quotes, but a literal `"` must be written `"_QQ_"`.
- **Placeholders:** `%s` (246 uses) and `%d` (152). Use positional forms `%1$s` / `%2$d` whenever a
  string has more than one placeholder, so translators can reorder them — 18 keys already do.
  The count and type of placeholders must match the `Text::sprintf()` call site exactly.
- **Plurals** go through `Text::plural()`, which needs the `_0` / `_1` / `_MORE` suffix set (31 keys
  today). Do not hand-build "1 item(s)".
- **Sentence case** for labels and buttons ("Media file", not "Media File"). Title case only where
  Joomla core uses it for the same element type.
- **`_DESC` keys are full sentences** with terminal punctuation; label keys are not.

## 5. Reviewing a change

Before accepting a new or edited string:

1. Does the key use an existing `JBS_` family — or `COM_` only because Joomla looks it up by name?
2. American spelling in the value?
3. Canonical glossary term — *message*, *teacher*, *media file*?
4. `KEY = "Value"`, double-quoted, spaced?
5. Placeholders positional where there is more than one, and matching the `Text::sprintf()` call?
6. If a key was **renamed or removed**, is every translation file updated? A renamed key silently
   falls back to `en-GB` and looks like an untranslated string.
