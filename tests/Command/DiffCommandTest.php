<?php

namespace IonBazan\ComposerDiff\Tests\Command;

use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\Package\PackageInterface;
use IonBazan\ComposerDiff\Command\DiffCommand;
use IonBazan\ComposerDiff\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

class DiffCommandTest extends TestCase
{
    public function testItGeneratesTable()
    {
        $base = 'base-composer.lock';
        $target = 'target-composer.lock';
        $diff = $this->getMockBuilder('IonBazan\ComposerDiff\PackageDiff')->getMock();
        $tester = new CommandTester(new DiffCommand($diff));
        $diff->expects($this->once())
            ->method('getPackageDiff')
            ->with($base, $target, false, false)
            ->willReturn(array(
                new InstallOperation($this->getPackage('a/package-1', '1.0.0')),
                new UpdateOperation($this->getPackage('a/package-2', '1.0.0'), $this->getPackage('a/package-2', '1.2.0')),
                new UninstallOperation($this->getPackage('a/package-3', '0.1.1')),
            ))
        ;
        $result = $tester->execute(array(
            '--base' => $base,
            '--target' => $target,
            '--no-dev' => null,
        ));
        $this->assertSame(0, $result);
        $this->assertSame(<<<OUTPUT
| Prod Packages | Base  | Target  |
|---------------|-------|---------|
| a/package-1   | New   | 1.0.0   |
| a/package-2   | 1.0.0 | 1.2.0   |
| a/package-3   | 0.1.1 | Removed |


OUTPUT
        , $tester->getDisplay());
    }

    public function testItFailsWithInvalidOperation()
    {
        $base = 'base-composer.lock';
        $target = 'target-composer.lock';
        $diff = $this->getMockBuilder('IonBazan\ComposerDiff\PackageDiff')->getMock();
        $tester = new CommandTester(new DiffCommand($diff));
        $diff->expects($this->once())
            ->method('getPackageDiff')
            ->with($base, $target, false, false)
            ->willReturn(array(
                $this->getMockBuilder('Composer\DependencyResolver\Operation\OperationInterface')->getMock(),
            ))
        ;
        $this->setExpectedException('InvalidArgumentException', 'Invalid operation');
        $tester->execute(array(
            '--base' => $base,
            '--target' => $target,
            '--no-dev' => null,
        ));
    }

    /**
     * @param string $name
     * @param string $version
     *
     * @return MockObject&PackageInterface
     */
    private function getPackage($name, $version)
    {
        $package = $this->getMockBuilder('Composer\Package\PackageInterface')->getMock();
        $package->method('getName')->willReturn($name);
        $package->method('getFullPrettyVersion')->willReturn($version);

        return $package;
    }
}
