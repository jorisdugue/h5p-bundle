<?php

namespace Studit\H5PBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\KernelInterface;

class H5pBundleIncludeAssetsCommand extends Command
{
    protected static $defaultName = 'h5p-bundle:IncludeAssetsCommand';
    /** KernelInterface $appKernel */
    private $appKernel;
    public function __construct(KernelInterface $appKernel)
    {
        $this->appKernel = $appKernel;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Include the assets from the h5p vendor bundle in the public resources directory of this bundle.');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->includeAssets();
    }

    private function includeAssets()
    {

        //get dir of vendor H5P

        $fromDir = $this->appKernel->getProjectDir()."/vendor/h5p/";
        //call service
        //$toDir = $this->appKernel->getProjectDir().'/vendor/studit/h5p-bundle/public/h5p/';
        $toDir = $this->appKernel->getProjectDir().'/public/bundles/studith5p/h5p/';

        //$toDir = new FileLocator($fromDir);
        // $test = $fileLocator->locate('@StuditH5PBundle');
//        var_dump($toDir->locate('h5p-bundle/public/h5p/'));

        //$toDir = $fileLocator->locate();

        $coreSubDir = "h5p-core/";
        $coreDirs = ["fonts", "images", "js", "styles"];
        $this->createSymLinks($fromDir, $toDir, $coreSubDir, $coreDirs);

        $editorSubDir = "h5p-editor/";
        $editorDirs = ["ckeditor", "images", "language", "libs", "scripts", "styles"];
        $this->createSymLinks($fromDir, $toDir, $editorSubDir, $editorDirs);
    }

    private function createSymLinks($fromDir, $toDir, $subDir, $subDirs)
    {
        foreach ($subDirs as $dir) {
            symlink($fromDir . $subDir . $dir, $toDir . $subDir . $dir);
        }
    }
}