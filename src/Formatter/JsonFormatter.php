<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

class JsonFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, bool $withUrls, bool $withLicenses): void
    {
        $this->format([
            'packages' => $this->transformEntries($prodEntries, $withUrls, $withLicenses),
            'packages-dev' => $this->transformEntries($devEntries, $withUrls, $withLicenses),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function renderSingle(DiffEntries $entries, string $title, bool $withUrls, bool $withLicenses): void
    {
        $this->format($this->transformEntries($entries, $withUrls, $withLicenses));
    }

    /**
     * @param array<string, array<string, string|null>>|array<string, array<array<string, string|null>>> $data
     */
    private function format(array $data): void
    {
        // @phpstan-ignore argument.type
        $this->output->writeln(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function transformEntries(DiffEntries $entries, bool $withUrls, bool $withLicenses): array
    {
        $rows = [];

        /** @var DiffEntry $entry */
        foreach ($entries as $entry) {
            $row = $entry->toArray();

            if (!$withUrls) {
                unset($row['compare'], $row['link']);
            }

            if (!$withLicenses) {
                unset($row['licenses']);
            }

            $rows[$row['name']] = $row;
        }

        return $rows;
    }
}
