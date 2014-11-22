<?php

namespace Inouire\MininetBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ThumbnailRefreshCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('mininet:thumbs:refresh')
            ->setDescription('Refresh thumbnail directory (create the missing needed, delete unused)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting refresh of thumnbails directory</info>');
        $this->getContainer()->get('inouire.thumbnailer')->createMissingThumbnails();
        $output->writeln(PHP_EOL.'<info>Done</info>');
    }
}
