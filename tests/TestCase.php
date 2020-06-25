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
     * @param string $name
     * @param string $version
     *
     * @return MockObject&PackageInterface
     */
    protected function getPackage($name, $version)
    {
        $package = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $package->method('getName')->willReturn($name);
        $package->method('getFullPrettyVersion')->willReturn($version);

        return $package;
    }
}
