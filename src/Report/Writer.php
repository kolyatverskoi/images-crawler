<?php

namespace ImagesCrawler\Report;

use ImagesCrawler\Interfaces\ReportWriter;
use RuntimeException;

final class Writer implements ReportWriter
{
    /**
     * @var string
     */
    protected $filePath;
    /**
     * @var string[] $fields
     */
    protected $fields;
    /**
     * @var resource
     */
    private $handle;

    /**
     * @param string $filePath
     * @param string[] $fields
     */
    public function __construct(string $filePath, array $fields)
    {
        $this->filePath = $filePath;
        $this->fields = $fields;

        if (!$handle = fopen($this->filePath, 'w+')) {
            throw new RuntimeException('Can\'t create new report file: ' . $this->filePath);
        }

        $this->handle = $handle;

        fputcsv($handle, $this->fields);
    }

    /**
     * @param array $row
     */
    final public function addRecord(array $row) : void
    {
        if (count($row) != count($this->fields)) {
            throw new \InvalidArgumentException(
                'Row is invalid: number of the row fields are not equal to the number of the report fields'
            );
        }

        fputcsv($this->handle, $row);
    }

    final public function __destruct()
    {
        fclose($this->handle);
    }
}
