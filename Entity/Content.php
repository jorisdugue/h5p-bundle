<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ContentRepository")
 * @ORM\Table(name="h5p_content")
 */
class Content
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var Library
     *
     * @ORM\ManyToOne(targetEntity="\Studit\H5PBundle\Entity\Library")
     * @ORM\JoinColumn(name="library_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $library;
    /**
     * @var string
     *
     * @ORM\Column(name="parameters", type="text", nullable=true)
     */
    private $parameters;
    /**
     * @var string
     *
     * @ORM\Column(name="filtered_parameters", type="text", nullable=true)
     */
    private $filteredParameters;
    /**
     * @var int
     *
     * @ORM\Column(name="disabled_features", type="integer", nullable=true)
     */
    private $disabledFeatures;
    public function __clone()
    {
        $this->id = null;
    }
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }
    /**
     * @return Library
     */
    public function getLibrary(): Library
    {
        return $this->library;
    }
    /**
     * @param Library $library
     */
    public function setLibrary(Library $library)
    {
        $this->library = $library;
    }
    /**
     * @return string
     */
    public function getParameters(): string
    {
        return $this->parameters;
    }
    /**
     * @param string $parameters
     */
    public function setParameters(string $parameters)
    {
        $this->parameters = $parameters;
    }
    /**
     * @return string
     */
    public function getFilteredParameters(): string
    {
        return $this->filteredParameters;
    }
    /**
     * @param string $filteredParameters
     */
    public function setFilteredParameters(string $filteredParameters)
    {
        $this->filteredParameters = $filteredParameters;
    }
    /**
     * @return int
     */
    public function getDisabledFeatures(): int
    {
        return $this->disabledFeatures;
    }
    /**
     * @param int $disabledFeatures
     */
    public function setDisabledFeatures(int $disabledFeatures)
    {
        $this->disabledFeatures = $disabledFeatures;
    }
}
