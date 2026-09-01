A patch release fixing three faults on the Servers screens, one of which stopped you setting up a server at all. If you manage servers in Proclaim, take this one.

## You could not choose a server type

Picking a type when creating or editing a server replaced the screen with an error page. There was no way past it, so a new server could not be set up and an existing one could not be changed.

Two separate faults were behind it, and both are fixed. The type you choose is now stored correctly, and the form returns to your server with that platform's own settings ready to fill in.

## Publishing a server from the list did nothing but show an error

Changing a server's status from the Servers list — publishing, unpublishing, archiving, trashing, or bringing a trashed server back — produced an error page instead. Checking in a server someone had left open did the same.

Every other list in Proclaim was unaffected; the Servers list alone was pointing at something that did not exist. It now works as the other lists do.

## Health checks no longer report content you have thrown away

System Health counted trashed messages, media files and images as work still to do. Most visibly, a **restricted media** warning could be raised about a sermon that had been deleted — a security notice about content nobody can reach.

Trashed content is no longer counted. Unpublished and archived content still is, because that is content that will show again.

## Findings now tell you what to do about them

Several health findings described a problem and stopped. They now say what the remedy is, and where it does not lie with Proclaim they say that too — an unenforced protected media folder is a web server setting, and the finding says so rather than implying a switch exists somewhere in the component.

The protected media folder also no longer warns that files kept there are publicly downloadable when nothing is kept there.

## Finding the media a warning is about

When Proclaim reported that restricted recordings were reachable by anyone holding their address, the button opened the whole media library. On a site with thousands of files that is not something you can act on.

It now opens the list filtered to restricted media, with a **Visibility** filter you can see and clear like any other. On our test data that is the difference between 112 rows and 14.

## Settings that were never reachable

Two fields were asked for by a screen but never declared by its form, so they simply did not appear:

- The site-wide **Custom CSS** tab showed a description and no editor. It never worked, no site could have saved anything in it, and it has been removed. Custom CSS on a **template** is unaffected and is where styling belongs.
- The Comments screen offered an **Access** field that could not appear. Comments take their visibility from the message they belong to, which is now also how the moderation list decides what to show you — previously it could hide comments left by visitors from anyone who was not a Super User.

## Requirements

Joomla 5.4 or later, PHP 8.3 or later. Unchanged.
