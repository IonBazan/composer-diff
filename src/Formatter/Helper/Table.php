<?php

declare(strict_types=1);

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
    protected $headers = [];

    /**
     * @var string[][]
     */
    protected $rows = [];

    /**
     * Column widths cache.
     *
     * @var int[]
     */
    private $columnWidths = [];

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param string[] $headers
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param string[][] $rows
     */
    public function setRows(array $rows): self
    {
        $this->rows = [];

        foreach ($rows as $row) {
            $this->rows[] = $row;
        }

        return $this;
    }

    public function render(): void
    {
        $this->renderRow($this->headers);
        $this->renderHorizontalLine();

        foreach ($this->rows as $row) {
            $this->renderRow($row);
        }
    }

    /**
     * @param string[] $row
     */
    private function renderRow(array $row): void
    {
        $this->output->writeln(sprintf('| %s |', implode(' | ', $this->prepareRow($row))));
    }

    /**
     * @param string[] $row
     *
     * @return string[]
     */
    private function prepareRow(array $row): array
    {
        $line = [];

        foreach ($row as $column => $cell) {
            $line[] = $this->prepareCell($row, $column);
        }

        return $line;
    }

    private function renderHorizontalLine(): void
    {
        $line = [];

        foreach ($this->headers as $column => $cell) {
            $line[] = str_repeat('-', $this->getColumnWidth($column) + 2);
        }

        $this->output->writeln(sprintf('|%s|', implode('|', $line)));
    }

    /**
     * @param string[] $row
     */
    private function prepareCell(array $row, int $column): string
    {
        $cleanLength = OutputHelper::strlenWithoutDecoration($this->output->getFormatter(), $row[$column]);

        return sprintf('%s%s', $row[$column], str_repeat(' ', $this->getColumnWidth($column) - $cleanLength));
    }

    private function getColumnWidth(int $column): int
    {
        if (isset($this->columnWidths[$column])) {
            return $this->columnWidths[$column];
        }

        $lengths = [];

        foreach (array_merge([$this->headers], $this->rows) as $row) {
            $lengths[] = OutputHelper::strlenWithoutDecoration($this->output->getFormatter(), $row[$column]);
        }

        return $this->columnWidths[$column] = max($lengths);
    }
}
