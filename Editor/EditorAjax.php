<?php

namespace Studit\H5PBundle\Editor;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Studit\H5PBundle\Entity\EventRepository;
use Studit\H5PBundle\Entity\LibraryRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class EditorAjax implements \H5PEditorAjaxInterface
{
    /**
     * @var EntityManager
     */
    private $manager;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * EditorAjax constructor.
     * @param EntityManagerInterface $manager
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManagerInterface $manager, TokenStorageInterface $tokenStorage)
    {
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Gets latest library versions that exists locally
     *
     * @return array Latest version of all local libraries
     */
    public function getLatestLibraryVersions()
    {
        /** @var LibraryRepository $repo */
        $repo = $this->manager->getRepository('Studit\H5PBundle\Entity\Library');
        return $repo->findLatestLibraryVersions();
    }


    /**
     * Get locally stored Content Type Cache. If machine name is provided
     * it will only get the given content type from the cache
     *
     * @param $machineName
     *
     * @return array|object|null Returns results from querying the database
     */
    public function getContentTypeCache($machineName = NULL)
    {
        // Get only the specified content type from cache
        if ($machineName !== NULL) {
            $contentTypeCache = $this->manager->getRepository('Studit\H5PBundle\Entity\LibrariesHubCache')->findOneBy(['machineName' => $machineName]);
            return [$contentTypeCache];
        }
        // Get all cached content types
        return $this->manager->getRepository('Studit\H5PBundle\Entity\LibrariesHubCache')->findAll();
    }

    /**
     * Create a list of the recently used libraries
     *
     * @return array machine names. The first element in the array is the most
     * recently used.
     */
    public function getAuthorsRecentlyUsedLibraries()
    {
        $recentlyUsed = [];
        $user = $this->tokenStorage->getToken()->getUser();
        if (is_object($user)) {
            /** @var EventRepository $repo */
            $repo = $this->manager->getRepository('Studit\H5PBundle\Entity\Event');
            $events = $repo->findRecentlyUsedLibraries(method_exists($user, 'getId') ? $user->getId() : (method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : -1));
            foreach ($events as $event) {
                $recentlyUsed[] = $event['libraryName'];
            }
        }
        return $recentlyUsed;
    }

    /**
     * Checks if the provided token is valid for this endpoint
     *
     * @param string $token The token that will be validated for.
     *
     * @return bool True
     *
     **/
    public function validateEditorToken($token)
    {
        //return  \H5PCore::validToken('editorajax', $token);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getTranslations($libraries, $language_code)
    {
        return [];
    }
}