<?php


namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="h5p_content_libraries")
 */
class ContentLibraries
{
    /**
     * @var Content
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\Studit\H5PBundle\Entity\Content")
     * @ORM\JoinColumn(name="content_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $content;
    /**
     * @var Library
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="\Studit\H5PBundle\Entity\Library", inversedBy="contentLibraries")
     * @ORM\JoinColumn(name="library_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $library;
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="dependency_type", type="string", length=31)
     */
    private $dependencyType;
    /**
     * @var bool
     *
     * @ORM\Column(name="drop_css", type="string", length=1)
     */
    private $dropCss;
    /**
     * @var int
     *
     * @ORM\Column(name="weight", type="integer")
     */
    private $weight;
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
     * @return int
     */
    public function getDependencyType()
    {
        return $this->dependencyType;
    }
    /**
     * @param int $dependencyType
     */
    public function setDependencyType($dependencyType)
    {
        $this->dependencyType = $dependencyType;
    }
    /**
     * @return bool
     */
    public function isDropCss()
    {
        return $this->dropCss;
    }
    /**
     * @param bool $dropCss
     */
    public function setDropCss($dropCss)
    {
        $this->dropCss = $dropCss;
    }
    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }
    /**
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }
}