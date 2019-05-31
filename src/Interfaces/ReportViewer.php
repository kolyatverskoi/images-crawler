<?php

namespace ImagesCrawler\Interfaces;

interface ReportViewer
{
    /**
     * @param string $filePath
     */
    public function view(string $filePath) : void;
}