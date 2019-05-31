<?php

namespace ImagesCrawler\Page;

use ImagesCrawler\Interfaces\WebPage;
use ImagesCrawler\Url;

class WebPageFactory
{
    /**
     * @param string $url
     * @param string $html
     *
     * @return WebPage
     */
    public function createWebPage(string $url, string $html) : WebPage
    {
        return new Page(new Url($url), $html);
    }
}
