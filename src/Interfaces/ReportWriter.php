<?php

namespace ImagesCrawler\Interfaces;

interface ReportWriter
{
    /**
     * @param array $row
     */
    public function addRecord(array $row) : void;
}
