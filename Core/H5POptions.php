<?php

namespace Studit\H5PBundle\Core;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Studit\H5PBundle\Entity\Option;

class H5POptions
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var array
     */
    private $storedConfig = null;

    private $h5pPath;
    private $folderPath;
    private $projectRootDir;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * H5POptions constructor.
     * @param array $config
     * @param $projectRootDir
     * @param EntityManagerInterface $manager
     */
    public function __construct(?array $config, $projectRootDir, EntityManagerInterface $manager)
    {
        $this->config = $config;
        $this->projectRootDir = $projectRootDir;
        $this->manager = $manager;
    }

    public function getOption($name, $default = null)
    {
        $this->retrieveStoredConfig();

        if (isset($this->storedConfig[$name])) {
            return $this->storedConfig[$name];
        }
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
        return $default;
    }

    public function setOption($name, $value)
    {
        $this->retrieveStoredConfig();

        if (!isset($this->storedConfig[$name]) || $this->storedConfig[$name] !== $value) {
            $this->storedConfig[$name] = $value;
            $option = $this->manager->getRepository('StuditH5PBundle:Option')->find($name);
            if (!$option) {
                $option = new Option($name);
            }
            $option->setValue($value);
            $this->manager->persist($option);
            $this->manager->flush();
        }
    }

    private function retrieveStoredConfig()
    {
        if ($this->storedConfig === null) {
            $this->storedConfig = [];
            $options = $this->manager->getRepository('StuditH5PBundle:Option')->findAll();
            foreach ($options as $option) {
                $this->storedConfig[$option->getName()] = $option->getValue();
            }
        }
    }

    public function getUploadedH5pFolderPath($set = null)
    {
        if (!empty($set)) {
            $this->folderPath = $set;
        }

        return $this->folderPath;
    }

    public function getUploadedH5pPath($set = null)
    {
        if (!empty($set)) {
            $this->h5pPath = $set;
        }

        return $this->h5pPath;
    }

    public function getRelativeH5PPath()
    {
        //add in db web_dir and storage_dir or default this because SF5 destroy this config
        $absolutelink = $this->getOption('web_dir') != null ? $this->getOption('web_dir') : "bundles/studith5p/";
        $storagelink = $this->getOption('storage_dir') != null ? $this->getOption('storage_dir') : 'h5p';
        return "/" . $absolutelink .$storagelink ;
    }

    public function getAbsoluteH5PPath()
    {
        $absolutelink = $this->getOption('web_dir') != null ? $this->getOption('web_dir') : "bundles/studith5p/";
        $linkabsolute = $this->getOption('storage_dir') != null ? $this->getOption('storage_dir'): "h5p";
        return $this->getAbsoluteWebPath() . '/' . $absolutelink .$linkabsolute;
    }

    public function getAbsoluteWebPath()
    {
        $linkabsolutewebpath = $this->getOption('web_dir') != null ? $this->getOption('web_dir') : "bundles/studith5p/";
        return $this->projectRootDir . '/' . $linkabsolutewebpath;
    }

    public function getLibraryFileUrl($libraryFolderName, $fileName)
    {

        return $this->getRelativeH5PPath() . "/libraries/$libraryFolderName/$fileName";
    }

    public function getH5PAssetPath()
    {
        return '/bundles/studith5p/h5p';
    }

}