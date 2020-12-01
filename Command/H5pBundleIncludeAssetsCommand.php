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
            ->setDescription('Include the assets from the h5p vendor bundle in the public resources directory of this bundle.')
            ->addOption('copy', 'c', InputOption::VALUE_NONE, 'Copy files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->includeAssets($input->getOption('copy') ?? false);

        return 0;
    }

    private function includeAssets(bool $copy)
    {
        //get dir of vendor H5P

        $fromDir = $this->appKernel->getProjectDir()."/vendor/h5p/";
        //call service
        //$toDir = $this->appKernel->getProjectDir().'/vendor/studit/h5p-bundle/public/h5p/';
        $toDir = $this->appKernel->getProjectDir().'/public/bundles/studith5p/h5p/';

        //$toDir = new FileLocator($fromDir);
        // $test = $fileLocator->locate('@StuditH5PBundle');

        //$toDir = $fileLocator->locate();

        $coreSubDir = "h5p-core/";
        $coreDirs = ["fonts", "images", "js", "styles"];
        $this->createFiles($fromDir, $toDir, $coreSubDir, $coreDirs, $copy);

        $editorSubDir = "h5p-editor/";
        $editorDirs = ["ckeditor", "images", "language", "libs", "scripts", "styles"];
        $this->createFiles($fromDir, $toDir, $editorSubDir, $editorDirs, $copy);

    }

    private function createFiles($fromDir, $toDir, $subDir, $subDirs, $copy)
    {
        foreach ($subDirs as $dir) {
            $src = $fromDir . $subDir . $dir;
            $dist = $toDir . $subDir . $dir;

            $copy
                ? $this->recurseCopy($src, $dist)
                : symlink($src, $dist);
            }
    }

    private function recurseCopy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurseCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
