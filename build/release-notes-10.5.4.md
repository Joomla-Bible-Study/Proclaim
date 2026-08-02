A bug fix release. If your podcast has download tracking enabled, this
release fixes a defect that could cause Apple Podcasts to stop accepting new
episodes and remove previously-published ones from your show.

## Podcast download tracking broke Apple Podcasts compliance

When **Track Downloads** was enabled on a podcast, every episode's audio
link in the RSS feed pointed to a tracking URL with no file extension
(`...&task=cwmpodcast.track&media_id=123`) and could not itself answer the
"byte-range" requests Apple's crawler uses to validate a show. Apple's own
publishing guide states the file extension "determines whether or not
content appears in the podcast directory" — on at least one production
site this caused new episodes to stop appearing in Apple Podcasts, and
earlier episodes to be shown as "no longer available."

This release fixes both issues:

- Tracked episode links now carry a real file extension (e.g. `.mp3`),
  routed through a new, cosmetic-only URL path that still resolves to the
  exact same tracking/counting logic as before.
- The tracking link itself now correctly answers Range and HEAD requests —
  the way Apple Podcasts and podcast apps check that streaming/scrubbing
  works — instead of only working after being redirected to the real file.

No action needed beyond updating. If your podcast previously stopped
appearing correctly in Apple Podcasts, allow a few days after updating for
Apple to re-crawl and restore it.

## Podcast owner/identity fields were scattered and one was invisible

Author, Editor Name, Editor Email, Publisher, and Copyright — all fields
describing who is behind a show — were spread across three different tabs
with no indication of how they relate. The Copyright field was fully wired
into the database and feed since 10.5.1 but had no visible form control at
all, so it could only ever fall back to the site name. All five fields now
live together in the Feed Owner tab, and Copyright is editable.
