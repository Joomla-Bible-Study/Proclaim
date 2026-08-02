Same-day follow-up to 10.5.4: closes an SSRF (server-side request forgery)
gap in the podcast download-tracking proxy introduced by that release.

10.5.4 replaced the tracking redirect with a server-side proxy for
externally-hosted media, so the tracking link itself could correctly answer
the Range/HEAD requests Apple Podcasts uses to validate a show. That proxy
fetched the configured media URL itself and relayed the response to the
caller, rather than sending the visitor's own browser/app to fetch it — a
real difference in capability: an admin-configured server path pointed at
an internal host (by mistake, or via a compromised admin account) could
have turned the public tracking endpoint into a gateway into the site's
internal network.

This release adds a guard that resolves the target host and refuses
anything that isn't a public, routable address before any request is made,
pins the connection to prevent DNS-rebinding around that check, stops
following redirects (which could point anywhere the guard never
validated), and restricts the proxy to HTTP/HTTPS only.

No action needed beyond updating. Sites that only ran 10.5.4 briefly, with
no internal hosts configured as media servers, were not meaningfully
exposed in practice — but updating removes the gap regardless.
