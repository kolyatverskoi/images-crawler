<?php

namespace ImagesCrawler\Interfaces;

use DOMElement;
use DOMNodeList;
use ImagesCrawler\Url;

interface WebPage
{
    /**
     * @return Url
     */
    public function getUrl() : Url;

    /**
     * @return Url[]
     */
    public function getLinks() : array;

    /**
     * @param string $tagName
     *
     * @return DOMNodeList
     */
    public function getElementsByTagName($tagName);

    /**
     * @param string $elementId
     *
     * @return DOMElement
     */
    public function getElementById($elementId);

    /**
     * @return string
     */
    public function getContent() : string;
}
