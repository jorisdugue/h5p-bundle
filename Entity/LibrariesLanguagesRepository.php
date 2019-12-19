<?php


namespace Studit\H5PBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * LibrariesLanguagesRepository
 */
class LibrariesLanguagesRepository extends EntityRepository
{
    public function findForLibrary($machineName, $majorVersion, $minorVersion, $languageCode)
    {
        $qb = $this->createQueryBuilder('ll')
            ->select('ll.languageJson')
            ->join('ll.library', 'l', 'WITH', 'l.machineName = :machineName and l.majorVersion = :majorVersion and l.minorVersion = :minorVersion')
            ->where('ll.languageCode = :languageCode')
            ->setParameters(['majorVersion' => $majorVersion, 'machineName' => $machineName, 'minorVersion' => $minorVersion, 'languageCode' => $languageCode]);
        try {
            $result = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }
        return $result['languageJson'] ? $result['languageJson'] : null;
    }
    public function findForLibraryAllLanguages($machineName, $majorVersion, $minorVersion, $defaultlang = "en")
    {
        $qb = $this->createQueryBuilder('ll')
            ->select('ll.languageCode')
            ->join('ll.library', 'l', 'WITH', 'l.machineName = :machineName and l.majorVersion = :majorVersion and l.minorVersion = :minorVersion')
            ->setParameters(['majorVersion' => $majorVersion, 'machineName' => $machineName, 'minorVersion' => $minorVersion]);
        try {
            $results = $qb->getQuery()->getArrayResult();
        } catch (NoResultException $e) {
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