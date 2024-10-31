<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
#[ORM\Table(name: 'h5p_content')]
class Content
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue(strategy:"AUTO")]
    /**
     * @var int
     */
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Library::class)]
    #[ORM\JoinColumn(name: "library_id", referencedColumnName: "id", onDelete: "CASCADE")]
    /**
     * @var Library
     */
    private ?Library $library;

    /**
     * @var string|null
     */
    #[ORM\Column(name: "parameters", type: "text", nullable: true)]
    private ?string $parameters;

    #[ORM\Column(name: "filtered_parameters", type: "text", nullable: true)]
    /**
     * @var string|null
     */
    private ?string $filteredParameters;

    #[ORM\Column(name: "disabled_features", type: "integer", nullable: true)]

    /**
     * @var int|null
     */
    private ?int $disabledFeatures;

    public function __clone(): void
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
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
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
    public function setLibrary(Library $library): self
    {
        $this->library = $library;
        return $this;
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
     * @return self
     */
    public function setParameters(string $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }
    /**
     * @return string|null
     */
    public function getFilteredParameters(): ?string
    {
        return $this->filteredParameters;
    }

    /**
     * @param string|null $filteredParameters
     */
    public function setFilteredParameters(?string $filteredParameters): self
    {
        $this->filteredParameters = $filteredParameters;
        return $this;
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
     * @return self
     */
    public function setDisabledFeatures(int $disabledFeatures): self
    {
        $this->disabledFeatures = $disabledFeatures;
        return $this;
    }
}
