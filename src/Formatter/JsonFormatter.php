<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use IonBazan\ComposerDiff\PackageDiff;

class JsonFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function render(array $prodOperations, array $devOperations, $withUrls)
    {
        $this->format(array(
            'packages' => $this->transformOperations($prodOperations, $withUrls),
            'packages-dev' => $this->transformOperations($devOperations, $withUrls),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function renderSingle(array $operations, $title, $withUrls)
    {
        $this->format($this->transformOperations($operations, $withUrls));
    }

    /**
     * @param array<string, array<string, string|null>>|array<string, array<array<string, string|null>>> $data
     *
     * @return void
     */
    private function format(array $data)
    {
        $this->output->writeln(json_encode($data, 128)); // JSON_PRETTY_PRINT
    }

    /**
     * @param OperationInterface[] $operations
     * @param bool                 $withUrls
     *
     * @return array<array<string, string|null>>
     */
    private function transformOperations(array $operations, $withUrls)
    {
        $rows = array();

        foreach ($operations as $operation) {
            $row = $this->transformOperation($operation);

            if ($withUrls) {
                $row['compare'] = $this->getUrl($operation);
            }

            $rows[$row['name']] = $row;
        }

        return $rows;
    }

    /**
     * @return array<string, string|null>
     */
    private function transformOperation(OperationInterface $operation)
    {
        if ($operation instanceof InstallOperation) {
            return array(
                'name' => $operation->getPackage()->getName(),
                'operation' => 'install',
                'version_base' => null,
                'version_target' => $operation->getPackage()->getFullPrettyVersion(),
            );
        }

        if ($operation instanceof UpdateOperation) {
            return array(
                'name' => $operation->getInitialPackage()->getName(),
                'operation' => PackageDiff::isUpgrade($operation) ? 'upgrade' : 'downgrade',
                'version_base' => $operation->getInitialPackage()->getFullPrettyVersion(),
                'version_target' => $operation->getTargetPackage()->getFullPrettyVersion(),
            );
        }

        if ($operation instanceof UninstallOperation) {
            return array(
                'name' => $operation->getPackage()->getName(),
                'operation' => 'remove',
                'version_base' => $operation->getPackage()->getFullPrettyVersion(),
                'version_target' => null,
            );
        }

        throw new \InvalidArgumentException('Invalid operation');
    }
}
