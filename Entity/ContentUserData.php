<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

 #[ORM\Entity()]
 #[ORM\Table(name: "h5p_content_user_data")]
class ContentUserData
{
    #[ORM\Id]
    #[ORM\Column(name: "user_id", type: "integer")]
    /**
     * @var int|null
     */
    private ?int $user;
    /**
     * @var Content|null
     */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Content::class)]
    #[ORM\JoinColumn(name: "content_main_id", referencedColumnName:"id", onDelete: 'CASCADE')]
    private ?Content $mainContent;

    #[ORM\Id]
    #[ORM\Column(name: "sub_content_id", type: "integer", length: 10)]

    /**
     * @var int|null
     */
    private ?int $subContentId;

    #[ORM\Id]
    #[ORM\Column(name: "data_id", type: "string", length: 127)]
    /**
     * @var string|null
     */
    private ?string $dataId;

    /**
     * @var int|null
     */
    #[ORM\Column(name: "timestamp", type: "integer", length: 10)]
    private ?int $timestamp;

    #[ORM\Column(name: "data", type: "text")]
    /**
     * @var string|null
     */
    private ?string $data;

    /**
     * @var bool|null
     */
    #[ORM\Column(name: "preloaded", type: "boolean", nullable: true)]
    private ?bool $preloaded;

    #[ORM\Column(name: "delete_on_content_change", type: "boolean", nullable: true)]
    /**
     * @var bool|null
     */
    private ?bool $deleteOnContentChange;

    /**
     * @return int|null
     */
    public function getUser(): ?int
    {
        return $this->user;
    }

    /**
     * @param null|int $user
     * @return self
     */
    public function setUser(?int $user): self
    {
        $this->user = $user;
        return $this;
    }
    /**
     * @return Content|null
     */
    public function getMainContent(): ?Content
    {
        return $this->mainContent;
    }
    /**
     * @param Content $mainContent
     * @return self
     */
    public function setMainContent(?Content $mainContent): self
    {
        $this->mainContent = $mainContent;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSubContentId(): ?int
    {
        return $this->subContentId;
    }

    /**
     * @param int $subContentId
     */
    public function setSubContentId($subContentId): self
    {
        $this->subContentId = $subContentId;
        return $this;
    }

    /**
     * @return int|string|null
     */
    public function getDataId()
    {
        return $this->dataId;
    }

    /**
     * @param int $dataId
     */
    public function setDataId(null|int|string $dataId): self
    {
        $this->dataId = $dataId;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }
    /**
     * @param int $timestamp
     */
    public function setTimestamp($timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }
    /**
     * @return string
     */
    public function getData(): ?string
    {
        return $this->data;
    }
    /**
     * @param string $data
     */
    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }
    /**
     * @return bool|null
     */
    public function isPreloaded(): ?bool
    {
        return $this->preloaded;
    }
    /**
     * @param bool $preloaded
     */
    public function setPreloaded(bool $preloaded): self
    {
        $this->preloaded = $preloaded;
        return $this;
    }
    /**
     * @return bool
     */
    public function isDeleteOnContentChange(): ?bool
    {
        return $this->deleteOnContentChange;
    }
    /**
     * @param bool $deleteOnContentChange
     */
    public function setDeleteOnContentChange($deleteOnContentChange): self
    {
        $this->deleteOnContentChange = $deleteOnContentChange;
        return $this;
    }
}
