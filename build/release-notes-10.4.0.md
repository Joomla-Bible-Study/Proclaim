Proclaim can now run your YouTube live stream. If your church streams straight
to YouTube — no restreaming service in between — Proclaim creates the weekly
broadcast for you, and after this release the entire admin area also meets the
WCAG 2.2 AA accessibility standard, verified by automated scans on every view.

## Live broadcasts on YouTube

Set up once, then going live each week is two steps: tick a box, start your
encoder.

* **Proclaim schedules the broadcast.** On a new media file, switch on *Create
  Live Broadcast* and save — a broadcast appears on YouTube, scheduled at the
  media file's date, titled after the sermon. Nothing is ever created
  automatically; you switch it on per sermon.
* **Your encoder is configured once.** Proclaim provisions one reusable stream
  for the server, and the connection details — RTMP address and stream key —
  live in the server's new *Live Streaming* settings, with the key kept masked
  until you choose to reveal it. Point OBS (or your hardware encoder) at it
  once and it works for every broadcast after.
* **Optionally, going live is automatic.** With the server's *"Go live when
  the encoder starts"* switch on, starting the encoder starts the stream — no
  YouTube Studio visit at any step. It is off by default so a sound check
  cannot go live by accident.
* **The recording takes care of itself.** The broadcast's address *is* the
  media file's address, so when the stream ends, your sermon page plays the
  recording — and playlist assignment and description sync treat it like any
  other video.
* **No orphans left behind.** Delete the media file before the stream happens
  and Proclaim removes the scheduled broadcast from YouTube too. A broadcast
  that is live or already recorded is never touched — a recording is never
  deleted.
* **Restreaming churches are unaffected.** If Resi, Restream or similar
  creates your broadcasts, set the server to *External* (the default) and none
  of this appears — the restreamer stays in charge.
* Uses your existing YouTube account connection — **no re-authorisation
  needed**.

## Accessibility: WCAG 2.2 AA

Every administrator screen and every public page now passes automated
WCAG 2.2 Level AA scans — contrast, labels, names, focus — and the scans run
on every release so it stays that way.

* **Reordering works without dragging.** Every orderable list (sermons,
  teachers, locations, and the rest) has Move Up / Move Down buttons beside
  the drag handle, and the layout editor's blocks and landing-page sections
  have movement buttons too — full keyboard and single-tap support.
* Low-contrast text, unlabeled form fields, and inaccessible tooltip and
  dialog markup were fixed across the admin — including three problems in
  Joomla itself that Proclaim now repairs on its own screens and has reported
  to the Joomla project.

## Fixed

* The media file **Options** tab no longer shows a stray, non-functional
  *Live Broadcast* section for YouTube servers.
* Opening a **brand-new Topic** no longer prints a PHP deprecation notice on
  servers that display errors.
* The **Terms of Use** page no longer prints PHP warnings when opened without
  a media file to download.
* Assorted spelling corrections in admin text.

## For developers

* A REST API acceptance test now installs the real release package on a
  disposable Joomla site — in continuous integration, nightly — and proves
  the API answers correctly before any release ships. The same guard runs in
  `composer test:release`.
* The E2E suite's login is retried, session-cached, and scoped to the sites a
  test run actually uses; local prerequisites are documented in
  `tests/e2e/README.md`.
