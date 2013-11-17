<?php

namespace Inouire\MininetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Inouire\MininetBundle\Entity\Tag;

class TagController extends Controller
{
    
    public function viewAction(){
        
        //get entity manager and Tag repository
        $em = $this->getDoctrine()->getManager();
        $tag_repo = $em->getRepository('InouireMininetBundle:Tag');   
        
        //get all tags
        $tags = $tag_repo->findAll();
        
        return $this->render('InouireMininetBundle:Main:tags.html.twig',array(
            'tags' => $tags,
        ));

    }
    
    
}
