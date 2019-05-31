<?php

namespace ImagesCrawler\Page\Parser;

use DOMElement;
use ImagesCrawler\Interfaces\ReportWriter;
use ImagesCrawler\Interfaces\WebCrawler;
use ImagesCrawler\Url;
use SplObserver;
use SplSubject;

class ImagesParser implements SplObserver
{
    /**
     * @var ReportWriter
     */
    private $report;

    /**
     * @param ReportWriter $report
     */
    public function __construct(ReportWriter $report)
    {
        $this->report = $report;
    }

    /**
     * @inheritdoc
     */
    final public function update(SplSubject $subject) : void
    {
        assert($subject instanceof WebCrawler);
        $page = $subject->getLastCrawled();
        foreach ($page->getElementsByTagName('img') as $img) {
            assert($img instanceof DOMElement);
            if (!$imgSrc = $img->getAttribute('src')) {
                // eg page with angular: <img data-ng-src="/assets/img/{[feed.image_generic]}">
                continue;
            }
            $this->report->addRecord([
                $page->getUrl()->getValue(),
                $imgSrc // todo save image absolute path?
            ]);
        }
    }
}
