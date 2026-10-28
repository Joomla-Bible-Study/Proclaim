Proclaim 10.7.3 is a maintenance release. It closes a gap that could put
restricted messages into your public podcast feed, ships a patched HTTP library
for the YouTube integration, and fixes a set of faults that broke pages,
discarded images, or sent migrated media links to the wrong address.

## Rebuild your podcast feeds after updating

A podcast feed is written to a file that podcast apps read without logging in.
Since 10.5.8, the feed was filtered by the access of whoever *built* it, so a
feed rebuilt while an administrator was logged in could list messages meant
only for registered users or other restricted groups. The feed is now always
built as a guest would see it.

The scheduled podcast task also crashed whenever it ran from the command line
(for example from cron), so feeds on those sites may not have been updating.
Both are fixed. Rebuild each feed once after updating so the file on disk
reflects the corrected access rules.

## Security update for the YouTube integration

The YouTube server addon bundles its own copy of the Guzzle HTTP library. That
copy is updated to 7.15.2, which fixes GHSA-v5mv-p594-2x33 (a host check that
could be bypassed) and GHSA-f7vp-7xgx-4w4r (cookie scope). The addon only talks
to Google's own servers, so practical exposure was low, but the vulnerable
version is no longer shipped.

## Images with spaces in their names

A message or series image whose filename contains a space — common for photos
uploaded straight from a phone or named like "Pastor John 2024.jpg" — failed
validation on save, and the image was cleared. Such images also failed to show
on the site, including the default images set in Administrative Settings. Both
are fixed; teacher images, which already worked, are unchanged.

## Media servers

- **Migrating legacy servers** could drop a file's remote host, so a migrated
  link to an MP3 on another site pointed at your own site instead and returned
  "not found", while the migration reported success. The host is now kept, and
  migrated servers get their permission records.
- **Legacy-type servers** can no longer be chosen for new media. Media already
  on one keeps working and can still be edited; the server picker simply stops
  offering them for new assignments.

## Pages that no longer break

- The **single message page** returned an error for a message with no teacher.
- The **custom series list layout** returned an error; the custom series
  layouts are retired in favour of the standard ones, which every template
  already supports.
- The **Proclaim module**, in its simple layout, showed blank scripture and
  media with warnings, and could return an error depending on how a setting
  had been saved. Its teacher filter also returned messages by other teachers.
- **Structured data (Schema.org)** for a message could be printed twice, or
  disagree between the edit form and the front end, depending on how the row
  was stored.
- The **REST API** now answers "not found" rather than a server error for a
  media file or server that does not exist.

## Admin forms

A round of fixes to individual form fields:

- Multi-select filters use the searchable chip picker again instead of a plain
  list box.
- Numeric settings were silently accepting any text; they are numeric fields
  again.
- Closing the series picker popup without choosing no longer leaves the series
  locked for editing.
- The multi-campus location default, and older stored icon choices, now show as
  selected.
- Two fields that displayed a raw language key, and the YouTube module's Yes/No
  switches, display correctly.

## Also in this release

Updated scripture library (lib_cwmscripture 1.1.24, ScriptureLinks 1.2.17),
API-originated action-log entries are now filterable, a broad modernisation of
database query code, much fuller automated test data behind the release checks,
and dependency updates across the board.
