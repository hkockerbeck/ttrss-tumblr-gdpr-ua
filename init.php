<?php
class Tumblr_GDPR_UA extends Plugin
{
    private $host;

    public function about()
    {
        return array(
          1.0,
          "Fixes Tumblr feeds for GDPR compliance by masquerading as GoogleBot
          (changing user agent). Requires curl.",
          "hkockerbeck");
    }

    public function flags()
    {
        return array("needs_curl" => true);
    }

    public function api_version()
    {
        return 2;
    }

    public function init($host)
    {
        // store the provided reference to host
        $this->host = $host;
        // hook on some hooks ;)
        if (function_exists("curl_init")) {
            $host->add_hook($host::HOOK_SUBSCRIBE_FEED, $this);
            $host->add_hook($host::HOOK_FEED_BASIC_INFO, $this);
            $host->add_hook($host::HOOK_FETCH_FEED, $this);
            $host->add_hook($host::HOOK_PREFS_TAB, $this);
        }
    }

    // when subscribing to a new feed
    public function hook_subscribe_feed(
      $feed_data,
      $fetch_url,
      $auth_login,
      $auth_pass
    ) {
        // if the feed is hosted by Tumblr
        if ($this->is_tumblr_domain($fetch_url)) {
            // re-fetch the feed data with changed user agent
            $feed_data = $this->fetch_contents($fetch_url, $auth_login, $auth_pass);
        }

        return $feed_data;
    }

    // get basic info about a feed (title and site url, mostly)
    public function hook_feed_basic_info(
      $basic_info,
      $fetch_url,
      $owner_uid,
      $feed,
      $auth_login,
      $auth_pass
    ) {
        // if the feed is hosted by Tumblr
        if ($this->is_tumblr_domain($fetch_url)) {
            // re-fetch the feed data with changed user agent
            $contents = $this->fetch_contents($fetch_url, $auth_login, $auth_pass);

            // extract info we need from the feed data
            $parser = new FeedParser($contents);
            $parser->init();
            if (!$parser->error()) {
                $basic_info = array(
              'title' => mb_substr($parser->get_title(), 0, 199),
              'site_url' => mb_substr(rewrite_relative_url($fetch_url, $parser->get_link()), 0, 245)
          );
            }
        }

        return $basic_info;
    }

    // fetch feed to look for new articles
    public function hook_fetch_feed(
      $feed_data,
      $fetch_url,
      $owner_uid,
      $feed,
      $last_article_timestamp,
      $auth_login,
      $auth_pass
    ) {
        // if the feed is hosted by Tumblr
        if ($this->is_tumblr_domain($fetch_url)) {
            // re-fetch the feed data with changed user agent
            $feed_data = $this->fetch_contents($fetch_url, $auth_login, $auth_pass);
        }
        return $feed_data;
    }

    // segment in TT-RSS' prefs to add additional domains
    public function hook_prefs_tab($args)
    {
        if ($args != "prefPrefs") {
            return;
        }

        // replacements in the template
        $replacements = array(
          '{title}' => 'Tumblr GDPR UA',
          '{content}' => implode(PHP_EOL, $this->host->get($this, 'tumblr_domains', array())). PHP_EOL
      );

        // set up a _very_ basic template engine
        // so we don't have print out everything
        $template = file_get_contents(__DIR__."/pref_template.html");
        $template = str_replace(array_keys($replacements), array_values($replacements), $template);
        print $template;
    }

    // save data from prefs segment
    public function save()
    {
        $tumblr_domains = explode("\r\n", $_POST['tumblr_domains']);
        $tumblr_domains = array_unique(array_filter($tumblr_domains));
        $this->host->set($this, 'tumblr_domains', $tumblr_domains);
    }

    // fetch feed data with changed user agent
    private function fetch_contents(
      $fetch_url,
      $auth_login = false,
      $auth_pass = false
    ) {
        $options = array(
          'url' => $fetch_url,
          'login' => $auth_login,
          'pass' => $auth_pass,
          'useragent' => 'googlebot');
        return fetch_file_contents($options);
    }

    // helper function: does string $haystack end with string $needle?
    private function ends_with($haystack, $needle)
    {
        return mb_substr($haystack, -mb_strlen($needle)) === $needle;
    }

    // is the domain in question on tumblr.com or one of the additional domains?
    private function is_tumblr_domain($fetch_url)
    {
        // extract domain from whole url
        $url = parse_url($fetch_url, PHP_URL_HOST);
        // look through list of "known tumblr" urls
        $domains = $this->host->get($this, 'tumblr_domains', array());
        array_push($domains, 'tumblr.com');
        $found = array_filter($domains, function ($t) use ($url) {
            // does the domain in question end with a tumblr url?
            return $this->ends_with($url, $t);
        });

        return !empty($found);
    }
}
