<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\OperationInterface;

interface Formatter
{
    /**
     * @param OperationInterface[] $prodOperations
     * @param OperationInterface[] $devOperations
     * @param bool                 $withUrls
     *
     * @return void
     */
    public function render(array $prodOperations, array $devOperations, $withUrls);

    /**
     * @param OperationInterface[] $operations
     * @param string               $title
     * @param bool                 $withUrls
     *
     * @return void
     */
    public function renderSingle(array $operations, $title, $withUrls);

    /**
     * @return string|null
     */
    public function getUrl(OperationInterface $operation);
}
