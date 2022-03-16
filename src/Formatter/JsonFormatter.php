<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

class JsonFormatter extends AbstractFormatter
{
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, bool $withUrls): void
    {
        $this->format([
            'packages' => $this->transformEntries($prodEntries, $withUrls),
            'packages-dev' => $this->transformEntries($devEntries, $withUrls),
        ]);
    }

    public function renderSingle(DiffEntries $entries, string $title, bool $withUrls): void
    {
        $this->format($this->transformEntries($entries, $withUrls));
    }

    /**
     * @param array<string, array<string, string|null>>|array<string, array<array<string, string|null>>> $data
     */
    private function format(array $data): void
    {
        $this->output->writeln(json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * @return array<array<string, string|null>>
     */
    private function transformEntries(DiffEntries $entries, bool $withUrls): array
    {
        $rows = [];

        foreach ($entries as $entry) {
            $row = $this->transformEntry($entry);

            if ($withUrls) {
                $row['compare'] = $this->getUrl($entry);
                $row['link'] = $this->getProjectUrl($entry->getOperation());
            }

            $rows[$row['name']] = $row;
        }

        return $rows;
    }

    /**
     * @return array<string, string|null>
     */
    private function transformEntry(DiffEntry $entry): array
    {
        $operation = $entry->getOperation();

        if ($operation instanceof InstallOperation) {
            return [
                'name' => $operation->getPackage()->getName(),
                'operation' => $entry->getType(),
                'version_base' => null,
                'version_target' => $operation->getPackage()->getFullPrettyVersion(),
            ];
        }

        if ($operation instanceof UpdateOperation) {
            return [
                'name' => $operation->getInitialPackage()->getName(),
                'operation' => $entry->getType(),
                'version_base' => $operation->getInitialPackage()->getFullPrettyVersion(),
                'version_target' => $operation->getTargetPackage()->getFullPrettyVersion(),
            ];
        }

        if ($operation instanceof UninstallOperation) {
            return [
                'name' => $operation->getPackage()->getName(),
                'operation' => $entry->getType(),
                'version_base' => $operation->getPackage()->getFullPrettyVersion(),
                'version_target' => null,
            ];
        }

        throw new \InvalidArgumentException('Invalid operation');
    }
}
