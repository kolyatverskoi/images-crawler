<?php

namespace ImagesCrawler\Exception;

use ImagesCrawler\Url;

final class ContentNotLoaded extends \RuntimeException
{
    /**
     * @param Url $url
     */
    public function __construct(Url $url)
    {
        parent::__construct('Content not loaded for ' . $url->getValue());
    }
}
