Proclaim's REST API works in this release. On every earlier 10.3.x release it
returned "not found" at every address, on every site — so if you tried it and
gave up, that was not your configuration.

**Upgrading is recommended for every site.** Two admin faults below could stop
you reaching Proclaim's settings at all.

## The REST API

* **Turning it on is one step** — enable the *Web Services - Proclaim* plugin
  under **System → Plugins**. There is no separate Proclaim setting to hunt for.
* **Reads need an API token by default.** If you want an open, read-only feed,
  switch on *Allow public reads* in that same plugin. Creating, updating and
  deleting always require a token either way.
* **Twelve kinds of content are available.** Sermons, teachers, series,
  podcasts, media files, topics, locations and study types can be read and
  written. Playlists, comments, servers and templates are read-only on purpose.
* **Nothing is exposed that the caller could not already see.** Every response
  is limited to the access levels of whoever is asking, so restricted content
  stays restricted. Media-server credentials and commenter email addresses are
  never returned to anyone.
* **Changes are recorded.** Edits made through the API appear in Joomla's Action
  Logs alongside edits made in the admin, each marked with where it came from,
  so an audit shows both together.

## Fixed

* Proclaim's **Options** screen could fail to open with a *"Cannot access offset
  of type string on string"* error, depending on how a site's text-filter
  settings were stored.
* Saving **Options** could lock you out of Proclaim entirely, returning you to
  the licence screen no matter how many times you accepted it. A site already
  stuck can clear the Joomla cache and accept once more.
