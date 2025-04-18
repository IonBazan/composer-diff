<?php

namespace IonBazan\ComposerDiff\Formatter;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

class JsonFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, $withUrls, $withLicenses)
    {
        $this->format(array(
            'packages' => $this->transformEntries($prodEntries, $withUrls, $withLicenses),
            'packages-dev' => $this->transformEntries($devEntries, $withUrls, $withLicenses),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function renderSingle(DiffEntries $entries, $title, $withUrls, $withLicenses)
    {
        $this->format($this->transformEntries($entries, $withUrls, $withLicenses));
    }

    /**
     * @param array<string, array<string, string|null>>|array<string, array<array<string, string|null>>> $data
     *
     * @return void
     */
    private function format(array $data)
    {
        // @phpstan-ignore argument.type
        $this->output->writeln(json_encode($data, 128)); // JSON_PRETTY_PRINT
    }

    /**
     * @param bool $withUrls
     * @param bool $withLicenses
     *
     * @return array<array<string, mixed>>
     */
    private function transformEntries(DiffEntries $entries, $withUrls, $withLicenses)
    {
        $rows = array();

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
