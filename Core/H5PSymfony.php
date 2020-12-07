<?php


namespace Studit\H5PBundle\Core;


use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Studit\H5PBundle\DependencyInjection\Configuration;
use Studit\H5PBundle\Editor\EditorStorage;
use Studit\H5PBundle\Entity\Content;
use Studit\H5PBundle\Entity\ContentLibraries;
use Studit\H5PBundle\Entity\Counters;
use Studit\H5PBundle\Entity\LibrariesHubCache;
use Studit\H5PBundle\Entity\LibrariesLanguages;
use Studit\H5PBundle\Entity\Library;
use Studit\H5PBundle\Entity\LibraryLibraries;
use Studit\H5PBundle\Event\H5PEvents;
use Studit\H5PBundle\Event\LibrarySemanticsEvent;
use GuzzleHttp\Client;
use H5PPermission;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * @param Session $session
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EventDispatcherInterface $eventDispatcher
     * @param RouterInterface $router
     */
    public function __construct(H5POptions $options,
        EditorStorage $editorStorage,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $manager,
        Session $session,
        AuthorizationCheckerInterface $authorizationChecker,
        EventDispatcherInterface $eventDispatcher,
        RouterInterface $router)
    {
        $this->options = $options;
        $this->editorStorage = $editorStorage;
        $this->tokenStorage = $tokenStorage;
        $this->manager = $manager;
        $this->session = $session;
        $this->authorizationChecker = $authorizationChecker;
        $this->eventDispatcher = $eventDispatcher;
        $this->router = $router;
    }

    /**
     * Grabs the relative URL to H5P files folder.
     *
     * @return string
     */
    public function getRelativeH5PPath()
    {
        return $this->options->getRelativeH5PPath();
    }

    /**
     * Implements getPlatformInfo
     */
    public function getPlatformInfo()
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
     * @param null $data
     * @param bool $blocking
     * @param null $stream
     * @return bool|string
     */
    public function fetchExternalData($url, $data = NULL, $blocking = TRUE, $stream = NULL)
    {
        $options = [];
        if (!empty($data)) {
            $options['headers'] = [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $options['form_params'] = $data;
        }
        if ($stream) {
            @set_time_limit(0);
        }
        try {
            $client = new Client();
            $response = $client->request(empty($data) ? 'GET' : 'POST', $url, $options);
            $response_data = (string)$response->getBody();
            if (empty($response_data)) {
                return FALSE;
            }
        } catch (\Exception $e) {
            $this->setErrorMessage($e->getMessage(), 'failed-fetching-external-data');
            return FALSE;
        }
        if ($stream && empty($response->error)) {
            // Create file from data need disable move file or enable ? default set is a fail
            $this->editorStorage->saveFileTemporarily($response_data, false);
            // TODO: Cannot rely on H5PEditor module – Perhaps we could use the
            // save_to/sink option to save directly to file when streaming ?
            // http://guzzle.readthedocs.io/en/latest/request-options.html#sink-option
            return TRUE;
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
     */
    public function setErrorMessage($message, $code = NULL)
    {
        $this->session->getFlashBag()->add("error", "[$code]: $message");
    }

    /**
     * Implements setInfoMessage
     * @param $message
     */
    public function setInfoMessage($message)
    {
        $this->session->getFlashBag()->add("info", "$message");
    }

    /**
     * Implements getMessages
     * @param $type
     * @return array|null
     */
    public function getMessages($type)
    {
        if (!$this->session->getFlashBag()->has($type)) {
            return NULL;
        }
        $messages = $this->session->getFlashBag()->get($type);
        return $messages;
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
     * @param $libraryFolderName
     * @param $fileName
     * @return string
     */
    public function getLibraryFileUrl($libraryFolderName, $fileName)
    {
        return $this->options->getLibraryFileUrl($libraryFolderName, $fileName);
    }

    /**
     * Implements getUploadedH5PFolderPath
     * @param null $set
     * @return null
     */
    public function getUploadedH5pFolderPath($set = null)
    {
        return $this->options->getUploadedH5pFolderPath($set);
    }


    /**
     * Implements getUploadedH5PPath
     * @param null $set
     * @return null
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
            ->from('StuditH5PBundle:Library', 'l1')
            ->leftJoin(
                'StuditH5PBundle:Library',
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
    public function getLibraryConfig($libraries = NULL)
    {
        // Same as wordpress do but i don't know what is H5P_LIBRARY_CONFIG
        return defined('H5P_LIBRARY_CONFIG') ? H5P_LIBRARY_CONFIG : NULL;    }

    /**
     * Implements loadLibraries
     */
    public function loadLibraries()
    {
        $res = $this->manager->getRepository('StuditH5PBundle:Library')->findBy([], ['title' => 'ASC', 'majorVersion' => 'ASC', 'minorVersion' => 'ASC']);
        $libraries = [];
        foreach ($res as $library) {
            $libraries[$library->getMachineName()][] = $library;
        }
        return $libraries;
    }

    /**
     * Implements getAdminUrl
     */
    public function getAdminUrl()
    {
        // Misplaced; not used by Core.
        $url = Url::fromUri('internal:/admin/content/h5p')->toString();
        return $url;
    }

    /**
     * Implements getLibraryId
     * @param $machineName
     * @param null $majorVersion
     * @param null $minorVersion
     * @return integer|null
     */
    public function getLibraryId($machineName, $majorVersion = NULL, $minorVersion = NULL)
    {
        $library = $this->manager->getRepository('StuditH5PBundle:Library')->findOneBy(['machineName' => $machineName, 'majorVersion' => $majorVersion, 'minorVersion' => $minorVersion]);
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
    public function isPatchedLibrary($library)
    {
        if ($this->getOption('dev_mode', FALSE)) {
            return TRUE;
        }
        return $this->manager->getRepository('StuditH5PBundle:Library')->isPatched($library);
    }


    /**
     * Implements isInDevMode
     * @return boolean
     */
    public function isInDevMode()
    {
        $h5p_dev_mode = $this->getOption('dev_mode', FALSE);
        return (bool)$h5p_dev_mode;
    }

    /**
     * Implements mayUpdateLibraries
     */
    public function mayUpdateLibraries()
    {
        return $this->hasPermission(\H5PPermission::UPDATE_LIBRARIES);
    }

    /**
     * Implements saveLibraryData
     *
     * @param array $libraryData
     * @param boolean $new
     */
    public function saveLibraryData(&$libraryData, $new = TRUE)
    {
        $preloadedJs = $this->pathsToCsv($libraryData, 'preloadedJs');
        $preloadedCss = $this->pathsToCsv($libraryData, 'preloadedCss');
        $dropLibraryCss = '';
        if (isset($libraryData['dropLibraryCss'])) {
            $libs = array();
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
                $h5p_first_runnable_saved = $this->getOption('first_runnable_saved', FALSE);
                if (!$h5p_first_runnable_saved) {
                    $this->setOption('first_runnable_saved', 1);
                }
            }
        } else {
            $library = $this->manager->getRepository('StuditH5PBundle:Library')->find($libraryData['libraryId']);
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
            $library->setAddTo(isset($libraryData['addTo']) ? json_encode($libraryData['addTo']) : NULL);
            $this->manager->persist($library);
            $this->manager->flush();
            $this->deleteLibraryDependencies($libraryData['libraryId']);
        }
        $languages = $this->manager->getRepository('StuditH5PBundle:LibrariesLanguages')->findBy(['library' => $library]);
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
    public function insertContent($contentData, $contentMainId = NULL)
    {
        $content = new Content();
        return $this->storeContent($contentData, $content);
    }

    private function storeContent($contentData, Content $content)
    {
        $library = $this->manager->getRepository('StuditH5PBundle:Library')->find($contentData['library']['libraryId']);
        $content->setLibrary($library);
        $content->setParameters(str_replace('#tmp','', $contentData['params']));
        $content->setDisabledFeatures($contentData['disable']);
        $content->setFilteredParameters(null);
        $this->manager->persist($content);
        $this->manager->flush();
        return $content->getId();
    }

    /**
     * @inheritDoc
     */
    public function updateContent($contentData, $contentMainId = NULL)
    {
        /** @var $content Content*/
        $content = $this->manager->getRepository('StuditH5PBundle:Content')->find($contentData['id']);
        return $this->storeContent($contentData, $content);
    }

    /**
     * @inheritDoc
     */
    public function resetContentUserData($contentId)
    {
        $contentUserDatas = $this->manager->getRepository('StuditH5PBundle:ContentUserData')->findBy(['mainContent' => $contentId, 'deleteOnContentChange' => true]);
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
            /** @var Library $library*/
            $library = $this->manager->getRepository('StuditH5PBundle:Library')->find($libraryId);
            /** @var Library $requiredLibrary*/
            $requiredLibrary = $this->manager->getRepository('StuditH5PBundle:Library')->findOneBy(['machineName' => $dependency['machineName'], 'majorVersion' => $dependency['majorVersion'], 'minorVersion' => $dependency['minorVersion']]);
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
    public function copyLibraryUsage($contentId, $copyFromId, $contentMainId = NULL)
    {
        $contentLibrariesFrom = $this->manager->getRepository('StuditH5PBundle:ContentLibraries')->findBy(['content' => $copyFromId]);
        $contentTo = $this->manager->getRepository('StuditH5PBundle:Content')->find($contentId);
        foreach ($contentLibrariesFrom as $contentLibrary) {
            $contentLibraryTo = clone $contentLibrary;
            $contentLibraryTo->setContent($contentTo);
            $this->manager->persist($contentLibraryTo);
        }
        $this->manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function deleteContentData($contentId)
    {
        $content = $this->manager->getRepository('StuditH5PBundle:Content')->find($contentId);
        if ($content) {
            $this->manager->remove($content);
            $this->manager->flush();
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteLibraryUsage($contentId)
    {
        $contentLibraries = $this->manager->getRepository('StuditH5PBundle:ContentLibraries')->findBy(['content' => $contentId]);
        foreach ($contentLibraries as $contentLibrary) {
            $this->manager->remove($contentLibrary);
        }
        $this->manager->flush();

    }

    /**
     * @inheritDoc
     */
    public function saveLibraryUsage($contentId, $librariesInUse)
    {

        $content = $this->manager->getRepository('StuditH5PBundle:Content')->find($contentId);
        $dropLibraryCssList = array();
        foreach ($librariesInUse as $dependency) {
            if (!empty($dependency['library']['dropLibraryCss'])) {
                $dropLibraryCssList = array_merge($dropLibraryCssList, explode(', ', $dependency['library']['dropLibraryCss']));
            }
        }
        foreach ($librariesInUse as $dependency) {
            $dropCss = in_array($dependency['library']['machineName'], $dropLibraryCssList);
            $contentLibrary = new ContentLibraries();
            $contentLibrary->setContent($content);
            $library = $this->manager->getRepository('StuditH5PBundle:Library')->find($dependency['library']['libraryId']);
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
    public function getLibraryUsage($libraryId, $skipContent = FALSE)
    {
        $usage = [];
        if ($skipContent) {
            $usage['content'] = -1;
        } else {
            $usage['content'] = $this->manager->getRepository('StuditH5PBundle:Library')->countContentLibrary($libraryId);
        }
        $usage['libraries'] = $this->manager->getRepository('StuditH5PBundle:LibraryLibraries')->countLibraries($libraryId);
        return $usage;
    }

    /**
     * @inheritDoc
     */
    public function loadLibrary($machineName, $majorVersion, $minorVersion)
    {
        $library = $this->manager->getRepository('StuditH5PBundle:Library')->findOneArrayBy(['machineName' => $machineName, 'majorVersion' => $majorVersion, 'minorVersion' => $minorVersion]);
        if (!$library) {
            return false;
        }
        $library['libraryId'] = $library['id'];
        $libraryLibraries = $this->manager->getRepository('StuditH5PBundle:LibraryLibraries')->findBy(['library' => $library['id']]);
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
        $library = $this->manager->getRepository('StuditH5PBundle:Library')->findOneBy(['machineName' => $machineName, 'majorVersion' => $majorVersion, 'minorVersion' => $minorVersion]);
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
        $libraries = $this->manager->getRepository('StuditH5PBundle:LibraryLibraries')->findBy(['library' => $libraryId]);
        foreach ($libraries as $library) {
            $this->manager->remove($library);
        }
        $this->manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function lockDependencyStorage()
    {
    }

    /**
     * @inheritDoc
     */
    public function unlockDependencyStorage()
    {
    }

    /**
     * Implements deleteLibrary. Will delete a library's data both in the database and file system
     * @param $library
     */
    public function deleteLibrary($library)
    {
        $library = $this->manager->getRepository('StuditH5PBundle:Library')->find($library);
        $this->manager->remove($library);
        $this->manager->flush();
        // Delete files
        \H5PCore::deleteFileTree($this->getRelativeH5PPath() . "/libraries/{$library->getMachineName()}-{$library->getMajorVersion()}.{$library->getMinorVersion()}");
    }

    /**
     * @inheritDoc
     */
    public function loadContent($id)
    {
    }

    /**
     * @inheritDoc
     */
    public function loadContentDependencies($id, $type = NULL)
    {
        $query = ['content' => $id];
        if ($type !== NULL) {
            $query['dependencyType'] = $type;
        }
        $contentLibraries = $this->manager->getRepository('StuditH5PBundle:ContentLibraries')->findBy($query, ['weight' => 'ASC']);
        $dependencies = [];
        foreach ($contentLibraries as $contentLibrary) {
            /** @var Library $library */
            $library = $contentLibrary->getLibrary();
            $dependencies[] = ['libraryId' => $library->getId(), 'machineName' => $library->getMachineName(), 'majorVersion' => $library->getMajorVersion(), 'minorVersion' => $library->getMinorVersion(),
                'patchVersion' => $library->getPatchVersion(), 'preloadedCss' => $library->getPreloadedCss(), 'preloadedJs' => $library->getPreloadedJs(), 'dropCss' => $contentLibrary->isDropCss(), 'dependencyType' => $contentLibrary->getDependencyType()];
        }
        return $dependencies;
    }

    /**
     * @inheritDoc
     */
    public function getOption($name, $default = NULL)
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
    public function setOption($name, $value)
    {
        $this->options->setOption($name, $value);
    }

    /**
     * Implements updateContent
     * @param $id
     * @param null $fields
     * @return void
     */
    public function updateContentFields($id, $fields = NULL)
    {
        if (!isset($fields['filtered'])) {
            return;
        }
        $content = $this->manager->getRepository('StuditH5PBundle:Content')->find($id);
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
    public function clearFilteredParameters($library_id)
    {
        $contents = $this->manager->getRepository('StuditH5PBundle:Content')->findBy(['library' => $library_id]);
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
        return $this->manager->getRepository('StuditH5PBundle:Content')->countNotFiltered();
    }

    /**
     * @inheritDoc
     */
    public function getNumContent($libraryId, $skip = NULL)
    {
        return $this->manager->getRepository('StuditH5PBundle:Content')->countLibraryContent($libraryId);
    }

    /**
     * @inheritDoc
     */
    public function isContentSlugAvailable($slug)
    {
        throw new \Exception();
    }

    /**
     * Implements getLibraryStats
     * @param $type
     * @return array
     */
    public function getLibraryStats($type)
    {
        $count = [];
        /**
         * @var Counters $results
         */
        $results = $this->manager->getRepository('StuditH5PBundle:Counters')->findBy(['type' => $type]);
        // Extract results
        foreach ($results as $library) {
            $count[$library->getLibraryName() . " " . $library->getLibraryVersion()] = $library->getNum();
        }
        return $count;
    }

    /**
     * Implements getNumAuthors
     */
    public function getNumAuthors()
    {
        $contents = $this->manager->getRepository('StuditH5PBundle:Content')->countContent();
        // Return 1 if there is content and 0 if there is none
        return !$contents;
    }

    /**
     * @inheritDoc
     */
    public function saveCachedAssets($key, $libraries)
    {
    }

    /**
     * @inheritDoc
     */
    public function deleteCachedAssets($library_id)
    {
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
    public function getLibraryContentCount()
    {
        $contentCount = [];
        $results = $this->manager->getRepository('StuditH5PBundle:Content')->libraryContentCount();
        // Format results
        foreach ($results as $library) {
            $contentCount[$library['machineName'] . " " . $library['majorVersion'] . "." . $library['minorVersion']] = $library[1];
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
    public function hasPermission($permission, $content_id = NULL)
    {
        if (!$this->options->getOption('use_permission')) return true;
        switch ($permission) {
            case \H5PPermission::DOWNLOAD_H5P:
                return $content_id !== NULL && $this->authorizationChecker->isGranted('ROLE_H5P_DOWNLOAD_ALL');
            case \H5PPermission::EMBED_H5P:
                return $content_id !== NULL && $this->authorizationChecker->isGranted('ROLE_H5P_EMBED_ALL');
            case \H5PPermission::CREATE_RESTRICTED:
                return $this->authorizationChecker->isGranted('ROLE_H5P_CREATE_RESTRICTED_CONTENT_TYPES');
            case \H5PPermission::UPDATE_LIBRARIES:
                return $this->authorizationChecker->isGranted('ROLE_H5P_UPDATE_LIBRARIES');
            case \H5PPermission::INSTALL_RECOMMENDED:
                return $this->authorizationChecker->isGranted('ROLE_H5P_INSTALL_RECOMMENDED_LIBRARIES');
            case \H5PPermission::COPY_H5P:
                return $content_id !== NULL && $this->authorizationChecker->isGranted('ROLE_H5P_COPY_ALL');
        }
        return FALSE;
    }

    /**
     * Replaces existing content type cache with the one passed in
     *
     * @param object $contentTypeCache Json with an array called 'libraries'
     *  containing the new content type cache that should replace the old one.
     * @throws \Exception
     */
    public function replaceContentTypeCache($contentTypeCache)
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
     * @param $tableClassName
     * @throws DBALException
     */
    private function truncateTable($tableClassName)
    {
        $cmd = $this->manager->getClassMetadata($tableClassName);
        $connection = $this->manager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        $connection->executeUpdate($q);
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
    }
    /**
     * @inheritDoc
     */
    public function libraryHasUpgrade($library)
    {
        // TODO: Implement libraryHasUpgrade() method.
    }
}