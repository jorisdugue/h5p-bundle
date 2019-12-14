<?php


namespace Studit\H5PBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserInterface;
/**
 * EventRepository
 */
class EventRepository extends EntityRepository
{
    public function findRecentlyUsedLibraries($userId)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.libraryName, MAX(e.createdAt) as max_created_at')
            ->where('e.type = \'content\' and e.subType = \'created\' and e.user = :user')
            ->groupBy('e.libraryName')
            ->orderBy('max_created_at', 'DESC')
            ->setParameter('user', $userId);
        return $qb->getQuery()->getArrayResult();
    }
}