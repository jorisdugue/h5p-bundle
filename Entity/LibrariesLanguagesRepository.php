<?php

namespace Studit\H5PBundle\Entity;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Studit\H5PBundle\Service\DoctrineParser;

/**
 * LibrariesLanguagesRepository
 */
class LibrariesLanguagesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly DoctrineParser $parser)
    {
        parent::__construct($registry, LibrariesLanguages::class);
    }

    /**
     * Finds the language JSON for a given library and language code.
     *
     * @param string $machineName The machine name of the library.
     * @param int $majorVersion The major version of the library.
     * @param int $minorVersion The minor version of the library.
     * @param string $languageCode The language code for the content.
     *
     * @return string|null The language JSON or null if not found.
     */
    public function findForLibrary(string $machineName, int $majorVersion, int $minorVersion, string $languageCode): ?string
    {
        $qb = $this->createQueryBuilder('ll')
            ->select('ll.languageJson')
            ->join('ll.library', 'l', 'WITH', 'l.machineName = :machineName and l.majorVersion = :majorVersion and l.minorVersion = :minorVersion')
            ->where('ll.languageCode = :languageCode')
            ->setParameters($this->parser->buildParams([
                'majorVersion' => $majorVersion,
                'machineName' => $machineName,
                'minorVersion' => $minorVersion,
                'languageCode' => $languageCode
            ]));
        try {
            $result = $qb->getQuery()->getSingleResult();
        } catch (NoResultException) {
            return null;
        }
        return $result['languageJson'] ?? null;
    }

    /**
     * Finds all language codes for a given library, including a default language.
     *
     * @param int $machineName The machine name of the library.
     * @param string $majorVersion The major version of the library.
     * @param string $minorVersion The minor version of the library.
     * @param string|null $defaultlang The default language code, defaulting to 'en'.
     *
     * @return array|null An array of language codes, or null if no languages are found.
     */
    public function findForLibraryAllLanguages(int $machineName, string $majorVersion, string $minorVersion, ?string $defaultlang = "en"): ?array
    {
        $qb = $this->createQueryBuilder('ll')
            ->select('ll.languageCode')
            ->join('ll.library', 'l', 'WITH', 'l.machineName = :machineName and l.majorVersion = :majorVersion and l.minorVersion = :minorVersion')
            ->setParameters($this->parser->buildParams([
                'majorVersion' => $majorVersion,
                'machineName' => $machineName,
                'minorVersion' => $minorVersion
            ]));
        try {
            $results = $qb->getQuery()->getArrayResult();
        } catch (NoResultException) {
            return null;
        }
        // Semantics is 'en' by default.
        $languageCodes = ['en'];
        foreach ($results as $result) {
            $languageCodes[] = $result['languageCode'];
        }
        return $languageCodes;
    }
}
