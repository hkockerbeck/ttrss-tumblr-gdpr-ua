<?php
class Tumblr_GDPR_UA extends Plugin
{
    private $host;

    public function about()
    {
        return array(
          0.01,
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
        $this->host = $host;
        if (function_exists("curl_init")) {
            $host->add_hook($host::HOOK_SUBSCRIBE_FEED, $this);
            $host->add_hook($host::HOOK_FEED_BASIC_INFO, $this);
            $host->add_hook($host::HOOK_FETCH_FEED, $this);
        }
    }

    public function hook_subscribe_feed(
      $feed_data,
      $fetch_url,
      $auth_login,
      $auth_pass
    ) {
        return $this->fetch_contents($fetch_url, $auth_login, $auth_pass);
    }

    public function hook_feed_basic_info(
      $basic_info,
      $fetch_url,
      $owner_uid,
      $feed,
      $auth_login,
      $auth_pass
    ) {
        $contents = $this->fetch_contents($fetch_url, $auth_login, $auth_pass);

        $parser = new FeedParser($contents);
        if (!$parser->error()) {
            $basic_info = array(
              'title' => mb_substr($parser->get_title(), 0, 199),
              'site_url' => mb_substr(rewrite_relative_url($fetch_url, $parser->get_link()), 0, 245)
          );
        }

        return $basic_info;
    }

    public function hook_fetch_feed(
      $feed_data,
      $fetch_url,
      $owner_uid,
      $feed,
      $last_article_timestamp,
      $auth_login,
      $auth_pass
    ) {
        return $this->fetch_contents($fetch_url, $auth_login, $auth_pass);
    }

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
}
