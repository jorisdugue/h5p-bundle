<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LibraryLibrariesRepository::class)]
#[ORM\Table(name: "h5p_library_libraries")]
class LibraryLibraries
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Library::class)]
    #[ORM\JoinColumn(name: "library_id", referencedColumnName: "id", onDelete: 'CASCADE')]
    private ?int $library;
    /**
     * @var Library|null
     */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Library::class)]
    #[ORM\JoinColumn(name: "required_library_id", referencedColumnName: "id", onDelete: 'CASCADE')]
    private ?Library $requiredLibrary;
    /**
     * @var string|null
     */
    #[ORM\Column(name: "dependency_type", type: "string", length: 31)]
    private ?string $dependencyType;
    /**
     * @return string
     */
    public function getDependencyType()
    {
        return $this->dependencyType;
    }
    /**
     * @param string $dependencyType
     */
    public function setDependencyType($dependencyType)
    {
        $this->dependencyType = $dependencyType;
    }
    /**
     * @return Library
     */
    public function getRequiredLibrary()
    {
        return $this->requiredLibrary;
    }
    /**
     * @param Library $requiredLibrary
     */
    public function setRequiredLibrary($requiredLibrary)
    {
        $this->requiredLibrary = $requiredLibrary;
    }
    /**
     * @return Library
     */
    public function getLibrary()
    {
        return $this->library;
    }
    /**
     * @param Library $library
     */
    public function setLibrary($library)
    {
        $this->library = $library;
    }
}
