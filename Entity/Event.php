<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table('h5p_event')]
class Event
{
    #[ORM\Id]
    #[ORM\Column(type:"integer")]
    #[ORM\GeneratedValue(strategy: "AUTO")]

    /**
     * @var integer
     */
    private ?int $id;

    #[ORM\Column(name: 'user_id', type: "integer")]
    /**
     * @var integer
     */
    private ?int $user;

    #[ORM\Column(name: 'created_at', type: "integer")]
    /**
     * @var integer
     */
    private ?int $createdAt;

    #[ORM\Column(name: "type", type: "string", length: 63)]
    /**
     * @var string
     */
    private ?string $type;

    /**
     * @var string
     */
    #[ORM\Column(name: "sub_type", type: "string", length: 63)]
    private ?string $subType;

    #[ORM\ManyToOne(targetEntity: Content::class)]
    #[ORM\JoinColumn(name: "content_id", referencedColumnName: "id", onDelete: 'CASCADE')]
    /**
     * @var Content
     */
    private ?Content $content;

    #[ORM\Column(name: "content_title", type: "string", length: 255)]

    /**
     * @var string
     */
    private ?string $contentTitle;

    #[ORM\Column(name: "library_name", type: "string", length: 127)]

    /**
     * @var string
     */
    private ?string $libraryName;

    #[ORM\Column(name: "library_version", type: "string", length: 31)]

    /**
     * @var string
     */
    private ?string $libraryVersion;

    /**
     * @return integer|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    /**
     * @param int|null $id
     * @return self
     */
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getUser(): ?int
    {
        return $this->user;
    }
    /**
     * @param int|null $user Current user
     */
    public function setUser(?int $user): self
    {
        $this->user = $user;
        return $this;
    }
    /**
     * @return int|null
     */
    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    /**
     * @param int|null $createdAt
     */
    public function setCreatedAt(?int $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }
    /**
     * @param string $type
     * @return self
     */
    public function setType($type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubType(): ?string
    {
        return $this->subType;
    }
    /**
     * @param string|null $subType
     * @return self
     */
    public function setSubType(?string $subType): self
    {
        $this->subType = $subType;
        return $this;
    }

    /**
     * @return Content|null
     */
    public function getContent(): ?Content
    {
        return $this->content;
    }

    /**
     * @param Content $content
     * @return self
     */
    public function setContent(Content $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContentTitle(): ?string
    {
        return $this->contentTitle;
    }

    /**
     * @param string|null $contentTitle
     * @return self
     */
    public function setContentTitle(?string $contentTitle): self
    {
        $this->contentTitle = $contentTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getLibraryName(): ?string
    {
        return $this->libraryName;
    }
    /**
     * @param string $libraryName
     * @return self
     */
    public function setLibraryName(string $libraryName): self
    {
        $this->libraryName = $libraryName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLibraryVersion(): ?string
    {
        return $this->libraryVersion;
    }

    /**
     * @param string $libraryVersion
     * @return self
     */
    public function setLibraryVersion(string $libraryVersion): self
    {
        $this->libraryVersion = $libraryVersion;
        return $this;
    }
}
