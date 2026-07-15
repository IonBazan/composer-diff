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

    public function __construct(OperationInterface $operation, ?UrlGenerator $urlGenerator = null, bool $direct = false)
    {
        $this->operation = $operation;
        $this->direct = $direct;
        $this->type = $this->determineType();

        if ($urlGenerator instanceof UrlGenerator) {
            $this->setUrls($urlGenerator);
        }
    }

    public function getOperation(): OperationInterface
    {
        return $this->operation;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isDirect(): bool
    {
        return $this->direct;
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

    public function getPackageName(): string
    {
        return $this->getPackage()->getName();
    }

    public function getPackage(): PackageInterface
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
    public function getLicenses(): array
    {
        $package = $this->getPackage();

        if (!$package instanceof CompletePackageInterface) {
            return [];
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
    public function toArray(): array
    {
        return [
            'name' => $this->getPackageName(),
            'direct' => $this->isDirect(),
            'operation' => $this->getType(),
            'version_base' => $this->getBaseVersion(),
            'version_target' => $this->getTargetVersion(),
            'licenses' => $this->getLicenses(),
            'compare' => $this->getUrl(),
            'link' => $this->getProjectUrl(),
        ];
    }

    public function getBaseVersion(): ?string
    {
        if ($this->operation instanceof UpdateOperation) {
            return $this->operation->getInitialPackage()->getFullPrettyVersion();
        }

        if ($this->operation instanceof UninstallOperation) {
            return $this->operation->getPackage()->getFullPrettyVersion();
        }

        return null;
    }

    public function getTargetVersion(): ?string
    {
        if ($this->operation instanceof UpdateOperation) {
            return $this->operation->getTargetPackage()->getFullPrettyVersion();
        }

        if ($this->operation instanceof InstallOperation) {
            return $this->operation->getPackage()->getFullPrettyVersion();
        }

        return null;
    }

    public function getUrl(): ?string
    {
        return $this->compareUrl;
    }

    public function getProjectUrl(): ?string
    {
        return $this->projectUrl;
    }

    private function setUrls(UrlGenerator $generator): void
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
