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
                (string) $entry->getBaseVersion(),
                (string) $entry->getTargetVersion(),
            ];

            if ($withLicenses) {
                $row[] = implode(', ', $entry->getLicenses());
            }

            if ($withUrls) {
                $row[] = (string) $entry->getUrl();
                $row[] = (string) $entry->getProjectUrl();
            }

            $this->writeRow($row);
        }
    }

    /**
     * @param string[] $fields
     */
    private function writeRow(array $fields): void
    {
        $this->output->writeln(implode(',', array_map([$this, 'escape'], $fields)), OutputInterface::OUTPUT_RAW);
    }

    /**
     * Quotes fields as described in RFC 4180. fputcsv() is not used because it cannot disable its non-standard escape character before PHP 7.4.
     */
    private function escape(string $field): string
    {
        if (!preg_match('/[",\r\n]/', $field)) {
            return $field;
        }

        return '"'.str_replace('"', '""', $field).'"';
    }
}
