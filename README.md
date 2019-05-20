# ttrss-tumblr-gdpr-ua

Plugin for the RSS Reader [Tiny Tiny RSS](https://tt-rss.org/) to handle RSS feeds from Tumblr in Europe.

# What does it do?

Because of European laws for the protection of data privacy ([GDPR](https://en.wikipedia.org/wiki/General_Data_Protection_Regulation)), Tumblr's parent company Oath doesn't directly deliver its contents to users in Europe. Instead, the users are redirected to a page where they're asked to give Oath permission to handle, process etc. their data. Once that permission is given, the users can access the content they came for. For future visits, the permission is stored in a cookie.

Obviously, an automated system like TT-RSS gets tripped up if it can't just get a feed from an url like normal, but has to jump through some hoops instead. A few months ago, GregThib published [a plugin](https://github.com/GregThib/ttrss-tumblr-gdpr) that basically provided Oath with the permission cookie it expected, so TT-RSS could get to the feed.

But a short while ago, the plugin stopped working. It looks like Oath changed some detail of the permission process somewhere, so the cookie provided by Greg's plugin doesn't work anymore. Instead of delving into the murky details of the plugin (and repeating that every time Oath modifies its process), this plugin uses another "trick": If Oath thinks the request comes not from a "real" user, but a search engine's crawler, it doesn't bother with the permission page and delivers the content directly. Hopefully, they will continue to do so in the long run.

So this plugin checks whether the feed that TT-RSS needs to handle is from _tumblr.com_ or a subdomain of it. If it is, the plugin switches the user agent to the UA specified in the plugin's settings. If no UA is specified, the plugin uses GoogleBot's UA. Additionally to _tumblr.com_, you can add other domains that are hosted by Tumblr in the preferences.

# How to install?

-   Download the plugin and put it into the `plugins.local` directory of your TT-RSS installation. Alternatively, you can put it into the `plugins` directory. The directory containing the plugin _must_ be named `tumblr_gdpr_ua`.
-   Activate the plugin in TT-RSS' settings.
-   In case you want to subscribe to a feed that's hosted by Tumblr, but at a domain _other_ than _tumblr.com_ or its subdomains, add that domain to the plugin's settings.
-   In case you want to use a different user agent for feeds hosted by Tumblr than GoogleBot's UA, add that UA in the plugin's settings.
-   In case you want to subscribe to feeds from TT-RSS's public backend, you need to register the plugin as a system plugin with your TT-RSS installation. The public backend is everything using `https://your/tt-rss/installation.tld/public.php`. It's for example used when TT-RSS is registered with Firefox as a feed reader. Requests to the public backend don't authenticate any user, so no user plugins are loaded. You need to add `tumblr_gdpr_ua` to the list of system plugins in `config.php`, so it looks something like this: `define('PLUGINS', 'auth_internal, note, tumblr_gdpr_ua');`

# Thanks

Big thanks to

-   [GregThib](https://github.com/GregThib) for writing the original plugin. This plugin is, you may say, largely inspired by his.
-   [homlett](https://discourse.tt-rss.org/t/change-on-tumblr-rss-feeds-not-working/1158/96) for pointing out that the right user agent make Oath deliver the goods directly.
