<?php

namespace ImagesCrawler\Interfaces;

use Generator;
use ImagesCrawler\Exception\ContentNotLoaded;
use ImagesCrawler\Url;

interface WebPageLoader
{
    /**
     * @param Url[] $urls
     *
     * @return Generator
     */
    public function load(array $urls) : Generator;
}
