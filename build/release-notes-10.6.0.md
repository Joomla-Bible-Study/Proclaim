## System Health

Proclaim now has a **System Health** panel on its Administration screen. It runs 24 checks and shows you what it found, including the ones that are passing.

Until now the dashboard carried eight separate notices with three different ways of dismissing them, and once you cleared one the information was gone rather than merely quiet. Health checks stay put. Clearing a notice on the dashboard never removes it from Health, so "why is that field missing?" or "why did nothing sync?" always has somewhere to be answered.

What it reports on: your database schema, scheduled tasks, permissions, media protection, template files, the scripture library, server connections, YouTube quota, and configuration settings whose effects are easy to mistake for a broken feature.

Three are worth calling out because nothing reported them before:

- **Restricted media that is still reachable.** An access level controls the *link*, not the *file*. A restricted recording stored under your site's web root is readable by anyone who has the address, and no screen said so.
- **YouTube daily quota.** The quota resets on a fixed schedule, so running out is not something that retries — a sync simply stops part way and looks like a sync that found nothing.
- **Backups.** How long since Proclaim last took one. A restore drops every Proclaim table before it writes anything, so that is worth knowing before you need it.

## Uninstalling Proclaim no longer leaves the job half done

⚠️ If you have "drop tables on uninstall" enabled, please read this.

Uninstalling would delete Proclaim's permission records and then stop, leaving every table in place. Your sermons were still there but nothing governed access to them, and the tables that should have gone remained. This had been the case since March.

It now drops the tables first and clears the permission records afterwards, so an interruption leaves the recoverable half of the job undone rather than the unrecoverable one. Three tables the uninstall had never removed are now cleaned up too.

The uninstall also no longer touches anything belonging to another extension whose name happens to begin the same way as Proclaim's.

## Restores tell you what is still outstanding

A finished restore used to report one line saying it worked. It now lists what was actually restored — tables, scheduled tasks, template files, configuration — and separately what still needs your attention, or says plainly that nothing does.

It will tell you if media records were restored without their files, which is the normal state after moving to a new server: a database backup carries the records, not the recordings.

## Template layouts

Custom template layouts were being written where the front end does not read them, so an edit could appear saved and change nothing. They are now written to the folder the site actually renders from, and exporting a template and importing it again produces the template you exported.

## Fixes

- Playlists are now included in Proclaim's permission maintenance. Their permission records were invisible to the Assets screen and to the clean-up tools, and a backup could not reattach them.
- The layout editor's element controls no longer run off the edge of a narrow card. They collapse into a menu instead, so every control stays reachable — including by keyboard.
- The permissions Clean Up screen now shows the section rows it would remove, instead of reporting fewer than it was about to act on.
- Simple Mode no longer discards a message's description, scripture references or teachers when saving.
- One copy of each translation is shipped, rather than two that could disagree.

## Accessibility

Element controls in the layout editor meet the minimum target size, and remain operable by keyboard when collapsed into a menu.

## Requirements

Joomla 5.4 or later, PHP 8.3 or later.
