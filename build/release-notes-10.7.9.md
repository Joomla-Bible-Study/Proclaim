Proclaim 10.7.9 makes the REST API work with the Access Web Services
permission that Joomla 5.4.11 and 6.2.1 added, and closes the API's public
mode. It also makes the comment captcha work again, and keeps podcast feeds
building when one podcast can't be.

## The REST API and Joomla's Access Web Services permission

Joomla 5.4.11 and 6.2.1 added a permission, Access Web Services, that a user
needs on a component before any API request to it is accepted. Proclaim now
declares it:

- You can grant it on Proclaim alone, in **Components > Proclaim > Options >
  Permissions**. Before, the only place to grant it was Global Configuration,
  where a grant opens the API of every component.
- Saving Proclaim's Options no longer removes it. When you updated Joomla, it
  gave Access Web Services to every group with the Manage permission, and the
  next save of Proclaim's Options could take that away again.
- Proclaim doesn't grant it to anyone when it installs, just as Joomla doesn't
  for its own components, so giving an account API access stays a deliberate
  step.

If an API client gets "403 Forbidden" after you update Joomla, grant its user
group Access Web Services on Proclaim. The
[REST API page](https://github.com/Joomla-Bible-Study/Proclaim/wiki/REST-API)
on the wiki has the steps.

## The API has no public mode

The **Allow public reads** option on the Web Services - Proclaim plugin is
removed. Every API request now needs a token. To publish your messages for
anyone to read, use Proclaim's RSS and podcast feeds, which don't use the API.

If you had public reads turned on, a client that reads without a token now
gets "401 Unauthorized". Give it a token for a user that has API access.

## Other API changes

- A delete that Joomla refuses, such as for an item that isn't trashed yet,
  now answers "409 Conflict" on every supported Joomla version. On Joomla 5.4,
  and on 6.0 to 6.2.0 when a plugin vetoed the delete, it answered "204 No
  Content", and the action log recorded a deletion that didn't happen.
- Proclaim's API log records requests refused for lacking a permission,
  including the ones Joomla refuses before Proclaim sees them.
- Action log entries for messages changed through the API name the message,
  not just its number.

## The comment captcha works again

The comment form on a message shows a captcha again when Proclaim's
**Options > Captcha**, or your site's default captcha, names an enabled
captcha plugin. Before, a site with a default captcha refused every comment.
To turn the captcha off for comments only, set Proclaim's Options > Captcha to
None.

When a comment fails the captcha check, the message now says so, instead of
"Incorrect Key".

## Podcasts

- One podcast that can't be built no longer stops the others. The remaining
  feeds are still written, and the one that failed is named and logged.
- A podcast needs a title, a website and a file name to be saved, including
  through a batch edit.
- A new System Health check, **Podcast feed settings**, lists the published
  podcasts that are missing settings their feed needs.
- An episode whose media file has no date takes its message's date, and is
  ordered by it. Before, it stopped the whole feed build, or was the first
  episode the episode limit left out even when it was the newest.
- The podcast the setup wizard creates now builds.

## Control panel

- **Start Tour** appears only while Joomla's System - Guided Tours plugin is
  enabled.
- The Getting Started post-install message's link starts the Getting Started
  tour.
