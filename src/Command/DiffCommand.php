<?php

namespace IonBazan\ComposerDiff\Command;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Formatter\FormatterContainer;
use IonBazan\ComposerDiff\PackageDiff;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * This is a trick to maintain compatibility with both PHP 5 and 7 with Symfony 2.3 all the way to 7 with typed returns.
 * This is only needed when using this package as a dependency with Symfony 7+, not when using as Composer plugin.
 */
class_alias(
    PHP_VERSION_ID >= 70000
        ? 'IonBazan\ComposerDiff\Command\BaseTypedCommand'
        : 'IonBazan\ComposerDiff\Command\BaseNotTypedCommand',
    'IonBazan\ComposerDiff\Command\BaseCommand'
);

class DiffCommand extends BaseCommand
{
    const CHANGES_PROD = 2;
    const CHANGES_DEV = 4;
    const DOWNGRADES_PROD = 8;
    const DOWNGRADES_DEV = 16;
    /**
     * @var PackageDiff
     */
    protected $packageDiff;

    /**
     * @var string[]
     */
    protected $gitlabDomains;

    /**
     * @param string[] $gitlabDomains
     */
    public function __construct(PackageDiff $packageDiff, array $gitlabDomains = array())
    {
        $this->packageDiff = $packageDiff;
        $this->gitlabDomains = $gitlabDomains;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('diff')
            ->setDescription('Compares composer.lock files and shows package changes')
            ->addArgument('base', InputArgument::OPTIONAL, 'Base (original) composer.lock file path or git ref')
            ->addArgument('target', InputArgument::OPTIONAL, 'Target (modified) composer.lock file path or git ref')
            ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'Base (original) composer.lock file path or git ref', 'HEAD:composer.lock')
            ->addOption('target', 't', InputOption::VALUE_REQUIRED, 'Target (modified) composer.lock file path or git ref', 'composer.lock')
            ->addOption('no-dev', null, InputOption::VALUE_NONE, 'Ignore dev dependencies')
            ->addOption('no-prod', null, InputOption::VALUE_NONE, 'Ignore prod dependencies')
            ->addOption('direct', 'D', InputOption::VALUE_NONE, 'Restricts the list of packages to your direct dependencies')
            ->addOption('with-platform', 'p', InputOption::VALUE_NONE, 'Include platform dependencies (PHP version, extensions, etc.)')
            ->addOption('with-links', 'l', InputOption::VALUE_NONE, 'Include compare/release URLs')
            ->addOption('with-licenses', 'c', InputOption::VALUE_NONE, 'Include licenses')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (mdtable, mdlist, json, github)', 'mdtable')
            ->addOption('gitlab-domains', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Extra Gitlab domains (inherited from Composer config by default)', array())
            ->addOption('strict', 's', InputOption::VALUE_NONE, 'Return non-zero exit code if there are any changes')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command displays all dependency changes between two <comment>composer.lock</comment> files.

By default, it will compare current filesystem changes with git <comment>HEAD</comment>:

    <info>%command.full_name%</info>

To compare with specific branch, pass its name as argument:

    <info>%command.full_name% master</info>
    
You can specify any valid git refs to compare with:

    <info>%command.full_name% HEAD~3 be4aabc</info>
    
You can also use more verbose syntax for <info>base</info> and <info>target</info> options:

    <info>%command.full_name% --base master --target composer.lock</info>
    
To compare files in specific path, use following syntax:

    <info>%command.full_name% master:subdirectory/composer.lock /path/to/another/composer.lock</info>
    
By default, <info>platform</info> dependencies are hidden. Add <info>--with-platform</info> option to include them in the report:
 
    <info>%command.full_name% --with-platform</info>
    
By default, <info>transient</info> dependencies are displayed. Add <info>--direct</info> option to only show direct dependencies:

<info>%command.full_name% --direct</info>

Use <info>--with-links</info> to include release and compare URLs in the report:

    <info>%command.full_name% --with-links</info>
    
You can customize output format by specifying it with <info>--format</info> option. Choose between <comment>mdtable</comment>, <comment>mdlist</comment> and <comment>json</comment>:

    <info>%command.full_name% --format=json</info>

Hide <info>dev</info> dependencies using <info>--no-dev</info> option:

    <info>%command.full_name% --no-dev</info>

Passing <info>--strict</info> option may help you to disallow changes or downgrades by returning non-zero exit code:

    <info>%command.full_name% --strict</info>

Exit code
---------

Exit code of the command is built using following bit flags:

*  0 - OK.
*  1 - General error.
*  2 - There were changes in prod packages.
*  4 - There were changes is dev packages.
*  8 - There were downgrades in prod packages.
* 16 - There were downgrades in dev packages.
EOF
            )
        ;
    }

    /**
     * @return int
     */
    protected function handle(InputInterface $input, OutputInterface $output)
    {
        $base = null !== $input->getArgument('base') ? $input->getArgument('base') : $input->getOption('base');
        $target = null !== $input->getArgument('target') ? $input->getArgument('target') : $input->getOption('target');
        $onlyDirect = $input->getOption('direct');
        $withPlatform = $input->getOption('with-platform');
        $withUrls = $input->getOption('with-links');
        $withLicenses = $input->getOption('with-licenses');
        $this->gitlabDomains = array_merge($this->gitlabDomains, $input->getOption('gitlab-domains'));

        $urlGenerators = new GeneratorContainer($this->gitlabDomains);
        $formatters = new FormatterContainer($output);
        $formatter = $formatters->getFormatter($input->getOption('format'));

        $this->packageDiff->setUrlGenerator($urlGenerators);

        $prodOperations = new DiffEntries(array());
        $devOperations = new DiffEntries(array());

        if (!$input->getOption('no-prod')) {
            $prodOperations = $this->packageDiff->getPackageDiff($base, $target, false, $withPlatform, $onlyDirect);
        }

        if (!$input->getOption('no-dev')) {
            $devOperations = $this->packageDiff->getPackageDiff($base, $target, true, $withPlatform, $onlyDirect);
        }

        $formatter->render($prodOperations, $devOperations, $withUrls, $withLicenses);

        return $input->getOption('strict') ? $this->getExitCode($prodOperations, $devOperations) : 0;
    }

    /**
     * @return int Exit code
     */
    private function getExitCode(DiffEntries $prodEntries, DiffEntries $devEntries)
    {
        $exitCode = 0;

        if (count($prodEntries)) {
            $exitCode = self::CHANGES_PROD;

            if ($this->hasDowngrades($prodEntries)) {
                $exitCode |= self::DOWNGRADES_PROD;
            }
        }

        if (count($devEntries)) {
            $exitCode |= self::CHANGES_DEV;

            if ($this->hasDowngrades($devEntries)) {
                $exitCode |= self::DOWNGRADES_DEV;
            }
        }

        return $exitCode;
    }

    /**
     * @return bool
     */
    private function hasDowngrades(DiffEntries $entries)
    {
        /** @var DiffEntry $entry */
        foreach ($entries as $entry) {
            if ($entry->isDowngrade()) {
                return true;
            }
        }

        return false;
    }
}
