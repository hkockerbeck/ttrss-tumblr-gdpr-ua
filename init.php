<?php
class Tumblr_GDPR_UA extends Plugin
{
    public function about()
    {
        return array(
          0.01,
          "Fixes Tumblr feeds for GDPR compliance by masquerading as GoogleBot
          (changing user agent). Requires curl.",
          "hkockerbeck");
    }

    public function api_version()
    {
        return 2;
    }

    public function init($host)
    {
    }
}
