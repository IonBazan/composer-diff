<?php

namespace IonBazan\ComposerDiff\Command;

use Composer\Command\BaseCommand;
use IonBazan\ComposerDiff\Formatter\Formatter;
use IonBazan\ComposerDiff\Formatter\JsonFormatter;
use IonBazan\ComposerDiff\Formatter\MarkdownListFormatter;
use IonBazan\ComposerDiff\Formatter\MarkdownTableFormatter;
use IonBazan\ComposerDiff\PackageDiff;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DiffCommand extends BaseCommand
{
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
            ->addOption('with-platform', 'p', InputOption::VALUE_NONE, 'Include platform dependencies (PHP version, extensions, etc.)')
            ->addOption('with-links', 'l', InputOption::VALUE_NONE, 'Include compare/release URLs')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (mdtable, mdlist, json)', 'mdtable')
            ->addOption('gitlab-domains', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Extra Gitlab domains (inherited from Composer config by default)', array())
            ->setHelp(<<<'EOF'
The <comment>%command.name%</comment> command displays all dependency changes between two <info>composer.lock</info> files.
By default, it will compare current filesystem changes with git <info>HEAD</info>:

    <comment>%command.full_name%</comment>

To compare with specific branch, pass its name as argument:

    <comment>%command.full_name% master</comment>
    
You can specify any valid git refs to compare with:

    <comment>%command.full_name% HEAD~3 be4aabc</comment>
    
You can also use more verbose syntax for <info>base</info> and <info>target</info> options:

    <comment>%command.full_name% --base master --target composer.lock</comment>
    
To compare files in specific path, use following syntax:

    <comment>%command.full_name% master:subdirectory/composer.lock /path/to/another/composer.lock</comment>
    
By default, <info>platform</info> dependencies are hidden. Add <comment>--with-platform</comment> option to include them in the report:
 
    <comment>%command.full_name% --with-platform</comment>

Use <comment>--with-links</comment> to include release and compare URLs in the report:

    <comment>%command.full_name% --with-links</comment>
    
You can customize output format by specifying it with <comment>--format</comment> option. Choose between <info>mdtable</info>, <info>mdlist</info> and <info>json</info>:

    <comment>%command.full_name% --format=json</comment>

Hide <info>dev</info> dependencies using <comment>--no-dev</comment> option:

    <comment>%command.full_name% --no-dev</comment>
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $base = null !== $input->getArgument('base') ? $input->getArgument('base') : $input->getOption('base');
        $target = null !== $input->getArgument('target') ? $input->getArgument('target') : $input->getOption('target');
        $withPlatform = $input->getOption('with-platform');
        $withUrls = $input->getOption('with-links');
        $this->gitlabDomains = array_merge($this->gitlabDomains, $input->getOption('gitlab-domains'));

        $formatter = $this->getFormatter($input, $output);

        $prodOperations = array();
        $devOperations = array();

        if (!$input->getOption('no-prod')) {
            $prodOperations = $this->packageDiff->getPackageDiff($base, $target, false, $withPlatform);
        }

        if (!$input->getOption('no-dev')) {
            $devOperations = $this->packageDiff->getPackageDiff($base, $target, true, $withPlatform);
        }

        $formatter->render($prodOperations, $devOperations, $withUrls);

        return 0;
    }

    /**
     * @return Formatter
     */
    private function getFormatter(InputInterface $input, OutputInterface $output)
    {
        $urlGenerators = new GeneratorContainer($this->gitlabDomains);

        switch ($input->getOption('format')) {
            case 'json':
                return new JsonFormatter($output, $urlGenerators);
            case 'mdlist':
                return new MarkdownListFormatter($output, $urlGenerators);
            // case 'mdtable':
            default:
                return new MarkdownTableFormatter($output, $urlGenerators);
        }
    }
}
