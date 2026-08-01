If you publish a podcast, this release is worth taking. An audit of a live feed
found four faults that make podcast directories reject episodes or ignore their
details, and all four are fixed here. Alongside them, Proclaim can now re-read a
media file's size, type and length on demand — so records that were wrong, or
that went stale when you replaced a file, can be put right without editing them
by hand.

## Your podcast feed now passes validation

Four faults, each invisible until a directory refuses the episode.

* **Episode length is written correctly.** A sermon of 27 minutes 7 seconds was
  published as `00:27:7` rather than `00:27:07`. Apple's validator rejects that
  outright; several apps fall back to reading the length from the file, and some
  simply show nothing. Any episode whose seconds, minutes or hours were entered
  as a single digit was affected — in the feed we audited, one episode in eight.
* **Episode files are described accurately.** The file type published with each
  episode came from whatever had been stored on the record, and the fallback
  value Proclaim used — `audio/mpeg3` — is not a real media type. Feeds were
  also declaring M4A files as MP3. The type is now worked out from the file
  itself, so it matches what subscribers actually download.
* **Episode identity is declared as an identity.** The tag podcast apps use to
  recognise an episode was being offered without saying what it is, which
  entitles an app to treat it as a web address and try to fetch it. It is now
  marked as an identifier, which is what it has been since 10.4.0.
* **Addresses use https.** On any site that had not changed the setting, every
  address in the feed — artwork, links, the episode files themselves — was
  written as `http://`. On a secure site that means a redirect for every one,
  and browsers may warn about the artwork. New installs and any site that never
  touched the setting now use `https://`.

Nothing needs doing to pick these up: rebuild your feed after updating and the
corrections are in it.

## Correcting media file details

Size, type and length are detected when you add a media file. Until now, if a
detail was wrong — or the file was replaced later — there was no way to ask
Proclaim to look again.

* **Re-detect Metadata.** A button on the media file edit screen reads the size,
  type and length from the file again and replaces what is stored. This is the
  one to use when a file has been replaced but kept its name, where the stored
  details are stale rather than missing.
* **Re-read every file.** The *Fix Missing Metadata* tool, under Podcasts →
  Validate, gains a *Re-read every file* option that applies the same thing in
  bulk. It is off by default and says why: it reads every file, so it is slower,
  and on YouTube or similar it spends one API call per video.
* **Clearing a detail asks for it again.** Emptying the file size and saving now
  re-runs detection for it — the obvious thing to try, which previously did
  nothing at all.
* **Mismatches are reported.** Feed validation now tells you when a file's
  stored type disagrees with the file itself, and the fix button beside it
  corrects those records rather than passing over them as already set.

## The sermons list

* **The filter panel starts closed.** It was opening on every visit, pushing the
  sermons themselves down the page. It now opens when you ask for it, or when a
  filter is actually applied — matching the rest of Joomla.
* **Filtering is half the work it was.** Each change of a filter was quietly
  asking the server for results twice. Now it asks once, so results arrive
  sooner on a busy site.

## Smaller corrections

* **Choose Playlist(s)** no longer appears on media files whose server cannot
  hold playlists, where it could only ever be an empty box.
* **Lengths entered by hand are stored properly**, which also makes those
  records visible to the tools meant to repair them. A length typed as `7` was
  being read as "already set" by every repair and detection routine in Proclaim,
  so precisely the records that needed fixing were the ones being skipped.
* **The file-type list no longer offers `audio/mp3`**, which is not a registered
  type and was the only MP3 choice available — which is how feeds came to
  publish it in the first place. Existing records keep working; re-detect them
  when convenient.
* **Caches are cleared after an update**, and any third-party cache Proclaim
  cannot clear is named so you can clear it yourself. A page held in cache can
  otherwise point at script files the update replaced.
