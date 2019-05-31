<?php

namespace ImagesCrawler\Report;

use ImagesCrawler\Interfaces\ReportViewer;

final class Viewer implements ReportViewer
{
    /**
     * @param string $filePath
     *
     * @return void
     */
    public function view(string $filePath) : void
    {
        exec("column -ts, < $filePath", $output);
        echo implode("\n", $output) . PHP_EOL;
    }
}
