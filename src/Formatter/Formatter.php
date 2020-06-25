<?php

namespace IonBazan\ComposerDiff\Formatter;

use Composer\DependencyResolver\Operation\OperationInterface;

interface Formatter
{
    /**
     * @param OperationInterface[] $operations
     * @param string               $title
     *
     * @return void
     */
    public function render(array $operations, $title);
}
