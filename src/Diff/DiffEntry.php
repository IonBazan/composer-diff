<?php

namespace IonBazan\ComposerDiff\Diff;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Url\UrlGenerator;

class DiffEntry
{
    const TYPE_INSTALL = 'install';
    const TYPE_UPGRADE = 'upgrade';
    const TYPE_DOWNGRADE = 'downgrade';
    const TYPE_REMOVE = 'remove';
    const TYPE_CHANGE = 'change';

    /** @var OperationInterface */
    private $operation;

    /** @var bool */
    private $direct;

    /** @var string */
    private $type;

    /**
     * @param bool $direct
     */
    public function __construct(OperationInterface $operation, $direct = false)
    {
        $this->operation = $operation;
        $this->direct = $direct;
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
    public function isDirect()
    {
        return $this->direct;
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
     * @return PackageInterface|null
     */
    public function getPackage()
    {
        $operation = $this->getOperation();

        if ($operation instanceof UpdateOperation) {
            return $operation->getInitialPackage();
        }

        if ($operation instanceof InstallOperation || $operation instanceof UninstallOperation) {
            return $operation->getPackage();
        }

        return null;
    }

    /**
     * @return string[]
     */
    public function getLicenses()
    {
        $package = $this->getPackage();

        if (!$package instanceof CompletePackageInterface) {
            return array();
        }

        return $package->getLicense();
    }

    /**
     * @return array<string, string|bool|null|string[]>
     */
    public function toArray(UrlGenerator $generator)
    {
        $array = $this->toBaseArray();
        $array['compare'] = $this->getUrl($generator);
        $array['link'] = $this->getProjectUrl($generator);

        return $array;
    }

    /**
     * @return array<string, string|bool|null|string[]>
     */
    public function toBaseArray()
    {
        $operation = $this->getOperation();

        if ($operation instanceof InstallOperation) {
            return array(
                'name' => $operation->getPackage()->getName(),
                'direct' => $this->isDirect(),
                'operation' => $this->getType(),
                'version_base' => null,
                'version_target' => $operation->getPackage()->getFullPrettyVersion(),
                'licenses' => $this->getLicenses(),
            );
        }

        if ($operation instanceof UpdateOperation) {
            return array(
                'name' => $operation->getInitialPackage()->getName(),
                'direct' => $this->isDirect(),
                'operation' => $this->getType(),
                'version_base' => $operation->getInitialPackage()->getFullPrettyVersion(),
                'version_target' => $operation->getTargetPackage()->getFullPrettyVersion(),
                'licenses' => $this->getLicenses(),
            );
        }

        if ($operation instanceof UninstallOperation) {
            return array(
                'name' => $operation->getPackage()->getName(),
                'direct' => $this->isDirect(),
                'operation' => $this->getType(),
                'version_base' => $operation->getPackage()->getFullPrettyVersion(),
                'version_target' => null,
                'licenses' => $this->getLicenses(),
            );
        }

        throw new \InvalidArgumentException('Invalid operation');
    }

    /**
     * @return string|null
     */
    public function getUrl(UrlGenerator $generator)
    {
        $operation = $this->getOperation();

        if ($operation instanceof UpdateOperation) {
            return $generator->getCompareUrl($operation->getInitialPackage(), $operation->getTargetPackage());
        }

        if ($operation instanceof InstallOperation || $operation instanceof UninstallOperation) {
            return $generator->getReleaseUrl($operation->getPackage());
        }

        return null;
    }

    public function getProjectUrl(UrlGenerator $generator)
    {
        $package = $this->getPackage();

        if (!isset($package)) {
            return null;
        }

        return $generator->getProjectUrl($package);
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
