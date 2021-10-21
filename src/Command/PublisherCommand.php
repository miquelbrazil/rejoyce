<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class PublisherCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this-> setName('publish')
             -> setDescription('Publish Articles ready for distribution.')
             -> setHelp('This command allows you to publish finished articles.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Publish Now');

        $finder = Finder::create()->directories()->name('*.textbundle')->in(MANUSCRIPTS);
        $output->writeln('The Finder found ' . $finder->count() . ' total textbundle files.');

        return 0;
    }
}