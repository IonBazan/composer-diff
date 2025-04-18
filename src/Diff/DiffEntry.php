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

    /** @var string|null */
    private $compareUrl;

    /** @var string|null */
    private $projectUrl;

    /**
     * @param UrlGenerator|null $urlGenerator
     * @param bool              $direct
     */
    public function __construct(OperationInterface $operation, $urlGenerator = null, $direct = false)
    {
        $this->operation = $operation;
        $this->direct = $direct;
        $this->type = $this->determineType();

        if ($urlGenerator instanceof UrlGenerator) {
            $this->setUrls($urlGenerator);
        }
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
     * @return string
     */
    public function getPackageName()
    {
        return $this->getPackage()->getName();
    }

    /**
     * @return PackageInterface
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

        throw new \InvalidArgumentException('Invalid operation');
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
     * @return array{
     *     name: string,
     *     direct: bool,
     *     operation: string,
     *     version_base: string|null,
     *     version_target: string|null,
     *     licenses: string[],
     *     compare: string|null,
     *     link: string|null,
     * }
     */
    public function toArray()
    {
        return array(
            'name' => $this->getPackageName(),
            'direct' => $this->isDirect(),
            'operation' => $this->getType(),
            'version_base' => $this->getBaseVersion(),
            'version_target' => $this->getTargetVersion(),
            'licenses' => $this->getLicenses(),
            'compare' => $this->getUrl(),
            'link' => $this->getProjectUrl(),
        );
    }

    /**
     * @return string|null
     */
    public function getBaseVersion()
    {
        if ($this->operation instanceof UpdateOperation) {
            return $this->operation->getInitialPackage()->getFullPrettyVersion();
        }

        if ($this->operation instanceof UninstallOperation) {
            return $this->operation->getPackage()->getFullPrettyVersion();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getTargetVersion()
    {
        if ($this->operation instanceof UpdateOperation) {
            return $this->operation->getTargetPackage()->getFullPrettyVersion();
        }

        if ($this->operation instanceof InstallOperation) {
            return $this->operation->getPackage()->getFullPrettyVersion();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getUrl()
    {
        return $this->compareUrl;
    }

    /**
     * @return string|null
     */
    public function getProjectUrl()
    {
        return $this->projectUrl;
    }

    /**
     * @return void
     */
    private function setUrls(UrlGenerator $generator)
    {
        $package = $this->getPackage();
        $this->projectUrl = $generator->getProjectUrl($package);

        $operation = $this->getOperation();

        if ($operation instanceof UpdateOperation) {
            $this->compareUrl = $generator->getCompareUrl($operation->getInitialPackage(), $operation->getTargetPackage());

            return;
        }

        $this->compareUrl = $generator->getReleaseUrl($package);
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
