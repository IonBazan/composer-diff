<?php

namespace IonBazan\ComposerDiff\Diff;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Url\GitGenerator;
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

    /** @var bool */
    private $withMetadata;

    /** @var array<string, string> */
    private $referenceFields;

    /**
     * @param array<string, string> $referenceFields package getter methods to compare, keyed by label
     */
    public function __construct(OperationInterface $operation, ?UrlGenerator $urlGenerator = null, bool $direct = false, bool $withMetadata = false, array $referenceFields = VersionComparator::REFERENCE_FIELDS)
    {
        $this->operation = $operation;
        $this->direct = $direct;
        $this->withMetadata = $withMetadata;
        $this->referenceFields = $referenceFields;
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

    public function isEffective(): bool
    {
        return $this->getPackage() instanceof EffectivePlatformPackage;
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

    public function getDisplayName(): string
    {
        return $this->getPackageName().($this->isEffective() ? ' (effective)' : '');
    }

    public function getPackage(): PackageInterface
    {
        $operation = $this->getOperation();

        if ($operation instanceof UpdateOperation) {
            return $operation->getTargetPackage();
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
     *     effective: bool,
     *     operation: string,
     *     version_base: string|null,
     *     version_target: string|null,
     *     reference_field?: string|null,
     *     reference_base?: string|null,
     *     reference_target?: string|null,
     *     type_base?: string|null,
     *     type_target?: string|null,
     *     licenses: string[],
     *     compare: string|null,
     *     link: string|null,
     * }
     */
    public function toArray(): array
    {
        $basePackage = $this->getBasePackage();
        $targetPackage = $this->getTargetPackage();
        $data = [
            'name' => $this->getPackageName(),
            'direct' => $this->isDirect(),
            'effective' => $this->isEffective(),
            'operation' => $this->getType(),
            'version_base' => null !== $basePackage ? $basePackage->getFullPrettyVersion() : null,
            'version_target' => null !== $targetPackage ? $targetPackage->getFullPrettyVersion() : null,
        ];

        if ($this->withMetadata) {
            $data += $this->getMetadata();
        }

        return $data + [
            'licenses' => $this->getLicenses(),
            'compare' => $this->getUrl(),
            'link' => $this->getProjectUrl(),
        ];
    }

    public function hasMetadata(): bool
    {
        return $this->withMetadata;
    }

    /**
     * @return array{reference_field: string|null, reference_base: string|null, reference_target: string|null, type_base: string|null, type_target: string|null}
     */
    public function getMetadata(): array
    {
        $metadata = [
            'reference_field' => null,
            'reference_base' => null,
            'reference_target' => null,
            'type_base' => null,
            'type_target' => null,
        ];

        if (!$this->operation instanceof UpdateOperation) {
            return $metadata;
        }

        $initialPackage = $this->operation->getInitialPackage();
        $targetPackage = $this->operation->getTargetPackage();
        $field = $this->getReferenceField();

        if (null !== $field) {
            $metadata['reference_field'] = $field;
            $metadata['reference_base'] = $this->getReference($initialPackage, $field);
            $metadata['reference_target'] = $this->getReference($targetPackage, $field);
        }

        if ($this->hasTypeChange()) {
            $metadata['type_base'] = $initialPackage->getType();
            $metadata['type_target'] = $targetPackage->getType();
        }

        return $metadata;
    }

    public function getBaseVersion(): ?string
    {
        $package = $this->getBasePackage();

        return null !== $package ? $this->describeVersion($package) : null;
    }

    public function getTargetVersion(): ?string
    {
        $package = $this->getTargetPackage();

        return null !== $package ? $this->describeVersion($package) : null;
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

        if ($operation instanceof UpdateOperation && !$this->isSameRevision($operation->getInitialPackage(), $package)) {
            $this->compareUrl = $generator->getCompareUrl($operation->getInitialPackage(), $package);

            return;
        }

        $this->compareUrl = $generator->getReleaseUrl($package);
    }

    /**
     * There is nothing to compare when only metadata changed, e.g. a moved Drupal release whose source reference is its tag.
     */
    private function isSameRevision(PackageInterface $initialPackage, PackageInterface $targetPackage): bool
    {
        return $initialPackage->getPrettyVersion() === $targetPackage->getPrettyVersion()
            && $initialPackage->getSourceReference() === $targetPackage->getSourceReference();
    }

    private function getBasePackage(): ?PackageInterface
    {
        if ($this->operation instanceof UpdateOperation) {
            return $this->operation->getInitialPackage();
        }

        if ($this->operation instanceof UninstallOperation) {
            return $this->operation->getPackage();
        }

        return null;
    }

    private function getTargetPackage(): ?PackageInterface
    {
        if ($this->operation instanceof UpdateOperation) {
            return $this->operation->getTargetPackage();
        }

        if ($this->operation instanceof InstallOperation) {
            return $this->operation->getPackage();
        }

        return null;
    }

    private function describeVersion(PackageInterface $package): string
    {
        $version = $package->getFullPrettyVersion();
        $field = $this->getReferenceField();

        if (null !== $field) {
            $version .= sprintf(' (%s: %s)', $field, $this->getReference($package, $field));
        }

        if ($this->hasTypeChange()) {
            $version .= sprintf(' [type: %s]', $package->getType());
        }

        return $version;
    }

    /**
     * References are only reported when the versions are the same, as otherwise the version change explains them.
     */
    private function getReferenceField(): ?string
    {
        if (!$this->withMetadata || !$this->operation instanceof UpdateOperation) {
            return null;
        }

        $initialPackage = $this->operation->getInitialPackage();
        $targetPackage = $this->operation->getTargetPackage();

        if ($initialPackage->getFullPrettyVersion() !== $targetPackage->getFullPrettyVersion()) {
            return null;
        }

        return VersionComparator::getReferenceDifference($initialPackage, $targetPackage, $this->referenceFields);
    }

    private function getReference(PackageInterface $package, string $field): string
    {
        return substr($package->{$this->referenceFields[$field]}(), 0, GitGenerator::SHORT_REFERENCE_LENGTH);
    }

    private function hasTypeChange(): bool
    {
        return $this->withMetadata
            && $this->operation instanceof UpdateOperation
            && $this->operation->getInitialPackage()->getType() !== $this->operation->getTargetPackage()->getType();
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
            if ($this->operation->getInitialPackage()->getVersion() === $this->operation->getTargetPackage()->getVersion()) {
                return self::TYPE_CHANGE;
            }

            $upgrade = VersionComparator::isUpgrade($this->operation);

            if (null === $upgrade) {
                return self::TYPE_CHANGE;
            }

            return $upgrade ? self::TYPE_UPGRADE : self::TYPE_DOWNGRADE;
        }

        return self::TYPE_CHANGE;
    }
}
