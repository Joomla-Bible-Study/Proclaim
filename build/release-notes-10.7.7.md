Proclaim 10.7.7 fixes links in the format of older Proclaim versions, so they
open the right message, teacher or series at its current address.

## Old links reach the right page

Sites upgraded from Proclaim 9 or earlier still get visitors on links in the
old format, from search engines, bookmarks and shared posts. Proclaim sends
those visitors on to the current page, but some of the links failed:

- An old link that did not start with a menu item's address, such as
  `/sermon/671-title/1`, ended on "page not found".
- An old link that carried only a message's number, such as
  `/sermons/sermon/671`, was not recognized and also ended on "page not found".
- Old download links went to a plain `index.php?...` address instead of the
  site's search engine friendly one.

Old links now go to the same address the site uses for that message, teacher
or series everywhere else, built from the site's own URL settings: search
engine friendly URLs, URL rewriting and the URL suffix. A link to a message
that is unpublished or no longer exists still shows "page not found".

If you added a rule to your server's configuration, such as `.htaccess`, to
redirect old Proclaim links yourself, those links never reach Proclaim. Update
to 10.7.7 first, then remove the rule.
