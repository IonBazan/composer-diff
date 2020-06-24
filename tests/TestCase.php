<?php

namespace IonBazan\ComposerDiff\Tests;

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
}
