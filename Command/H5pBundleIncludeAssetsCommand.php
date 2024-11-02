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
    /** KernelInterface $appKernel */
    private KernelInterface $appKernel;

    /**
     * @param KernelInterface $appKernel
     */
    public function __construct(KernelInterface $appKernel)
    {
        $this->appKernel = $appKernel;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(
                'Include the assets from the h5p vendor bundle in the public resources directory of this bundle.'
            )
            ->addOption('copy', 'c', InputOption::VALUE_NONE, 'Copy files')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->includeAssets($input->getOption('copy') ?? false);
        return Command::SUCCESS;
    }

    private function includeAssets(bool $copy): void
    {
        $projectDir = $this->appKernel->getProjectDir();

        //get dir of vendor H5P
        $fromDir = $projectDir . "/vendor/h5p/";

        //call service
        $toDir = $projectDir . '/public/bundles/studith5p/h5p/';

        $coreSubDir = "h5p-core/";
        $coreDirs = ["fonts", "images", "js", "styles"];
        $this->createFiles($fromDir, $toDir, $coreSubDir, $coreDirs, $copy);

        $editorSubDir = "h5p-editor/";
        $editorDirs = ["ckeditor", "images", "language", "libs", "scripts", "styles"];
        $this->createFiles($fromDir, $toDir, $editorSubDir, $editorDirs, $copy);
    }

    private function createFiles(string $fromDir, string $toDir, string $subDir, array $subDirs, bool $copy): void
    {
        foreach ($subDirs as $dir) {
            $src = $fromDir . $subDir . $dir;
            $dist = $toDir . $subDir . $dir;

            $copy
                ? $this->recurseCopy($src, $dist)
                : symlink($src, $dist);
        }
    }

    private function recurseCopy(string $src, string $dst): void
    {
        $dir = opendir($src);
        // Restrict the permission to 0750 not upper
        @mkdir($dst, 0750);
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
