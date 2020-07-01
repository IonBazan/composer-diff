<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\OperationInterface;

interface Formatter
{
    /**
     * @param OperationInterface[] $operations
     * @param string               $title
     * @param bool                 $withUrls
     *
     * @return void
     */
    public function render(array $operations, $title, $withUrls);

    /**
     * @return string|null
     */
    public function getUrl(OperationInterface $operation);
}
