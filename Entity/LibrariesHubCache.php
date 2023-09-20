<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="h5p_libraries_hub_cache")
 */
class LibrariesHubCache
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column(name="machine_name", type="string", length=127)
     */
    private $machineName;
    /**
     * @var int
     *
     * @ORM\Column(name="major_version", type="integer")
     */
    private $majorVersion;
    /**
     * @var int
     *
     * @ORM\Column(name="minor_version", type="integer")
     */
    private $minorVersion;
    /**
     * @var int
     *
     * @ORM\Column(name="patch_version", type="integer")
     */
    private $patchVersion;
    /**
     * @var int
     *
     * @ORM\Column(name="h5p_major_version", type="integer", nullable=true)
     */
    private $h5pMajorVersion;
    /**
     * @var int
     *
     * @ORM\Column(name="h5p_minor_version", type="integer", nullable=true)
     */
    private $h5pMinorVersion;
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="text")
     */
    private $summary;
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;
    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="text")
     */
    private $icon;
    /**
     * @var integer
     *
     * @ORM\Column(name="created_at", type="integer")
     */
    private $createdAt;
    /**
     * @var integer
     *
     * @ORM\Column(name="updated_at", type="integer")
     */
    private $updatedAt;
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_recommended", type="boolean", options={"default": 1})
     */
    private $isRecommended = true;
    /**
     * @var integer
     *
     * @ORM\Column(name="popularity", type="integer")
     */
    private $popularity = false;
    /**
     * @var string
     *
     * @ORM\Column(name="screenshots", type="text", nullable=true)
     */
    private $screenshots;
    /**
     * @var string
     *
     * @ORM\Column(name="license", type="text", nullable=true)
     */
    private $license;
    /**
     * @var string
     *
     * @ORM\Column(name="example", type="text")
     */
    private $example;
    /**
     * @var string
     *
     * @ORM\Column(name="tutorial", type="text", nullable=true)
     */
    private $tutorial;
    /**
     * @var string
     *
     * @ORM\Column(name="keywords", type="text", nullable=true)
     */
    private $keywords;
    /**
     * @var string
     *
     * @ORM\Column(name="categories", type="text", nullable=true)
     */
    private $categories;
    /**
     * @var string
     *
     * @ORM\Column(name="owner", type="text", nullable=true)
     */
    private $owner;
    public function __get($name)
    {
        $name = \H5PCore::snakeToCamel([$name => 1]);
        $name = array_keys($name)[0];
        return $this->$name;
    }
    public function __isset($name)
    {
        $name = \H5PCore::snakeToCamel([$name => 1]);
        $name = array_keys($name)[0];
        return isset($this->$name);
    }
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    /**
     * @return string
     */
    public function getMachineName()
    {
        return $this->machineName;
    }
    /**
     * @param string $machineName
     */
    public function setMachineName($machineName)
    {
        $this->machineName = $machineName;
    }
    /**
     * @return int
     */
    public function getMajorVersion()
    {
        return $this->majorVersion;
    }
    /**
     * @param int $majorVersion
     */
    public function setMajorVersion($majorVersion)
    {
        $this->majorVersion = $majorVersion;
    }
    /**
     * @return int
     */
    public function getMinorVersion()
    {
        return $this->minorVersion;
    }
    /**
     * @param int $minorVersion
     */
    public function setMinorVersion($minorVersion)
    {
        $this->minorVersion = $minorVersion;
    }
    /**
     * @return int
     */
    public function getPatchVersion()
    {
        return $this->patchVersion;
    }
    /**
     * @param int $patchVersion
     */
    public function setPatchVersion($patchVersion)
    {
        $this->patchVersion = $patchVersion;
    }
    /**
     * @return int
     */
    public function getH5pMajorVersion()
    {
        return $this->h5pMajorVersion;
    }
    /**
     * @param int $h5pMajorVersion
     */
    public function setH5pMajorVersion($h5pMajorVersion)
    {
        $this->h5pMajorVersion = $h5pMajorVersion;
    }
    /**
     * @return int
     */
    public function getH5pMinorVersion()
    {
        return $this->h5pMinorVersion;
    }
    /**
     * @param int $h5pMinorVersion
     */
    public function setH5pMinorVersion($h5pMinorVersion)
    {
        $this->h5pMinorVersion = $h5pMinorVersion;
    }
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }
    /**
     * @param string $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }
    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }
    /**
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }
    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    /**
     * @param int $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
    /**
     * @return int
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    /**
     * @param int $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
    /**
     * @return bool
     */
    public function isRecommended()
    {
        return $this->isRecommended;
    }
    /**
     * @param bool $isRecommended
     */
    public function setIsRecommended($isRecommended)
    {
        $this->isRecommended = $isRecommended;
    }
    /**
     * @return int
     */
    public function getPopularity()
    {
        return $this->popularity;
    }
    /**
     * @param int $popularity
     */
    public function setPopularity($popularity)
    {
        $this->popularity = $popularity;
    }
    /**
     * @return string
     */
    public function getScreenshots()
    {
        return $this->screenshots;
    }
    /**
     * @param string $screenshots
     */
    public function setScreenshots($screenshots)
    {
        $this->screenshots = $screenshots;
    }
    /**
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }
    /**
     * @param string $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
    }
    /**
     * @return string
     */
    public function getExample()
    {
        return $this->example;
    }
    /**
     * @param string $example
     */
    public function setExample($example)
    {
        $this->example = $example;
    }
    /**
     * @return string
     */
    public function getTutorial()
    {
        return $this->tutorial;
    }
    /**
     * @param string $tutorial
     */
    public function setTutorial($tutorial)
    {
        $this->tutorial = $tutorial;
    }
    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }
    /**
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }
    /**
     * @return string
     */
    public function getCategories()
    {
        return $this->categories;
    }
    /**
     * @param string $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }
    /**
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }
    /**
     * @param string $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }
}
