A follow-up to 10.5.0, and worth taking if you publish a podcast. Auditing a
live feed against the RSS and Apple specifications turned up five further faults
in what Proclaim writes — including episode dates that were eight hours from the
truth on any site whose host sits in a different time zone from the one you
configured. It also settles two things 10.5.0 itself got wrong during the update.

## More podcast feed corrections

* **Episode dates use your time zone.** Dates were written in the *server's* time
  zone rather than the one set in Global Configuration. On a site configured for
  Chicago but hosted on a machine set to Los Angeles, every episode was published
  two hours adrift on the clock and eight hours adrift as a moment in time — and
  podcast apps sort episodes by that moment.
* **The channel address is absolute.** If your podcast's link is set to a menu
  item, Proclaim wrote a partial address such as `/resources/sermons.html`. RSS
  does not allow that, and feed validators reject it.
* **Seasons work in Apple Podcasts.** Season and episode numbers were written in
  a form Apple ignores, so episodes never grouped into seasons there. They are
  now written in both forms.
* **The copyright line names you.** Every feed said `© (2026) All rights
  reserved.` — stray brackets, and naming nobody. Podcasts gain a **Copyright
  Holder** field; leave it empty and Proclaim uses your site name, so existing
  feeds gain a real owner without you editing anything.
* **The feed language is no longer tied to Joomla's.** The language was limited
  to the languages installed in Joomla, and Joomla installs British English —
  so every church outside the UK published its sermons as British English with
  no way to correct it. Podcasts now have a **Feed Language** covering the codes
  directories act on. Apple and Spotify use it for language and territory
  targeting, so it is worth setting.

Rebuild your feed after updating and the corrections are in it.

## Two things 10.5.0 got wrong while updating

* **A successful update no longer reports a failure.** Updating showed a red
  "Could not delete folder" warning beside the success message. Joomla cannot
  remove a cache folder that the page doing the clearing is still using, and said
  so in a way that looked like something had gone wrong. Nothing had.
* **The cache notice no longer repeats itself.** The message naming a caching
  plugin Proclaim cannot clear for you listed the same plugin twice.
