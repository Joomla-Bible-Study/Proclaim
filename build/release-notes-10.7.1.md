Proclaim 10.7.1 is a maintenance release. It closes two ways credentials could
end up in server logs, hardens how template code is written to disk, fixes
three faults that only ever appeared on a freshly installed site, and stops two
places where the admin quietly discarded your work.

## Please rotate these keys after updating

Two settings screens used to send credentials as part of the web address rather
than in the request body. Web servers, proxies and analytics routinely record
full addresses, so those values may be sitting in log files.

- Your **AI provider API key**, if you use the AI assistant.
- **Server Test API credentials**, including the YouTube Data API key and the
  Resi client secret, if you have used the Test API button.

The release stops it happening. It cannot remove what was already written, so
issue new keys and retire the old ones.

## Template code is written more carefully

A template code record becomes a real PHP file in your site. Three faults in
how that file was named and placed are fixed: the filename is now validated on
every path that writes one, a record can no longer overwrite a layout the
package itself ships, and a template's title can no longer decide where an
export is written or what a download is called.

Permissions for template code are also clearer. Because those files are PHP
that your site runs, being allowed to create or edit them is as powerful as
being a Super User. The permissions screen now says so on the three settings
that carry it, the edit form says it above the editor, and System Health reports
any group that has it without being a Super User. Nothing is taken away — if
delegating this is deliberate, the finding can be dismissed.

## Fixed on newly installed sites

- The **Layout Editor** tab showed a Joomla error page instead of the editor.
  It affected every fresh install; a site that had been configured never saw it.
- Two accessibility faults: the control panel and location wizard now meet the
  WCAG AA contrast requirement, and article information lists are marked up so a
  screen reader can read them.

## The admin no longer discards your work

- **Saving Administrative Settings** removed every System Health finding you had
  dismissed, so they all came back. Settings that have no field on the form are
  now kept.
- **A save that fails validation** repopulated the form from the stored record
  rather than from what you had typed, silently reverting your edits. Eleven
  screens were affected, including messages, teachers, series, locations,
  podcasts, comments and templates.

## System Health

- The summary chips are now filters. Click *Needs attention* to see only what
  needs looking at; click again, or *Show all*, to go back. The panel still opens
  showing everything.
- A new check reports Schema.org records stored where the edit form cannot see
  them, which can happen on sites upgraded from older versions.
- A new check reports which user groups can write template code, as above.

## Also in this release

Better test coverage behind the scenes: the browser tests now run on every
change against both Joomla 5 and Joomla 6, the nightly checks can report their
own failures, and dependency updates across the board.
