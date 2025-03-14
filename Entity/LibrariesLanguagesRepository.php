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

    public function findForLibrary($machineName, $majorVersion, $minorVersion, $languageCode)
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
        return $result['languageJson'] ? $result['languageJson'] : null;
    }
    public function findForLibraryAllLanguages($machineName, $majorVersion, $minorVersion, $defaultlang = "en")
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
        $codes = array('en'); // Semantics is 'en' by default.
        foreach ($results as $result) {
            $codes[] = $result['languageCode'];
        }
        return $codes;
        //return $result['languageJson'] ? $result['languageJson'] : null;
    }
}
