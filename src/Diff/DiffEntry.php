<?php

namespace IonBazan\ComposerDiff\Diff;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;

class DiffEntry
{
    const TYPE_INSTALL = 'install';
    const TYPE_UPGRADE = 'upgrade';
    const TYPE_DOWNGRADE = 'downgrade';
    const TYPE_REMOVE = 'remove';
    const TYPE_CHANGE = 'change';

    /** @var OperationInterface */
    private $operation;

    /** @var string */
    private $type;

    public function __construct(OperationInterface $operation)
    {
        $this->operation = $operation;
        $this->type = $this->determineType();
    }

    /**
     * @return OperationInterface
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isInstall()
    {
        return self::TYPE_INSTALL === $this->type;
    }

    /**
     * @return bool
     */
    public function isUpgrade()
    {
        return self::TYPE_UPGRADE === $this->type;
    }

    /**
     * @return bool
     */
    public function isDowngrade()
    {
        return self::TYPE_DOWNGRADE === $this->type;
    }

    /**
     * @return bool
     */
    public function isRemove()
    {
        return self::TYPE_REMOVE === $this->type;
    }

    /**
     * @return bool
     */
    public function isChange()
    {
        return self::TYPE_CHANGE === $this->type;
    }

    /**
     * @return string
     */
    private function determineType()
    {
        if ($this->operation instanceof InstallOperation) {
            return self::TYPE_INSTALL;
        }

        if ($this->operation instanceof UninstallOperation) {
            return self::TYPE_REMOVE;
        }

        if ($this->operation instanceof UpdateOperation) {
            $upgrade = VersionComparator::isUpgrade($this->operation);

            if (null === $upgrade) {
                return self::TYPE_CHANGE;
            }

            return $upgrade ? self::TYPE_UPGRADE : self::TYPE_DOWNGRADE;
        }

        return self::TYPE_CHANGE;
    }
}
