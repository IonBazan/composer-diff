<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

class GitHubFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function render(DiffEntries $prodEntries, DiffEntries $devEntries, $withUrls)
    {
        $this->renderSingle($prodEntries, 'Prod Packages', $withUrls);
        $this->renderSingle($devEntries, 'Dev Packages', $withUrls);
    }

    /**
     * {@inheritdoc}
     */
    public function renderSingle(DiffEntries $entries, $title, $withUrls)
    {
        if (!\count($entries)) {
            return;
        }

        $message = str_replace("\n", '%0A', implode("\n", $this->transformEntries($entries, $withUrls)));
        $this->output->writeln(sprintf('::notice title=%s::%s', $title, $message));
    }

    /**
     * @param bool $withUrls
     *
     * @return string[]
     */
    private function transformEntries(DiffEntries $entries, $withUrls)
    {
        $rows = array();

        foreach ($entries as $entry) {
            $rows[] = $this->transformEntry($entry, $withUrls);
        }

        return $rows;
    }

    /**
     * @param bool $withUrls
     *
     * @return string
     */
    private function transformEntry(DiffEntry $entry, $withUrls)
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
