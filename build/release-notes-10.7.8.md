Proclaim 10.7.8 brings the bundled CWM Scripture up to 1.2.21, with its security fix, and fixes PHP warnings on admin pages and on newer PHP versions.

## Security fix in the bundled CWM Scripture

Proclaim now bundles CWM Scripture 1.2.21, up from 1.2.18. It includes the security fix from CWM Scripture 1.2.20. The translation tools in the plugin's settings, and on the Scripture tab of Proclaim's Admin Center, could be used by anyone visiting your site, without logging in. A visitor could delete your downloaded Bible translations (all except KJV and WEB), or keep starting large downloads. These tools now work only in your site's administrator, for users who can manage and edit plugins. Details are in [GHSA-g2v4-h5q3-xcwh](https://github.com/Joomla-Bible-Study/CWMScriptureLinks/security/advisories/GHSA-g2v4-h5q3-xcwh).

If you already updated CWM Scripture to 1.2.20 or later on its own, your site has this fix.

## Fixes

- The media files list, the message edit form and the message picker no longer print a PHP "Deprecated" warning for a media file or message without a date.
- On PHP 8.4 and 8.5, downloading the CSV import template no longer raises a deprecation notice. On PHP 8.5, neither do streamed media downloads.
- The Validate Podcasts report no longer says "Podcast image not found" for artwork chosen with the media field.
- The setup wizard's sample message now shows its teacher.

## PHP

On a PHP version older than 8.3, Proclaim now switches itself off instead of breaking your site. That can happen when a host moves a site to an older PHP after Proclaim was installed. In the administrator, a notice explains why, and opening Proclaim returns to the dashboard. On the site, Proclaim's pages report "service unavailable" until PHP is upgraded.

PHP 8.3 is supported until Proclaim 11 is released. Proclaim 11, and the other CWM extensions from then on, require PHP 8.4 or later.
