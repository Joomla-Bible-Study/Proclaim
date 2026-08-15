Proclaim 10.5.9 is a maintenance release, and its subject is the update itself. Updating Proclaim is now much faster on a site with real content, and the update tells you what it did instead of leaving you watching a spinner.

## Updating is faster, and it explains itself

Every Proclaim update used to re-run every data migration written since 10.0, each one reading whole tables to discover it had nothing to do. On a site with years of sermons in it, that is what made a routine update take minutes.

Each migration is now gated on the version you are updating from. It runs once, on the update that actually needs it, and is skipped from then on.

The update also reports on itself now. When it finishes, it lists which migrations ran, which were skipped and how long each took, together with the time spent unpacking and copying files before that. The same record is written to a log file, so an update can still be looked at days later, on a site that has already been updated.

## The download button may look different

If you never chose a style for the sermon download button, it was rendering as an outline button — a style the settings form does not offer and you could not have picked. It now renders solid, matching what the form says it is, and the label is readable against the button colour.

This is visible on upgrade. If you prefer a different style, Proclaim's options now offer every variant the button can actually use.

## Also fixed

- Comment moderation buttons in the admin no longer depend on inline scripts, so they keep working on sites that enforce a Content Security Policy.
- Removed a superseded layout and two dialogs that nothing could open.

## Scripture

Ships CWM Scripture Library 1.1.18 and the CWM Scripture package 1.2.10.
