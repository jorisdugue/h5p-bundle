<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LibraryRepository::class)]
#[ORM\Table(name: 'h5p_library')]
class Library
{
    #[ORM\Id]
    #[ORM\Column(type:"integer")]
    #[ORM\GeneratedValue(strategy: "AUTO")]

    /**
     * @var int|null
     */
    private ?int $id;
    /**
     * @var string|null
     */
    #[ORM\Column(name: "machine_name", type: "string", length: 127)]
    private ?string $machineName;

    #[ORM\Column(name: "title", type: "string", length: 255)]

    /**
     * @var string|null
     */
    private ?string $title;

    #[ORM\Column(name: "major_version", type: "integer")]
    /**
     * @var int|null
     */
    private ?int $majorVersion;

    #[ORM\Column(name: "minor_version", type: "integer")]
    /**
     * @var int|null
     */
    private ?int $minorVersion;

    #[ORM\Column(name: "patch_version", type: "integer")]

    /**
     * @var int|null
     */
    private ?int $patchVersion;

    /**
     * @var bool
     */
    #[ORM\Column(name: "patch_version_in_folder_name", type: "boolean", options: [ "default" => 0])]
    private bool $patchVersionInFolderName = false;

    #[ORM\Column(name: "runnable", type: "boolean", options: [ "default" => 1])]

    /**
     * @var bool
     */
    private bool $runnable = true;

    #[ORM\Column(name: "fullscreen", type: "boolean", options: [ "default" => 0])]
    /**
     * @var boolean
     */
    private bool $fullscreen = false;

    #[ORM\Column(name: "embed_types", type: "string", length: 255)]
    /**
     * @var string|null
     */
    private ?string $embedTypes;

    /**
     * @var string|null
     */
    #[ORM\Column(name: "preloaded_js", type: "text", nullable: true)]
    private ?string $preloadedJs;
    /**
     * @var string|null
     */
    #[ORM\Column(name: "preloaded_css", type: "text", nullable: true)]
    private ?string $preloadedCss;
    /**
     * @var string|null
     */
    #[ORM\Column(name: "drop_library_css", type: "text", nullable: true)]
    private ?string $dropLibraryCss;
    /**
     * @var string|null
     */
    #[ORM\Column(name: "semantics", type: "text")]
    private ?string $semantics;

    #[ORM\Column(name: "restricted", type: "boolean", options: ['default' => 0])]
    /**
     * @var bool
     */
    private bool $restricted = false;

    /**
     * @var string|null
     */
    #[ORM\Column(name: "tutorial_url", type: "string", length: 1000, nullable: true)]
    private ?string $tutorialUrl;

    /**
     * @var boolean
     */
    #[ORM\Column(name: "has_icon", type: "boolean", options: ['default' => 0])]
    private bool $hasIcon = false;

    #[ORM\OneToMany(targetEntity: ContentLibraries::class, mappedBy: "library")]
    /**
     * @var ArrayCollection|Collection
     */
    private ArrayCollection|Collection $contentLibraries;
    /**
     * @var string|null
     */
    #[ORM\Column(name: "metadata_settings", type: "text", nullable: true)]
    private ?string $metadataSettings;

    /**
     * @var string|null
     */
    #[ORM\Column(name: "add_to", type: "text", nullable: true)]
    private ?string $addTo;

    public function __get($name)
    {
        if ($name === "name") {
            return $this->machineName;
        }
        $name = $this->getLocalName($name);
        return $this->$name;
    }
    public function __isset($name): bool
    {
        $name = $this->getLocalName($name);
        return isset($this->$name);
    }
    public function __set($name, $value): void
    {
        $name = $this->getLocalName($name);
        $this->$name = $value;
    }
    private function getLocalName($name): string
    {
        $name = \H5PCore::snakeToCamel([$name => 1]);
        return array_keys($name)[0];
    }
    /**
     * Library constructor.
     */
    public function __construct()
    {
        $this->contentLibraries = new ArrayCollection();
    }
    public function __toString(): string
    {
        return "{$this->machineName} {$this->majorVersion}.{$this->minorVersion}";
    }
    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    /**
     * @param int $id
     * @return self
     */
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMachineName(): ?string
    {
        return $this->machineName;
    }
    /**
     * @param string $machineName
     * @return self
     */
    public function setMachineName($machineName): self
    {
        $this->machineName = $machineName;
        return $this;
    }
    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }
    /**
     * @param string $title
     * @return self
     */
    public function setTitle($title): self
    {
        $this->title = $title;
        return $this;
    }
    /**
     * @return int|null
     */
    public function getMajorVersion(): ?int
    {
        return $this->majorVersion;
    }

    /**
     * @param int $majorVersion
     * @return self
     */
    public function setMajorVersion($majorVersion): self
    {
        $this->majorVersion = $majorVersion;
        return $this;
    }
    /**
     * @return int|null
     */
    public function getMinorVersion(): ?int
    {
        return $this->minorVersion;
    }
    /**
     * @param int|null $minorVersion
     * @return self
     */
    public function setMinorVersion(?int $minorVersion): self
    {
        $this->minorVersion = $minorVersion;
        return $this;
    }
    /**
     * @return int
     */
    public function getPatchVersion(): ?int
    {
        return $this->patchVersion;
    }
    /**
     * @param int $patchVersion
     * @return self
     */
    public function setPatchVersion(?int $patchVersion): self
    {
        $this->patchVersion = $patchVersion;
        return $this;
    }
    /**
     * @return bool
     */
    public function isRunnable(): bool
    {
        return $this->runnable;
    }
    /**
     * @param bool $runnable
     */
    public function setRunnable($runnable): self
    {
        $this->runnable = $runnable;
        return $this;
    }
    /**
     * @return bool
     */
    public function isFullscreen(): bool
    {
        return $this->fullscreen;
    }
    /**
     * @param bool $fullscreen
     */
    public function setFullscreen($fullscreen): self
    {
        $this->fullscreen = $fullscreen;
        return $this;
    }
    /**
     * @return string
     */
    public function getEmbedTypes(): ?string
    {
        return $this->embedTypes;
    }
    /**
     * @param string $embedTypes
     */
    public function setEmbedTypes($embedTypes): self
    {
        $this->embedTypes = $embedTypes;
        return $this;
    }
    /**
     * @return string
     */
    public function getPreloadedJs(): ?string
    {
        return $this->preloadedJs;
    }
    /**
     * @param string $preloadedJs
     */
    public function setPreloadedJs($preloadedJs): self
    {
        $this->preloadedJs = $preloadedJs;
        return $this;
    }
    /**
     * @return string
     */
    public function getPreloadedCss(): ?string
    {
        return $this->preloadedCss;
    }
    /**
     * @param string $preloadedCss
     */
    public function setPreloadedCss($preloadedCss): self
    {
        $this->preloadedCss = $preloadedCss;
        return $this;
    }
    /**
     * @return string
     */
    public function getDropLibraryCss(): ?string
    {
        return $this->dropLibraryCss;
    }
    /**
     * @param string $dropLibraryCss
     */
    public function setDropLibraryCss($dropLibraryCss): self
    {
        $this->dropLibraryCss = $dropLibraryCss;
        return $this;
    }
    /**
     * @return string|null
     */
    public function getSemantics(): ?string
    {
        return $this->semantics;
    }
    /**
     * @param string $semantics
     * @return self
     */
    public function setSemantics($semantics): self
    {
        $this->semantics = $semantics;
        return $this;
    }
    /**
     * @return bool
     */
    public function isRestricted(): bool
    {
        return $this->restricted;
    }
    /**
     * @param bool $restricted
     */
    public function setRestricted($restricted): self
    {
        $this->restricted = $restricted;
        return $this;
    }
    /**
     * @return string
     */
    public function getTutorialUrl(): ?string
    {
        return $this->tutorialUrl;
    }
    /**
     * @param string $tutorialUrl
     * @return self
     */
    public function setTutorialUrl($tutorialUrl): self
    {
        $this->tutorialUrl = $tutorialUrl;
        return $this;
    }
    /**
     * @return bool
     */
    public function isHasIcon(): bool
    {
        return $this->hasIcon;
    }
    /**
     * @param bool $hasIcon
     */
    public function setHasIcon(bool $hasIcon): self
    {
        $this->hasIcon = $hasIcon;
        return $this;
    }
    public function isFrame(): bool
    {
        return (strpos($this->embedTypes, 'iframe') !== false);
    }

    /**
     * @return string
     */
    public function getMetadataSettings(): ?string
    {
        return $this->metadataSettings;
    }

    /**
     * @param string $metadataSettings
     */
    public function setMetadataSettings(string $metadataSettings): ?self
    {
        $this->metadataSettings = $metadataSettings;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddTo(): ?string
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

    public function isPatchVersionInFolderName(): bool
    {
        return $this->patchVersionInFolderName;
    }

    public function setPatchVersionInFolderName(bool $patchVersionInFolderName): void
    {
        $this->patchVersionInFolderName = $patchVersionInFolderName;
    }
}
