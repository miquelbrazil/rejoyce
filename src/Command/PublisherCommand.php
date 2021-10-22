<?php
namespace App\Command;

use HTMLPurifier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use Symfony\Component\Finder\SplFileInfo;
use HTMLPurifier_Config;
class PublisherCommand extends Command
{
    protected Environment $environment;
    protected array $config;
    protected FrontMatterExtension $parser;
    protected Filesystem $fs;

    public function __construct()
    {
        parent::__construct();

        $this->fs = new Filesystem();

        $this->config = [];
        $this->environment = new Environment($this->config);
        $this->parser = new FrontMatterExtension();
    }

    protected function configure()
    {
        $this-> setName('publish')
             -> setDescription('Publish Articles ready for distribution.')
             -> setHelp('This command allows you to publish finished articles.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.HiddenElements', ['style']);
        $purifier = new HTMLPurifier($config);

        $output->writeln('Publish Now');

        $textbundles = Finder::create()->directories()->name('*.textbundle')->in(MANUSCRIPTS);

        $output->writeln('The Finder found ' . $textbundles->count() . ' total textbundle files.');

        $parser = new FrontMatterExtension();

        foreach ($textbundles as $bundle) {
            $manuscript = $this->getManuscript($bundle);

            if ($manuscript instanceof SplFileInfo) {
                $text = trim($purifier->purify($manuscript->getContents()));
                $delimiter = '---';
                $delimiter1 = strpos($text, $delimiter);
                $delimiter2 = strpos($text, $delimiter, $delimiter1+strlen($delimiter));

                if ($delimiter !== false && $delimiter2 !== false) {
                    $front_matter = substr($text, $delimiter1, $delimiter2+strlen($delimiter));
                    $markdown = trim(str_replace($front_matter, '', $text));
                    $text = $front_matter . "\n" . $markdown;
                }

                $parsed = $parser->getFrontMatterParser()->parse($text);

                if (!empty($parsed->getFrontMatter())) {
                    $this->fs->dumpFile($bundle->getRealPath() . '/rejoyce.json', json_encode($parsed->getFrontMatter(), JSON_PRETTY_PRINT));
                }
            }
        }

        return 0;
    }

    private function getManuscript($bundle)
    {
        $manuscripts = Finder::create()->files()->name('text.md')->in($bundle->getRealPath());
        $manuscripts = $manuscripts->hasResults() ? iterator_to_array($manuscripts, false) : null;
        return !is_null($manuscripts) ? array_shift($manuscripts) : null;
    }
}