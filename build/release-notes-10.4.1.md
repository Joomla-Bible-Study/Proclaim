If you have ever downloaded a Bible translation for offline use, please read the
first section — earlier updates were deleting those downloads, and this release
stops that. It also fixes the Bible version switcher, which has not worked on
sermon pages since 10.3.0, and repairs media icons that were rendering as empty
boxes.

## Downloaded Bible translations are no longer lost when you update

Updating Proclaim was deleting every Bible translation you had downloaded for
offline use, along with the cached passages fetched from online providers. The
Local Translations panel came back looking like a fresh install, with nothing
marked as downloaded.

The cause was in the scripture library rather than Proclaim itself. Joomla has no
way to update a library in place — it uninstalls the old one and installs the new
one — and the library's uninstall step removed its own database tables. On a real
uninstall that is correct. On an update it destroyed data, silently, every time.

This release blocks that on all three paths an update can take: through the
Proclaim package, through the scripture package, and through Joomla's Update
Manager when only the library is updated. There is no configuration to change.

**If a previous update already wiped your translations**, they do not come back
on their own:

* The two translations Proclaim ships with — King James Version and World English
  Bible — re-download by themselves shortly after this update, through the
  scheduled task that handles background downloads.
* Anything else you had downloaded needs downloading again, from
  **Proclaim → Admin Center → Scripture**. Your settings, API keys and provider
  choices were never affected — only the downloaded verse text.

Cached passages from GetBible or API.Bible are also protected now. Losing those
was less serious, since they refill themselves, but each one cost a fresh request
to the provider.

## The Bible version switcher works again

Switching translations on a sermon page did nothing at all. The dropdown opened,
you chose a version, and the passage never changed — no error, nothing in the log.

It broke in 10.3.0 when the scripture code moved into its own library and the
switcher's script started looking for its settings under a different name than
Proclaim was publishing. Confusingly, it *did* work on pages where the Scripture
Links plugin had already run, which is why the fault came and went depending on
the page.

The scripture autocomplete in the sermon editor was broken by the same mismatch —
its book list has been empty since 10.3.0 — and is fixed too.

Both need at least one translation available: if an earlier update wiped your
downloads, download one first or the switcher will still have nothing to switch
to.

## Media icons render properly again

YouTube, Vimeo and video icons were showing as empty boxes or blank squares on
sermon pages and in the media list.

An earlier migration converted stored icon settings to Font Awesome 6 but put
brand icons in the wrong font family, where the glyph simply does not exist. This
release corrects the stored settings for you when you update, and Proclaim now
handles the brand icons correctly whatever is in your database — including on
sites whose settings were imported from another install and never converted at
all.

## Accessibility

Two buttons in the administrator failed the WCAG 2.2 AA contrast requirement:
the *Fix database* action on the control panel notice, and any warning-coloured
button placed inside a coloured alert. Both are corrected, and the accessibility
scan now checks every button style in every alert context on each release, rather
than only the ones a page happens to show.

## Removing the scripture library is now refused while Proclaim needs it

If you try to uninstall **CWM Scripture Library** while Proclaim or the Scripture
Links plugin is installed, Joomla now refuses and tells you which extension still
depends on it. Previously the uninstall succeeded and took the shared Bible tables
with it, leaving Proclaim unable to display scripture.

Uninstall the extensions that use it first, and removal works as before. The
library also detects extensions that use it without registering, so a third-party
extension's downloaded translations are protected too.

## Under the hood

* Release packages are now checked against their sources, so a file left behind
  by an old build cannot ship in a release. The published 1.1.6 scripture library
  had carried three such files for months.
* The upgrade test suite now plants a downloaded translation, cached passages and
  an unregistered dependent extension before upgrading, and fails if any of them
  are harmed — including a deliberate check that the old fault is still detectable,
  so the protection cannot quietly stop working.
