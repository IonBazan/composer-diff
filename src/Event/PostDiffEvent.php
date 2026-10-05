<?php

namespace IonBazan\ComposerDiff\Event;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Script\Event;
use IonBazan\ComposerDiff\Diff\DiffEntries;

class PostDiffEvent extends Event
{
    const NAME = 'post-composer-diff';

    /**
     * @var DiffEntries
     */
    private $prodEntries;

    /**
     * @var DiffEntries
     */
    private $devEntries;

    /**
     * @var int
     */
    private $exitCode = 0;

    public function __construct(Composer $composer, IOInterface $io, DiffEntries $prodEntries, DiffEntries $devEntries)
    {
        parent::__construct(self::NAME, $composer, $io);

        $this->prodEntries = $prodEntries;
        $this->devEntries = $devEntries;
    }

    public function getProdEntries(): DiffEntries
    {
        return $this->prodEntries;
    }

    public function setProdEntries(DiffEntries $prodEntries): void
    {
        $this->prodEntries = $prodEntries;
    }

    public function getDevEntries(): DiffEntries
    {
        return $this->devEntries;
    }

    public function setDevEntries(DiffEntries $devEntries): void
    {
        $this->devEntries = $devEntries;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * The value is combined (bitwise OR) with the exit code computed by the command.
     */
    public function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }
}
