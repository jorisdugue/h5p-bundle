<?php


namespace Studit\H5PBundle\Core;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Studit\H5PBundle\Entity\Content;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;



class H5PIntegration
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var \H5PCore
     */
    private $core;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var H5POptions
     */
    private $options;
    /**
     * @var RequestStack
     */
    private $requestStack;
    /**
     * @var Packages
     */
    private $assetsPaths;
    /**
     * @var \H5PContentValidator
     */
    private $contentValidator;

    /**
     * H5PContent constructor.
     * @param \H5PCore $core
     * @param H5POptions $options
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManagerInterface $entityManager
     * @param RouterInterface $router
     * @param RequestStack $requestStack
     * @param Packages $packages
     * @param \H5PContentValidator $contentValidator
     */
    public function __construct(\H5PCore $core, H5POptions $options, TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager, RouterInterface $router, RequestStack $requestStack, Packages $packages, \H5PContentValidator $contentValidator)
    {
        $this->core = $core;
        $this->options = $options;
        $this->tokenStorage = $tokenStorage;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->requestStack = $requestStack;
        $this->assetsPaths = $packages;
        $this->contentValidator = $contentValidator;
    }
    /**
     * Prepares the generic H5PIntegration settings
     */
    public function getGenericH5PIntegrationSettings()
    {
        static $settings;
        if (!empty($settings)) {
            return $settings; // Only needs to be generated the first time
        }
        // Load current user
        $user = $this->tokenStorage->getToken()->getUser();
        // Load configuration settings
        $saveContentState = $this->options->getOption('save_content_state', false);
        $saveContentFrequency = $this->options->getOption('save_content_frequency', 30);
        $hubIsEnabled = $this->options->getOption('hub_is_enabled', true);
        // Create AJAX URLs
        $setFinishedUrl = $this->router->generate('studit_h5p_h5pinteraction_setfinished', ['token' => \H5PCore::createToken('result')]);
        $contentUserDataUrl = $this->router->generate('studit_h5p_h5pinteraction_contentuserdata', ['contentId' => ':contentId', 'dataType' => ':dataType', 'subContentId' => ':subContentId', 'token' => \H5PCore::createToken('contentuserdata')]);
        // Define the generic H5PIntegration settings
        $settings = array(
            'baseUrl' => "/",
            'url' => $this->options->getRelativeH5PPath(),
            'postUserStatistics' => is_object($user) ? $user->getId() : null,
            'ajax' => array(
                'setFinished' => $setFinishedUrl,
                'contentUserData' => $contentUserDataUrl,
            ),
            'saveFreq' => $saveContentState ? $saveContentFrequency : false,
            'l10n' => array(
                'H5P' => $this->core->getLocalization(),
            ),
            'hubIsEnabled' => $hubIsEnabled,
            'siteUrl' => $this->requestStack->getMasterRequest()->getUri()
        );
        if (is_object($user)) {
            $settings['user'] = [
                'name' => $user->getUsername(),
                'mail' => $user->getEmail(),
            ];
        }
        return $settings;
    }
    /**
     * Get a list with prepared asset links that is used when JS loads components.
     *
     * @param array [$keys] Optional keys, first for JS second for CSS.
     * @return array
     */
    public function getCoreAssets($keys = NULL)
    {
        if (empty($keys)) {
            $keys = ['scripts', 'styles'];
        }
        // Prepare arrays
        $assets = [
            $keys[0] => [],
            $keys[1] => [],
        ];
        // Add all core scripts
        foreach (\H5PCore::$scripts as $script) {
            $assets[$keys[0]][] = "{$this->options->getH5PAssetPath()}/h5p-core/{$script}";
        }
        // and styles
        foreach (\H5PCore::$styles as $style) {
            $assets[$keys[1]][] = "{$this->options->getH5PAssetPath()}/h5p-core/{$style}";
        }
        return $assets;
    }
    public function getH5PContentIntegrationSettings(Content $content)
    {
        $content_user_data = [
            0 => [
                'state' => '{}',
            ]
        ];
        if (is_object($this->tokenStorage->getToken()->getUser())) {
            $contentUserData = $this->entityManager->getRepository('StuditH5PBundle:ContentUserData')->findOneBy(['mainContent' => $content, 'user' => $this->tokenStorage->getToken()->getUser()->getId()]);
            if ($contentUserData) {
                $content_user_data[$contentUserData->getSubContentId()][$contentUserData->getDataId()] = $contentUserData->getData();
            }
        }
        $filteredParameters = $this->getFilteredParameters($content);
        $embedUrl = $this->router->generate('studit_h5p_h5p_interaction_embed', ['content' => $content->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        $resizerUrl = $this->getH5PAssetUrl() . '/h5p-core/js/h5p-resizer.js';
        $displayOptions = $this->core->getDisplayOptionsForView($content->getDisabledFeatures(), $content->getId());
        return array(
            'library' => (string)$content->getLibrary(),
            'jsonContent' => $filteredParameters,
            'fullScreen' => $content->getLibrary()->isFullscreen(),
            'exportUrl' => $this->getExportUrl($content),
            'embedCode' => '<iframe src="' . $embedUrl . '" width=":w" height=":h" frameborder="0" allowfullscreen="allowfullscreen"></iframe>',
            'resizeCode' => '<script src="' . $resizerUrl . '" charset="UTF-8"></script>',
            'url' => $embedUrl,
            'title' => 'Not Available',
            'contentUserData' => $content_user_data,
            'displayOptions' => $displayOptions,
        );
    }
    public function getFilteredParameters(Content $content)
    {
        $contentData = [
            'title' => 'Interactive Content',
            'id' => $content->getId(),
            'slug' => 'interactive-content',
            'library' => [
                'name' => $content->getLibrary()->getMachineName(),
                'majorVersion' => $content->getLibrary()->getMajorVersion(),
                'minorVersion' => $content->getLibrary()->getMinorVersion(),
            ],
            'params' => $content->getParameters(),
            'filtered' => $content->getFilteredParameters(),
            'embedType' => 'div',
        ];
        return $this->core->filterParameters($contentData);
    }
    protected function getExportUrl(Content $content)
    {
        if ($this->options->getOption('export', true)) {
            return $this->options->getRelativeH5PPath() . "/exports/interactive-content-" . $content->getId() . '.h5p';
        } else {
            return '';
        }
    }
    public function getEditorIntegrationSettings($contentId = null)
    {
        $editorSettings = [
            'filesPath' => $this->options->getRelativeH5PPath(),
            'fileIcon' => [
                'path' => $this->getH5PAssetUrl() . "/h5p-editor/images/binary-file.png",
                'width' => 50,
                'height' => 50,
            ],
            'ajaxPath' => $this->router->getContext()->getBaseUrl() . "/h5p/ajax/",
            'libraryPath' => $this->getH5PAssetUrl() . "/h5p-editor/",
            'copyrightSemantics' => $this->contentValidator->getCopyrightSemantics(),
            'metadataSemantics' => $this->contentValidator->getMetadataSemantics(),
            'assets' => $this->getEditorAssets(),
            'apiVersion' => \H5PCore::$coreApi,
        ];
        if ($contentId) {
            $editorSettings['contentId'] = $contentId;
        }
        $settings = $this->getGenericH5PIntegrationSettings();
        $settings['editor'] = $editorSettings;
        return $settings;
    }
    /**
     * Get assets needed to display editor. These are fetched from core.
     *
     * @return array Js and css for showing the editor
     */
    private function getEditorAssets()
    {
        $h5pAssetUrl = $this->getH5PAssetUrl();
        $corePath = "{$h5pAssetUrl}/h5p-core/";
        $editorPath = "{$h5pAssetUrl}/h5p-editor/";
        $css = array_merge(
            $this->getAssets(\H5PCore::$styles, $corePath),
            $this->getAssets(\H5PEditor::$styles, $editorPath)
        );
        $js = array_merge(
            $this->getAssets(\H5PCore::$scripts, $corePath),
            $this->getAssets(\H5PEditor::$scripts, $editorPath, ['scripts/h5peditor-editor.js'])
        );
        $js[] = $this->getTranslationFilePath();
        return ['css' => $css, 'js' => $js];
    }
    /**
     * Extracts assets from a collection of assets
     *
     * @param array $collection Collection of assets
     * @param string $prefix Prefix needed for constructing the file-path of the assets
     * @param null|array $exceptions Exceptions from the collection that should be skipped
     *
     * @return array Extracted assets from the source collection
     */
    private function getAssets($collection, $prefix, $exceptions = NULL)
    {
        $assets = [];
        $cacheBuster = $this->getCacheBuster();
        foreach ($collection as $item) {
            // Skip exceptions
            if ($exceptions && in_array($item, $exceptions)) {
                continue;
            }
            $assets[] = "{$prefix}{$item}{$cacheBuster}";
        }
        return $assets;
    }
    /**
     * Get cache buster
     *
     * @return string A cache buster that may be applied to resources
     */
    public function getCacheBuster()
    {
        $cache_buster = \H5PCore::$coreApi['majorVersion'] . '.' . \H5PCore::$coreApi['minorVersion'];
        return $cache_buster ? "?={$cache_buster}" : '';
    }
    /**
     * Translation file path for the editor. Defaults to English if chosen
     * language is not available.
     *
     * @return string Path to translation file for editor
     */
    public function getTranslationFilePath()
    {
        $language = $this->requestStack->getCurrentRequest()->getLocale();
        $h5pAssetUrl = $this->getH5PAssetUrl();
        $languageFolder = "{$h5pAssetUrl}/h5p-editor/language";
        $defaultLanguage = "{$languageFolder}/en.js";
        $chosenLanguage = "{$languageFolder}/{$language}.js";
        $cacheBuster = $this->getCacheBuster();
        return (file_exists($this->options->getAbsoluteWebPath() . $chosenLanguage) ? $chosenLanguage : $defaultLanguage) . $cacheBuster;
    }
    private function getH5PAssetUrl()
    {
        return $this->assetsPaths->getUrl($this->options->getH5PAssetPath());
    }
}