<?php

namespace IonBazan\ComposerDiff\Command;

use Composer\Command\BaseCommand;
use IonBazan\ComposerDiff\Formatter\Formatter;
use IonBazan\ComposerDiff\Formatter\MarkdownListFormatter;
use IonBazan\ComposerDiff\Formatter\MarkdownTableFormatter;
use IonBazan\ComposerDiff\PackageDiff;
use IonBazan\ComposerDiff\Url\GeneratorContainer;
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
            ->setDescription('Displays package diff')
            ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'Base composer.lock file path or git ref', 'HEAD:composer.lock')
            ->addOption('target', 't', InputOption::VALUE_REQUIRED, 'Target composer.lock file path or git ref', 'composer.lock')
            ->addOption('no-dev', null, InputOption::VALUE_NONE, 'Ignore dev dependencies')
            ->addOption('no-prod', null, InputOption::VALUE_NONE, 'Ignore prod dependencies')
            ->addOption('with-platform', 'p', InputOption::VALUE_NONE, 'Include platform dependencies (PHP version, extensions, etc.)')
            ->addOption('with-links', 'l', InputOption::VALUE_NONE, 'Include compare/release URLs')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format (mdtable, mdlist)', 'mdtable')
            ->addOption('gitlab-domains', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Gitlab domains', array())
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $base = $input->getOption('base');
        $target = $input->getOption('target');
        $withPlatform = $input->getOption('with-platform');
        $withUrls = $input->getOption('with-links');
        $this->gitlabDomains = array_merge($this->gitlabDomains, $input->getOption('gitlab-domains'));

        $formatter = $this->getFormatter($input, $output);

        if (!$input->getOption('no-prod')) {
            $operations = $this->packageDiff->getPackageDiff($base, $target, false, $withPlatform);
            $formatter->render($operations, 'Prod Packages', $withUrls);
        }

        if (!$input->getOption('no-dev')) {
            $operations = $this->packageDiff->getPackageDiff($base, $target, true, $withPlatform);
            $formatter->render($operations, 'Dev Packages', $withUrls);
        }

        return 0;
    }

    /**
     * @return Formatter
     */
    private function getFormatter(InputInterface $input, OutputInterface $output)
    {
        $urlGenerators = new GeneratorContainer($this->gitlabDomains);

        switch ($input->getOption('format')) {
            case 'mdlist':
                return new MarkdownListFormatter($output, $urlGenerators);
            case 'mdtable':
            default:
                return new MarkdownTableFormatter($output, $urlGenerators);
        }
    }
}
