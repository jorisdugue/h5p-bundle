<?php


namespace Studit\H5PBundle\Entity;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * LibraryLibrariesRepository
 */
class LibraryLibrariesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LibraryLibraries::class);
    }

    public function countLibraries($libraryId)
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l)')
            ->where('l.libraryId = :id')
            ->setParameter('id', $libraryId);
        return $qb->getQuery()->getSingleScalarResult();
    }
}