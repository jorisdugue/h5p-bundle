<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LibrariesLanguagesRepository::class)]
#[ORM\Table(name: "h5p_libraries_languages")]
class LibrariesLanguages
{
    /**
     * @var Library|null
     */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Library::class)]
    #[ORM\JoinColumn(name: "library_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private ?Library $library;
    /**
     * @var string|null
     */
    #[ORM\Id]
    #[ORM\Column(name: "language_code", type: "string", length: 31)]
    private ?string $languageCode;
    /**
     * @var string|null
     */
    #[ORM\Column(name: "language_json", type: "text")]
    private ?string $languageJson;
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
    /**
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }
    /**
     * @param string $languageCode
     */
    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;
    }
    /**
     * @return string
     */
    public function getLanguageJson()
    {
        return $this->languageJson;
    }
    /**
     * @param string $languageJson
     */
    public function setLanguageJson($languageJson)
    {
        $this->languageJson = $languageJson;
    }
}
