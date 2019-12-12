<?php


namespace Studit\H5PBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class LibraryFileEvent extends Event
{
    private $files;
    private $libraryList;
    private $mode;
    /**
     * LibraryFileEvent constructor.
     * @param $files
     * @param $libraryList
     * @param $mode
     */
    public function __construct($files, $libraryList, $mode)
    {
        $this->files = $files;
        $this->libraryList = $libraryList;
        $this->mode = $mode;
    }
    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }
    /**
     * @return mixed
     */
    public function getLibraryList()
    {
        return $this->libraryList;
    }
    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }
}