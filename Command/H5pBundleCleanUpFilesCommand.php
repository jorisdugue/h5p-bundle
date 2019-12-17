<?php

namespace Studit\H5PBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class H5pBundleCleanUpFilesCommand extends Command
{
    protected static $defaultName = 'h5p-bundle:cleanup-files';
    protected function configure()
    {
        $this
            ->addArgument('location', InputArgument::OPTIONAL, 'The location of the files to clean up.')
            ->setDescription('Include the assets from the h5p vendor bundle in the public resources directory of this bundle.');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cleanupFiles($input);
    }

    private function cleanupFiles(InputInterface $input)
    {
        $location = $input->getArgument('location');
        if (!$location) {
            $location = $this->get('studit_h5p.options')->getAbsoluteH5PPath() . '/editor';
        }
        \H5PCore::deleteFileTree($location);
    }
}
