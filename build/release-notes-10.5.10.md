Proclaim 10.5.10 is a maintenance release about installing and updating: what the installer does to your update settings, what happens when a step fails partway, and what a backup is allowed to touch.

## Your site was being pointed at a channel that can never offer it an update

The Proclaim installer's finish step rewrote the component's update settings to a channel that stops at 9.2.8 — 9.x to 10.x is a migration rather than an in-place update, so that channel is not meant to announce 10.x at all. It ran on essentially every pass of the routine, including the "no database changes detected" one.

The same step also undid the cleanup that removes the old component-owned update entry, which is why the old stream kept coming back after it appeared to have been removed. It was removed, then written straight back.

Updates belong to the Proclaim package, which declares its own channel. Where the package is installed the component now keeps no update entry of its own, so your site polls once instead of twice and Proclaim stops showing up as a separate component update. Component-only installs, which predate the package, still get a channel — the one carrying current releases.

If your site accumulated a stale entry, updating removes it for you — the package's finish step retires component-owned update entries once the package holds one on the current channel. There is nothing to do by hand.

## A failed step no longer throws the whole install away

If a postflight step failed, Joomla rolled the entire install back — after the files were already copied and the database already migrated. That turned a small, recoverable problem into a half-installed site.

Postflight failures are now reported rather than fatal. The install completes, and you are told what did not finish.

Relatedly, an update that finds a migration's work already done now skips it and carries on, instead of treating it as a failure and aborting.

## Backups leave the scripture library alone

Proclaim's backup and restore identified tables by name prefix, which swept in four tables belonging to the CWM Scripture library. Restoring a Proclaim backup could therefore overwrite scripture data Proclaim does not own — including on sites where another extension shares that library.

Backup and restore now cover Proclaim's own tables only.

## The update report covers the whole update

10.5.9 began timing and reporting migrations. That report now covers the rest of the postflight work too, so the summary at the end accounts for the whole update rather than one phase of it.

## Also fixed

- Seed data aliases are written as readable text rather than hex, so they are legible in a database client.
- Table-existence checks now ask the database directly instead of matching a name pattern, which could match tables belonging to another extension.

## Scripture

Ships CWM Scripture Library 1.1.18 and the CWM Scripture package 1.2.11.

## For developers

Most of this release is work on the release gate itself — the install, upgrade, frontend and uninstall probes that run before every Proclaim release. Several were passing while asserting nothing, or stopping at the first failure and hiding the rest; those are fixed and are why the user-visible fixes above were found. Every migration is now executed against a scratch database on each pull request, which nothing did before.
