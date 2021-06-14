<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\PackageDiff;

class MarkdownListFormatter extends MarkdownFormatter
{
    /**
     * {@inheritdoc}
     */
    public function render(array $prodOperations, array $devOperations, $withUrls)
    {
        $this->renderSingle($prodOperations, 'Prod Packages', $withUrls);
        $this->renderSingle($devOperations, 'Dev Packages', $withUrls);
    }

    /**
     * {@inheritdoc}
     */
    public function renderSingle(array $operations, $title, $withUrls)
    {
        if (!\count($operations)) {
            return;
        }

        $this->output->writeln($title);
        $this->output->writeln(str_repeat('=', strlen($title)));
        $this->output->writeln('');

        foreach ($operations as $operation) {
            $this->output->writeln($this->getRow($operation, $withUrls));
        }

        $this->output->writeln('');
    }

    /**
     * @param bool $withUrls
     *
     * @return string
     */
    private function getRow(OperationInterface $operation, $withUrls)
    {
        $url = $withUrls ? $this->formatUrl($this->getUrl($operation), 'Compare') : null;
        $url = (null !== $url) ? ' '.$url : '';

        if ($operation instanceof InstallOperation) {
            return sprintf(
                ' - Install <fg=green>%s</> (<fg=yellow>%s</>)%s',
                $operation->getPackage()->getName(),
                $operation->getPackage()->getFullPrettyVersion(),
                $url
            );
        }

        if ($operation instanceof UpdateOperation) {
            $isUpgrade = PackageDiff::isUpgrade($operation);

            return sprintf(
                ' - %s <fg=green>%s</> (<fg=yellow>%s</> => <fg=yellow>%s</>)%s',
                $isUpgrade ? 'Upgrade' : 'Downgrade',
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
