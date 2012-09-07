<?php

namespace Inouire\MininetBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PostRepository extends EntityRepository
{
    
    /*
     * Get all the post of a given month of a year
     */
    public function getMonthlyPosts($year,$month){
        
        $qb = $this->createQueryBuilder('post');
        
        $qb->where('post.date BETWEEN :monthBeginning AND :monthEnd')
           ->setParameter('monthBeginning',  new \Datetime($year.'-'.$month.'-01'))
           ->setParameter('monthEnd', new \Datetime($year.'-'.$month.'-31 23:59:59'))
           ->orderBy('post.date', 'ASC');
        
        return $qb->getQuery()
                  ->getResult();
    }
}
