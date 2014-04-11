<?php

namespace Inouire\MininetBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShareLinkChecker
{
    
    protected $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
    
    public function checkShareLinkToken($token)
    {
        // try to find this link
        $sharelink_repo = $this->em->getRepository('InouireMininetBundle:ShareLink');
        $sharelink = $sharelink_repo->findOneBy(array('token' => $token));
        
        // check if it exists
        if($sharelink == null){
            throw new NotFoundHttpException('Le lien que vous demandez n\'existe pas');
        }
        
        // check expiration date
        if($sharelink->getExpirationDate() <= new \DateTime()){
            return null;
        }
        
        // everything is fine !
        return $sharelink;
    }
    
}
