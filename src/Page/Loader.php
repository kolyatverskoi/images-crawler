<?php

namespace ImagesCrawler\Page;

use Generator;
use ImagesCrawler\Exception\ContentNotLoaded;
use ImagesCrawler\Interfaces\WebPageLoader;
use ImagesCrawler\Url;
use RuntimeException;

/**
 * Loads web pages multithreaded
 *
 * @package ImagesCrawler\WebPage
 */
final class Loader implements WebPageLoader
{
    /**
     * @var resource
     */
    private $curlMulti;
    /**
     * @var int
     */
    private $threadsLimit;
    /**
     * Map url => resource
     *
     * @var resource[]
     */
    private $threads = [];
    /**
     * Chunked array of the url value objects
     *
     * @var array[]
     */
    private $queue;
    /**
     * @var WebPageFactory
     */
    private $webPageFactory;

    /**
     * @param int $threadsLimit
     * @param WebPageFactory $webPageFactory
     */
    public function __construct(int $threadsLimit, WebPageFactory $webPageFactory)
    {
        $this->threadsLimit = $threadsLimit;
        $this->curlMulti = curl_multi_init();
        $this->webPageFactory = $webPageFactory;
    }

    /**
     * @param Url[] $urls
     *
     * @return Generator
     */
    public function load(array $urls) : Generator
    {
        $this->queue = $urls;

        do {
            yield from $this->doLoad(array_splice($this->queue, 0, $this->threadsLimit));
        } while (!empty($this->queue));
    }

    /**
     * @param Url $url
     *
     * @return resource
     * @throws RuntimeException
     */
    private function initializeThread(Url $url)
    {
        if (!$thread = curl_init()) {
            throw new RuntimeException('Can\'t initialize curl');
        }

        curl_setopt($thread, CURLOPT_URL, $url->getValue());
        curl_setopt($thread, CURLOPT_HEADER, false);
        curl_setopt($thread, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($thread, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($thread, CURLOPT_TIMEOUT, 10);
        curl_setopt($thread, CURLOPT_MAXREDIRS, 10);
        curl_setopt($thread, CURLOPT_USERAGENT, 'spider');

        if ('https' === $url->getScheme()) {
            curl_setopt($thread, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($thread, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_multi_add_handle($this->curlMulti, $thread);

        return $thread;
    }

    private function executeThreadsAndWaitResponses() : void
    {
        $active = null;
        do {
            $mrc = curl_multi_exec($this->curlMulti, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($this->curlMulti) == -1) {
                continue;
            }

            do {
                $mrc = curl_multi_exec($this->curlMulti, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
    }

    /**
     * @param Url[] $urls
     *
     * @return Generator
     */
    private function doLoad(array $urls) : Generator
    {
        foreach ($urls as $url) {
            try {
                $this->threads[$url->getValue()] = $this->initializeThread($url);
            } catch (\Exception $e) {
                continue;
            }
        }

        $this->executeThreadsAndWaitResponses();

        foreach ($this->threads as $url => $thread) {
            try {
                yield $this->webPageFactory->createWebPage($url, curl_multi_getcontent($thread));
            } catch (ContentNotLoaded $e) {
                curl_multi_remove_handle($this->curlMulti, $thread);
                error_log($e->getMessage());
                continue;
            }
            curl_multi_remove_handle($this->curlMulti, $thread);
        }
    }

    public function __destruct()
    {
        curl_multi_close($this->curlMulti);
    }
}
