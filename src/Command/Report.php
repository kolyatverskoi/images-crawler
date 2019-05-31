<?php

namespace ImagesCrawler\Command;

use ImagesCrawler\Interfaces\Command;
use ImagesCrawler\Interfaces\ReportViewer;

final class Report implements Command
{
    /**
     * @var string
     */
    private $domain;
    /**
     * @var ReportViewer
     */
    private $viewer;

    /**
     * @param string $domain
     * @param ReportViewer $viewer
     */
    public function __construct(string $domain, ReportViewer $viewer)
    {
        $this->domain = $domain;
        $this->viewer = $viewer;
    }

    /**
     * @return string
     */
    public static function getName() : string
    {
        return 'report';
    }

    /**
     * @return string
     */
    public static function getDescription() : string
    {
        return "<domain>\tdisplay report for the domain";
    }

    /**
     * Execute the command
     */
    public function execute() : void
    {
        $this->viewer->view(REPORTS_DIR . $this->domain . '.csv');
    }
}
