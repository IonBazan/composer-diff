<?php

namespace IonBazan\ComposerDiff\Formatter\Helper;

use Symfony\Component\Console\Output\OutputInterface;

class Table
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var string[]
     */
    protected $headers = array();

    /**
     * @var string[][]
     */
    protected $rows = array();

    /**
     * Column widths cache.
     *
     * @var int[]
     */
    private $columnWidths = array();

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param string[] $headers
     *
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param string[][] $rows
     *
     * @return $this
     */
    public function setRows(array $rows)
    {
        $this->rows = array();

        foreach ($rows as $row) {
            $this->rows[] = $row;
        }

        return $this;
    }

    /**
     * @return void
     */
    public function render()
    {
        $this->renderRow($this->headers);
        $this->renderHorizontalLine();

        foreach ($this->rows as $row) {
            $this->renderRow($row);
        }
    }

    /**
     * @param string[] $row
     *
     * @return void
     */
    private function renderRow(array $row)
    {
        $this->output->writeln(sprintf('| %s |', implode(' | ', $this->prepareRow($row))));
    }

    /**
     * @param string[] $row
     *
     * @return string[]
     */
    private function prepareRow(array $row)
    {
        $line = array();

        foreach ($row as $column => $cell) {
            $line[] = $this->prepareCell($row, $column);
        }

        return $line;
    }

    /**
     * @return void
     */
    private function renderHorizontalLine()
    {
        $line = array();

        foreach ($this->headers as $column => $cell) {
            $line[] = str_repeat('-', $this->getColumnWidth($column) + 2);
        }

        $this->output->writeln(sprintf('|%s|', implode('|', $line)));
    }

    /**
     * @param string[] $row
     * @param int      $column
     *
     * @return string
     */
    private function prepareCell(array $row, $column)
    {
        $cleanLength = OutputHelper::strlenWithoutDecoration($this->output->getFormatter(), $row[$column]);

        return sprintf('%s%s', $row[$column], str_repeat(' ', $this->getColumnWidth($column) - $cleanLength));
    }

    /**
     * @param int $column
     *
     * @return int
     */
    private function getColumnWidth($column)
    {
        if (isset($this->columnWidths[$column])) {
            return $this->columnWidths[$column];
        }

        $lengths = array();

        foreach (array_merge(array($this->headers), $this->rows) as $row) {
            $lengths[] = OutputHelper::strlenWithoutDecoration($this->output->getFormatter(), $row[$column]);
        }

        return $this->columnWidths[$column] = max($lengths);
    }
}
