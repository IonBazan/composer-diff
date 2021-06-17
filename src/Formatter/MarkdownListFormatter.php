<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;

class MarkdownListFormatter extends MarkdownFormatter
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

        $this->output->writeln($title);
        $this->output->writeln(str_repeat('=', strlen($title)));
        $this->output->writeln('');

        foreach ($entries as $entry) {
            $this->output->writeln($this->getRow($entry, $withUrls));
        }

        $this->output->writeln('');
    }

    /**
     * @param bool $withUrls
     *
     * @return string
     */
    private function getRow(DiffEntry $entry, $withUrls)
    {
        $url = $withUrls ? $this->formatUrl($this->getUrl($entry), 'Compare') : null;
        $url = (null !== $url) ? ' '.$url : '';
        $operation = $entry->getOperation();

        if ($operation instanceof InstallOperation) {
            return sprintf(
                ' - Install <fg=green>%s</> (<fg=yellow>%s</>)%s',
                $operation->getPackage()->getName(),
                $operation->getPackage()->getFullPrettyVersion(),
                $url
            );
        }

        if ($operation instanceof UpdateOperation) {
            return sprintf(
                ' - %s <fg=green>%s</> (<fg=yellow>%s</> => <fg=yellow>%s</>)%s',
                ucfirst($entry->getType()),
                $operation->getInitialPackage()->getName(),
                $operation->getInitialPackage()->getFullPrettyVersion(),
                $operation->getTargetPackage()->getFullPrettyVersion(),
                $url
            );
        }

        if ($operation instanceof UninstallOperation) {
            return sprintf(
                ' - Uninstall <fg=green>%s</> (<fg=yellow>%s</>)%s',
                $operation->getPackage()->getName(),
                $operation->getPackage()->getFullPrettyVersion(),
                $url
            );
        }

        throw new \InvalidArgumentException('Invalid operation');
    }
}
