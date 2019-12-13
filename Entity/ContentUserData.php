<?php


namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="h5p_content_user_data")
 */
class ContentUserData
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user;
    /**
     * @var Content
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\Studit\H5PBundle\Entity\Content")
     * @ORM\JoinColumn(name="content_main_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $mainContent;
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="sub_content_id", type="integer", length=10)
     */
    private $subContentId;
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="data_id", type="string", length=127)
     */
    private $dataId;
    /**
     * @var integer
     *
     * @ORM\Column(name="timestamp", type="integer", length=10)
     */
    private $timestamp;
    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text")
     */
    private $data;
    /**
     * @var boolean
     *
     * @ORM\Column(name="preloaded", type="boolean", nullable=true)
     */
    private $preloaded;
    /**
     * @var boolean
     *
     * @ORM\Column(name="delete_on_content_change", type="boolean", nullable=true)
     */
    private $deleteOnContentChange;
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
     * @return Content
     */
    public function getMainContent()
    {
        return $this->mainContent;
    }
    /**
     * @param Content $mainContent
     */
    public function setMainContent($mainContent)
    {
        $this->mainContent = $mainContent;
    }
    /**
     * @return int
     */
    public function getSubContentId()
    {
        return $this->subContentId;
    }
    /**
     * @param int $subContentId
     */
    public function setSubContentId($subContentId)
    {
        $this->subContentId = $subContentId;
    }
    /**
     * @return int
     */
    public function getDataId()
    {
        return $this->dataId;
    }
    /**
     * @param int $dataId
     */
    public function setDataId($dataId)
    {
        $this->dataId = $dataId;
    }
    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
    /**
     * @param int $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }
    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
    /**
     * @return bool
     */
    public function isPreloaded()
    {
        return $this->preloaded;
    }
    /**
     * @param bool $preloaded
     */
    public function setPreloaded($preloaded)
    {
        $this->preloaded = $preloaded;
    }
    /**
     * @return bool
     */
    public function isDeleteOnContentChange()
    {
        return $this->deleteOnContentChange;
    }
    /**
     * @param bool $deleteOnContentChange
     */
    public function setDeleteOnContentChange($deleteOnContentChange)
    {
        $this->deleteOnContentChange = $deleteOnContentChange;
    }

}