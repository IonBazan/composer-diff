<?php

namespace IonBazan\ComposerDiff\Tests;

use Composer\Package\PackageInterface;
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
     * @return MockObject&PackageInterface
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
     * @param string      $sourceUrl
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
}
