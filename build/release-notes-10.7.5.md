Proclaim 10.7.5 is a maintenance release. It fixes filtering, searching and
paging on the message list for sites whose list has an address ending in a
name like `sermons`, and updates the libraries Proclaim bundles.

## The message list filters and pages again

Proclaim redirects links left over from the old Bible Study component to the
pages that replaced them. Since 10.2.0, that redirect also caught some of
Proclaim's own requests. If the menu item for your message list has the alias
`sermons`, or another name the old component used for a page, such as
`latest`:

- choosing a filter, searching, or moving to another page showed "Could not
  load results. Please try again.";
- only the first page of the list could be reached.

Proclaim now leaves its own requests alone, and the list filters, searches and
pages as it should. Old Bible Study links, including old download links, still
redirect.

Two related fixes:

- On a site installed in a subfolder, such as `example.org/church/`, old
  message and landing-page links were sent to an address that named the folder
  twice, and failed. They now stay within the folder.
- An old landing-page link on a site whose message list is the home page now
  goes to the home page.

## Also in this release

- The YouTube addon's Google API library is updated to 2.20.1, with its
  authentication library at 1.55.3. These are maintenance updates, and nothing
  changes in how the addon behaves.
- The bundled CWM Scripture package is updated to 1.2.18, with library 1.1.25.
  Both are maintenance releases, and nothing changes in how scripture behaves.
