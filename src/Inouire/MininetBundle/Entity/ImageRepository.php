<?php

namespace Inouire\MininetBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ImageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ImageRepository extends EntityRepository{
    
    /*
     * Get all the images of the posts of a given year and month
     */
    public function getImagesOfMonth($year,$month){
        
        $month_interval = new \DateInterval('P01M');
        $time_from = new \Datetime($year.'-'.$month.'-01');
        $time_to = new \Datetime($year.'-'.$month.'-01');
        $time_to = $time_to->add($month_interval);
        
        $qb = $this->createQueryBuilder('image');
          
        $qb->leftJoin('image.post','post')
           ->addSelect('post')
           ->where('post.date BETWEEN :yearBeginning AND :yearEnd')
           ->andWhere('post.published = true')
           ->setParameter('yearBeginning',  $time_from)
           ->setParameter('yearEnd', $time_to)
           ->orderBy('post.date', 'ASC');
           
        return $qb->getQuery()
                  ->getResult();
    }
    
    public function getImagesWithTag($tag){
        $qb = $this->createQueryBuilder('image');
          
        $qb->join('image.tags','tag')
           ->where('tag.name = :tag')
           ->setParameter('tag', $tag)
           ->leftJoin('image.post','post')
           ->orderBy('post.date', 'DESC');
           
        return $qb->getQuery()
                  ->getResult();
    }
}
