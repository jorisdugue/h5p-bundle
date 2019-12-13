<?php


namespace Studit\H5PBundle\Event;


use Symfony\Contracts\EventDispatcher\Event;

class LibrarySemanticsEvent extends Event
{
    private $semantics;
    private $name;
    private $majorVersion;
    private $minorVersion;
    /**
     * LibrarySemanticsEvent constructor.
     * @param $semantics
     * @param $name
     * @param $majorVersion
     * @param $minorVersion
     */
    public function __construct($semantics, $name, $majorVersion, $minorVersion)
    {
        $this->semantics = $semantics;
        $this->name = $name;
        $this->majorVersion = $majorVersion;
        $this->minorVersion = $minorVersion;
    }
    /**
     * @return mixed
     */
    public function getSemantics()
    {
        return $this->semantics;
    }
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @return mixed
     */
    public function getMajorVersion()
    {
        return $this->majorVersion;
    }
    /**
     * @return mixed
     */
    public function getMinorVersion()
    {
        return $this->minorVersion;
    }

}