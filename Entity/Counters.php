<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: "h5p_counters")]
class Counters
{
    #[ORM\Id]
    #[ORM\Column(name: "type", type: "string", length: 63)]
    /**
     * @var string|null
     */
    private ?string $type;

    /**
     * @var string|null
     */
    #[ORM\Id]
    #[ORM\Column(name: "library_name", type: "string", length: 127)]
    private ?string $libraryName;

    /**
     * @var string|null
     */
    #[ORM\Id]
    #[ORM\Column(name: "library_version", type: "string", length: 31)]
    private ?string $libraryVersion;

    /**
     * @var int|null
     */
    #[ORM\Column(name: "num", type: "integer")]
    private ?int $num;
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    /**
     * @return string
     */
    public function getLibraryName()
    {
        return $this->libraryName;
    }
    /**
     * @param string $libraryName
     */
    public function setLibraryName($libraryName)
    {
        $this->libraryName = $libraryName;
    }
    /**
     * @return string
     */
    public function getLibraryVersion()
    {
        return $this->libraryVersion;
    }
    /**
     * @param string $libraryVersion
     */
    public function setLibraryVersion($libraryVersion)
    {
        $this->libraryVersion = $libraryVersion;
    }
    /**
     * @return int
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
      * @param int $num
      * @return self
     */
    public function setNum($num): self
    {
        $this->num = $num;
        return $this;
    }
}
