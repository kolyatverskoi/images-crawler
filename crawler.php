<?php

require 'vendor/autoload.php';

use ImagesCrawler\Command\Help;
use ImagesCrawler\Command\Parse;
use ImagesCrawler\Command\Report;
use ImagesCrawler\Crawler;
use ImagesCrawler\Exception\CommandNotFound;
use ImagesCrawler\Interfaces\Command;
use ImagesCrawler\Page\Loader;
use ImagesCrawler\Page\Parser\ImagesParser;
use ImagesCrawler\Page\WebPageFactory;
use ImagesCrawler\Report\Viewer;
use ImagesCrawler\Report\Writer;
use ImagesCrawler\Url;

const REPORTS_DIR = __DIR__ . '/reports/';
const THREADS_LIMIT = 20;

try {
    getCommand($argv)->execute();
} catch (CommandNotFound $e) {
    echo $e->getMessage() . PHP_EOL;
    getCommand([null, 'help'])->execute();
} catch (\Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}

/**
 * @param array $arguments
 *
 * @return Command
 * @throws InvalidArgumentException
 */
function getCommand(array $arguments) : Command
{
    if (empty($arguments[1])) {
        throw new CommandNotFound('Please use one of the following commands:');
    }

    [, $commandName] = $arguments;

    switch ($commandName) {
        case 'parse':
            $crawler = new Crawler(new Loader(THREADS_LIMIT, new WebPageFactory));
            $crawler->attach(
                new ImagesParser(
                    new Writer(
                        REPORTS_DIR . getDomain($arguments) . '.csv',
                        ['Source page', 'Image path']
                    )
                )
            );
            return new Parse(getDomain($arguments), $crawler);
        case 'report':
            return new Report(getDomain($arguments), new Viewer);
        case 'help':
            return new Help(
                Parse::class,
                Report::class,
                Help::class
            );
        default:
            throw new CommandNotFound(sprintf('Command %s not found', $commandName));
    }
}

/**
 * @param array $arguments
 *
 * @return string
 * @throws InvalidArgumentException
 */
function getDomain($arguments)
{
    if (empty($arguments[2])) {
        throw new InvalidArgumentException('<domain> is required');
    }

    return (new Url($arguments[2]))->getHost();
}
