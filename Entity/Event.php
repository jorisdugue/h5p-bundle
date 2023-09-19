<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="EventRepository")
 * @ORM\Table(name="h5p_event")
 */
class Event
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user;
    /**
     * @var integer
     *
     * @ORM\Column(name="created_at", type="integer")
     */
    private $createdAt;
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=63)
     */
    private $type;
    /**
     * @var string
     *
     * @ORM\Column(name="sub_type", type="string", length=63)
     */
    private $subType;
    /**
     * @var Content
     *
     * @ORM\ManyToOne(targetEntity="\Studit\H5PBundle\Entity\Content")
     * @ORM\JoinColumn(name="content_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $content;
    /**
     * @var string
     *
     * @ORM\Column(name="content_title", type="string", length=255)
     */
    private $contentTitle;
    /**
     * @var string
     *
     * @ORM\Column(name="library_name", type="string", length=127)
     */
    private $libraryName;
    /**
     * @var string
     *
     * @ORM\Column(name="library_version", type="string", length=31)
     */
    private $libraryVersion;
    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    /**
     * @return integer
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @param integer $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
    /**
     * @return integer
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    /**
     * @param integer $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
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
    public function getSubType()
    {
        return $this->subType;
    }
    /**
     * @param string $subType
     */
    public function setSubType($subType)
    {
        $this->subType = $subType;
    }
    /**
     * @return Content
     */
    public function getContent()
    {
        return $this->content;
    }
    /**
     * @param Content $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    /**
     * @return string
     */
    public function getContentTitle()
    {
        return $this->contentTitle;
    }
    /**
     * @param string $contentTitle
     */
    public function setContentTitle($contentTitle)
    {
        $this->contentTitle = $contentTitle;
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
}
