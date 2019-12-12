<?php


namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * LibraryLibrariesRepository
 */
class LibraryLibrariesRepository extends EntityRepository
{
    public function countLibraries($libraryId)
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l)')
            ->where('l.libraryId = :id')
            ->setParameter('id', $libraryId);
        return $qb->getQuery()->getSingleScalarResult();
    }
}