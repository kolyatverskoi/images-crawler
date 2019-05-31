<?php

namespace ImagesCrawler;

/**
 * Url value object.
 *
 * @package ImagesCrawler
 */
final class Url
{
    const DOMAIN_REGEXP = '/^((?:[a-z0-9-]+\.)+(?:[a-z0-9-]+))/';
    const FALLBACK_SCHEME = 'http';

    /**
     * @var null|string
     */
    private $scheme;
    /**
     * @var null|string
     */
    private $host;
    /**
     * @var null|string
     */
    private $path;
    /**
     * @var null|string
     */
    private $query;
    /**
     * @var null|string
     */
    private $fragment;

    /**
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->scheme = parse_url($url, PHP_URL_SCHEME);
        $this->host = parse_url($url, PHP_URL_HOST);
        $this->path = parse_url($url, PHP_URL_PATH);
        $this->query = parse_url($url, PHP_URL_QUERY);
        $this->fragment = parse_url($url, PHP_URL_FRAGMENT);

        $this->negotiateHostPathScheme();
    }

    /**
     * @param string $scheme
     *
     * @return Url
     */
    public function withScheme(string $scheme) : self
    {
        $url = clone $this;
        $url->scheme = $scheme;
        $url->negotiateHostPathScheme();

        return $url;
    }

    /**
     * @param string $host
     *
     * @return Url
     */
    public function withHost(string $host) : self
    {
        $url = clone $this;
        // hack for correctly host determination
        // eg. parse_url('www.test.com', PHP_URL_HOST) => null
        $url->host = (new Url($host))->getHost();

        $url->negotiateHostPathScheme();

        return $url;
    }

    /**
     * Normalize path
     *
     *  - removes dot segments
     *  - removes doubles of slash
     *
     * @link https://tools.ietf.org/html/rfc3986#section-5.2.4
     *
     * @return self
     */
    public function withNormalizedPath() : self
    {
        $pathOld = preg_replace('/(\/)+/', '/', $this->path);

        $a = '/^(\.\.\/|\.\/)/';
        $b = '/(^\/\.\/)|(^\/\.$)/';
        $c = '/^(\/\.\.\/|\/\.\.)/';
        $d = '/^(\.|\.\.)$/';
        $e = '/(\/*[^\/]*)/';
        $lastSegment = '/\/([^\/]+)$/';

        $pathNew = '';

        while (!empty($pathOld)) {
            if (preg_match($a, $pathOld)) {
                $pathOld = preg_replace($a, '', $pathOld);
            } elseif (preg_match($b, $pathOld)) {
                $pathOld = preg_replace($b, '/', $pathOld);
            } elseif (preg_match($c, $pathOld, $matches)) {
                $pathOld = preg_replace('/^' . preg_quote($matches[1], '/') . '/', '/', $pathOld);
                $pathNew = preg_replace($lastSegment, '', $pathNew);
            } elseif (preg_match($d, $pathOld)) {
                $pathOld = preg_replace($d, '', $pathOld);
            } else {
                if (preg_match($e, $pathOld, $matches)) {
                    $firstSegment = $matches[1];
                    $pathOld = preg_replace('/^' . preg_quote($firstSegment, '/') . '/', '', $pathOld, 1);
                    $pathNew .= $firstSegment;
                }
            }
        }

        if (0 !== strpos($pathNew, '/')) {
            $pathNew = '/' . $pathNew;
        }

        $url = clone $this;
        $url->path = $pathNew;

        $url->negotiateHostPathScheme();

        return $url;
    }

    /**
     * @return Url
     */
    public function withoutFragment() : self
    {
        $url = clone $this;
        $url->fragment = null;

        return $url;
    }

    /**
     * @return bool
     */
    public function isAbsolute() : bool
    {
        return (bool)$this->host;
    }

    /**
     * @return bool
     */
    public function isRelative() : bool
    {
        return !$this->host;
    }

    /**
     * @param Url $url
     *
     * @return bool
     */
    public function equals(Url $url) : bool
    {
        return $this->getValue() === $url->getValue();
    }

    /**
     * @return string
     */
    public function getValue() : string
    {
        $url = '';

        if ($this->scheme) {
            $url .= $this->scheme . '://';
        }

        if ($this->host) {
            $url .= $this->host;
        }

        if ($this->path) {
            $url .= $this->path;
        }

        if ($this->query) {
            $url .= '?' . $this->query;
        }

        if ($this->fragment) {
            $url .= '#' . $this->fragment;
        }

        return $url;
    }

    /**
     * @return string|null
     */
    public function getScheme() : ?string
    {
        return $this->scheme;
    }

    /**
     * @return string|null
     */
    public function getHost() : ?string
    {
        return $this->host;
    }

    /**
     * @return string|null
     */
    public function getPath() : ?string
    {
        return $this->path;
    }

    /**
     * @return string|null
     */
    public function getQuery() : ?string
    {
        return $this->query;
    }

    /**
     * @return string|null
     */
    public function getFragment() : ?string
    {
        return $this->fragment;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->getValue();
    }

    /**
     * When url is changed we need to update host, path and scheme
     */
    private function negotiateHostPathScheme() : void
    {
        // host and path
        if ($this->host && !preg_match(self::DOMAIN_REGEXP, $this->host)) {
            $this->host = null;
        }
        if (!$this->host &&
            0 !== strpos($this->path, '/') &&
            preg_match(self::DOMAIN_REGEXP, $this->path, $matches)
        ) {
            $this->path = preg_replace(self::DOMAIN_REGEXP, '', $this->path); // todo "3.html" - it's not a domain
            $this->host = $matches[0];
        }

        // scheme
        if (!$this->scheme && $this->host) {
            $this->scheme = self::FALLBACK_SCHEME;
        }
    }
}
