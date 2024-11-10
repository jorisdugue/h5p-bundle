<?php

namespace Studit\H5PBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class LibrarySemanticsEvent extends Event
{
    private array $semantics;
    private string $name;
    private int $majorVersion;
    private int $minorVersion;

    /**
     * LibrarySemanticsEvent constructor.
     * @param array $semantics array of semantics
     * @param string $name Nom of package
     * @param int $majorVersion number of major version
     * @param int $minorVersion number of minor version
     */
    public function __construct(array $semantics, string $name, int $majorVersion, int $minorVersion)
    {
        $this->semantics = $semantics;
        $this->name = $name;
        $this->majorVersion = $majorVersion;
        $this->minorVersion = $minorVersion;
    }

    /**
     * @return array
     */
    public function getSemantics(): array
    {
        return $this->semantics;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getMajorVersion(): int
    {
        return $this->majorVersion;
    }

    /**
     * @return int
     */
    public function getMinorVersion(): int
    {
        return $this->minorVersion;
    }
}
