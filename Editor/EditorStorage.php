<?php


namespace Studit\H5PBundle\Editor;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use H5peditorFile;
use Studit\H5PBundle\Core\H5POptions;
use Studit\H5PBundle\Core\H5PSymfony;
use Studit\H5PBundle\Entity\Library;
use Studit\H5PBundle\Event\H5PEvents;
use Studit\H5PBundle\Event\LibraryFileEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EditorStorage implements \H5peditorStorage
{

    /**
     * @var H5POptions
     */
    private $options;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * For some reason the creators of H5peditorStorage made some functions static
     * which causes problems with the Symfony service structure like circular references.
     * This instance is a workaround to call instance methods from static functions.
     *
     * @var EditorStorage
     */
    private static $instance;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * EditorStorage constructor.
     * @param H5POptions $options
     * @param Filesystem $filesystem
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param EntityManagerInterface $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(H5POptions $options, Filesystem $filesystem, AuthorizationCheckerInterface $authorizationChecker, EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->options = $options;
        $this->filesystem = $filesystem;
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        self::$instance = $this;
    }


    /**
     * Load language file(JSON) from database.
     * This is used to translate the editor fields(title, description etc.)
     *
     * @param string $machineName The machine readable name of the library(content type)
     * @param int $majorVersion Major part of version number
     * @param int $minorVersion Minor part of version number
     * @param string $language Language code
     * @return string Translation in JSON format
     */
    public function getLanguage($machineName, $majorVersion, $minorVersion, $language)
    {
        return $this->entityManager->getRepository('Studit\H5PBundle\Entity\LibrariesLanguages')->findForLibrary($machineName, $majorVersion, $minorVersion, $language);
    }

    /**
     * @inheritDoc
     */
    /**
     * Load language all file(JSON) from database.
     * This is used to translate the editor fields(title, description etc.)
     *
     * @param string $machineName The machine readable name of the library(content type)
     * @param int $majorVersion Major part of version number
     * @param int $minorVersion Minor part of version number
     * @param string $language Language code default is EN
     * @return string Translation in JSON format
     */
    public function getAvailableLanguages($machineName, $majorVersion, $minorVersion)
    {
        return $this->entityManager->getRepository('Studit\H5PBundle\Entity\LibrariesLanguages')->findForLibraryAllLanguages($machineName, $majorVersion, $minorVersion);
    }

    /**
     * "Callback" for mark the given file as a permanent file.
     * Used when saving content that has new uploaded files.
     *
     * @param string $path To new file
     */
    public function keepFile($path)
    {
        var_dump($path);
    }

    /**
     * Decides which content types the editor should have.
     *
     * Two usecases:
     * 1. No input, will list all the available content types.
     * 2. Libraries supported are specified, load additional data and verify
     * that the content types are available. Used by e.g. the Presentation Tool
     * Editor that already knows which content types are supported in its
     * slides.
     *
     * @param array $libraries List of library names + version to load info for
     * @return array List of all libraries loaded
     */
    public function getLibraries($libraries = NULL)
    {
        $canCreateRestricted = $this->authorizationChecker->isGranted('ROLE_H5P_CREATE_RESTRICTED_CONTENT_TYPES');
        if ($libraries !== NULL) {
            return $this->getLibrariesWithDetails($libraries, $canCreateRestricted);
        }
        $libraries = [];
        $librariesResult = $this->entityManager->getRepository('Studit\H5PBundle\Entity\Library')->findAllRunnableWithSemantics();
        foreach ($librariesResult as $library) {
            //Decode metadata setting
            $library->metadataSettings = json_decode($library->metadataSettings);
            // Make sure we only display the newest version of a library.
            foreach ($libraries as $existingLibrary) {
                if ($library->name === $existingLibrary->name) {
                    // Mark old ones
                    // This is the newest
                    if (($library->majorVersion === $existingLibrary->majorVersion && $library->minorVersion > $existingLibrary->minorVersion) ||
                        ($library->majorVersion > $existingLibrary->majorVersion)) {
                        $existingLibrary->isOld = true;
                    } else {
                        $library->isOld = true;
                    }
                }
            }
            $library->restricted = ($canCreateRestricted ? false : ($library->restricted === '1'));
            $libraries[] = $library;
        }
        return $libraries;
    }

    /**
     * Get librairies with full informations
     * @param $libraries
     * @param $canCreateRestricted
     * @return array
     */
    private function getLibrariesWithDetails($libraries, $canCreateRestricted)
    {
        $librariesWithDetails = [];
        foreach ($libraries as $library) {
            /** @var Library $details */
            $details = $this->entityManager->getRepository('Studit\H5PBundle\Entity\Library')->findHasSemantics($library->name, $library->majorVersion, $library->minorVersion);
            if ($details) {
                $library->tutorialUrl = $details->getTutorialUrl();
                $library->title = $details->getTitle();
                $library->runnable = $details->isRunnable();
                $library->restricted = $canCreateRestricted ? false : ($details->isRestricted() === '1');
                $library->metadataSettings = json_decode($details->getMetadataSettings());
                $librariesWithDetails[] = $library;
            }
        }
        return $librariesWithDetails;
    }

    /**
     * Allow for other plugins to decide which styles and scripts are attached.
     * This is useful for adding and/or modifing the functionality and look of
     * the content types.
     *
     * @param array $files
     *  List of files as objects with path and version as properties
     * @param array $libraries
     *  List of libraries indexed by machineName with objects as values. The objects
     *  have majorVersion and minorVersion as properties.
     */
    public function alterLibraryFiles(&$files, $libraries)
    {
        $mode = 'editor';
        $library_list = [];
        foreach ($libraries as $dependency) {
            $library_list[$dependency['machineName']] = [
                'majorVersion' => $dependency['majorVersion'],
                'minorVersion' => $dependency['minorVersion'],
            ];
        }
        $event = new LibraryFileEvent($files['scripts'], $library_list, $mode);
        $this->eventDispatcher->dispatch($event, H5PEvents::SCRIPTS);
        $files['scripts'] = $event->getFiles();
        $event = new LibraryFileEvent($files['styles'], $library_list, $mode);
        $this->eventDispatcher->dispatch($event, H5PEvents::STYLES);
        $files['styles'] = $event->getFiles();
    }

    /**
     * Saves a file temporarily with a given name
     *
     * @param string $data
     * @param bool $move_file Only move the uploaded file
     *
     * @return bool|false|string Real absolute path of the temporary folder
     */
    public static function saveFileTemporarily($data, $move_file)
    {
        return self::$instance->saveFileTemporarilyUnstatic($data, $move_file);
    }


    private function saveFileTemporarilyUnstatic($data, $move_file = false)
    {
        $h5p_path = $this->options->getAbsoluteH5PPath();
        $temp_id = uniqid('h5p-');
        $temporary_file_path = "{$h5p_path}/temp/{$temp_id}";
        $this->filesystem->mkdir($temporary_file_path);
        $name = $temp_id . '.h5p';
        $target = $temporary_file_path . DIRECTORY_SEPARATOR . $name;
        if ($move_file) {
            $file = move_uploaded_file($data, $target);
        } else {
            try {
                $this->filesystem->dumpFile($target, $data);
            } catch (IOException $e) {
                return false;
            }
        }
        $this->options->getUploadedH5pFolderPath($temporary_file_path);
        $this->options->getUploadedH5pPath("{$temporary_file_path}/{$name}");
        return (object)array(
            'dir' => $temporary_file_path,
            'fileName' => $name
        );
    }

    /**
     * Marks a file for later cleanup, useful when files are not instantly cleaned
     * up. E.g. for files that are uploaded through the editor.
     *
     * @param \H5peditorFile $file
     * @param int $content_id
     */
    public static function markFileForCleanup($file, $content_id = null)
    {
        //clean file after editor
    }

    /**
     * Clean up temporary files
     *
     * @param string $filePath Path to file or directory
     */
    public static function removeTemporarilySavedFiles($filePath)
    {
        if (is_dir($filePath)) {
            \H5PCore::deleteFileTree($filePath);
        } else {
            unlink($filePath);
        }
    }
}