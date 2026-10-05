<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use Symfony\Component\Console\Output\OutputInterface;

class CsvFormatter extends AbstractFormatter
{
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, bool $withUrls, bool $withLicenses): void
    {
        $this->writeHeader($withUrls, $withLicenses);
        $this->writeEntries($prodEntries, 'prod', $withUrls, $withLicenses);
        $this->writeEntries($devEntries, 'dev', $withUrls, $withLicenses);
    }

    public function renderSingle(DiffEntries $entries, string $title, bool $withUrls, bool $withLicenses): void
    {
        $this->writeHeader($withUrls, $withLicenses);
        $this->writeEntries($entries, $title, $withUrls, $withLicenses);
    }

    private function writeHeader(bool $withUrls, bool $withLicenses): void
    {
        $header = ['section', 'name', 'direct', 'operation', 'version_base', 'version_target'];

        if ($withLicenses) {
            $header[] = 'licenses';
        }

        if ($withUrls) {
            $header[] = 'compare';
            $header[] = 'link';
        }

        $this->writeRow($header);
    }

    private function writeEntries(DiffEntries $entries, string $section, bool $withUrls, bool $withLicenses): void
    {
        /** @var DiffEntry $entry */
        foreach ($entries as $entry) {
            $row = [
                $section,
                $entry->getPackageName(),
                $entry->isDirect() ? 'true' : 'false',
                $entry->getType(),
                $entry->getBaseVersion(),
                $entry->getTargetVersion(),
            ];

            if ($withLicenses) {
                $row[] = implode(', ', $entry->getLicenses());
            }

            if ($withUrls) {
                $row[] = $entry->getUrl();
                $row[] = $entry->getProjectUrl();
            }

            $this->writeRow($row);
        }
    }

    /**
     * @param array<string|null> $fields
     */
    private function writeRow(array $fields): void
    {
        $stream = fopen('php://memory', 'w+');
        assert(false !== $stream);
        // PHP 8.4 deprecates relying on the default escape character
        fputcsv($stream, $fields, ',', '"', '\\');
        rewind($stream);
        $this->output->write(stream_get_contents($stream), false, OutputInterface::OUTPUT_RAW);
    }
}
