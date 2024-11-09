<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: "h5p_content_libraries")]
class ContentLibraries
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Content::class)]
    #[ORM\JoinColumn(name: "content_id", referencedColumnName: "id", onDelete: "CASCADE")]
    /**
     * @var Content|null
     */
    private ?Content $content;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Library::class, inversedBy: "contentLibraries")]
    #[ORM\JoinColumn(name: "library_id", referencedColumnName: "id", onDelete: "CASCADE")]

    /**
     * @var Library
     */
    private ?Library $library;

    #[ORM\Id()]
    #[ORM\Column(name: "dependency_type", type: "string", length: 31)]

    /**
     * @var null|string
     */
    private null|string $dependencyType;

    #[ORM\Column(name: "drop_css", type: "boolean", length: 1)]
    /**
     * @var bool|null
     */
    private ?bool $dropCss;
    /**
     * @var int|null
     */
    #[ORM\Column(name: "weight", type: "integer")]
    private ?int $weight;

    /**
     * @return Content|null
     */
    public function getContent(): ?Content
    {
        return $this->content;
    }
    /**
     * @param Content|null $content
     * @return self
     */
    public function setContent(?Content $content): self
    {
        $this->content = $content;
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
     * @return self
     */
    public function setLibrary($library): self
    {
        $this->library = $library;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDependencyType(): ?string
    {
        return $this->dependencyType;
    }
    /**
     * @param null|string $dependencyType
     * @return self
     */
    public function setDependencyType(?string $dependencyType): self
    {
        $this->dependencyType = $dependencyType;
        return $this;
    }
    /**
     * @return bool
     */
    public function isDropCss(): ?bool
    {
        return $this->dropCss;
    }
    /**
     * @param bool $dropCss
     */
    public function setDropCss($dropCss): self
    {
        $this->dropCss = $dropCss;
        return $this;
    }
    /**
     * @return int
     */
    public function getWeight(): ?int
    {
        return $this->weight;
    }
    /**
     * @param int $weight
     */
    public function setWeight($weight): self
    {
        $this->weight = $weight;
        return $this;
    }
}
