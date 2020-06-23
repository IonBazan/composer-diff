<?php

namespace IonBazan\ComposerDiff;

use Symfony\Component\Console\Output\OutputInterface;

class MarkdownTable
{
    /**
     * @var OutputInterface
     */
    protected $output;

    protected $headers = array();

    protected $rows = array();

    /**
     * Column widths cache.
     *
     * @var array
     */
    private $columnWidths = array();

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = array_values($headers);

        return $this;
    }

    public function setRows(array $rows)
    {
        $this->rows = array();

        return $this->addRows($rows);
    }

    public function addRows(array $rows)
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }

        return $this;
    }

    public function addRow(array $row)
    {
        $this->rows[] = array_values($row);

        return $this;
    }

    public function render()
    {
        $this->renderRow($this->headers);
        $this->renderHorizontalLine();

        foreach ($this->rows as $row) {
            $this->renderRow($row);
        }
    }

    private function renderRow(array $row)
    {
        $this->output->writeln(sprintf('| %s |', implode(' | ', $this->prepareRow($row))));
    }

    private function prepareRow(array $row)
    {
        $line = array();

        foreach ($row as $column => $cell) {
            $line[] = $this->prepareCell($row, $column);
        }

        return $line;
    }

    private function renderHorizontalLine()
    {
        $line = array();

        foreach ($this->headers as $column => $cell) {
            $line[] = str_repeat('-', $this->getColumnWidth($column) + 2);
        }

        $this->output->writeln(sprintf('|%s|', implode('|', $line)));
    }

    private function prepareCell(array $row, $column)
    {
        return str_pad($row[$column], $this->getColumnWidth($column));
    }

    private function getColumnWidth($column)
    {
        if (isset($this->columnWidths[$column])) {
            return $this->columnWidths[$column];
        }

        foreach (array_merge(array($this->headers), $this->rows) as $row) {
            $lengths[] = strlen($row[$column]);
        }

        return $this->columnWidths[$column] = max($lengths);
    }
}
