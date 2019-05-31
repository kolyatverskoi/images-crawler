<?php

namespace ImagesCrawler\Page;

use DOMDocument;
use DOMElement;
use ImagesCrawler\Exception\ContentNotLoaded;
use ImagesCrawler\Interfaces\WebPage;
use ImagesCrawler\Url;
use RuntimeException;

final class Page extends DOMDocument implements WebPage
{
    /**
     * @var Url
     */
    private $url;
    /**
     * @var Url[]
     */
    private $links = [];
    /**
     * @var string
     */
    private $html;

    /**
     * @param Url $url
     * @param string $html
     * @throws RuntimeException when html is not loaded
     */
    public function __construct(Url $url, string $html)
    {
        parent::__construct('1.0', 'utf-8');

        $this->url = $url;

        if (!@$this->loadHTML($html)) {
            throw new ContentNotLoaded($url);
        }
        $this->html = $html;

        foreach ($this->getElementsByTagName('a') as $link) {
            /** @var DOMElement|Url $link */
            $link = new Url($link->getAttribute('href'));
            if ($link->isRelative()) {
                $link = $link->withHost($url->getHost());
            }
            if ($url->getHost() === $link->getHost()) {
                $this->links[] = $link->withNormalizedPath();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getUrl() : Url
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function getLinks() : array
    {
        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function getContent() : string
    {
        return $this->html;
    }
}
