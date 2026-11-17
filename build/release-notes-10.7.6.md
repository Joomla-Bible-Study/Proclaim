Proclaim 10.7.6 fixes the message list's filters and paging on sites that use
Joomla's cache.

## The message list filters and pages on cached sites

On a site with Joomla's system cache turned on, the message list's filters,
search and page links showed "Could not load results. Please try again." for
nearly every visitor, from 10.5.6 on. The cached list carried a security token
that belonged to whichever visit filled the cache, and Proclaim refused every
other visitor's request.

The list now filters, searches and pages for every visitor, whether the page
comes from the cache or not. Nothing needs to change in your site's settings.

If your site was also affected by the problem 10.7.5 fixed, where the list's
menu item has an alias such as `sermons`, this is the second half of the same
symptom: after 10.7.5 the list's requests reached Proclaim, and on a cached
site they were then refused.
