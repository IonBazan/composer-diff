<?php declare(strict_types=1);

namespace IonBazan\ComposerDiff\Diff;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;

class DiffEntry
{
    public const TYPE_INSTALL = 'install';
    public const TYPE_UPGRADE = 'upgrade';
    public const TYPE_DOWNGRADE = 'downgrade';
    public const TYPE_REMOVE = 'remove';
    public const TYPE_CHANGE = 'change';

    /** @var OperationInterface */
    private $operation;

    /** @var string */
    private $type;

    public function __construct(OperationInterface $operation)
    {
        $this->operation = $operation;
        $this->type = $this->determineType();
    }

    public function getOperation(): OperationInterface
    {
        return $this->operation;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isInstall(): bool
    {
        return self::TYPE_INSTALL === $this->type;
    }

    public function isUpgrade(): bool
    {
        return self::TYPE_UPGRADE === $this->type;
    }

    public function isDowngrade(): bool
    {
        return self::TYPE_DOWNGRADE === $this->type;
    }

    public function isRemove(): bool
    {
        return self::TYPE_REMOVE === $this->type;
    }

    public function isChange(): bool
    {
        return self::TYPE_CHANGE === $this->type;
    }

    private function determineType(): string
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
