<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use Symfony\Component\Console\Output\OutputInterface;

class CsvFormatter extends AbstractFormatter
{
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, bool $withUrls, bool $withLicenses, bool $withMetadata): void
    {
        $this->writeHeader($withUrls, $withLicenses, $withMetadata);
        $this->writeEntries($prodEntries, 'prod', $withUrls, $withLicenses, $withMetadata);
        $this->writeEntries($devEntries, 'dev', $withUrls, $withLicenses, $withMetadata);
    }

    public function renderSingle(DiffEntries $entries, string $title, bool $withUrls, bool $withLicenses, bool $withMetadata): void
    {
        $this->writeHeader($withUrls, $withLicenses, $withMetadata);
        $this->writeEntries($entries, $title, $withUrls, $withLicenses, $withMetadata);
    }

    private function writeHeader(bool $withUrls, bool $withLicenses, bool $withMetadata): void
    {
        $header = ['section', 'name', 'direct', 'operation', 'version_base', 'version_target'];

        if ($withMetadata) {
            $header = array_merge($header, ['reference_field', 'reference_base', 'reference_target', 'type_base', 'type_target']);
        }

        if ($withLicenses) {
            $header[] = 'licenses';
        }

        if ($withUrls) {
            $header[] = 'compare';
            $header[] = 'link';
        }

        $header[] = 'effective';
        $this->writeRow($header);
    }

    private function writeEntries(DiffEntries $entries, string $section, bool $withUrls, bool $withLicenses, bool $withMetadata): void
    {
        /** @var DiffEntry $entry */
        foreach ($entries as $entry) {
            $data = $entry->toArray();
            $row = [
                $section,
                $entry->getPackageName(),
                $entry->isDirect() ? 'true' : 'false',
                $entry->getType(),
                $data['version_base'],
                $data['version_target'],
            ];

            if ($withMetadata) {
                $row = array_merge($row, $entry->getMetadata());
            }

            if ($withLicenses) {
                $row[] = implode(', ', $entry->getLicenses());
            }

            if ($withUrls) {
                $row[] = $entry->getUrl();
                $row[] = $entry->getProjectUrl();
            }

            $row[] = $entry->isEffective() ? 'true' : 'false';
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
        // Drop the "\n" added by fputcsv() so rows end with PHP_EOL like other formatters
        $this->output->writeln(substr(stream_get_contents($stream), 0, -1), OutputInterface::OUTPUT_RAW);
    }
}
