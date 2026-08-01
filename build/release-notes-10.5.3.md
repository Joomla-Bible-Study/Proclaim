A bug fix release. If your site has been running Proclaim since before March
2026, two settings screens have been silently discarding your input since
then — this release repairs the database and both screens start saving
correctly.

## Podcasting 2.0 fields wouldn't save

Filling in **Funding/Donation URL**, **Funding Button Text**, **Content
License**, **License URL**, **Publisher Name**, **Podcast Index
Verification**, or **Publishing Schedule** on a podcast and clicking save
showed "Item saved" — and then reopening the podcast showed the field empty
again. The database columns for these fields were added to fresh installs
back in March 2026, but sites that already existed at that point never
received them, so every save of these fields was silently discarded. This
update adds the missing columns; the fields will save normally afterward.

## Linking a teacher to a Joomla user account had the same bug

Teachers → **User Account** had an identical gap: on any site that existed
before March 2026, choosing a Joomla user to link to a teacher record never
persisted. Fixed by the same database update.

No action needed beyond updating — both fixes are automatic once the update
completes.
