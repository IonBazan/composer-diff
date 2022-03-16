<?php

declare(strict_types=1);

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use Iterator;

class GitHubFormatter extends AbstractFormatter
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

        $message = str_replace("\n", '%0A', implode("\n", iterator_to_array($this->transformEntries($entries, $withUrls))));
        $this->output->writeln(sprintf('::notice title=%s::%s', $title, $message));
    }

    /**
     * @return Iterator<int, string>
     */
    private function transformEntries(DiffEntries $entries, bool $withUrls): Iterator
    {
        foreach ($entries as $entry) {
            yield $this->transformEntry($entry, $withUrls);
        }
    }

    private function transformEntry(DiffEntry $entry, bool $withUrls): string
    {
        $operation = $entry->getOperation();
        $url = $withUrls ? $this->getUrl($entry) : null;
        $url = (null !== $url) ? ' '.$url : '';

        if ($operation instanceof InstallOperation) {
            return sprintf(
                ' - Install %s (%s)%s',
                $operation->getPackage()->getName(),
                $operation->getPackage()->getFullPrettyVersion(),
                $url
            );
        }

        if ($operation instanceof UpdateOperation) {
            return sprintf(
                ' - %s %s (%s => %s)%s',
                ucfirst($entry->getType()),
                $operation->getInitialPackage()->getName(),
                $operation->getInitialPackage()->getFullPrettyVersion(),
                $operation->getTargetPackage()->getFullPrettyVersion(),
                $url
            );
        }

        if ($operation instanceof UninstallOperation) {
            return sprintf(
                ' - Uninstall %s (%s)%s',
                $operation->getPackage()->getName(),
                $operation->getPackage()->getFullPrettyVersion(),
                $url
            );
        }

        throw new \InvalidArgumentException('Invalid operation');
    }
}
