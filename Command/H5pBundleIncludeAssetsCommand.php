<?php

namespace Studit\H5PBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class H5pBundleIncludeAssetsCommand extends Command
{
    protected static $defaultName = 'h5p-bundle:IncludeAssetsCommand';
    private KernelInterface $appKernel;

    public function __construct(KernelInterface $appKernel)
    {
        $this->appKernel = $appKernel;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Include the assets from the official h5p vendor bundle and our js script in the public resources directory.')
            ->addOption('copy', 'c', InputOption::VALUE_NONE, 'Copy files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $copy = $input->getOption('copy') ?? false;
        $this->includeH5PVendorAssets($copy);
        $this->includeSfScript($copy);

        return 0;
    }

    private function getTargetDir(): string
    {
        return $this->appKernel->getProjectDir().'/public/bundles/studith5p/h5p/';
    }

    private function includeH5PVendorAssets(bool $copy): void
    {
        $fromDir = $this->appKernel->getProjectDir()."/vendor/h5p/";

        $coreSubDir = "h5p-core/";
        $coreDirs = ["fonts", "images", "js", "styles"];
        $this->createFiles($fromDir, $this->getTargetDir(), $coreSubDir, $coreDirs, $copy);

        $editorSubDir = "h5p-editor/";
        $editorDirs = ["ckeditor", "images", "language", "libs", "scripts", "styles"];
        $this->createFiles($fromDir, $this->getTargetDir(), $editorSubDir, $editorDirs, $copy);
    }

    private function includeSfScript(bool $copy): void
    {
        $filename = "symfony-h5p.js";
        $from = $this->appKernel->getProjectDir()."/vendor/jorisdugue/h5p-bundle/public/h5p/".$filename;
        $to = $this->getTargetDir().$filename;

        $copy
            ? copy($from, $to)
            : symlink($from, $to);
    }

    private function createFiles(string $fromDir, string $toDir, string $subDir, array $subDirs, bool $copy): void
    {
        foreach ($subDirs as $dir) {
            $src = $fromDir . $subDir . $dir;
            $dist = $toDir . $subDir . $dir;
            mkdir($dist, 0777, true);

            $copy
                ? $this->recurseCopy($src, $dist)
                : symlink($src, $dist);
        }
    }

    private function recurseCopy(string $srcDirPath, string $dstDirPath): void
    {
        $dir = opendir($srcDirPath);

        while (false !== ($file = readdir($dir)))
        {
            if (($file != '.') && ($file != '..'))
            {
                $srcFilePath = $srcDirPath . '/' . $file;
                $dstFilePath = $dstDirPath . '/' . $file;

                if (is_dir($srcFilePath))
                {
                    mkdir($dstFilePath, 0777, true);
                    $this->recurseCopy($srcFilePath, $dstFilePath);
                }
                else
                {
                    copy($srcFilePath, $dstFilePath);
                }
            }
        }

        closedir($dir);
    }
}
