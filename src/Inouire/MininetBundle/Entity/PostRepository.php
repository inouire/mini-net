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
    public function getMonthlyPosts($year,$month)
    {
        $month_interval = new \DateInterval('P01M');
        $time_from = new \Datetime($year.'-'.$month.'-01');
        $time_to = new \Datetime($year.'-'.$month.'-01');
        $time_to = $time_to->add($month_interval);
        
        $query = $this->createQueryBuilder('post')
                    ->where('post.date BETWEEN :monthBeginning AND :monthEnd')
                    ->andWhere('post.published = true')
                    ->setParameter('monthBeginning',  $time_from)
                    ->setParameter('monthEnd', $time_to )
                    ->orderBy('post.date', 'DESC')
                    ->getQuery();
                    
        return $query->getResult();
    }
    
    public function getPostsSince($date)
    {
        $query = $this->createQueryBuilder('post')
                    ->where('post.date > :since_date')
                    ->andwhere('post.published = true')
                    ->setParameter('since_date', $date)
                    ->orderBy('post.date', 'DESC')
                    ->getQuery();
                    
        return $query->getResult();
    }
    
}
