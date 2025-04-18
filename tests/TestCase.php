<?php

namespace IonBazan\ComposerDiff\Tests;

use Composer\DependencyResolver\Operation\OperationInterface;
use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Tests\Util\ComposerApplication;
use IonBazan\ComposerDiff\Tests\Util\TypedComposerApplication;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function setExpectedException($exception, $message = '', $code = null)
    {
        if (!class_exists('PHPUnit\Framework\Error\Notice')) {
            $exception = str_replace('PHPUnit\\Framework\\Error\\', 'PHPUnit_Framework_Error_', $exception);
        }
        if (method_exists($this, 'expectException')) {
            $this->expectException($exception);
            if (strlen($message)) {
                $this->expectExceptionMessage($message);
            }
        } else {
            parent::setExpectedException($exception, $message, $code);
        }
    }

    /**
     * @param string      $name
     * @param string      $version
     * @param string|null $fullVersion
     *
     * @return MockObject|PackageInterface
     */
    protected function getPackage($name, $version, $fullVersion = null)
    {
        $package = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $package->method('getName')->willReturn($name);
        $package->method('getVersion')->willReturn($version);
        $package->method('getPrettyVersion')->willReturn($version);
        $package->method('getFullPrettyVersion')->willReturn(null !== $fullVersion ? $fullVersion : $version);

        return $package;
    }

    /**
     * @param string      $name
     * @param string      $version
     * @param string|null $sourceUrl
     * @param string|null $sourceReference
     *
     * @return mixed
     */
    protected function getPackageWithSource($name, $version, $sourceUrl, $sourceReference = null)
    {
        $package = $this->getPackage($name, $version, $sourceReference);
        $package->method('getSourceUrl')->willReturn($sourceUrl);
        $package->method('getSourceReference')->willReturn($sourceReference);
        $package->method('isDev')->willReturn(0 === strpos($version, 'dev-') || '-dev' === substr($version, -4));

        return $package;
    }

    /**
     * @param string      $name
     * @param string      $version
     * @param string|null $fullVersion
     *
     * @return MockObject|CompletePackageInterface
     */
    protected function getCompletePackage($name, $version, $fullVersion = null, $license = array())
    {
        $package = $this->getMockBuilder('Composer\Package\CompletePackageInterface')->getMock();
        $package->method('getName')->willReturn($name);
        $package->method('getVersion')->willReturn($version);
        $package->method('getPrettyVersion')->willReturn($version);
        $package->method('getFullPrettyVersion')->willReturn(null !== $fullVersion ? $fullVersion : $version);
        $package->method('getLicense')->willReturn($license);

        return $package;
    }

    /**
     * @param OperationInterface[] $operations
     *
     * @return DiffEntries
     */
    protected function getEntries(array $operations, GeneratorContainer $urlGenerators)
    {
        return new DiffEntries(array_map(function (OperationInterface $operation) use ($urlGenerators) {
            return new DiffEntry($operation, $urlGenerators);
        }, $operations));
    }

    /**
     * @return ComposerApplication|TypedComposerApplication
     */
    protected function getComposerApplication()
    {
        return PHP_VERSION_ID >= 70000 ? new TypedComposerApplication() : new ComposerApplication();
    }

    /**
     * @return MockObject|GeneratorContainer
     */
    protected function getGenerators()
    {
        $generator = $this->getMockBuilder('IonBazan\ComposerDiff\Url\UrlGenerator')->getMock();
        $generator->method('getCompareUrl')->willReturnCallback(function (PackageInterface $base, PackageInterface $target) {
            return sprintf('https://example.com/c/%s..%s', $base->getVersion(), $target->getVersion());
        });
        $generator->method('getReleaseUrl')->willReturnCallback(function (PackageInterface $package) {
            return sprintf('https://example.com/r/%s', $package->getVersion());
        });
        $generator->method('getProjectUrl')->willReturnCallback(function (PackageInterface $package) {
            return sprintf('https://example.com/r/%s', $package->getName());
        });

        $generators = $this->getMockBuilder('IonBazan\ComposerDiff\Url\GeneratorContainer')
            ->disableOriginalConstructor()
            ->setMethods(array('get'))
            ->getMock();
        $generators->method('get')
            ->willReturnCallback(function (PackageInterface $package) use ($generator) {
                if ('php' === $package->getName() || false !== strpos($package->getName(), 'a/no-link')) {
                    return null;
                }

                return $generator;
            });

        return $generators;
    }
}
