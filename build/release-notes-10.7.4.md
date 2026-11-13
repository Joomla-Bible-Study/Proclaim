Proclaim 10.7.4 is a maintenance release. It gives every message and series an
address of its own, lets Save as Copy start the next episode in a series, sends
the comment emails that were not going out, and makes the YouTube module's live
status update by itself.

## Every message and series gets its own address

Proclaim's search-engine-friendly addresses leave out record IDs, so a message
or series is found by its alias alone. Two messages, or two series, could end
up with the same alias. They then shared one address, and only one of them
could be reached.

When you update:

- Where records share an alias, the one the address already showed keeps it:
  the published (or archived) record with the lowest ID. Links that worked
  before keep working.
- Each of the others gets a numbered alias, such as `christmas-program-2`, and
  with it an address of its own.
- A message or series with no alias gets one made from its title.
- Proclaim lists every record it renamed, with a link to edit each one. The
  same list is emailed to the Super Users who receive system emails, so you get
  it even when the update ran from a site-management service, where nobody sees
  the messages on screen.

Go through that list and give each renamed record a clearer title and alias,
for example `christmas-program-2023` rather than `christmas-program-2`.

From now on:

- A message can't be saved with an alias another message already has, and the
  same goes for series. In the administrator, if you can see the record that
  has it, the error names it and links to it.
- An alias made from the title takes the next free number, with a warning, as
  Joomla does for articles.
- Save as Copy gives the copy a numbered title and a matching alias: "The Road
  to Emmaus (2)", then "(3)" for the next copy.
- The setup wizard, the CSV import, and the PreachIt and SermonSpeaker
  converters give each message and series a free alias too.
- Restoring an older backup repairs shared aliases the same way, and lists its
  renames the same way.

## Save as Copy starts the next episode

Save as Copy on a message with an episode number in a series was refused with
"Episode number 4 is already used by … in this series", because the copy kept
the original's number. Copying last week's message is the usual way to start
this week's, so the most common copy was the one that failed.

The copy now becomes the next episode in the series. A number you type on the
copy yourself is kept, and refused only if another message in the series
already has it.

## Comment emails are sent

On Joomla 5 and 6, neither of Proclaim's comment emails had been sent since
10.1.0:

- The email to the template's **Moderation Notification Email** address when a
  comment is held for approval. Visitors whose comment was held were told
  "Comment saved, but notification email could not be sent."
- The email for each new comment when the template's **Email when comments
  entered** is on.

Both are sent now.

## The YouTube module's live status updates by itself

Around a scheduled stream, the YouTube module is meant to check whether the
stream has started and update itself without a reload. Those checks never
reached the site: each page tried once, got an error, and stopped. In every
release since 10.1.0, the live badge showed whatever was true when the page
loaded, and visitors who clicked **Notify me when live** were never notified.

Both work now. When the stream starts, the badge changes to LIVE NOW, visitors
who asked are notified, and the page reloads once to show the live stream.

- The checks run every **Status Poll Interval** (two minutes by default),
  starting **Poll Window Before Event** hours before the scheduled start and
  ending **Poll Window After Event** hours after it.
- Every visitor's checks share one answer for two minutes, so a busy page costs
  one unit of your YouTube quota every two minutes, not one per visitor.
- A check only answers about the video the page is showing, so it can't be
  used to spend your quota on other videos.

## Also in this release

Dependency updates.
