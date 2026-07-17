<?php

namespace IonBazan\ComposerDiff\Command;

use IonBazan\ComposerDiff\Diff\DiffEntries;
use IonBazan\ComposerDiff\Diff\DiffEntry;
use IonBazan\ComposerDiff\Formatter\FormatterContainer;
use IonBazan\ComposerDiff\PackageDiff;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
    public function __construct(PackageDiff $packageDiff, array $gitlabDomains = [])
    {
        $this->packageDiff = $packageDiff;
        $this->gitlabDomains = $gitlabDomains;

        parent::__construct();
    }

    protected function configure(): void
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
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (mdtable, mdlist, json, github, pr)', 'mdtable')
            ->addOption('gitlab-domains', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Extra Gitlab domains (inherited from Composer config by default)', [])
            ->addOption('filter', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Limit output to packages matching given glob pattern(s)', [])
            ->addOption('sort', null, InputOption::VALUE_OPTIONAL, 'Sort packages by "name" or "operation"', false)
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
    
You can customize output format by specifying it with <info>--format</info> option. Choose between <comment>mdtable</comment>, <comment>mdlist</comment>, <comment>json</comment>, <comment>github</comment> and <comment>pr</comment>:

    <info>%command.full_name% --format=json</info>

Use <info>--format=pr</info> to wrap the output in collapsible <comment><details></comment> blocks, suitable for GitHub PR descriptions:

    <info>%command.full_name% --format=pr</info>

Hide <info>dev</info> dependencies using <info>--no-dev</info> option:

    <info>%command.full_name% --no-dev</info>

Use <info>--filter</info> to restrict output to packages matching a glob pattern:

    <info>%command.full_name% --filter="symfony/*"</info>
    <info>%command.full_name% --filter="symfony/*" --filter="doctrine/*"</info>

Use <info>--sort</info> to order packages alphabetically by name, or <info>--sort=operation</info> to group by operation type:

    <info>%command.full_name% --sort</info>
    <info>%command.full_name% --sort=operation</info>

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $base = $input->getArgument('base') ?? $input->getOption('base');
        $target = $input->getArgument('target') ?? $input->getOption('target');
        $onlyDirect = $input->getOption('direct');
        $withPlatform = $input->getOption('with-platform');
        $withUrls = $input->getOption('with-links');
        $withLicenses = $input->getOption('with-licenses');
        $this->gitlabDomains = array_merge($this->gitlabDomains, $input->getOption('gitlab-domains'));

        $urlGenerators = new GeneratorContainer($this->gitlabDomains);
        $formatters = new FormatterContainer($output);
        $formatter = $formatters->getFormatter($input->getOption('format'));

        $this->packageDiff->setUrlGenerator($urlGenerators);

        $prodOperations = new DiffEntries([]);
        $devOperations = new DiffEntries([]);
        $filters = $input->getOption('filter');

        if (!$input->getOption('no-prod')) {
            $prodOperations = $this->packageDiff->getPackageDiff($base, $target, false, $withPlatform, $onlyDirect);
        }

        if (!$input->getOption('no-dev')) {
            $devOperations = $this->packageDiff->getPackageDiff($base, $target, true, $withPlatform, $onlyDirect);
        }

        if (!empty($filters)) {
            $prodOperations = $prodOperations->matching($filters);
            $devOperations = $devOperations->matching($filters);
        }

        $sort = $input->getOption('sort');
        if (false !== $sort) {
            $sortBy = is_string($sort) ? $sort : 'name';
            $prodOperations = $prodOperations->sorted($sortBy);
            $devOperations = $devOperations->sorted($sortBy);
        }

        $formatter->render($prodOperations, $devOperations, $withUrls, $withLicenses);

        return $input->getOption('strict') ? $this->getExitCode($prodOperations, $devOperations) : 0;
    }

    /**
     * @return int Exit code
     */
    private function getExitCode(DiffEntries $prodEntries, DiffEntries $devEntries): int
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

    private function hasDowngrades(DiffEntries $entries): bool
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
