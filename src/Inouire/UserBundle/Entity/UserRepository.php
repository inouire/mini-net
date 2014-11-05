<?php

namespace Inouire\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    
    /**
     * Get the list of users who did not connect since a given date
     */
    public function getUsersNotConnectedSince($date)
    {
        $query = $this->createQueryBuilder('user')
                    ->where('user.lastLogin < :since_date')
                    ->setParameter('since_date', $date)
                    ->getQuery();
                    
        return $query->getResult();
    }
    
    
}
