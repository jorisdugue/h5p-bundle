<?php

namespace Studit\H5PBundle\Core;

use DateTimeInterface;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonSerializable;
use Studit\H5PBundle\DependencyInjection\Configuration;
use Studit\H5PBundle\Editor\EditorStorage;
use Studit\H5PBundle\Entity\Content;
use Studit\H5PBundle\Entity\ContentLibraries;
use Studit\H5PBundle\Entity\ContentRepository;
use Studit\H5PBundle\Entity\Counters;
use Studit\H5PBundle\Entity\LibrariesHubCache;
use Studit\H5PBundle\Entity\LibrariesLanguages;
use Studit\H5PBundle\Entity\Library;
use Studit\H5PBundle\Entity\LibraryLibraries;
use Studit\H5PBundle\Entity\LibraryLibrariesRepository;
use Studit\H5PBundle\Entity\LibraryRepository;
use Studit\H5PBundle\Event\H5PEvents;
use Studit\H5PBundle\Event\LibrarySemanticsEvent;
use GuzzleHttp\Client;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;

class H5PSymfony implements \H5PFrameworkInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    /**
     * @var Session
     */
    private $session;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;
    /**
     * @var H5POptions
     */
    private $options;
    /**
     * @var EditorStorage
     */
    private $editorStorage;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var Router
     */
    private $router;

    /**
     * H5PSymfony constructor.
     * @param H5POptions $options
     * @param EditorStorage $editorStorage
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManagerInterface $manager
     * @param Session|null $session
     * @param RequestStack|null $requestStack
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EventDispatcherInterface $eventDispatcher
     * @param RouterInterface $router
     */
    public function __construct(
        H5POptions $options,
        EditorStorage $editorStorage,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $manager,
        ?Session $session,
        ?RequestStack $requestStack,
        AuthorizationCheckerInterface $authorizationChecker,
        EventDispatcherInterface $eventDispatcher,
        RouterInterface $router
    ) {
        $this->options = $options;
        $this->editorStorage = $editorStorage;
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
        $this->authorizationChecker = $authorizationChecker;
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
        try {
            $this->session = !$requestStack ? null : $requestStack->getSession();
        } catch (SessionNotFoundException $e) {
        }
        if (!$this->session) {
            $this->session = $session;
        }
    }

    /**
     * Grabs the relative URL to H5P files folder.
     *
     * @return string
     */
    public function getRelativeH5PPath(): string
    {
        return $this->options->getRelativeH5PPath();
    }

    /**
     * Implements getPlatformInfo
     * @return array
     */
    public function getPlatformInfo(): array
    {
        return [
            'name' => 'symfony',
            'version' => Kernel::VERSION,
            'h5pVersion' => Configuration::H5P_VERSION,
        ];
    }

    /**
     * Implements fetchExternalData
     * @param $url
     * @param null|mixed $data
     * @param bool $blocking
     * @param null $stream
     * @param bool $fullData
     * @param array $headers
     * @param array $files
     * @param string $method
     * @return array|bool|string
     * @throws GuzzleException
     */
    public function fetchExternalData(
        $url,
        $data = null,
        $blocking = true,
        $stream = null,
        $fullData = false,
        $headers = [],
        $files = [],
        $method = 'POST'
    ) {
        $options = [];
        if (!empty($data)) {
            $options['headers'] = ['Content-Type' => 'application/x-www-form-urlencoded'];
            $options['form_params'] = $data;
        }
        if (!empty($headers)) {
            if (isset($options['headers'])) {
                $options['headers'] = array_merge($options['headers'], $headers);
            } else {
                $options['headers'] = $headers;
            }
        }
        if ($stream) {
            @set_time_limit(0);
        }
        try {
            $client = new Client();
            $response = $client->request($method, $url, $options);
            $response_data = (string)$response->getBody();
            if (empty($response_data)) {
                return false;
            }
        } catch (\Exception $e) {
            $this->setErrorMessage($e->getMessage(), 'failed-fetching-external-data');
            return false;
        }
        if ($stream && empty($response->error)) {
            // Create file from data need disable move file or enable ? default set is a fail
            $this->editorStorage->saveFileTemporarily($response_data, false);
            // TODO: Cannot rely on H5PEditor module – Perhaps we could use the
            // save_to/sink option to save directly to file when streaming ?
            // http://guzzle.readthedocs.io/en/latest/request-options.html#sink-option
            return true;
        }
        if ($fullData) {
            // Compatibility with response of h5p.classe.php
            return [
                'status' => $response->getStatusCode(),
                'data' => $response_data
            ];
        }
        return $response_data;
    }

    /**
     * Implements setLibraryTutorialUrl
     *
     * Set the tutorial URL for a library. All versions of the library is set
     *
     * @param string $machineName
     * @param string $tutorialUrl
     */
    public function setLibraryTutorialUrl($machineName, $tutorialUrl)
    {
        /**
         * Implements setLibraryTutorialUrl
         *
         * Set the tutorial URL for a library. All versions of the library is set
         *
         * @param string $machineName
         * @param string $tutorialUrl
         */
    }

    /**
     * Keeps track of messages for the user.
     * @var array
     */
    private $messages = array('error' => array(), 'info' => array());

    /**
     * Implements setErrorMessage
     * @param $message
     * @param null $code
     * @return void
     */
    public function setErrorMessage($message, $code = null): void
    {
        if ($this->session) {
            $this->session->getFlashBag()->add("error", "[$code]: $message");
        }
    }

    /**
     * Implements setInfoMessage
     * @param $message
     * @return void
     */
    public function setInfoMessage($message): void
    {
        if ($this->session) {
            $this->session->getFlashBag()->add("info", "$message");
        }
    }

    /**
     * Implements getMessages
     * @param $type
     * @return array|null
     */
    public function getMessages($type): ?array
    {
        if (!$this->session || !$this->session->getFlashBag()->has($type)) {
            return null;
        }
        return $this->session->getFlashBag()->get($type);
    }

    /**
     * Implements t
     * @param string $message
     * @param array $replacements
     * @return string|string[]
     */
    public function t($message, $replacements = array())
    {
        foreach ($replacements as $search => $replace) {
            $message = str_replace($search, $replace, $message);
        }
        return $message;
    }

    /**
     * Implements getLibraryFileUrl
     * @param string $libraryFolderName
     * @param string $fileName
     * @return string
     */
    public function getLibraryFileUrl($libraryFolderName, $fileName): string
    {
        return $this->options->getLibraryFileUrl($libraryFolderName, $fileName);
    }

    /**
     * Implements getUploadedH5PFolderPath
     * @param null $set
     * @return null|mixed
     */
    public function getUploadedH5pFolderPath($set = null)
    {
        return $this->options->getUploadedH5pFolderPath($set);
    }


    /**
     * Implements getUploadedH5PPath
     * @param null $set
     * @return null|mixed
     */
    public function getUploadedH5pPath($set = null)
    {
        return $this->options->getUploadedH5pPath($set);
    }

    /**
     * @inheritDoc
     */
    public function loadAddons()
    {
        $q = $this->manager
            ->createQueryBuilder()
            ->select([
                'l1.id as libraryId',
                'l1.machineName as machineName',
                'l1.majorVersion as majorVersion',
                'l1.minorVersion as minorVersion',
                'l1.patchVersion as patchVersion',
                'l1.addTo as addTo',
                'l1.preloadedJs as preloadedJs',
                'l1.preloadedCss as preloadedCss',
            ])
            ->from('Studit\H5PBundle\Entity\Library', 'l1')
            ->leftJoin(
                'Studit\H5PBundle\Entity\Library',
                'l2',
                Expr\Join::WITH,
                new Expr\Andx([
                    'l1.machineName = l2.machineName',
                    new Expr\Orx([
                        'l1.majorVersion > l2.majorVersion',
                        new Expr\Andx([
                            'l1.majorVersion = l2.majorVersion',
                            'l1.minorVersion > l2.minorVersion'
                        ])
                    ])
                ])
            )
            ->where(new Expr\Andx([
                'l1.addTo IS NOT NULL',
                'l2.machineName IS NULL'
            ]))
            ->getQuery();
        return $q->execute();
    }

    /**
     * @inheritDoc
     */
    public function getLibraryConfig($libraries = null)
    {
        // Same as wordpress do but i don't know what is H5P_LIBRARY_CONFIG
        return defined('H5P_LIBRARY_CONFIG') ? H5P_LIBRARY_CONFIG : null;
    }

    /**
     * Implements loadLibraries
     * @return array
     */
    public function loadLibraries(): array
    {
        $res = $this->manager->getRepository('Studit\H5PBundle\Entity\Library')->findBy(
            [],
            ['title' => 'ASC', 'majorVersion' => 'ASC', 'minorVersion' => 'ASC']
        );
        $libraries = [];
        foreach ($res as $library) {
            $libraries[$library->getMachineName()][] = $library;
        }
        return $libraries;
    }

    /**
     * Implements getAdminUrl
     * @return string
     */
    public function getAdminUrl(): string
    {
        // Misplaced; not used by Core.
        // $url = Url::fromUri('internal:/admin/content/h5p')->toString();
        return '';
    }

    /**
     * Implements getLibraryId
     * @param $machineName
     * @param null $majorVersion
     * @param null $minorVersion
     * @return integer|null
     */
    public function getLibraryId($machineName, $majorVersion = null, $minorVersion = null)
    {
        $library = $this->manager->getRepository('Studit\H5PBundle\Entity\Library')->findOneBy(
            ['machineName' => $machineName, 'majorVersion' => $majorVersion, 'minorVersion' => $minorVersion]
        );
        return $library ? $library->getId() : null;
    }

    /**
     * @inheritDoc
     */
    public function getWhitelist($isLibrary, $defaultContentWhitelist, $defaultLibraryWhitelist)
    {
        // Misplaced; should be done by Core.
        $h5p_whitelist = $this->getOption('whitelist', $defaultContentWhitelist);
        $whitelist = $h5p_whitelist;
        if ($isLibrary) {
            $h5p_library_whitelist_extras = $this->getOption('library_whitelist_extras', $defaultLibraryWhitelist);
            $whitelist .= ' ' . $h5p_library_whitelist_extras;
        }
        return $whitelist;
    }


    /**
     * Implements isPatchedLibrary
     * @param $library
     * @return bool
     */
    public function isPatchedLibrary($library): bool
    {
        if ($this->getOption('dev_mode', false)) {
            return true;
        }
        /** @var LibraryRepository $repo */
        $repo = $this->manager->getRepository('Studit\H5PBundle\Entity\Library');
        return $repo->isPatched($library);
    }


    /**
     * Implements isInDevMode
     * @return bool
     */
    public function isInDevMode(): bool
    {
        $h5p_dev_mode = $this->getOption('dev_mode', false);
        return (bool)$h5p_dev_mode;
    }

    /**
     * Implements mayUpdateLibraries
     * @return bool
     */
    public function mayUpdateLibraries(): bool
    {
        return $this->hasPermission(\H5PPermission::UPDATE_LIBRARIES);
    }

    /**
     * Implements saveLibraryData
     *
     * @param array $libraryData
     * @param boolean $new
     */
    public function saveLibraryData(&$libraryData, $new = true)
    {
        $preloadedJs = $this->pathsToCsv($libraryData, 'preloadedJs');
        $preloadedCss = $this->pathsToCsv($libraryData, 'preloadedCss');
        $dropLibraryCss = '';
        if (isset($libraryData['dropLibraryCss'])) {
            $libs = [];
            foreach ($libraryData['dropLibraryCss'] as $lib) {
                $libs[] = $lib['machineName'];
            }
            $dropLibraryCss = implode(', ', $libs);
        }
        $embedTypes = '';
        if (isset($libraryData['embedTypes'])) {
            $embedTypes = implode(', ', $libraryData['embedTypes']);
        }
        if (!isset($libraryData['semantics'])) {
            $libraryData['semantics'] = '';
        }
        if (!isset($libraryData['fullscreen'])) {
            $libraryData['fullscreen'] = 0;
        }
        if (!isset($libraryData['hasIcon'])) {
            $libraryData['hasIcon'] = 0;
        }
        if ($new) {
            $library = new Library();
            $library->setTitle($libraryData['title']);
            $library->setMachineName($libraryData['machineName']);
            $library->setMajorVersion($libraryData['majorVersion']);
            $library->setMinorVersion($libraryData['minorVersion']);
            $library->setPatchVersion($libraryData['patchVersion']);
            $library->setRunnable($libraryData['runnable']);
            $library->setFullscreen($libraryData['fullscreen']);
            $library->setEmbedTypes($embedTypes);
            $library->setPreloadedJs($preloadedJs);
            $library->setPreloadedCss($preloadedCss);
            $library->setDropLibraryCss($dropLibraryCss);
            $library->setSemantics($libraryData['semantics']);
            $library->setHasIcon($libraryData['hasIcon']);
            // $library->setMetadataSettings($libraryData['metadataSettings']);
            // $library->setAddTo(isset($libraryData['addTo']) ? json_encode($libraryData['addTo']) : NULL);
            $this->manager->persist($library);
            $this->manager->flush();
            $libraryData['libraryId'] = $library->getId();
            if ($libraryData['runnable']) {
                $h5p_first_runnable_saved = $this->getOption('first_runnable_saved', false);
                if (!$h5p_first_runnable_saved) {
                    $this->setOption('first_runnable_saved', 1);
                }
            }
        } else {
            $library = $this->manager->getRepository('Studit\H5PBundle\Entity\Library')->find($libraryData['libraryId']);
            $library->setTitle($libraryData['title']);
            $library->setPatchVersion($libraryData['patchVersion']);
            $library->setFullscreen($libraryData['fullscreen']);
            $library->setEmbedTypes($embedTypes);
            $library->setPreloadedJs($preloadedJs);
            $library->setPreloadedCss($preloadedCss);
            $library->setDropLibraryCss($dropLibraryCss);
            $library->setSemantics($libraryData['semantics']);
            $library->setHasIcon($libraryData['hasIcon']);
            $library->setMetadataSettings($libraryData['metadataSettings']);
            $library->setAddTo(isset($libraryData['addTo']) ? json_encode($libraryData['addTo']) : null);
            $this->manager->persist($library);
            $this->manager->flush();
            $this->deleteLibraryDependencies($libraryData['libraryId']);
        }
        $languages = $this->manager->getRepository('Studit\H5PBundle\Entity\LibrariesLanguages')->findBy(['library' => $library]);
        foreach ($languages as $language) {
            $this->manager->remove($language);
        }
        $this->manager->flush();
        if (isset($libraryData['language'])) {
            foreach ($libraryData['language'] as $languageCode => $languageJson) {
                $language = new LibrariesLanguages();
                $language->setLibrary($library);
                $language->setLanguageCode($languageCode);
                $language->setLanguageJson($languageJson);
                $this->manager->persist($language);
            }
        }
        $this->manager->flush();
    }

    /**
     * Convert list of file paths to csv
     *
     * @param array $libraryData
     *  Library data as found in library.json files
     * @param string $key
     *  Key that should be found in $libraryData
     * @return string
     *  file paths separated by ', '
     */
    private function pathsToCsv($libraryData, $key)
    {
        if (isset($libraryData[$key])) {
            $paths = array();
            foreach ($libraryData[$key] as $file) {
                $paths[] = $file['path'];
            }
            return implode(', ', $paths);
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function insertContent($contentData, $contentMainId = null)
    {
        $content = new Content();
        return $this->storeContent($contentData, $content);
    }

    private function storeContent($contentData, Content $content)
    {
        $library = $this->manager->getRepository('Studit\H5PBundle\Entity\Library')
            ->find($contentData['library']['libraryId']);
        $content->setLibrary($library);
        $content->setParameters(str_replace('#tmp', '', $contentData['params']));
        $content->setDisabledFeatures($contentData['disable']);
        $content->setFilteredParameters(null);
        $this->manager->persist($content);
        $this->manager->flush();
        return $content->getId();
    }

    /**
     * @inheritDoc
     */
    public function updateContent($contentData, $contentMainId = null)
    {
        /** @var Content $content */
        $content = $this->manager->getRepository('Studit\H5PBundle\Entity\Content')->find($contentData['id']);
        return $this->storeContent($contentData, $content);
    }

    /**
     * @inheritDoc
     */
    public function resetContentUserData($contentId)
    {
        $contentUserDatas = $this->manager->getRepository('Studit\H5PBundle\Entity\ContentUserData')->findBy(
            ['mainContent' => $contentId, 'deleteOnContentChange' => true]
        );
        foreach ($contentUserDatas as $contentUserData) {
            $contentUserData->setData('RESET');
            $contentUserData->setTimestamp(time());
            $this->manager->persist($contentUserData);
        }
        $this->manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function saveLibraryDependencies($libraryId, $dependencies, $dependency_type)
    {
        foreach ($dependencies as $dependency) {
            /** @var Library $library */
            $library = $this->manager->getRepository('Studit\H5PBundle\Entity\Library')->find($libraryId);
            /** @var Library $requiredLibrary */
            $requiredLibrary = $this->manager->getRepository('Studit\H5PBundle\Entity\Library')->findOneBy([
                'machineName' => $dependency['machineName'],
                'majorVersion' => $dependency['majorVersion'],
                'minorVersion' => $dependency['minorVersion']
            ]);
            $libraryLibraries = new LibraryLibraries();
            $libraryLibraries->setLibrary($library);
            $libraryLibraries->setRequiredLibrary($requiredLibrary);
            $libraryLibraries->setDependencyType($dependency_type);
            $this->manager->persist($libraryLibraries);
        }
        $this->manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function copyLibraryUsage($contentId, $copyFromId, $contentMainId = null): void
    {
        $contentLibrariesFrom = $this->manager->getRepository('Studit\H5PBundle\Entity\ContentLibraries')->findBy(
            ['content' => $copyFromId]
        );
        $contentTo = $this->manager->getRepository('Studit\H5PBundle\Entity\Content')->find($contentId);
        foreach ($contentLibrariesFrom as $contentLibrary) {
            $contentLibraryTo = clone $contentLibrary;
            $contentLibraryTo->setContent($contentTo);
            $this->manager->persist($contentLibraryTo);
        }
        $this->manager->flush();
    }

    /**
     * @inheritDoc
     * @return void
     */
    public function deleteContentData($contentId): void
    {
        $content = $this->manager->getRepository('Studit\H5PBundle\Entity\Content')->find($contentId);
        if ($content) {
            $this->manager->remove($content);
            $this->manager->flush();
        }
    }

    /**
     * @inheritDoc
     * @return void
     */
    public function deleteLibraryUsage($contentId): void
    {
        $contentLibraries = $this->manager->getRepository('Studit\H5PBundle\Entity\ContentLibraries')->findBy(['content' => $contentId]);
        foreach ($contentLibraries as $contentLibrary) {
            $this->manager->remove($contentLibrary);
        }
        $this->manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function saveLibraryUsage($contentId, $librariesInUse): void
    {

        $content = $this->manager->getRepository('Studit\H5PBundle\Entity\Content')->find($contentId);
        $dropLibraryCssList = array();
        foreach ($librariesInUse as $dependency) {
            if (!empty($dependency['library']['dropLibraryCss'])) {
                $dropLibraryCssList = array_merge(
                    $dropLibraryCssList,
                    explode(', ', $dependency['library']['dropLibraryCss'])
                );
            }
        }
        foreach ($librariesInUse as $dependency) {
            $dropCss = in_array($dependency['library']['machineName'], $dropLibraryCssList);
            $contentLibrary = new ContentLibraries();
            $contentLibrary->setContent($content);
            $library = $this->manager->getRepository('Studit\H5PBundle\Entity\Library')->find(
                $dependency['library']['libraryId']
            );
            $contentLibrary->setLibrary($library);
            $contentLibrary->setWeight($dependency['weight']);
            $contentLibrary->setDropCss($dropCss);
            $contentLibrary->setDependencyType($dependency['type']);
            $this->manager->persist($contentLibrary);
        }
        try {
            // avoid content libraries inserted at the same time from two diff requests
            $this->manager->flush();
        } catch (UniqueConstraintViolationException $exception) {
        }
    }

    /**
     * Implements getLibraryUsage
     *
     * Get number of content using a library, and the number of
     * dependencies to other libraries
     *
     * @param int $libraryId
     * @param bool $skipContent
     * @return array The array contains two elements, keyed by 'content' and 'libraries'.
     *               Each element contains a number
     */
    public function getLibraryUsage($libraryId, $skipContent = false)
    {
        $usage = [];
        if ($skipContent) {
            $usage['content'] = -1;
        } else {
            /** @var LibraryRepository $libraryRepository */
            $libraryRepository = $this->manager->getRepository('Studit\H5PBundle\Entity\Library');
            $usage['content'] = $libraryRepository->countContentLibrary($libraryId);
        }
        /** @var LibraryLibrariesRepository $libraryLibrariesRepository */
        $libraryLibrariesRepository = $this->manager->getRepository('Studit\H5PBundle\Entity\LibraryLibraries');
        $usage['libraries'] = $libraryLibrariesRepository->countLibraries($libraryId);
        return $usage;
    }

    /**
     * @inheritDoc
     */
    public function loadLibrary($machineName, $majorVersion, $minorVersion)
    {
        /** @var LibraryRepository $libraryRepo */
        $libraryRepo = $this->manager->getRepository('Studit\H5PBundle\Entity\Library');
        $library = $libraryRepo->findOneArrayBy([
            'machineName' => $machineName,
            'majorVersion' => $majorVersion,
            'minorVersion' => $minorVersion
        ]);
        if (!$library) {
            return false;
        }
        $library['libraryId'] = $library['id'];
        $libraryLibraries = $this->manager->getRepository('Studit\H5PBundle\Entity\LibraryLibraries')->findBy([
            'library' => $library['id']
        ]);
        foreach ($libraryLibraries as $dependency) {
            $requiredLibrary = $dependency->getRequiredLibrary();
            $library["{$dependency->getDependencyType()}Dependencies"][] = [
                'machineName' => $requiredLibrary->getMachineName(),
                'majorVersion' => $requiredLibrary->getMajorVersion(),
                'minorVersion' => $requiredLibrary->getMinorVersion(),
            ];
        }
        return $library;
    }

    /**
     * @inheritDoc
     */
    public function loadLibrarySemantics($machineName, $majorVersion, $minorVersion)
    {
        $library = $this->manager->getRepository('Studit\H5PBundle\Entity\Library')->findOneBy([
            'machineName' => $machineName,
            'majorVersion' => $majorVersion,
            'minorVersion' => $minorVersion
        ]);
        if ($library) {
            return $library->getSemantics();
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function alterLibrarySemantics(&$semantics, $name, $majorVersion, $minorVersion)
    {
        $this->eventDispatcher->dispatch(new LibrarySemanticsEvent($semantics, $name, $majorVersion, $minorVersion), H5PEvents::SEMANTICS);
    }

    /**
     * Implements deleteLibraryDependencies
     * @param $libraryId
     */
    public function deleteLibraryDependencies($libraryId)
    {
        $libraries = $this->manager->getRepository('Studit\H5PBundle\Entity\LibraryLibraries')->findBy(['library' => $libraryId]);
        foreach ($libraries as $library) {
            $this->manager->remove($library);
        }
        $this->manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function lockDependencyStorage(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function unlockDependencyStorage(): void
    {
    }

    /**
     * Implements deleteLibrary. Will delete a library's data both in the database and file system
     * @param $library
     */
    public function deleteLibrary($library): void
    {
        $library = $this->manager->getRepository('Studit\H5PBundle\Entity\Library')->find($library);
        $this->manager->remove($library);
        $this->manager->flush();
        // Delete files
        \H5PCore::deleteFileTree(
            $this->getRelativeH5PPath() .
            "/libraries/{$library->getMachineName()}-{$library->getMajorVersion()}.{$library->getMinorVersion()}"
        );
    }

    /**
     * @inheritDoc
     */
    public function loadContent($id): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function loadContentDependencies($id, $type = null)
    {
        $query = ['content' => $id];
        if ($type !== null) {
            $query['dependencyType'] = $type;
        }
        $contentLibraries = $this->manager->getRepository('Studit\H5PBundle\Entity\ContentLibraries')->findBy(
            $query,
            ['weight' => 'ASC']
        );
        $dependencies = [];
        foreach ($contentLibraries as $contentLibrary) {
            /** @var Library $library */
            $library = $contentLibrary->getLibrary();
            $dependencies[] = [
                'libraryId' => $library->getId(),
                'machineName' => $library->getMachineName(),
                'majorVersion' => $library->getMajorVersion(),
                'minorVersion' => $library->getMinorVersion(),
                'patchVersion' => $library->getPatchVersion(),
                'preloadedCss' => $library->getPreloadedCss(),
                'preloadedJs' => $library->getPreloadedJs(),
                'dropCss' => $contentLibrary->isDropCss(),
                'dependencyType' => $contentLibrary->getDependencyType()
            ];
        }
        return $dependencies;
    }

    /**
     * @inheritDoc
     */
    public function getOption($name, $default = null)
    {
        try {
            // return default if db/table still not created
            return $this->options->getOption($name, $default);
        } catch (ConnectionException | TableNotFoundException $e) {
            return $default;
        }
    }

    /**
     * @inheritDoc
     */
    public function setOption($name, $value): void
    {
        $this->options->setOption($name, $value);
    }

    /**
     * Implements updateContent
     * @param $id
     * @param null $fields
     * @return void
     */
    public function updateContentFields($id, $fields = null): void
    {
        if (!isset($fields['filtered'])) {
            return;
        }
        $content = $this->manager->getRepository('Studit\H5PBundle\Entity\Content')->find($id);
        $content->setFilteredParameters($fields['filtered']);
        $this->manager->persist($content);
        $this->manager->flush();
    }

    /**
     * Will clear filtered params for all the content that uses the specified
     * library. This means that the content dependencies will have to be rebuilt,
     * and the parameters refiltered.
     *
     * @param int $library_id
     */
    public function clearFilteredParameters($library_id): void
    {
        $contents = $this->manager->getRepository('Studit\H5PBundle\Entity\Content')->findBy(
            ['library' => $library_id]
        );
        foreach ($contents as $content) {
            $content->setFilteredParameters('');
            $this->manager->persist($content);
        }
        $this->manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getNumNotFiltered()
    {
        /** @var ContentRepository $contentRepo */
        $contentRepo = $this->manager->getRepository('Studit\H5PBundle\Entity\Content');
        return $contentRepo->countNotFiltered();
    }

    /**
     * @inheritDoc
     */
    public function getNumContent($libraryId, $skip = null)
    {
        /** @var ContentRepository $contentRepo */
        $contentRepo = $this->manager->getRepository('Studit\H5PBundle\Entity\Content');
        return $contentRepo->countLibraryContent($libraryId);
    }

    /**
     * @inheritDoc
     */
    public function isContentSlugAvailable($slug): bool
    {
        throw new \Exception();
    }

    /**
     * Implements getLibraryStats
     * @param $type
     * @return array
     */
    public function getLibraryStats($type): array
    {
        $count = [];
        /**
         * @var Counters $results
         */
        $results = $this->manager->getRepository('Studit\H5PBundle\Entity\Counters')->findBy(['type' => $type]);
        // Extract results
        foreach ($results as $library) {
            $count[$library->getLibraryName() . " " . $library->getLibraryVersion()] = $library->getNum();
        }
        return $count;
    }

    /**
     * Implements getNumAuthors
     */
    public function getNumAuthors(): bool
    {
        /** @var ContentRepository $contentRepo */
        $contentRepo = $this->manager->getRepository('Studit\H5PBundle\Entity\Content');
        $contents = $contentRepo->countContent();
        // Return 1 if there is content and 0 if there is none
        return !$contents;
    }

    /**
     * @inheritDoc
     */
    public function saveCachedAssets($key, $libraries): void
    {
    }

    /**
     * @inheritDoc
     */
    public function deleteCachedAssets($library_id): array
    {
        return [];
    }

    /**
     * Implements getLibraryContentCount
     *
     * Get a key value list of library version and count of content created
     * using that library.
     *
     * @return array
     *  Array containing library, major and minor version - content count
     *  e.g. "H5P.CoursePresentation 1.6" => "14"
     */
    public function getLibraryContentCount(): array
    {
        $contentCount = [];
        /** @var ContentRepository $contentRepo */
        $contentRepo = $this->manager->getRepository('Studit\H5PBundle\Entity\Content');
        $results = $contentRepo->libraryContentCount();
        // Format results
        foreach ($results as $library) {
            $contentCount[$library['machineName']
            . " "
            . $library['majorVersion']
            . "."
            . $library['minorVersion']] = $library[1];
        }
        return $contentCount;
    }

    /**
     * @inheritDoc
     */
    public function afterExportCreated($content, $filename)
    {
    }


    /**
     * Implements hasPermission
     *
     * @param int $permission
     * @param int $content_id
     * @return bool
     */
    public function hasPermission($permission, $content_id = null)
    {
        if (!$this->options->getOption('use_permission')) {
            return true;
        }
        switch ($permission) {
            case \H5PPermission::DOWNLOAD_H5P:
                return $content_id !== null && $this->authorizationChecker->isGranted('ROLE_H5P_DOWNLOAD_ALL');
            case \H5PPermission::EMBED_H5P:
                return $content_id !== null && $this->authorizationChecker->isGranted('ROLE_H5P_EMBED_ALL');
            case \H5PPermission::CREATE_RESTRICTED:
                return $this->authorizationChecker->isGranted('ROLE_H5P_CREATE_RESTRICTED_CONTENT_TYPES');
            case \H5PPermission::UPDATE_LIBRARIES:
                return $this->authorizationChecker->isGranted('ROLE_H5P_UPDATE_LIBRARIES');
            case \H5PPermission::INSTALL_RECOMMENDED:
                return $this->authorizationChecker->isGranted('ROLE_H5P_INSTALL_RECOMMENDED_LIBRARIES');
            case \H5PPermission::COPY_H5P:
                return $content_id !== null && $this->authorizationChecker->isGranted('ROLE_H5P_COPY_ALL');
        }
        return false;
    }

    /**
     * Replaces existing content type cache with the one passed in
     *
     * @param object $contentTypeCache Json with an array called 'libraries'
     *  containing the new content type cache that should replace the old one.
     * @throws \Exception
     */
    public function replaceContentTypeCache($contentTypeCache): void
    {
        $this->truncateTable(LibrariesHubCache::class);
        foreach ($contentTypeCache->contentTypes as $ct) {
            $created_at = new \DateTime($ct->createdAt);
            $updated_at = new \DateTime($ct->updatedAt);
            $cache = new LibrariesHubCache();
            $cache->setMachineName($ct->id);
            $cache->setMajorVersion($ct->version->major);
            $cache->setMinorVersion($ct->version->minor);
            $cache->setPatchVersion($ct->version->patch);
            $cache->setH5pMajorVersion($ct->coreApiVersionNeeded->major);
            $cache->setH5pMinorVersion($ct->coreApiVersionNeeded->minor);
            $cache->setTitle($ct->title);
            $cache->setSummary($ct->summary);
            $cache->setDescription($ct->description);
            $cache->setIcon($ct->icon);
            $cache->setCreatedAt($created_at->getTimestamp());
            $cache->setUpdatedAt($updated_at->getTimestamp());
            $cache->setIsRecommended($ct->isRecommended);
            $cache->setPopularity($ct->popularity);
            $cache->setScreenshots(json_encode($ct->screenshots));
            $cache->setLicense(json_encode(isset($ct->license) ? $ct->license : []));
            $cache->setExample($ct->example);
            $cache->setTutorial(isset($ct->tutorial) ? $ct->tutorial : '');
            $cache->setKeywords(json_encode(isset($ct->keywords) ? $ct->keywords : []));
            $cache->setCategories(json_encode(isset($ct->categories) ? $ct->categories : []));
            $cache->setOwner($ct->owner);
            $this->manager->persist($cache);
        }
        $this->manager->flush();
    }

    /**
     * @param string $tableClassName
     * @throws Exception
     * @return void
     */
    private function truncateTable(string $tableClassName): void
    {
        $cmd = $this->manager->getClassMetadata($tableClassName);
        $connection = $this->manager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeStatement($q);
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @inheritDoc
     */
    public function libraryHasUpgrade($library): bool
    {
        return false;
    }

    /**
     * @param JsonSerializable $metadata
     * @param string $lang
     * @return mixed
     */
    public function replaceContentHubMetadataCache($metadata, $lang)
    {
        // TODO: Implement replaceContentHubMetadataCache() method.
    }

    /**
     * @param string $lang
     * @return string
     */
    public function getContentHubMetadataCache($lang = 'en'): string
    {
        return '';
    }

    public function getContentHubMetadataChecked($lang = 'en'): ?string
    {
        // Todo fetch the timestamp of current language here
//        dd(Languages::getName($lang));
        $date = new \DateTime('now');
        return $date->format(DateTimeInterface::RFC7231);
    }

    /**
     * Update the database with the latest time was been checked
     * @param int|null $time
     * @param string $lang
     * @return bool
     */
    public function setContentHubMetadataChecked($time, $lang = 'en'): bool
    {
        // For moment only return true in future implement this db
        return true;
    }
}
