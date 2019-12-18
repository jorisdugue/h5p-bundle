<?php


namespace Studit\H5PBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="LibraryRepository")
 * @ORM\Table(name="h5p_library")
 */
class Library
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
     * @var string
     *
     * @ORM\Column(name="machine_name", type="string", length=127)
     */
    private $machineName;
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;
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
     * @var boolean
     *
     * @ORM\Column(name="runnable", type="boolean", options={"default": 1})
     */
    private $runnable = true;
    /**
     * @var boolean
     *
     * @ORM\Column(name="fullscreen", type="boolean", options={"default": 0})
     */
    private $fullscreen = false;
    /**
     * @var string
     *
     * @ORM\Column(name="embed_types", type="string", length=255)
     */
    private $embedTypes;
    /**
     * @var string
     *
     * @ORM\Column(name="preloaded_js", type="text", nullable=true)
     */
    private $preloadedJs;
    /**
     * @var string
     *
     * @ORM\Column(name="preloaded_css", type="text", nullable=true)
     */
    private $preloadedCss;
    /**
     * @var string
     *
     * @ORM\Column(name="drop_library_css", type="text", nullable=true)
     */
    private $dropLibraryCss;
    /**
     * @var string
     *
     * @ORM\Column(name="semantics", type="text")
     */
    private $semantics;
    /**
     * @var boolean
     *
     * @ORM\Column(name="restricted", type="boolean", options={"default": 0})
     */
    private $restricted = false;
    /**
     * @var string
     *
     * @ORM\Column(name="tutorial_url", type="string", length=1000, nullable=true)
     */
    private $tutorialUrl;
    /**
     * @var boolean
     *
     * @ORM\Column(name="has_icon", type="boolean", options={"default": 0})
     */
    private $hasIcon = false;
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Studit\H5PBundle\Entity\ContentLibraries", mappedBy="library")
     */
    private $contentLibraries;
    /**
     * @var string
     *
     * @ORM\Column(name="metadata_settings", type="text", nullable=true)
     */
    private $metadataSettings;

    /**
     * @var string
     *
     * @ORM\Column(name="add_to", type="text", nullable=true)
     */
    private $addTo;

    public function __get($name)
    {
        if ($name === "name") {
            return $this->machineName;
        }
        $name = $this->getLocalName($name);
        return $this->$name;
    }
    public function __isset($name)
    {
        $name = $this->getLocalName($name);
        return isset($this->$name);
    }
    public function __set($name, $value)
    {
        $name = $this->getLocalName($name);
        $this->$name = $value;
    }
    private function getLocalName($name)
    {
        $name = \H5PCore::snakeToCamel([$name => 1]);
        $name = array_keys($name)[0];
        return $name;
    }
    /**
     * Library constructor.
     */
    public function __construct()
    {
        $this->contentLibraries = new ArrayCollection();
    }
    public function __toString()
    {
        return "{$this->machineName} {$this->majorVersion}.{$this->minorVersion}";
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
     * @return bool
     */
    public function isRunnable()
    {
        return $this->runnable;
    }
    /**
     * @param bool $runnable
     */
    public function setRunnable($runnable)
    {
        $this->runnable = $runnable;
    }
    /**
     * @return bool
     */
    public function isFullscreen()
    {
        return $this->fullscreen;
    }
    /**
     * @param bool $fullscreen
     */
    public function setFullscreen($fullscreen)
    {
        $this->fullscreen = $fullscreen;
    }
    /**
     * @return string
     */
    public function getEmbedTypes()
    {
        return $this->embedTypes;
    }
    /**
     * @param string $embedTypes
     */
    public function setEmbedTypes($embedTypes)
    {
        $this->embedTypes = $embedTypes;
    }
    /**
     * @return string
     */
    public function getPreloadedJs()
    {
        return $this->preloadedJs;
    }
    /**
     * @param string $preloadedJs
     */
    public function setPreloadedJs($preloadedJs)
    {
        $this->preloadedJs = $preloadedJs;
    }
    /**
     * @return string
     */
    public function getPreloadedCss()
    {
        return $this->preloadedCss;
    }
    /**
     * @param string $preloadedCss
     */
    public function setPreloadedCss($preloadedCss)
    {
        $this->preloadedCss = $preloadedCss;
    }
    /**
     * @return string
     */
    public function getDropLibraryCss()
    {
        return $this->dropLibraryCss;
    }
    /**
     * @param string $dropLibraryCss
     */
    public function setDropLibraryCss($dropLibraryCss)
    {
        $this->dropLibraryCss = $dropLibraryCss;
    }
    /**
     * @return string
     */
    public function getSemantics()
    {
        return $this->semantics;
    }
    /**
     * @param string $semantics
     */
    public function setSemantics($semantics)
    {
        $this->semantics = $semantics;
    }
    /**
     * @return bool
     */
    public function isRestricted()
    {
        return $this->restricted;
    }
    /**
     * @param bool $restricted
     */
    public function setRestricted($restricted)
    {
        $this->restricted = $restricted;
    }
    /**
     * @return string
     */
    public function getTutorialUrl()
    {
        return $this->tutorialUrl;
    }
    /**
     * @param string $tutorialUrl
     */
    public function setTutorialUrl($tutorialUrl)
    {
        $this->tutorialUrl = $tutorialUrl;
    }
    /**
     * @return bool
     */
    public function isHasIcon()
    {
        return $this->hasIcon;
    }
    /**
     * @param bool $hasIcon
     */
    public function setHasIcon($hasIcon)
    {
        $this->hasIcon = $hasIcon;
    }
    public function isFrame()
    {
        return (strpos($this->embedTypes, 'iframe') !== false);
    }

    /**
     * @return string
     */
    public function getMetadataSettings()
    {
        return $this->metadataSettings;
    }

    /**
     * @param string $metadataSettings
     */
    public function setMetadataSettings($metadataSettings)
    {
        $this->metadataSettings = $metadataSettings;
    }

    /**
     * @return string
     */
    public function getAddTo()
    {
        return $this->addTo;
    }

    /**
     * @param string $addTo
     */
    public function setAddTo($addTo)
    {
        $this->addTo = $addTo;
    }


}