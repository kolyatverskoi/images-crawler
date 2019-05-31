<?php

namespace ImagesCrawler\Command;

use ImagesCrawler\Interfaces\Command;
use ImagesCrawler\Interfaces\WebCrawler;
use ImagesCrawler\Url;

final class Parse implements Command
{
    /**
     * @var string
     */
    private $domain;
    /**
     * @var WebCrawler
     */
    private $crawler;

    public function __construct(string $domain, WebCrawler $crawler)
    {
        $this->domain = $domain;
        $this->crawler = $crawler;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'parse';
    }

    /**
     * @return string
     */
    public static function getDescription(): string
    {
        return "<domain>\tScans the domain: collect image urls and it page urls. Creates a CSV report.";
    }

    public function execute() : void
    {
        $start = new \DateTime();
        $url = new Url($this->domain);
        echo $url->getValue() . ' parsing started...' . PHP_EOL;
        $this->crawler->crawl($url);
        $finish = new \DateTime();
        $time = $finish->diff($start)->format('%ad %hh %im %ss');
        echo 'file:///' . REPORTS_DIR . $url->getHost() . '.csv' . PHP_EOL;
        echo "Parsing finished in $time!" . PHP_EOL;
    }
}
