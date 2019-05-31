<?php

namespace ImagesCrawler\Interfaces;

use ImagesCrawler\Url;
use SplSubject;

interface WebCrawler extends SplSubject
{
    /**
     * @param Url $domain
     */
    public function crawl(Url $domain) : void;

    /**
     * @return null|WebPage
     */
    public function getLastCrawled() : ?WebPage;
}
