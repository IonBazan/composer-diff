<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Formatter\Helper\Table;

class MarkdownTableFormatter extends MarkdownFormatter
{
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, bool $withUrls): void
    {
        $this->renderSingle($prodEntries, 'Prod Packages', $withUrls);
        $this->renderSingle($devEntries, 'Dev Packages', $withUrls);
    }

    public function renderSingle(DiffEntries $entries, string $title, bool $withUrls): void
    {
        if (!\count($entries)) {
            return;
        }

        $rows = [];

        foreach ($entries as $entry) {
            $row = $this->getTableRow($entry, $withUrls);

            if ($withUrls) {
                $row[] = $this->formatUrl($this->getUrl($entry), 'Compare');
            }

            $rows[] = $row;
        }

        $table = new Table($this->output);
        $headers = [$title, 'Operation', 'Base', 'Target'];

        if ($withUrls) {
            $headers[] = 'Link';
        }

        $table->setHeaders($headers)->setRows($rows)->render();
        $this->output->writeln('');
    }

    /**
     * @return string[]
     */
    private function getTableRow(DiffEntry $entry, bool $withUrls): array
    {
        $operation = $entry->getOperation();
        $packageName = $this->getDecoratedPackageName($entry);
        $packageUrl = $withUrls ? $this->formatUrl($this->getProjectUrl($entry), $packageName) : $packageName;

        if ($operation instanceof InstallOperation) {
            return [
                $packageUrl ?: $packageName,
                '<fg=green>New</>',
                '-',
                $operation->getPackage()->getFullPrettyVersion(),
            ];
        }

        if ($operation instanceof UpdateOperation) {
            return [
                $packageUrl ?: $packageName,
                $entry->isChange() ? '<fg=magenta>Changed</>' : ($entry->isUpgrade() ? '<fg=cyan>Upgraded</>' : '<fg=yellow>Downgraded</>'),
                $operation->getInitialPackage()->getFullPrettyVersion(),
                $operation->getTargetPackage()->getFullPrettyVersion(),
            ];
        }

        if ($operation instanceof UninstallOperation) {
            return [
                $packageUrl ?: $packageName,
                '<fg=red>Removed</>',
                $operation->getPackage()->getFullPrettyVersion(),
                '-',
            ];
        }

        throw new \InvalidArgumentException('Invalid operation');
    }
}
