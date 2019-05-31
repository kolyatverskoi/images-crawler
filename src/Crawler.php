<?php

namespace ImagesCrawler;

use ImagesCrawler\Interfaces\WebCrawler;
use ImagesCrawler\Interfaces\WebPage;
use ImagesCrawler\Interfaces\WebPageLoader;
use SplObjectStorage;
use SplObserver;

final class Crawler implements WebCrawler
{
    /**
     * @var string[] visited url
     */
    private $history;
    /**
     * @var Url[]
     */
    private $queue;
    /**
     * @var WebPageLoader
     */
    private $loader;
    /**
     * @var SplObjectStorage
     */
    private $observers;

    /**
     * @var null|WebPage
     */
    private $lastCrawled;

    /**
     * @param WebPageLoader $loader
     */
    public function __construct(WebPageLoader $loader)
    {
        $this->loader = $loader;
        $this->observers = new SplObjectStorage;
    }

    /**
     * @param Url $url
     */
    public function crawl(Url $url) : void
    {
        $this->queue[] = $url;

        do {
            foreach ($this->loader->load(array_splice($this->queue, 0)) as $page) {
                assert($page instanceof WebPage);
                $this->lastCrawled = $page;
                $this->history[] = $page->getUrl();
                $this->queue += array_diff($page->getLinks(), $this->history);

                $this->notify();
            }
        } while (!empty($this->queue));
    }

    /**
     * @return null|WebPage
     */
    public function getLastCrawled() : ?WebPage
    {
        return $this->lastCrawled;
    }

    /**
     * @inheritdoc
     */
    public function attach(SplObserver $observer) : void
    {
        $this->observers->attach($observer);
    }

    /**
     * @inheritdoc
     */
    public function detach(SplObserver $observer) : void
    {
        $this->observers->detach($observer);
    }

    /**
     * @inheritdoc
     */
    public function notify() : void
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
}
