<?php

namespace ImagesCrawler\Command;

use ImagesCrawler\Interfaces\Command;

final class Help implements Command
{
    /**
     * @var string[]
     */
    private $commands;

    /**
     * @param string ...$commands Class names of the commands what will display in the help
     */
    public function __construct(string ...$commands)
    {
        $this->commands = $commands;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'help';
    }

    /**
     * @return string
     */
    public static function getDescription(): string
    {
        return "\t\tDisplay this help";
    }

    /**
     * @inheritdoc
     */
    public function execute() : void
    {
        foreach ($this->commands as $command) {
            echo $command::getName() . "\t" . $command::getDescription() . PHP_EOL;
        }
    }
}
