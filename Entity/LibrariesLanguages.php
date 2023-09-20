<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="LibrariesLanguagesRepository")
 * @ORM\Table(name="h5p_libraries_languages")
 */
class LibrariesLanguages
{
    /**
     * @var Library
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\Studit\H5PBundle\Entity\Library")
     * @ORM\JoinColumn(name="library_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $library;
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="language_code", type="string", length=31)
     */
    private $languageCode;
    /**
     * @var string
     *
     * @ORM\Column(name="language_json", type="text")
     */
    private $languageJson;
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
