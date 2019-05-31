<?php

namespace ImagesCrawler\Interfaces;

interface Command
{
    /**
     * @return string
     */
    public static function getName() : string;

    /**
     * @return string
     */
    public static function getDescription() : string;

    /**
     * Execute the command
     */
    public function execute() : void;
}
